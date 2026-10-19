<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Helpers;
use App\Models\Job;
use App\Core\SecurityLogger;

class JobController extends BaseController {

    public function create() {
        $this->render('jobs/create', [
            'pageTitle' => 'Create New Job'
        ]);
    }
	
    // FIXED: Removed formal parameter argument to align perfectly with the route engine mapping
    public function show() {
        $jobModel = new Job();
        
        // Pull context safe ID natively from global GET scope
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        // 1. Fetch single job secure data along with the related company details
        $job = $jobModel->getJobWithTenant($id);
        
        // 2. Fallback protection if a user manually changes the URL ID parameters to something invalid
        if (!$job || ($job['status'] ?? 'active') !== 'active') {
            $_SESSION['error_message'] = 'Job posting not found or no longer active.';
            Helpers::redirect('/');
            return;
        }
        
        // 3. Prepare old form input context arrays if validation fails
        $oldInput = $_SESSION['old_input'] ?? [];
        
        // 4. Render 'jobs/details' matching your view layout file name perfectly
        $this->render('jobs/details', [
            'pageTitle' => $job['title'] . ' - ' . ($job['company_name'] ?? 'View Job'),
            'job'       => $job,
            'oldInput'  => $oldInput
        ]);
    }

    public function store() {
        if (!isset($_POST['csrf_token']) || !Helpers::csrf_verify($_POST['csrf_token'])) {
            SecurityLogger::logAlert("CSRF_CREATION_VIOLATION", $_SESSION['tenant_id'] ?? 0, "Unauthorized job creation attempt.");
            die("Security Token Mismatch.");
        }

        $jobModel = new Job();
        $tenantId = (int)$_SESSION['tenant_id'];

        if ($jobModel->getPostCountLastHour($tenantId) >= 15) {
            SecurityLogger::logAlert("RESOURCE_FLOODING_ATTEMPT", $tenantId, "Rate-limit threshold reached.");
            $_SESSION['error_message'] = 'You have created multiple job postings recently. Please wait a few moments before posting another role.';
            Helpers::redirect('/jobs/create');
            return;
        }

        $data = [
            'tenant_id'       => $tenantId,
            'title'           => trim((string)($_POST['title'] ?? '')),
            'description'     => trim((string)($_POST['description'] ?? '')),
            'requirements'    => trim((string)($_POST['requirements'] ?? '')),
            'location'        => trim((string)($_POST['location'] ?? '')),
            'salary'          => trim((string)($_POST['salary'] ?? '')),
            'employment_type' => $_POST['employment_type'] ?? 'full-time',
            'status'          => $_POST['status'] ?? 'active'
        ];

        // Specific, helpful validation messages
        if (mb_strlen($data['title']) < 5) {
            $_SESSION['error_message'] = 'Job title must be at least 5 characters long.';
            Helpers::redirect('/jobs/create');
            return;
        }

        if (mb_strlen($data['description']) < 50) {
            $_SESSION['error_message'] = 'Job description must be at least 50 characters long to provide adequate detail for candidates.';
            Helpers::redirect('/jobs/create');
            return;
        }

        if (empty($data['requirements'])) {
            $_SESSION['error_message'] = 'Please specify the key requirements and qualifications for this role.';
            Helpers::redirect('/jobs/create');
            return;
        }

        try {
            $jobId = $jobModel->createJob($data);
            SecurityLogger::logAlert("JOB_RECORD_CREATED", $tenantId, "New job created (ID: $jobId).");
            $_SESSION['success_message'] = 'Job posted successfully!';
            Helpers::redirect('/dashboard');
        } catch (\Exception $e) {
            error_log('Job creation error: ' . $e->getMessage());
            $_SESSION['error_message'] = 'An unexpected error occurred while saving your job posting. Please try again or contact support.';
            Helpers::redirect('/jobs/create');
        }
    }

