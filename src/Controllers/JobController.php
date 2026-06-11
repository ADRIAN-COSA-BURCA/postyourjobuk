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
        $tenantId = $_SESSION['tenant_id'];

        if ($jobModel->getPostCountLastHour($tenantId) >= 15) {
            SecurityLogger::logAlert("RESOURCE_FLOODING_ATTEMPT", $tenantId, "Rate-limit threshold reached.");
            $_SESSION['error_message'] = 'System velocity threshold exceeded.';
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

        if (strlen($data['title']) < 5 || strlen($data['description']) < 50 || empty($data['requirements'])) {
            $_SESSION['error_message'] = 'Validation failed: Please ensure all fields meet the criteria.';
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
            $_SESSION['error_message'] = 'Internal architecture error during record creation.';
            Helpers::redirect('/jobs/create');
        }
    }
}