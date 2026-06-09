<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Job;
use App\Models\Recruiter; // Aligning with your core recruiter/tenant layout
use App\Core\Helpers;

class DashboardController extends BaseController {
    
    public function index() {
        // Ensure session environment scope is open
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Proactive Authentication Guard: Protect route from unauthenticated guests
        if (!isset($_SESSION['tenant_id'])) {
            $_SESSION['error_message'] = "Please log in to access the dashboard.";
            Helpers::redirect('/login');
            return;
        }

        // 2. Proactive Session Integrity Check (Zero-Trust Verification)
        $currentAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $currentIpHash = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

        if (($_SESSION['user_agent'] !== $currentAgent) || ($_SESSION['client_ip_hash'] !== $currentIpHash)) {
            // Immediate Session Revocation on anomaly detection
            $_SESSION = [];
            session_destroy();
            
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['error_message'] = "Session security verification failed. Please re-authenticate.";
            Helpers::redirect('/login');
            return;
        }

        $tenantId = $_SESSION['tenant_id'];
        
        // 3. Initialize Domain Models safely
        $jobModel = new Job();
        $recruiterModel = new Recruiter(); 
        
        // Fetch data sets safely matched against the validated session landlord token
        $stats = method_exists($jobModel, 'getStats') ? $jobModel->getStats($tenantId) : [
    'total_jobs' => 0, 
    'active_jobs' => 0, 
    'inactive_jobs' => 0,
    'total_applicants' => 0
];
        $jobs = method_exists($jobModel, 'getTenantJobs') ? $jobModel->getTenantJobs($tenantId) : [];

        // 4. Render standardized workspace view layout
        $this->render('dashboard/index', [
            'pageTitle' => 'Recruitment Dashboard',
            'stats'     => $stats,
            'jobs'      => $jobs
        ]);
    }
}