<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Helpers;
use App\Models\Applicant;
use App\Models\Job;
use App\Core\SecurityLogger;
use App\Services\StorageService;
use App\Services\AzureBlobStorage;

class ApplicantController extends BaseController {

    /**
     * PROTECTED: Recruiter views applicants for a specific job
     * Route: /applicants?id=XX
     */
    public function index() {
		
        $jobId = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;
		$tenantId = isset($_SESSION['tenant_id']) ? (int)$_SESSION['tenant_id'] : 0;

        $jobModel = new Job();
        $applicantModel = new Applicant();

        // 1. Security Check
        if (!$jobModel->belongsToTenant($jobId, $tenantId)) {
            SecurityLogger::logAlert("UNAUTHORIZED_ACCESS", $tenantId, "Unauthorized attempt to view Job ID: $jobId");
            $_SESSION['error_message'] = "You do not have permission to view this job's applicants.";
            Helpers::redirect('/dashboard');
            return;
        }

        // 2. Fetch Data
        $job = $jobModel->find($jobId);
        
        // Handle Sorting Logic safely
        $allowedSorts = ['ai_score', 'applied_at', 'name'];
        $sortBy = in_array($_GET['sort'] ?? '', $allowedSorts) ? $_GET['sort'] : 'ai_score';
        $order = strtoupper($_GET['order'] ?? '') === 'ASC' ? 'ASC' : 'DESC';
        
        // Make sure these methods exist in your Applicant model!
        $applicants = method_exists($applicantModel, 'getJobApplicants') 
            ? $applicantModel->getJobApplicants($tenantId, $jobId, $sortBy, $order) 
            : [];
            
        $stats = method_exists($applicantModel, 'getJobStats') 
            ? $applicantModel->getJobStats($tenantId, $jobId) 
            : ['total' => 0, 'reviewed' => 0];

        // 3. Render View
        $this->render('applicants/list', [
            'pageTitle'  => 'Applicants - ' . $job['title'],
            'job'        => $job,
            'applicants' => $applicants,
            'stats'      => $stats,
            'sortBy'     => $sortBy
        ]);
    }
	
	
	/**
     * PROTECTED: Recruiter reviews a single applicant profile detail
     * Route: /applicant/view?id=XX
     */
    public function view() {
        $applicantId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $tenantId = isset($_SESSION['tenant_id']) ? (int)$_SESSION['tenant_id'] : 0;

        $applicantModel = new Applicant();
        $jobModel = new Job();
        
        // 1. Fetch the applicant metadata entry
        $applicant = $applicantModel->find($applicantId);

        // 2. CRITICAL BOLA PROTECTION: Verify the candidate belongs to this company tenant workspace
        if (!$applicant || (int)$applicant['tenant_id'] !== $tenantId) {
            SecurityLogger::logAlert(
                "BOLA_PROFILE_VIOLATION", 
                $tenantId, 
                "Unauthorized profile inspection blocked for Applicant ID: $applicantId"
            );
            $_SESSION['error_message'] = "Access denied. The profile requested does not exist in your workspace.";
			Helpers::redirect('/dashboard');
			return;
        }

        // 3. Fetch associated job information for UI breadcrumbs & layout context
        $job = $jobModel->find((int)$applicant['job_id']);

        // 4. Render the profile inspection dashboard view
        $this->render('applicants/view', [
            'pageTitle' => 'Review Profile - ' . htmlspecialchars($applicant['name']),
            'applicant' => $applicant,
            'job'       => $job
        ]);
    }
	
