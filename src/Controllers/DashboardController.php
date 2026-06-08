<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Job;
use App\Models\Tenant;

class DashboardController extends BaseController {
    
    public function index() {
        $jobModel = new Job();
        $tenantModel = new Tenant();
        $tenantId = $_SESSION['tenant_id'];
        
        // Passing data to the view, now pointing to the standardized view path
        $this->render('dashboard/index', [
            'pageTitle' => 'Tenant Dashboard',
            'stats'     => $tenantModel->getStats($tenantId),
            'jobs'      => $jobModel->getTenantJobs($tenantId)
        ]);
    }
}