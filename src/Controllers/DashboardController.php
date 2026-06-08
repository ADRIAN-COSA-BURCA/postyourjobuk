<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Job;
use App\Models\Tenant;

class DashboardController extends BaseController {
    public function index() {
    $jobModel = new Job();
    $tenantModel = new Tenant();
    
    // The data fetching remains clean and focused
    $this->render('portal/dashboard', [
        'stats' => $tenantModel->getStats($_SESSION['tenant_id']),
        'jobs'  => $jobModel->getTenantJobs($_SESSION['tenant_id'])
    ]);
}
}