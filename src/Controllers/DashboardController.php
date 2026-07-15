<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Job;
use App\Models\Recruiter;

class DashboardController extends BaseController {
    
    public function index() {
        
        error_log("DEBUG: Dashboard check - Session tenant_id is: " . ($_SESSION['tenant_id'] ?? 'MISSING'));
    
        if (!isset($_SESSION['tenant_id'])) {
            error_log("DEBUG: Dashboard blocking access - No tenant_id in session.");
        }

        $tenantId = $_SESSION['tenant_id'];
        
        // 1. CRITICAL FIX: Extract the full profile from the session
        $tenantProfile = $_SESSION['tenant_profile'] ?? ['company_name' => 'Recruiter']; 
        
        $jobModel = new Job();
        $recruiterModel = new Recruiter(); 
        
        $stats = method_exists($jobModel, 'getStats') ? $jobModel->getStats($tenantId) : [
            'total_jobs' => 0, 'active_jobs' => 0, 'inactive_jobs' => 0, 'total_applicants' => 0
        ];
        $jobs = method_exists($jobModel, 'getTenantJobs') ? $jobModel->getTenantJobs($tenantId) : [];

        $this->render('dashboard/index', [
            'pageTitle' => 'Recruitment Dashboard',
            'stats'     => $stats,
            'jobs'      => $jobs,
            'tenant'    => $tenantProfile // Passed to the view here!
        ]);
    }
}