    /**
     * Render the job editing form layout views.
     */
    public function edit() {
        $jobModel = new Job();
        $jobId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $tenantId = (int)$_SESSION['tenant_id'];

        // Secure Multi-Tenancy Guard Check
        if (!$jobModel->belongsToTenant($jobId, $tenantId)) {
            SecurityLogger::logAlert("UNAUTHORIZED_ACCESS_ATTEMPT", $tenantId, "Attempted to edit unauthorized job ID: $jobId");
            $_SESSION['error_message'] = '❌ You do not have permission to edit this job posting.';
            Helpers::redirect('/dashboard');
            return;
        }

        $job = $jobModel->find($jobId);

        $this->render('jobs/edit', [
            'pageTitle' => 'Edit Job - ' . ($job['title'] ?? ''),
            'job'       => $job
        ]);
    }

    /**
     * Process the secure update data payload.
     */
    public function update() {
        $jobId = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
        $tenantId = (int)$_SESSION['tenant_id'];

        if (!isset($_POST['csrf_token']) || !Helpers::csrf_verify($_POST['csrf_token'])) {
            SecurityLogger::logAlert("CSRF_UPDATE_VIOLATION", $tenantId, "Unauthorized update attempt.");
            $_SESSION['error_message'] = 'Your session has expired. Please refresh the page and try again.';
            Helpers::redirect("/jobs/edit?id=" . $jobId);
            return;
        }

        $jobModel = new Job();

        $data = [
            'title'           => trim((string)($_POST['title'] ?? '')),
            'description'     => trim((string)($_POST['description'] ?? '')),
            'requirements'    => trim((string)($_POST['requirements'] ?? '')),
            'location'        => trim((string)($_POST['location'] ?? '')),
            'salary'          => trim((string)($_POST['salary'] ?? '')),
            'employment_type' => $_POST['employment_type'] ?? 'full-time',
            'status'          => $_POST['status'] ?? 'active'
        ];

        // Specific, helpful validation messages
        if (mb_strlen($data['title']) < 5) {
            $_SESSION['error_message'] = 'Job title must be at least 5 characters long.';
            Helpers::redirect("/jobs/edit?id=" . $jobId);
            return;
        }

        if (mb_strlen($data['description']) < 50) {
            $_SESSION['error_message'] = 'Job description must be at least 50 characters long to provide adequate detail for candidates.';
            Helpers::redirect("/jobs/edit?id=" . $jobId);
            return;
        }

        if (empty($data['requirements'])) {
            $_SESSION['error_message'] = 'Please specify the key requirements and qualifications for this role.';
            Helpers::redirect("/jobs/edit?id=" . $jobId);
            return;
        }

        // Run Tenant-Scoped Update via the updated Model method
        $updated = $jobModel->updateJobSecure($tenantId, $jobId, $data);

        if ($updated) {
            SecurityLogger::logAlert("JOB_RECORD_UPDATED", $tenantId, "Job record modified (ID: $jobId).");
            $_SESSION['success_message'] = 'Job details updated successfully!';
            Helpers::redirect('/dashboard');
        } else {
            SecurityLogger::logAlert("UNAUTHORIZED_UPDATE_ATTEMPT", $tenantId, "Attempted modification on unauthorized job ID: $jobId");
            $_SESSION['error_message'] = 'Unable to update job posting: Record not found or access denied.';
            Helpers::redirect('/dashboard');
        }
    }

    /**
     * Securely remove a job posting.
     */
    public function delete() {
        // Can read from POST (form submit) or GET (link click with validation tokens depending on routing setup)
        $jobId = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
        $tenantId = (int)$_SESSION['tenant_id'];
        $jobModel = new Job();

        $deleted = $jobModel->deleteJobSecure($tenantId, $jobId);

        if ($deleted) {
            SecurityLogger::logAlert("JOB_RECORD_DELETED", $tenantId, "Job deleted (ID: $jobId).");
            $_SESSION['success_message'] = 'Job posting has been removed successfully.';
        } else {
            SecurityLogger::logAlert("UNAUTHORIZED_DELETE_ATTEMPT", $tenantId, "Attempted deletion on unauthorized job ID: $jobId");
            $_SESSION['error_message'] = 'Unable to delete job posting: Record not found or access denied.';
        }

        Helpers::redirect('/dashboard');
    }
}