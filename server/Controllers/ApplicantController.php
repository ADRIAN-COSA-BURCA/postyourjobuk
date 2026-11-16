<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Helpers;
use App\Core\SecurityLogger;
use App\Models\Applicant;
use App\Models\Job;
use App\Services\StorageService;
use App\Services\AzureBlobStorage;
use App\Services\AzureQueueService;
use App\Services\MailerService;

class ApplicantController extends BaseController {

    /**
     * GET /api/applicants?job_id={id}&sort={sort}&order={order}
     */
    public function index(): void {
        $tenantId = $this->requireTenantAuth();
        $jobId    = isset($_GET['job_id']) ? (int)$_GET['job_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

        $jobModel       = new Job();
        $applicantModel = new Applicant();

        if ($jobId <= 0 || !$jobModel->belongsToTenant($jobId, $tenantId)) {
            SecurityLogger::logAlert("UNAUTHORIZED_ACCESS", $tenantId, "Unauthorized attempt to view Job ID: $jobId");
            $this->json([
                'success'  => false,
                'error'    => "You do not have permission to view this job's applicants.",
                'redirect' => '/dashboard'
            ], 403);
        }

        $job          = $jobModel->find($jobId);
        $allowedSorts = ['ai_score', 'applied_at', 'name'];
        $sortBy       = in_array($_GET['sort'] ?? '', $allowedSorts, true) ? $_GET['sort'] : 'ai_score';
        $order        = strtoupper($_GET['order'] ?? '') === 'ASC' ? 'ASC' : 'DESC';

        $applicants = $applicantModel->getJobApplicants($tenantId, $jobId, $sortBy, $order);
        foreach ($applicants as &$app) {
            $app['fingerprint'] = !empty($app['ai_audit_fingerprint'])
                ? json_decode($app['ai_audit_fingerprint'], true)
                : null;
        }
        unset($app);

        $stats = $applicantModel->getJobStats($tenantId, $jobId);

        $this->json([
            'success'    => true,
            'job'        => $job,
            'applicants' => $applicants,
            'stats'      => $stats,
            'sort_by'    => $sortBy,
            'order'      => $order,
            'csrf_token' => Helpers::csrf_token()
        ]);
    }

    /**
     * GET /api/applicants/detail?id={id}
     * Returns single applicant profile + parsed AI summary sections + decoded audit fingerprint
     */
    public function view(): void {
        $tenantId    = $this->requireTenantAuth();
        $applicantId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        $applicantModel = new Applicant();
        $jobModel       = new Job();

        $applicant = $applicantModel->find($applicantId);

        if (!$applicant || (int)$applicant['tenant_id'] !== $tenantId) {
            SecurityLogger::logAlert(
                "BOLA_PROFILE_VIOLATION",
                $tenantId,
                "Unauthorized profile inspection blocked for Applicant ID: $applicantId"
            );
            $this->json([
                'success'  => false,
                'error'    => 'Access denied. The profile requested does not exist in your workspace.',
                'redirect' => '/dashboard'
            ], 403);
        }

        $job = $jobModel->find((int)$applicant['job_id']);

        // Parse <h3> sections from ai_summary for structured card rendering
        $summarySections = [];
        $rawSummary      = trim((string)($applicant['ai_summary'] ?? ''));
        if ($rawSummary !== '' && preg_match_all('/(<h3>.*?<\/h3>)(.*?)(?=(?:<h3>|$))/is', $rawSummary, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $summarySections[] = [
                    'heading' => trim(strip_tags($m[1])),
                    'html'    => trim($m[1] . $m[2])
                ];
            }
        }

        $applicant['fingerprint'] = !empty($applicant['ai_audit_fingerprint'])
            ? json_decode($applicant['ai_audit_fingerprint'], true)
            : null;

        $this->json([
            'success'          => true,
            'applicant'        => $applicant,
            'job'              => $job,
            'summary_sections' => $summarySections,
            'csrf_token'       => Helpers::csrf_token()
        ]);
    }

    /**
     * POST /api/apply
     * Public candidate application endpoint (Multipart FormData)
     */
    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'Method not allowed.'], 405);
        }

        $jobId    = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
        $jobModel = new Job();
        $job      = $jobModel->find($jobId);

        if (!$job || ($job['status'] ?? '') !== 'active') {
            $this->json([
                'success' => false,
                'error'   => 'This job is no longer accepting applications.'
            ], 404);
        }

        $tenantId = (int)$job['tenant_id'];

        // CSRF Verification (Header or POST field)
        if (!Helpers::csrf_verify($_POST['csrf_token'] ?? null)) {
            $this->json([
                'success' => false,
                'error'   => 'Your session has expired. Please refresh the page and try again.'
            ], 403);
        }

        // Anti-Spam 1: Honeypot Trap
        if (!empty($_POST['candidate_website_url'])) {
            SecurityLogger::logAlert(
                'SPAM_BOT_BLOCKED',
                $tenantId,
                "Automated honeypot bot trap triggered for Job ID: $jobId from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'),
                'WARNING'
            );
            $this->json([
                'success' => false,
                'error'   => 'Submission flagged as automated request.'
            ], 400);
        }

        // Anti-Spam 2: 2-Second Speed Verification
        $renderedAt = isset($_POST['form_rendered_at']) ? (int)$_POST['form_rendered_at'] : 0;
        if ($renderedAt > 0 && (time() - $renderedAt) < 2) {
            $this->json([
                'success' => false,
                'error'   => 'Application submitted unusually fast. Please wait a moment and try again.'
            ], 429);
        }

        // Anti-Spam 3: Session Rate Limiting (Max 5 per 10 minutes)
        $now = time();
        if (!isset($_SESSION['application_timestamps']) || !is_array($_SESSION['application_timestamps'])) {
            $_SESSION['application_timestamps'] = [];
        }
        $_SESSION['application_timestamps'] = array_filter(
            $_SESSION['application_timestamps'],
            fn($ts) => ($now - $ts) < 600
        );
        if (count($_SESSION['application_timestamps']) >= 5) {
            $this->json([
                'success' => false,
                'error'   => 'You have submitted multiple applications recently. Please wait a few minutes before submitting another application.'
            ], 429);
        }

        $applicantModel = new Applicant();
        $name  = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));

        if ($name === '' || mb_strlen($name) < 2 || mb_strlen($name) > 100) {
            $this->json([
                'success' => false,
                'error'   => 'Please enter a valid full name (between 2 and 100 characters).'
            ], 422);
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            $this->json([
                'success' => false,
                'error'   => 'Please enter a valid email address.'
            ], 422);
        }

        if ($phone !== '' && mb_strlen($phone) > 30) {
            $this->json([
                'success' => false,
                'error'   => 'Phone number is too long (maximum 30 characters).'
            ], 422);
        }

        // Duplicate Check: Return HTTP 409 Conflict with duplicate details for the Duplicate Modal
        $existing = $applicantModel->findByEmailAndJob($email, $jobId);
        if ($existing) {
            $this->json([
                'success'   => false,
                'duplicate' => true,
                'error'     => 'You have already submitted an application for this position.',
                'duplicate_details' => [
                    'email'      => $email,
                    'job_title'  => $job['title'],
                    'applied_at' => $existing['applied_at'] ?? date('Y-m-d H:i:s')
                ]
            ], 409);
        }

        // File Validation
        $cvFile = $_FILES['cv_document'] ?? null;
        if (!$cvFile || !isset($cvFile['error'])) {
            $this->json(['success' => false, 'error' => 'Please upload a CV document.'], 422);
        }

        if ($cvFile['error'] === UPLOAD_ERR_INI_SIZE || $cvFile['error'] === UPLOAD_ERR_FORM_SIZE) {
            $this->json(['success' => false, 'error' => 'The uploaded CV exceeds the maximum allowed file size (5 MB).'], 422);
        }

        if ($cvFile['error'] !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'error' => 'File upload error occurred. Please select your CV file and try again.'], 422);
        }

        $ext = strtolower(pathinfo((string)$cvFile['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf', 'docx'], true)) {
            $this->json(['success' => false, 'error' => 'Invalid CV format. Only PDF and DOCX files are allowed.'], 422);
        }

        $maxBytes = 5 * 1024 * 1024;
        if ((int)$cvFile['size'] > $maxBytes) {
            $this->json(['success' => false, 'error' => 'Your CV file is too large (maximum allowed size is 5 MB).'], 422);
        }

        if (function_exists('finfo_open') && !empty($cvFile['tmp_name']) && file_exists($cvFile['tmp_name'])) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $detectedMime = finfo_file($finfo, $cvFile['tmp_name']);
            finfo_close($finfo);

            $allowedMimes = [
                'application/pdf',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/octet-stream',
                'application/zip'
            ];
            if (!in_array($detectedMime, $allowedMimes, true)) {
                $this->json(['success' => false, 'error' => 'Invalid file format. Please upload a genuine PDF or DOCX file.'], 422);
            }
        }

        try {
            $storageService   = new StorageService();
            $storagePath      = $storageService->store($cvFile, $tenantId, $jobId);
            $rawBasename      = basename((string)$cvFile['name']);
            $cleanBase        = preg_replace('/[^A-Za-z0-9._-]/', '_', pathinfo($rawBasename, PATHINFO_FILENAME));
            $originalFilename = substr((string)$cleanBase, 0, 80) . '.' . $ext;
        } catch (\Throwable $e) {
            error_log("CV Storage Failed: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Failed to upload CV. Please try again.'], 500);
        }

        $data = [
            'tenant_id'       => $tenantId,
            'job_id'          => $jobId,
            'name'            => $name,
            'email'           => $email,
            'phone'           => $phone,
            'cv_storage_path' => $storagePath,
            'cv_filename'     => $originalFilename,
            'status'          => 'new'
        ];

        try {
            $newApplicantId = (int)$applicantModel->create($data);

            if ($newApplicantId > 0) {
                $queueService = new AzureQueueService();
                $queueService->dispatchJob($newApplicantId);

                try {
                    $jobWithTenant = $jobModel->getJobWithTenant($jobId) ?: $job;
                    $mailer = new MailerService();
                    $mailer->sendApplicationReceipt($email, $name, $jobWithTenant, $newApplicantId);
                } catch (\Throwable $mailErr) {
                    error_log("Applicant receipt email failed for ID {$newApplicantId}: " . $mailErr->getMessage());
                }
            }

            $_SESSION['application_timestamps'][] = time();
            $this->json([
                'success'      => true,
                'applicant_id' => $newApplicantId,
                'message'      => 'Your application has been submitted successfully! A confirmation email has been sent to ' . $email . '.'
            ], 201);
        } catch (\Throwable $e) {
            if ($e instanceof \PDOException && ($e->getCode() == 23000 || str_contains($e->getMessage(), '1062') || str_contains($e->getMessage(), 'unique_email_job'))) {
                $this->json([
                    'success'   => false,
                    'duplicate' => true,
                    'error'     => 'You have already submitted an application for this position.',
                    'duplicate_details' => [
                        'email'      => $email,
                        'job_title'  => $job['title'],
                        'applied_at' => date('Y-m-d H:i:s')
                    ]
                ], 409);
            }

            error_log("Application submission failed: " . $e->getMessage());
            $this->json([
                'success' => false,
                'error'   => 'An unexpected error occurred while processing your application. Please try again.'
            ], 500);
        }
    }

    /**
     * GET /api/applicants/download?id={id} (or /applicant/download?id={id})
     */
    public function download(): void {
        $tenantId    = $this->requireTenantAuth();
        $applicantId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        $applicantModel = new Applicant();
        $applicant      = $applicantModel->find($applicantId);

        if (!$applicant || (int)$applicant['tenant_id'] !== $tenantId) {
            SecurityLogger::logAlert('BOLA_DATA_EXFIL_ATTEMPT', $tenantId, 'Unauthorized CV download attempt for Applicant ID: ' . $applicantId);
            $this->json(['success' => false, 'error' => 'Access denied. You do not have permission to download this document.'], 403);
        }

        $storagePath      = $applicant['cv_storage_path'] ?? '';
        $downloadFilename = $applicant['cv_filename'] ?? 'resume.pdf';

        if ($storagePath === '') {
            $this->json(['success' => false, 'error' => 'No CV file reference was found for this candidate.'], 404);
        }

        if (str_starts_with($storagePath, 'blob://')) {
            try {
                $azureBlob = new AzureBlobStorage();
                $blob      = $azureBlob->downloadBlob($storagePath);

                if (empty($blob)) {
                    $this->json(['success' => false, 'error' => 'The requested CV document is currently unavailable from cloud storage.'], 404);
                }

                $this->streamFileHeaders($downloadFilename, $blob['content_type'], $blob['content_length']);
                echo $blob['content'];
                exit;
            } catch (\Throwable $e) {
                error_log("Blob download error: " . $e->getMessage());
                $this->json(['success' => false, 'error' => 'Unable to download CV from cloud storage at this time.'], 500);
            }
        } elseif (str_starts_with($storagePath, 'local://')) {
            $realPath = str_replace('local://', '', $storagePath);
            if (file_exists($realPath)) {
                $contentType = mime_content_type($realPath) ?: 'application/octet-stream';
                $this->streamFileHeaders($downloadFilename, $contentType, (int)filesize($realPath));
                readfile($realPath);
                exit;
            }
            $this->json(['success' => false, 'error' => 'The requested CV file could not be found on the server.'], 404);
        }

        $this->json(['success' => false, 'error' => 'Invalid file storage format.'], 400);
    }

    /**
     * POST /api/applicants/status
     */
    public function changeStatus(): void {
        $tenantId = $this->requireTenantAuth();
        $this->requireCsrf('CSRF_STATUS_VIOLATION');

        $body        = $this->getBody();
        $applicantId = (int)($body['applicant_id'] ?? 0);
        $jobId       = (int)($body['job_id'] ?? 0);
        $newStatus   = strtolower(trim((string)($body['status'] ?? '')));

        $applicantModel   = new Applicant();
        $updatedApplicant = $applicantModel->updateStatusSecure($tenantId, $applicantId, $newStatus);

        if ($updatedApplicant === null) {
            SecurityLogger::logAlert("UNAUTHORIZED_STATUS_UPDATE", $tenantId, "Blocked invalid or unauthorized status transition ('$newStatus') for Applicant ID: $applicantId");
            $this->json([
                'success' => false,
                'error'   => 'Unable to update candidate status: Record not found, unauthorized, or invalid stage.'
            ], 403);
        }

        $resolvedJobId  = (int)($updatedApplicant['job_id'] ?? $jobId);
        $jobModel       = new Job();
        $jobWithTenant  = $jobModel->getJobWithTenant($resolvedJobId) ?: [];
        $tenantProfile  = $_SESSION['tenant_profile'] ?? [];
        $candidateEmail = trim((string)($updatedApplicant['email'] ?? ''));
        $candidateName  = trim((string)($updatedApplicant['name'] ?? 'Candidate'));

        SecurityLogger::logAlert(
            "APPLICANT_STATUS_UPDATED",
            $tenantId,
            "Candidate ID {$applicantId} ({$candidateName}) moved to pipeline stage '{$newStatus}' for Job ID {$resolvedJobId}.",
            'INFO'
        );

        $message = "Candidate status updated to " . ucfirst($newStatus) . ".";
        try {
            $mailer = new MailerService();
            switch ($newStatus) {
                case 'interviewing':
                    $mailer->sendInterviewInvitation($candidateEmail, $candidateName, $jobWithTenant, $tenantProfile);
                    $message = "🟢 {$candidateName} moved to Interviewing stage — an official Interview Invitation email has been sent to {$candidateEmail}.";
                    break;
                case 'hired':
                    $mailer->sendJobOffer($candidateEmail, $candidateName, $jobWithTenant, $tenantProfile);
                    $message = "🎉 {$candidateName} marked as Hired — an official Job Offer email has been sent to {$candidateEmail}.";
                    break;
                case 'rejected':
                    $message = "🛑 {$candidateName} has been moved to Rejected stage (silent update — no email sent).";
                    break;
                case 'reviewed':
                default:
                    $message = "Candidate status for {$candidateName} updated to Reviewed.";
                    break;
            }
        } catch (\Throwable $mailErr) {
            error_log("ATS pipeline email failed for Applicant ID {$applicantId} ({$newStatus}): " . $mailErr->getMessage());
            $message = "Candidate status updated to '" . ucfirst($newStatus) . "', but the automated email notification could not be delivered right now.";
        }

        $stats = $applicantModel->getJobStats($tenantId, $resolvedJobId);

        $this->json([
            'success'   => true,
            'message'   => $message,
            'applicant' => $updatedApplicant,
            'stats'     => $stats
        ]);
    }

    /**
     * POST /api/applicants/delete
     */
    public function delete(): void {
        $tenantId = $this->requireTenantAuth();
        $this->requireCsrf('CSRF_DELETE_VIOLATION');

        $body        = $this->getBody();
        $applicantId = (int)($body['applicant_id'] ?? 0);
        $jobId       = (int)($body['job_id'] ?? 0);

        $applicantModel = new Applicant();
        $storagePath    = $applicantModel->deleteSecure($tenantId, $applicantId);

        if ($storagePath !== null) {
            if ($storagePath !== '') {
                try {
                    $storageService = new StorageService();
                    $storageService->delete($storagePath);
                } catch (\Throwable $e) {
                    error_log("Azure/Local CV Deletion error on applicant ID $applicantId: " . $e->getMessage());
                }
            }

            SecurityLogger::logAlert("APPLICANT_RECORD_DELETED", $tenantId, "Applicant deleted (ID: $applicantId).", 'INFO');
            $stats = $jobId > 0 ? $applicantModel->getJobStats($tenantId, $jobId) : [];

            $this->json([
                'success' => true,
                'message' => 'Candidate and associated CV have been permanently deleted.',
                'stats'   => $stats
            ]);
        }

        SecurityLogger::logAlert("UNAUTHORIZED_DELETE_ATTEMPT", $tenantId, "Attempted deletion on unauthorized applicant ID: $applicantId");
        $this->json([
            'success' => false,
            'error'   => 'Unable to delete candidate: Record not found or access denied.'
        ], 403);
    }

    /**
     * POST /api/applicants/delete-all
     */
    public function deleteAll(): void {
        $tenantId = $this->requireTenantAuth();
        $this->requireCsrf('CSRF_MASS_DELETE_VIOLATION');

        $body  = $this->getBody();
        $jobId = (int)($body['job_id'] ?? 0);

        $jobModel       = new Job();
        $applicantModel = new Applicant();

        if (!$jobModel->belongsToTenant($jobId, $tenantId)) {
            SecurityLogger::logAlert("UNAUTHORIZED_MASS_DELETE", $tenantId, "Attempted mass delete on unowned job ID: $jobId");
            $this->json([
                'success' => false,
                'error'   => 'Unauthorized action: You do not own this job posting.'
            ], 403);
        }

        $storagePaths = $applicantModel->deleteAllForJobSecure($tenantId, $jobId);
        if (!empty($storagePaths)) {
            $storageService = new StorageService();
            $deletedCount   = 0;
            foreach ($storagePaths as $path) {
                if (!empty($path)) {
                    try {
                        $storageService->delete($path);
                        $deletedCount++;
                    } catch (\Throwable $e) {
                        error_log("Mass CV Deletion error on path $path: " . $e->getMessage());
                    }
                }
            }
            SecurityLogger::logAlert("MASS_APPLICANT_DELETE", $tenantId, "Deleted $deletedCount candidates for Job ID: $jobId", 'WARNING');
        }

        $this->json([
            'success' => true,
            'message' => 'All candidates and their CVs have been permanently wiped.',
            'stats'   => $applicantModel->getJobStats($tenantId, $jobId)
        ]);
    }

    private function streamFileHeaders(string $filename, string $contentType, int $contentLength): void {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $safeFilename = preg_replace('/[^A-Za-z0-9._ -]/', '_', basename($filename));
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $safeFilename . '"');
        header('Content-Length: ' . max(0, $contentLength));
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: private, no-transform, no-cache, must-revalidate');
        header('Pragma: no-cache');
    }
}