       /**
     * PUBLIC: Candidate submits an application form
     * Route: /applicants/store
     */
    public function store() {
        // 1. Candidate is public. Get the Job ID and verify it exists
        $jobId = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
        $jobModel = new Job();
        $job = $jobModel->find($jobId);

        if (!$job || $job['status'] !== 'active') {
            $_SESSION['error_message'] = 'This job is no longer accepting applications.';
            Helpers::redirect('/');
            return;
        }

        $tenantId = (int)$job['tenant_id']; // Extract tenant from the active job

        // CSRF Protection: Validate token from application form
        if (!isset($_POST['csrf_token']) || !Helpers::csrf_verify($_POST['csrf_token'])) {
            $_SESSION['error_message'] = 'Your session has expired. Please refresh the page and try again.';
            Helpers::redirect('/job?id=' . $jobId);
            return;
        }

        // Anti-Spam Protection 1: Honeypot trap (reject automated bot submissions)
        if (!empty($_POST['candidate_website_url'])) {
            SecurityLogger::logAlert(
                'SPAM_BOT_BLOCKED', 
                $tenantId, 
                "Automated honeypot bot trap triggered for Job ID: $jobId from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'),
                'MEDIUM'
            );
            $_SESSION['error_message'] = 'Submission flagged as automated request.';
            Helpers::redirect('/job?id=' . $jobId);
            return;
        }

        // Anti-Spam Protection 2: Submission speed verification (Reject sub-2 second automated POSTs)
        $renderedAt = isset($_POST['form_rendered_at']) ? (int)$_POST['form_rendered_at'] : 0;
        if ($renderedAt > 0 && (time() - $renderedAt) < 2) {
            $_SESSION['error_message'] = 'Application submitted unusually fast. Please try again.';
            Helpers::redirect('/job?id=' . $jobId);
            return;
        }

        // Anti-Spam Protection 3: Session/IP Rate Limiting (Max 5 submissions per 10 minutes)
        $now = time();
        if (!isset($_SESSION['application_timestamps']) || !is_array($_SESSION['application_timestamps'])) {
            $_SESSION['application_timestamps'] = [];
        }
        $_SESSION['application_timestamps'] = array_filter(
            $_SESSION['application_timestamps'],
            function($timestamp) use ($now) { return ($now - $timestamp) < 600; }
        );
        if (count($_SESSION['application_timestamps']) >= 5) {
            $_SESSION['error_message'] = 'You have submitted multiple applications recently. Please wait a few minutes before submitting another application.';
            Helpers::redirect('/job?id=' . $jobId);
            return;
        }

        $applicantModel = new Applicant();
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        // Anti-Spam Protection 4: Input Validation & Sanitization
        if (empty($name) || mb_strlen($name) < 2 || mb_strlen($name) > 100) {
            $_SESSION['error_message'] = 'Please enter a valid full name (between 2 and 100 characters).';
            Helpers::redirect('/job?id=' . $jobId);
            return;
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            $_SESSION['error_message'] = 'Please enter a valid email address.';
            Helpers::redirect('/job?id=' . $jobId);
            return;
        }

        if (!empty($phone) && mb_strlen($phone) > 30) {
            $_SESSION['error_message'] = 'Phone number is too long (maximum 30 characters).';
            Helpers::redirect('/job?id=' . $jobId);
            return;
        }

        // Pre-check: Prevent duplicate application for the same job and email
        if (!empty($email)) {
            $existing = $applicantModel->findByEmailAndJob($email, $jobId);
            if ($existing) {
                $_SESSION['duplicate_application_error'] = [
                    'email' => $email,
                    'job_title' => $job['title'],
                    'applied_at' => $existing['applied_at'] ?? date('Y-m-d H:i:s')
                ];
                Helpers::redirect('/job?id=' . $jobId);
                return;
            }
        }

        // Anti-Spam Protection 5: Handle File Upload with Size & MIME Type Verification
        $cvFile = $_FILES['cv_document'] ?? null;
        $storagePath = '';
        $originalFilename = '';

        if (!$cvFile || !isset($cvFile['error'])) {
            $_SESSION['error_message'] = 'Please upload a CV document.';
            Helpers::redirect('/job?id=' . $jobId);
            return;
        }

        // Check specific PHP upload errors
        if ($cvFile['error'] === UPLOAD_ERR_INI_SIZE || $cvFile['error'] === UPLOAD_ERR_FORM_SIZE) {
            $_SESSION['error_message'] = 'The uploaded CV exceeds the maximum allowed file size (5 MB).';
            Helpers::redirect('/job?id=' . $jobId);
            return;
        }

        if ($cvFile['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error_message'] = 'File upload error occurred. Please select your CV file and try again.';
            Helpers::redirect('/job?id=' . $jobId);
            return;
        }

        // Validate Extension (Strictly PDF and DOCX - legacy DOC is unparseable)
        $ext = strtolower(pathinfo($cvFile['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf', 'docx'])) {
            $_SESSION['error_message'] = 'Invalid CV format. Only PDF and DOCX files are allowed.';
            Helpers::redirect('/job?id=' . $jobId);
            return;
        }

        // Validate File Size (Strict 5 MB cap)
        $maxBytes = 5 * 1024 * 1024; // 5 MB
        if ($cvFile['size'] > $maxBytes) {
            $_SESSION['error_message'] = 'Your CV file is too large (maximum allowed size is 5 MB).';
            Helpers::redirect('/job?id=' . $jobId);
            return;
        }

        // Validate MIME type via finfo
        if (function_exists('finfo_open') && !empty($cvFile['tmp_name']) && file_exists($cvFile['tmp_name'])) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $detectedMime = finfo_file($finfo, $cvFile['tmp_name']);
            finfo_close($finfo);

            $allowedMimes = [
                'application/pdf',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/octet-stream' // Standard binary fallback for docx
            ];

            if (!in_array($detectedMime, $allowedMimes)) {
                $_SESSION['error_message'] = 'Invalid file format. Please upload a genuine PDF or DOCX file.';
                Helpers::redirect('/job?id=' . $jobId);
                return;
            }
        }

