<?php
namespace App\Controllers;

use App\Core\BaseController;
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
            unset($freshTenant['password_hash'], $freshTenant['recovery_code']);
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
