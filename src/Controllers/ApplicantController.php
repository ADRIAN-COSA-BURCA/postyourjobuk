<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Applicant;
use App\Models\Job;
use App\Core\SecurityLogger;

class ApplicantController extends BaseController {

    public function index($jobId) {
    $jobModel = new \App\Models\Job();
    $applicantModel = new \App\Models\Applicant();
    $tenantId = $_SESSION['tenant_id'];

    // 1. Security Check
    if (!$jobModel->belongsToTenant($jobId, $tenantId)) {
        \App\Core\SecurityLogger::logAlert("UNAUTHORIZED_ACCESS", $tenantId, "Unauthorized attempt to view Job ID: $jobId");
        $this->render('errors/403');
        return;
    }

    // 2. Fetch Data
    $job = $jobModel->find($jobId);
    
    // Handle Sorting Logic
    $sortBy = in_array($_GET['sort'] ?? '', ['ai_score', 'applied_at', 'name']) ? $_GET['sort'] : 'ai_score';
    $order = strtoupper($_GET['order'] ?? '') === 'ASC' ? 'ASC' : 'DESC';
    
    $applicants = $applicantModel->getJobApplicants($tenantId, $jobId, $sortBy, $order);
    $stats = $applicantModel->getJobStats($tenantId, $jobId); // Ensure this method exists in Applicant model

    // 3. Render View
    $this->render('applicants/list', [
        'job' => $job,
        'applicants' => $applicants,
        'stats' => $stats,
        'sortBy' => $sortBy
    ]);
}
	
	
	public function store() {
    // 1. Handle File Upload
    $cvFile = $_FILES['cv'];
    // Assuming you have a simple helper or class for storage
    $storagePath = \App\Services\Storage::store($cvFile, $_SESSION['tenant_id']);

    // 2. Prepare Data
    $data = [
        'tenant_id' => $_SESSION['tenant_id'],
        'job_id'    => $_POST['job_id'],
        'name'      => $_POST['name'],
        'cv_storage_path' => $storagePath
    ];

    // 3. Save to Model
    $applicantModel = new \App\Models\Applicant();
    $applicantModel->createApplication($data);
    
    // Redirect...
}
}