        try {
            $storageService = new StorageService();
            $storagePath = $storageService->store($cvFile, $tenantId, $jobId);
            
            // Filename sanitization against path traversal (Issue 1)
            $rawBasename = basename($cvFile['name']);
            $cleanBase = preg_replace('/[^A-Za-z0-9._-]/', '_', pathinfo($rawBasename, PATHINFO_FILENAME));
            $cleanFilename = substr($cleanBase, 0, 80) . '.' . $ext;
            $originalFilename = $cleanFilename;
        } catch (\Exception $e) {
            error_log("CV Storage Failed: " . $e->getMessage());
            $_SESSION['error_message'] = 'Failed to upload CV. Please try again.';
            Helpers::redirect('/job?id=' . $jobId);
            return;
        }

        // 3. Prepare Data for Database
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

        // 4. Save to Model and Dispatch to Asynchronous AI Queue
        try {
            // Perform record creation
            $newApplicantId = $applicantModel->create($data); 

            // If your BaseModel's create() returns the last inserted ID, use it.
            // If it returns a boolean, extract it via PDO wrapper directly:
            if (!$newApplicantId || is_bool($newApplicantId)) {
                $newApplicantId = (int)$applicantModel->getDbConnection()->lastInsertId();
            }

            // Fire-and-Forget Asynchronous Handshake dispatch to background infrastructure
            if ($newApplicantId > 0) {
                $queueService = new \App\Services\AzureQueueService();
                $queueService->dispatchJob($newApplicantId);
            }
            
            $_SESSION['application_timestamps'][] = time();
            $_SESSION['success_message'] = 'Your application has been submitted successfully!';
            Helpers::redirect('/job?id=' . $jobId);
        } catch (\Throwable $e) {
            // Handle duplicate entry gracefully (SQLSTATE 23000, error 1062, unique_email_job)
            if ($e instanceof \PDOException && ($e->getCode() == 23000 || str_contains($e->getMessage(), '1062') || str_contains($e->getMessage(), 'unique_email_job'))) {
                $_SESSION['duplicate_application_error'] = [
                    'email' => $data['email'],
                    'job_title' => $job['title'],
                    'applied_at' => date('Y-m-d H:i:s')
                ];
                Helpers::redirect('/job?id=' . $jobId);
                return;
            }

            error_log("Application submission failed: " . $e->getMessage());
            $_SESSION['error_message'] = 'An unexpected error occurred while processing your application. Please try again or contact support.';
            Helpers::redirect('/job?id=' . $jobId);
            return;
        }
    }

    /**
     * PROTECTED: Recruiter downloads a CV
     * Route: /applicants/download?id=XX
     * Transplants your legacy download-cv.php logic!
     */
    public function download() {
        $applicantId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $tenantId = $_SESSION['tenant_id'];

        $applicantModel = new Applicant();
        
        // Ensure the applicant belongs to the logged-in tenant!
        $applicant = $applicantModel->find($applicantId);

        if (!$applicant || (int)$applicant['tenant_id'] !== $tenantId) {
            SecurityLogger::logAlert('BOLA_DATA_EXFIL_ATTEMPT', $tenantId, 'Unauthorized CV download attempt for Applicant ID: ' . $applicantId);
            $_SESSION['error_message'] = 'Access denied. You do not have permission to download this document.';
            Helpers::redirect('/dashboard');
            return;
        }

        $storagePath = $applicant['cv_storage_path'] ?? '';
        $downloadFilename = $applicant['cv_filename'] ?? 'resume.pdf';

        if (empty($storagePath)) {
            $_SESSION['error_message'] = 'No CV file reference was found for this candidate.';
            Helpers::redirect('/applicant/view?id=' . $applicantId);
            return;
        }

        // Handle Azure Blob Download
        if (str_starts_with($storagePath, 'blob://')) {
            try {
                $azureBlob = new AzureBlobStorage();
                $blob = $azureBlob->downloadBlob($storagePath);
                
                if (empty($blob)) {
                    $_SESSION['error_message'] = 'The requested CV document is currently unavailable from cloud storage.';
                    Helpers::redirect('/applicant/view?id=' . $applicantId);
                    return;
                }

                $this->streamFileHeaders($downloadFilename, $blob['content_type'], $blob['content_length']);
                echo $blob['content'];
                exit;

            } catch (\Exception $e) {
                error_log("Blob download error: " . $e->getMessage());
                $_SESSION['error_message'] = 'Unable to download CV from cloud storage at this time. Please try again.';
                Helpers::redirect('/applicant/view?id=' . $applicantId);
                return;
            }
        } 
        // Handle Local Docker Volume Fallback Download
        else if (str_starts_with($storagePath, 'local://')) {
            $realPath = str_replace('local://', '', $storagePath);
            if (file_exists($realPath)) {
                $contentType = mime_content_type($realPath) ?: 'application/octet-stream';
                $this->streamFileHeaders($downloadFilename, $contentType, filesize($realPath));
                readfile($realPath);
                exit;
            }
            $_SESSION['error_message'] = 'The requested CV file could not be found on the server.';
            Helpers::redirect('/applicant/view?id=' . $applicantId);
            return;
        }

        $_SESSION['error_message'] = 'Invalid file storage format.';
        Helpers::redirect('/applicant/view?id=' . $applicantId);
        return;
    }
	
	
	/**
     * PROTECTED: Recruiter deletes a single applicant
     * Route: /applicant/delete (POST)
     */
    public function delete() {
        $applicantId = isset($_POST['applicant_id']) ? (int)$_POST['applicant_id'] : 0;
        $jobId = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0; // Used to redirect back to the correct job list
        $tenantId = (int)$_SESSION['tenant_id'];

        if (!isset($_POST['csrf_token']) || !Helpers::csrf_verify($_POST['csrf_token'])) {
            SecurityLogger::logAlert("CSRF_DELETE_VIOLATION", $tenantId, "Unauthorized applicant deletion attempt.");
            $_SESSION['error_message'] = 'Your session has expired. Please refresh the page and try again.';
            Helpers::redirect('/applicants?job_id=' . $jobId);
            return;
        }

        $applicantModel = new Applicant();
        
        // This securely drops the DB row AND gives us the file path back
        $storagePath = $applicantModel->deleteSecure($tenantId, $applicantId);

        if ($storagePath !== null) {
            // If there's an actual file path, wipe it from Azure or Local Volume
            if (!empty($storagePath)) {
                try {
                    $storageService = new StorageService();
                    $storageService->delete($storagePath);
                } catch (\Exception $e) {
                    error_log("Azure/Local CV Deletion error on applicant ID $applicantId: " . $e->getMessage());
                }
            }
            
            SecurityLogger::logAlert("APPLICANT_RECORD_DELETED", $tenantId, "Applicant deleted (ID: $applicantId).");
            $_SESSION['success_message'] = 'Candidate and associated CV have been permanently deleted.';
        } else {
            SecurityLogger::logAlert("UNAUTHORIZED_DELETE_ATTEMPT", $tenantId, "Attempted deletion on unauthorized applicant ID: $applicantId");
            $_SESSION['error_message'] = 'Unable to delete candidate: Record not found or access denied.';
        }

        Helpers::redirect('/applicants?job_id=' . $jobId);
    }
	
	
	/**
     * PROTECTED: Recruiter deletes ALL applicants for a specific job
     * Route: /applicant/delete-all (POST)
     */
    public function deleteAll() {
        $jobId = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
        $tenantId = (int)$_SESSION['tenant_id'];

        if (!isset($_POST['csrf_token']) || !Helpers::csrf_verify($_POST['csrf_token'])) {
            SecurityLogger::logAlert("CSRF_MASS_DELETE_VIOLATION", $tenantId, "Unauthorized mass deletion attempt.");
            $_SESSION['error_message'] = 'Your session has expired. Please refresh the page and try again.';
            Helpers::redirect('/applicants?job_id=' . $jobId);
            return;
        }

        $jobModel = new Job();
        $applicantModel = new Applicant();

        // 1. Verify the tenant actually owns this job before doing a mass wipe
        if (!$jobModel->belongsToTenant($jobId, $tenantId)) {
            SecurityLogger::logAlert("UNAUTHORIZED_MASS_DELETE", $tenantId, "Attempted mass delete on unowned job ID: $jobId");
            $_SESSION['error_message'] = 'Unauthorized action: You do not own this job posting.';
            Helpers::redirect('/dashboard');
            return;
        }

        // 2. Wipe database rows and get the list of cloud files
        $storagePaths = $applicantModel->deleteAllForJobSecure($tenantId, $jobId);

        // 3. Loop through and wipe the files from Azure / Local Storage
        if (!empty($storagePaths)) {
            $storageService = new StorageService();
            $deletedCount = 0;
            
            foreach ($storagePaths as $path) {
                if (!empty($path)) {
                    try {
                        $storageService->delete($path);
                        $deletedCount++;
                    } catch (\Exception $e) {
                        error_log("Mass CV Deletion error on path $path: " . $e->getMessage());
                    }
                }
            }
            SecurityLogger::logAlert("MASS_APPLICANT_DELETE", $tenantId, "Deleted $deletedCount candidates for Job ID: $jobId");
        }

        $_SESSION['success_message'] = "All candidates and their CVs have been permanently wiped.";
        Helpers::redirect('/applicants?job_id=' . $jobId);
    }

    /**
     * Helper to output secure HTTP download headers
     */
    private function streamFileHeaders($filename, $contentType, $contentLength) {
        while (ob_get_level() > 0) ob_end_clean();
        
        $safeFilename = preg_replace('/[^A-Za-z0-9._ -]/', '_', basename($filename));
        
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $safeFilename . '"');
        header('Content-Length: ' . max(0, $contentLength));
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: private, no-transform, no-cache, must-revalidate');
        header('Pragma: no-cache');
    }
}