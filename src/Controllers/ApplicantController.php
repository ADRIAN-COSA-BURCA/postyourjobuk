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

        // 2. Handle File Upload using our new StorageService
        $cvFile = $_FILES['cv_document'] ?? null;
        $storagePath = '';
        $originalFilename = '';

        if ($cvFile && $cvFile['error'] === UPLOAD_ERR_OK) {
            // Validate Extension
            $ext = strtolower(pathinfo($cvFile['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['pdf', 'doc', 'docx'])) {
                $_SESSION['error_message'] = 'Invalid CV format. Only PDF and DOCX are allowed.';
                Helpers::redirect('/job?id=' . $jobId);
                return;
            }

            try {
                $storageService = new StorageService();
                $storagePath = $storageService->store($cvFile, $tenantId, $jobId);
                $originalFilename = $cvFile['name'];
            } catch (\Exception $e) {
                error_log("CV Storage Failed: " . $e->getMessage());
                $_SESSION['error_message'] = 'Failed to upload CV. Please try again.';
                Helpers::redirect('/job?id=' . $jobId);
                return;
            }
        } else {
            $_SESSION['error_message'] = 'Please upload a CV document.';
            Helpers::redirect('/job?id=' . $jobId);
            return;
        }

        // 3. Prepare Data for Database
        $data = [
            'tenant_id'       => $tenantId,
            'job_id'          => $jobId,
            'name'            => trim($_POST['name'] ?? ''),
            'email'           => trim($_POST['email'] ?? ''),
            'phone'           => trim($_POST['phone'] ?? ''),
            'cv_storage_path' => $storagePath,
            'cv_filename'     => $originalFilename,
            'status'          => 'new'
        ];

        // 4. Save to Model and Dispatch to Asynchronous AI Queue
        try {
            $applicantModel = new Applicant();
            
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
            
            $_SESSION['success_message'] = 'Your application has been submitted successfully!';
            Helpers::redirect('/job?id=' . $jobId);
        } catch (\Exception $e) {
            error_log("Applicant creation error: " . $e->getMessage());
            $_SESSION['error_message'] = 'An error occurred while submitting your application.';
            Helpers::redirect('/job?id=' . $jobId);
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
            die('Access denied.');
        }

        $storagePath = $applicant['cv_storage_path'] ?? '';
        $downloadFilename = $applicant['cv_filename'] ?? 'resume.pdf';

        if (empty($storagePath)) {
            die('CV file reference missing.');
        }

        // Handle Azure Blob Download
        if (str_starts_with($storagePath, 'blob://')) {
            try {
                $azureBlob = new AzureBlobStorage();
                $blob = $azureBlob->downloadBlob($storagePath);
                
                if (empty($blob)) {
                    die('Azure file not found.');
                }

                $this->streamFileHeaders($downloadFilename, $blob['content_type'], $blob['content_length']);
                echo $blob['content'];
                exit;

            } catch (\Exception $e) {
                error_log("Blob download error: " . $e->getMessage());
                die('Failed to retrieve file from cloud storage.');
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
            die('Local file not found.');
        }

        die('Invalid storage path format.');
    }
	
	
	/**
     * PROTECTED: Recruiter deletes a single applicant
     * Route: /applicant/delete (POST)
     */
    public function delete() {
        if (!isset($_POST['csrf_token']) || !Helpers::csrf_verify($_POST['csrf_token'])) {
            SecurityLogger::logAlert("CSRF_DELETE_VIOLATION", $_SESSION['tenant_id'] ?? 0, "Unauthorized applicant deletion attempt.");
            die("Security Token Mismatch.");
        }

        $applicantId = isset($_POST['applicant_id']) ? (int)$_POST['applicant_id'] : 0;
        $jobId = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0; // Used to redirect back to the correct job list
        $tenantId = (int)$_SESSION['tenant_id'];

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
            $_SESSION['error_message'] = 'Deletion failed: Access denied or applicant missing.';
        }

        Helpers::redirect('/applicants?job_id=' . $jobId);
    }
	
	
	/**
     * PROTECTED: Recruiter deletes ALL applicants for a specific job
     * Route: /applicant/delete-all (POST)
     */
    public function deleteAll() {
        if (!isset($_POST['csrf_token']) || !Helpers::csrf_verify($_POST['csrf_token'])) {
            SecurityLogger::logAlert("CSRF_MASS_DELETE_VIOLATION", $_SESSION['tenant_id'] ?? 0, "Unauthorized mass deletion attempt.");
            die("Security Token Mismatch.");
        }

        $jobId = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
        $tenantId = (int)$_SESSION['tenant_id'];

        $jobModel = new Job();
        $applicantModel = new Applicant();

        // 1. Verify the tenant actually owns this job before doing a mass wipe
        if (!$jobModel->belongsToTenant($jobId, $tenantId)) {
            SecurityLogger::logAlert("UNAUTHORIZED_MASS_DELETE", $tenantId, "Attempted mass delete on unowned job ID: $jobId");
            $_SESSION['error_message'] = 'Unauthorized action.';
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