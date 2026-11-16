<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Helpers;
use App\Core\SecurityLogger;
use App\Models\Job;

class JobController extends BaseController {

    private const ALLOWED_EMPLOYMENT_TYPES = ['full-time', 'part-time', 'contract', 'internship'];
    private const ALLOWED_STATUSES         = ['active', 'inactive', 'closed'];

    /**
     * GET /api/jobs/detail?id={id}
     * Public endpoint for job details + employer business card (or recruiter edit fetch)
     */
    public function show(): void {
        $jobModel = new Job();
        $id       = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $mode     = trim((string)($_GET['mode'] ?? 'public'));

        if ($id <= 0) {
            $this->json(['success' => false, 'error' => 'Invalid job ID.'], 400);
        }

        if ($mode === 'edit') {
            $tenantId = $this->requireTenantAuth();
            if (!$jobModel->belongsToTenant($id, $tenantId)) {
                SecurityLogger::logAlert("UNAUTHORIZED_ACCESS_ATTEMPT", $tenantId, "Attempted to edit unauthorized job ID: $id");
                $this->json(['success' => false, 'error' => 'You do not have permission to edit this job posting.'], 403);
            }
            $job = $jobModel->find($id);
            $this->json([
                'success'    => true,
                'job'        => $job,
                'csrf_token' => Helpers::csrf_token()
            ]);
        }

        $job = $jobModel->getJobWithTenant($id);
        if (!$job || ($job['status'] ?? 'active') !== 'active') {
            $this->json([
                'success'  => false,
                'error'    => 'Job posting not found or no longer active.',
                'redirect' => '/'
            ], 404);
        }

        $this->json([
            'success'          => true,
            'job'              => $job,
            'form_rendered_at' => time(),
            'csrf_token'       => Helpers::csrf_token()
        ]);
    }

    /**
     * POST /api/jobs/store
     */
    public function store(): void {
        $tenantId = $this->requireTenantAuth();
        $this->requireCsrf('CSRF_CREATION_VIOLATION');

        $jobModel = new Job();
        if ($jobModel->getPostCountLastHour($tenantId) >= 15) {
            SecurityLogger::logAlert("RESOURCE_FLOODING_ATTEMPT", $tenantId, "Rate-limit threshold reached.");
            $this->json([
                'success' => false,
                'error'   => 'You have created multiple job postings recently. Please wait a few moments before posting another role.'
            ], 429);
        }

        $body    = $this->getBody();
        $empType = strtolower(trim((string)($body['employment_type'] ?? 'full-time')));
        if (!in_array($empType, self::ALLOWED_EMPLOYMENT_TYPES, true)) {
            $empType = 'full-time';
        }

        $status = strtolower(trim((string)($body['status'] ?? 'active')));
        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            $status = 'active';
        }

        $data = [
            'tenant_id'       => $tenantId,
            'title'           => trim((string)($body['title'] ?? '')),
            'description'     => trim((string)($body['description'] ?? '')),
            'requirements'    => trim((string)($body['requirements'] ?? '')),
            'location'        => trim((string)($body['location'] ?? '')),
            'salary'          => trim((string)($body['salary'] ?? '')),
            'employment_type' => $empType,
            'status'          => $status
        ];

        if (mb_strlen($data['title']) < 5) {
            $this->json(['success' => false, 'error' => 'Job title must be at least 5 characters long.'], 422);
        }
        if (mb_strlen($data['description']) < 50) {
            $this->json(['success' => false, 'error' => 'Job description must be at least 50 characters long to provide adequate detail for candidates.'], 422);
        }
        if ($data['requirements'] === '') {
            $this->json(['success' => false, 'error' => 'Please specify the key requirements and qualifications for this role.'], 422);
        }

