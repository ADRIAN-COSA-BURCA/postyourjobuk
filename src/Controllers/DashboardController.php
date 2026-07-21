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

    public function analytics() {
        if (!isset($_SESSION['tenant_id'])) {
            header("Location: /login");
            exit;
        }

        $tenantId = $_SESSION['tenant_id'];
        $tenantProfile = $_SESSION['tenant_profile'] ?? ['company_name' => 'Recruiter'];

        $jobModel = new Job();
        
        // Fetch base stats
        $stats = method_exists($jobModel, 'getStats') ? $jobModel->getStats($tenantId) : [
            'total_jobs' => 0, 'active_jobs' => 0, 'inactive_jobs' => 0, 'total_applicants' => 0
        ];

        // Fetch funnel data
        $funnelData = method_exists($jobModel, 'getApplicantFunnelData') 
            ? $jobModel->getApplicantFunnelData($tenantId) 
            : ['applied' => 0, 'interviewing' => 0, 'offered' => 0, 'hired' => 0];

        // Fetch trend data
        $trendData = method_exists($jobModel, 'getTrendData') 
            ? $jobModel->getTrendData($tenantId) 
            : ['labels' => [], 'data' => []];

        // NEW: Fetch top performing jobs
        $topJobs = method_exists($jobModel, 'getTopPerformingJobs') 
            ? $jobModel->getTopPerformingJobs($tenantId) 
            : [];

        // Render the analytics view with all datasets
        $this->render('dashboard/analytics', [
            'pageTitle'  => 'Dashboard Analytics',
            'tenant'     => $tenantProfile,
            'stats'      => $stats,
            'funnelData' => $funnelData,
            'trendData'  => $trendData,
            'topJobs'    => $topJobs // Pass the new data here
        ]);
    }
}