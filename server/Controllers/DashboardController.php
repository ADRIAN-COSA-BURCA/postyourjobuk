<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\AuditLogger;
use App\Models\Job;
use App\Models\Recruiter;

class DashboardController extends BaseController {

    /**
     * GET /api/dashboard
     * Returns recruiter workspace corporate profile, KPI stats, and tenant job postings
     */
    public function index(): void {
        $tenantId = $this->requireTenantAuth();

        $recruiterModel = new Recruiter();
        $freshTenant    = $recruiterModel->find($tenantId);
        if ($freshTenant) {
            unset($freshTenant['password_hash']);
            $_SESSION['tenant_profile'] = $freshTenant;
        }
        $tenantProfile = $_SESSION['tenant_profile'] ?? ['company_name' => 'Recruiter'];

        $jobModel = new Job();
        $stats    = $jobModel->getStats($tenantId);
        $jobs     = $jobModel->getTenantJobs($tenantId);

        $this->json([
            'success' => true,
            'tenant'  => $tenantProfile,
            'stats'   => [
                'total_jobs'       => (int)($stats['total_jobs'] ?? 0),
                'active_jobs'      => (int)($stats['active_jobs'] ?? 0),
                'inactive_jobs'    => (int)($stats['inactive_jobs'] ?? 0),
                'total_applicants' => (int)($stats['total_applicants'] ?? 0)
            ],
            'jobs'    => $jobs
        ]);
    }

    /**
     * POST /api/dashboard/profile
     * Updates the authenticated recruiter's corporate profile, email, recovery code, and optional password.
     * Immediately reflects across all job postings because jobs dynamically join with `tenants`.
     */
    public function updateProfile(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'Method not allowed.'], 405);
        }

        $tenantId = $this->requireTenantAuth();
        $this->requireCsrf('CSRF_RECRUITER_UPDATE_PROFILE');

        $body = $this->getBody();

        $companyName    = trim((string)($body['company_name'] ?? ''));
        $email          = trim((string)($body['email'] ?? ''));
        $contactPerson  = trim((string)($body['contact_person'] ?? ''));
        $phoneNumber    = trim((string)($body['phone_number'] ?? ''));
        $industry       = trim((string)($body['industry'] ?? ''));
        $websiteUrl     = trim((string)($body['website_url'] ?? ''));
        $companyAddress = trim((string)($body['company_address'] ?? ''));
        $recoveryCode   = strtoupper(trim((string)($body['recovery_code'] ?? '')));
        $newPassword    = (string)($body['new_password'] ?? '');

        if ($companyName === '' || $email === '') {
            $this->json(['success' => false, 'error' => 'Company Name and Email Address are required.'], 422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['success' => false, 'error' => 'Please enter a valid corporate email address.'], 422);
        }

        // Check email uniqueness against other tenants
        $stmtEmail = $this->db->prepare("SELECT tenant_id FROM tenants WHERE email = ? AND tenant_id != ? LIMIT 1");
        $stmtEmail->execute([$email, $tenantId]);
        if ($stmtEmail->fetch()) {
            $this->json(['success' => false, 'error' => 'That email address is already registered to another organization.'], 422);
        }

        $fields = [
            'company_name = :company_name',
            'email = :email',
            'contact_person = :contact_person',
            'phone_number = :phone_number',
            'industry = :industry',
            'website_url = :website_url',
            'company_address = :company_address',
            'updated_at = :updated_at'
        ];
        $params = [
            'company_name'    => $companyName,
            'email'           => $email,
            'contact_person'  => $contactPerson,
            'phone_number'    => $phoneNumber,
            'industry'        => $industry,
            'website_url'     => $websiteUrl,
            'company_address' => $companyAddress,
            'updated_at'      => date('Y-m-d H:i:s'),
            'tenant_id'       => $tenantId
        ];

        if ($recoveryCode !== '') {
            $fields[]                = 'recovery_code = :recovery_code';
            $params['recovery_code'] = $recoveryCode;
        }

        $passwordUpdated = false;
        if ($newPassword !== '') {
            if (mb_strlen($newPassword) < 8) {
                $this->json(['success' => false, 'error' => 'New password must be at least 8 characters long.'], 422);
            }
            $fields[]                = 'password_hash = :password_hash';
            $params['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
            $passwordUpdated         = true;
        }

        $sql  = "UPDATE tenants SET " . implode(', ', $fields) . " WHERE tenant_id = :tenant_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        // Refresh session profile so header and dashboard reflect changes immediately
        $recruiterModel = new Recruiter();
        $freshTenant    = $recruiterModel->find($tenantId);
        if ($freshTenant) {
            unset($freshTenant['password_hash']);
            $_SESSION['tenant_profile'] = $freshTenant;
        }

        AuditLogger::log('TENANT_PROFILE_SELF_UPDATED', $companyName, 'INFO', $tenantId, 'tenant', $tenantId, [
            'email'            => $email,
            'password_updated' => $passwordUpdated,
            'recovery_updated' => ($recoveryCode !== '')
        ]);

        $this->json([
            'success' => true,
            'message' => $passwordUpdated
                ? 'Corporate profile, contact details, and password updated successfully!'
                : 'Corporate profile and contact details updated successfully across all job postings!',
            'tenant'  => $_SESSION['tenant_profile']
        ]);
    }

    /**
     * GET /api/dashboard/analytics
     * Returns all 6 chart datasets + Top 5 Candidates leaderboard for Recruiter Analytics
     */
    public function analytics(): void {
        $tenantId      = $this->requireTenantAuth();
        $tenantProfile = $_SESSION['tenant_profile'] ?? ['company_name' => 'Recruiter'];

        $jobModel = new Job();

        $stats               = $jobModel->getStats($tenantId);
        $funnelData          = $jobModel->getApplicantFunnelData($tenantId);
        $trendData           = $jobModel->getTrendData($tenantId);
        $topJobs             = $jobModel->getTopPerformingJobs($tenantId, 6);
        $scoreDistribution   = $jobModel->getScoreDistributionData($tenantId);
        $employmentBreakdown = $jobModel->getEmploymentTypeBreakdown($tenantId);
        $topCandidates       = $jobModel->getTopCandidatesForTenant($tenantId, 5);

        $this->json([
            'success'              => true,
            'tenant'               => $tenantProfile,
            'stats'                => [
                'total_jobs'       => (int)($stats['total_jobs'] ?? 0),
                'active_jobs'      => (int)($stats['active_jobs'] ?? 0),
                'inactive_jobs'    => (int)($stats['inactive_jobs'] ?? 0),
                'total_applicants' => (int)($stats['total_applicants'] ?? 0)
            ],
            'funnel_data'          => $funnelData,
            'trend_data'           => $trendData,
            'top_jobs'             => $topJobs,
            'score_distribution'   => $scoreDistribution,
            'employment_breakdown' => $employmentBreakdown,
            'top_candidates'       => $topCandidates
        ]);
    }
}
