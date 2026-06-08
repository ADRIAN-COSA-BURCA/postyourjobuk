<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Applicant;
use App\Models\Job;
use App\Core\SecurityLogger;

class ApplicantController extends BaseController {

    public function index($jobId) {
        $jobModel = new Job();
        $applicantModel = new Applicant();
        $tenantId = $_SESSION['tenant_id'];

        // 1. Data Boundary Gatekeeper
        if (!$jobModel->belongsToTenant($jobId, $tenantId)) {
            SecurityLogger::logAlert("UNAUTHORIZED_ACCESS", $tenantId, "Attempted to view applicants for Job ID: $jobId");
            $this->render('errors/403');
            return;
        }

        // 2. Fetch Data
        $job = $jobModel->find($jobId);
        $applicants = $applicantModel->getJobApplicants($tenantId, $jobId);
        
        $this->render('applicants/list', [
            'job' => $job,
            'applicants' => $applicants
        ]);
    }
}