        try {
            $jobId = $jobModel->createJob($data);
            SecurityLogger::logAlert("JOB_RECORD_CREATED", $tenantId, "New job created (ID: $jobId).", 'INFO');
            $this->json([
                'success'  => true,
                'job_id'   => (int)$jobId,
                'message'  => 'Job posted successfully!',
                'redirect' => '/dashboard'
            ], 201);
        } catch (\Throwable $e) {
            error_log('Job creation error: ' . $e->getMessage());
            $this->json([
                'success' => false,
                'error'   => 'An unexpected error occurred while saving your job posting. Please try again.'
            ], 500);
        }
    }

    /**
     * POST /api/jobs/update
     */
    public function update(): void {
        $tenantId = $this->requireTenantAuth();
        $this->requireCsrf('CSRF_UPDATE_VIOLATION');

        $body  = $this->getBody();
        $jobId = (int)($body['job_id'] ?? $_GET['id'] ?? 0);

        if ($jobId <= 0) {
            $this->json(['success' => false, 'error' => 'Invalid job ID.'], 400);
        }

        $jobModel = new Job();
        if (!$jobModel->belongsToTenant($jobId, $tenantId)) {
            SecurityLogger::logAlert("UNAUTHORIZED_UPDATE_ATTEMPT", $tenantId, "Attempted modification on unauthorized job ID: $jobId");
            $this->json(['success' => false, 'error' => 'Unable to update job posting: Record not found or access denied.'], 403);
        }

        $empType = strtolower(trim((string)($body['employment_type'] ?? 'full-time')));
        if (!in_array($empType, self::ALLOWED_EMPLOYMENT_TYPES, true)) {
            $empType = 'full-time';
        }

        $status = strtolower(trim((string)($body['status'] ?? 'active')));
        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            $status = 'active';
        }

        $data = [
            'title'           => trim((string)($body['title'] ?? '')),
            'description'     => trim((string)($body['description'] ?? '')),
            'requirements'    => trim((string)($body['requirements'] ?? '')),
            'location'        => trim((string)($body['location'] ?? '')),
            'salary'          => trim((string)($body['salary'] ?? '')),
            'employment_type' => $empType,
            'status'          => $status
        ];

        if (mb_strlen($data['title']) < 5) {
            $this->json(['success' => false, 'error' => 'Job title must be at least 5 characters long.'], 422);
        }
        if (mb_strlen($data['description']) < 50) {
            $this->json(['success' => false, 'error' => 'Job description must be at least 50 characters long to provide adequate detail for candidates.'], 422);
        }
        if ($data['requirements'] === '') {
            $this->json(['success' => false, 'error' => 'Please specify the key requirements and qualifications for this role.'], 422);
        }

        $currentJob = $jobModel->find($jobId);
        if ($currentJob) {
            $textChanged = ($currentJob['title'] !== $data['title'])
                || ($currentJob['description'] !== $data['description'])
                || ($currentJob['requirements'] !== $data['requirements']);

            if ($textChanged) {
                $data['ai_required_skills']      = null;
                $data['ai_required_years']       = null;
                $data['ai_job_years_variance']   = null;
                $data['ai_criteria_computed_at'] = null;
            }
        }

        $updated = $jobModel->updateJobSecure($tenantId, $jobId, $data);
        if ($updated) {
            SecurityLogger::logAlert("JOB_RECORD_UPDATED", $tenantId, "Job record modified (ID: $jobId).", 'INFO');
            $this->json([
                'success'  => true,
                'message'  => 'Job details updated successfully!',
                'redirect' => '/dashboard'
            ]);
        }

        $this->json(['success' => false, 'error' => 'Unable to update job posting.'], 500);
    }

    /**
     * POST /api/jobs/delete
     */
    public function delete(): void {
        $tenantId = $this->requireTenantAuth();
        $this->requireCsrf('CSRF_DELETE_VIOLATION');

        $body  = $this->getBody();
        $jobId = (int)($body['id'] ?? $body['job_id'] ?? 0);

        $jobModel = new Job();
        $deleted  = $jobModel->deleteJobSecure($tenantId, $jobId);

        if ($deleted) {
            SecurityLogger::logAlert("JOB_RECORD_DELETED", $tenantId, "Job deleted (ID: $jobId).", 'INFO');
            $this->json([
                'success' => true,
                'message' => 'Job posting has been removed successfully.'
            ]);
        }

        SecurityLogger::logAlert("UNAUTHORIZED_DELETE_ATTEMPT", $tenantId, "Attempted deletion on unauthorized job ID: $jobId");
        $this->json([
            'success' => false,
            'error'   => 'Unable to delete job posting: Record not found or access denied.'
        ], 403);
    }
}
