<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Helpers;
use App\Models\Job;
use App\Core\SecurityLogger; // Assuming you have this helper or want to implement it

class JobController extends BaseController {
    
    public function show($id) {
        $jobModel = new Job();
        $job = $jobModel->find((int)$id);

        if (!$job) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $this->render('job/details', ['job' => $job]);
    }

    public function store() {
        // Ensure user is authenticated
        if (!$this->isLoggedIn()) {
            Helpers::redirect('/login');
        }

        $tenantId = $_SESSION['tenant_id'];

        // 1. CSRF Verification
        if (!isset($_POST['csrf_token']) || !Helpers::csrf_verify($_POST['csrf_token'])) {
            SecurityLogger::logAlert("CSRF_VIOLATION", $tenantId, "Unauthorized form submission.");
            die("Security Token Mismatch.");
        }

        // 2. Proactive Velocity Monitoring (Rate Limiting)
        $jobModel = new Job();
        if ($this->hasExceededPostingLimit($tenantId)) {
            $_SESSION['error'] = 'Velocity threshold exceeded. Please try again later.';
            Helpers::redirect('/portal/dashboard');
        }

        // 3. Data Validation & Sanitization
        $title = trim($_POST['title'] ?? '');
        if (strlen($title) < 5) {
            $_SESSION['error'] = 'Job title is too short.';
            Helpers::redirect('/portal/create-job');
        }

        // 4. Persistence
        $jobModel->createJob([
            'tenant_id' => $tenantId,
            'title'     => $title,
            'description' => $_POST['description'] ?? '',
            // ... add remaining fields
        ]);

        $_SESSION['success'] = 'Job posted successfully!';
        Helpers::redirect('/portal/dashboard');
    }

    private function hasExceededPostingLimit($tenantId) {
        // Migration of your "Level 6 Security" logic
        $sql = "SELECT COUNT(*) FROM jobs WHERE tenant_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        $count = $this->db->fetchColumn($sql, [$tenantId]);
        return ($count >= 15);
    }
}