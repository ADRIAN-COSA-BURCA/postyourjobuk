<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Job;

class JobController extends Controller {
    public function index() {
        $jobModel = new Job();
        $jobs = $jobModel->getTenantJobs($_SESSION['tenant_id']);
        $this->render('jobs/index', ['jobs' => $jobs]);
    }

    public function create() {
        // Logic to show form or process post request
    }
}