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

    public function store() {
        // CSRF Verification
        if (!isset($_POST['csrf_token']) || !Helpers::csrf_verify($_POST['csrf_token'])) {
            SecurityLogger::logAlert("CSRF_CREATION_VIOLATION", $_SESSION['tenant_id'], "Unauthorized job creation attempt.");
            die("Security Token Mismatch.");
        }

        $jobModel = new Job();
        $tenantId = $_SESSION['tenant_id'];

        // Proactive Velocity Monitoring (Using Model method)
        if ($jobModel->getPostCountLastHour($tenantId) >= 15) {
            SecurityLogger::logAlert("RESOURCE_FLOODING_ATTEMPT", $tenantId, "Rate-limit threshold reached.");
            $_SESSION['error_message'] = 'System velocity threshold exceeded.';
            Helpers::redirect('/jobs/create');
            return;
        }

        // Data Mapping
        $data = [
            'tenant_id'       => $tenantId,
            'title'           => trim($_POST['title'] ?? ''),
            'description'     => trim($_POST['description'] ?? ''),
            'requirements'    => trim($_POST['requirements'] ?? ''),
            'location'        => trim($_POST['location'] ?? null),
            'salary'          => trim($_POST['salary'] ?? null),
            'employment_type' => $_POST['employment_type'] ?? 'full-time',
            'status'          => $_POST['status'] ?? 'active'
        ];

        // Validation
        if (strlen($data['title']) < 5 || strlen($data['description']) < 50 || empty($data['requirements'])) {
            $_SESSION['error_message'] = 'Validation failed: Please ensure all fields meet the criteria.';
            Helpers::redirect('/jobs/create');
            return;
        }

        // Persistence
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