<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Job;
use App\Models\Recruiter;

class DashboardController extends BaseController {
    
    public function index() {
        // You do NOT need the session checks or the IP/Agent hashes here.
        // BaseController::__construct() already verified them and will redirect 
        // intruders before this code even runs.

        $tenantId = $_SESSION['tenant_id'];
        
        $jobModel = new Job();
        $recruiterModel = new Recruiter(); 
        
        $stats = method_exists($jobModel, 'getStats') ? $jobModel->getStats($tenantId) : [
            'total_jobs' => 0, 'active_jobs' => 0, 'inactive_jobs' => 0, 'total_applicants' => 0
        ];
        $jobs = method_exists($jobModel, 'getTenantJobs') ? $jobModel->getTenantJobs($tenantId) : [];

        $this->render('dashboard/index', [
            'pageTitle' => 'Recruitment Dashboard',
            'stats'     => $stats,
            'jobs'      => $jobs
        ]);
    }
}