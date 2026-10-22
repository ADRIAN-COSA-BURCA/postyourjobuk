<?php
namespace App\Models;

use App\Core\Database;

class Admin {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Fetch central system diagnostics and counters using native PDO
     */
    public function getSystemStats(): array {
        try {
            // Count total non-admin tenants (excluding synthetic audit tenant 9999)
            $totalStmt = $this->db->query("SELECT COUNT(*) FROM tenants WHERE is_super_admin = 0 AND tenant_id != 9999 AND deleted_at IS NULL");
            $totalTenants = (int)$totalStmt->fetchColumn();

            // Count ONLY active tenants
            $activeStmt = $this->db->query("SELECT COUNT(*) FROM tenants WHERE is_super_admin = 0 AND tenant_id != 9999 AND status = 'active' AND deleted_at IS NULL");
            $activeTenants = (int)$activeStmt->fetchColumn();

            $jobStmt = $this->db->query("SELECT COUNT(*) FROM jobs WHERE tenant_id != 9999");
            $totalJobs = (int)$jobStmt->fetchColumn();

            return [
                'total_tenants'  => $totalTenants,
                'active_tenants' => $activeTenants,
                'total_jobs'     => $totalJobs
            ];
        } catch (\PDOException $e) {
            error_log("Admin Model Stats Failure: " . $e->getMessage());
            return [
                'total_tenants'    => 0,
                'active_tenants'   => 0,
                'total_jobs'       => 0,
                'total_applicants' => 0
            ];
        }
    }

    /**
     * Comprehensive Platform-Wide Analytics for Super-Admin Dashboard (/admin/analytics)
     */
    public function getAnalyticsData(): array {
        try {
            // 1. Top 3 KPI Counters (Production tenants excluding synthetic fairness benchmark 9999)
            $appStmt = $this->db->query("SELECT COUNT(*) FROM applicants WHERE tenant_id != 9999");
            $totalApplications = (int)$appStmt->fetchColumn();

            $recentJobsStmt = $this->db->query("SELECT COUNT(*) FROM jobs WHERE tenant_id != 9999 AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $recentJobs = (int)$recentJobsStmt->fetchColumn();
            if ($recentJobs === 0) {
                $allJobsStmt = $this->db->query("SELECT COUNT(*) FROM jobs WHERE tenant_id != 9999");
                $recentJobs = (int)$allJobsStmt->fetchColumn();
            }

            $tenantsStmt = $this->db->query("SELECT COUNT(*) FROM tenants WHERE is_super_admin = 0 AND tenant_id != 9999 AND status = 'active' AND deleted_at IS NULL");
            $activeTenants = (int)$tenantsStmt->fetchColumn();

            // 2. Top Tenants by Activity (Jobs, Applicants, Avg AI Score, Hired/Interviewing)
            $topTenantsStmt = $this->db->query("
                SELECT 
                    t.tenant_id,
                    t.company_name,
                    COALESCE(NULLIF(TRIM(t.industry), ''), 'General Recruitment') AS industry,
                    t.status,
                    COUNT(DISTINCT j.job_id) AS job_count,
                    SUM(CASE WHEN j.status = 'open' THEN 1 ELSE 0 END) AS active_jobs,
                    COUNT(DISTINCT a.applicant_id) AS applicant_count,
                    ROUND(AVG(CASE WHEN a.ai_score IS NOT NULL AND a.ai_score > 0 THEN a.ai_score ELSE NULL END), 1) AS avg_ai_score,
                    SUM(CASE WHEN LOWER(a.status) = 'interviewing' THEN 1 ELSE 0 END) AS interviewing_count,
                    SUM(CASE WHEN LOWER(a.status) = 'hired' THEN 1 ELSE 0 END) AS hired_count
                FROM tenants t
                LEFT JOIN jobs j ON t.tenant_id = j.tenant_id
                LEFT JOIN applicants a ON t.tenant_id = a.tenant_id
                WHERE t.is_super_admin = 0 AND t.tenant_id != 9999 AND t.deleted_at IS NULL
                GROUP BY t.tenant_id, t.company_name, t.industry, t.status
                ORDER BY applicant_count DESC, job_count DESC
                LIMIT 8
            ");
            $topTenantsData = $topTenantsStmt->fetchAll(\PDO::FETCH_ASSOC);

            // 3. Dual-Curve Platform Velocity (Jobs Created vs. Applications Submitted by Date)
            $dateUnionStmt = $this->db->query("
                SELECT activity_date,
                       SUM(jobs_created) AS jobs_count,
                       SUM(apps_submitted) AS apps_count
                FROM (
                    SELECT DATE(created_at) AS activity_date, COUNT(job_id) AS jobs_created, 0 AS apps_submitted
                    FROM jobs
                    WHERE tenant_id != 9999
                    GROUP BY DATE(created_at)
                    UNION ALL
                    SELECT DATE(created_at) AS activity_date, 0 AS jobs_created, COUNT(applicant_id) AS apps_submitted
                    FROM applicants
                    WHERE tenant_id != 9999
                    GROUP BY DATE(created_at)
                ) combined
                WHERE activity_date IS NOT NULL
                GROUP BY activity_date
                ORDER BY activity_date DESC
                LIMIT 8
            ");
            $velocityRows = array_reverse($dateUnionStmt->fetchAll(\PDO::FETCH_ASSOC));
            $velocityLabels = [];
            $velocityJobs   = [];
            $velocityApps   = [];
            foreach ($velocityRows as $r) {
                $ts = strtotime($r['activity_date']);
                $velocityLabels[] = $ts ? date('M d', $ts) : $r['activity_date'];
                $velocityJobs[]   = (int)$r['jobs_count'];
                $velocityApps[]   = (int)$r['apps_count'];
            }

            // 4. Global Platform ATS Funnel (All Production Tenants)
            $funnelStmt = $this->db->query("
                SELECT LOWER(status) AS stage, COUNT(*) AS count
                FROM applicants
                WHERE tenant_id != 9999
                GROUP BY LOWER(status)
            ");
            $rawFunnel = $funnelStmt->fetchAll(\PDO::FETCH_KEY_PAIR);
            $globalFunnel = [
                'reviewed'     => (int)($rawFunnel['reviewed'] ?? 0) + (int)($rawFunnel['new'] ?? 0) + (int)($rawFunnel['pending'] ?? 0),
                'interviewing' => (int)($rawFunnel['interviewing'] ?? 0),
                'hired'        => (int)($rawFunnel['hired'] ?? 0),
                'rejected'     => (int)($rawFunnel['rejected'] ?? 0)
            ];

            // 5. AI CV Processing Engine & Queue Health (Includes All Evaluations)
            $aiHealthStmt = $this->db->query("
                SELECT
                    SUM(CASE WHEN ai_score IS NOT NULL AND status NOT IN ('pending', 'processing', 'processing_failed') THEN 1 ELSE 0 END) AS scored_complete,
                    SUM(CASE WHEN status IN ('pending', 'processing') OR (ai_score IS NULL AND status != 'processing_failed') THEN 1 ELSE 0 END) AS queued_pending,
                    SUM(CASE WHEN status = 'processing_failed' THEN 1 ELSE 0 END) AS failed_count,
                    ROUND(AVG(CASE WHEN ai_score IS NOT NULL AND ai_score > 0 THEN ai_score ELSE NULL END), 1) AS platform_avg_score,
                    COUNT(*) AS total_evaluations
                FROM applicants
                WHERE tenant_id != 9999
            ");
            $aiHealth = $aiHealthStmt->fetch(\PDO::FETCH_ASSOC) ?: [];
            $scoredComplete = (int)($aiHealth['scored_complete'] ?? 0);
            $queuedPending  = (int)($aiHealth['queued_pending'] ?? 0);
            $failedCount    = (int)($aiHealth['failed_count'] ?? 0);
            $totalEvals     = max(1, $scoredComplete + $queuedPending + $failedCount);
            $aiSuccessRate  = round(($scoredComplete / $totalEvals) * 100, 1);

            // 6. Tenant Industry Sector Breakdown
            $industryStmt = $this->db->query("
                SELECT COALESCE(NULLIF(TRIM(industry), ''), 'Technology & General') AS sector,
                       COUNT(*) AS count
                FROM tenants
                WHERE is_super_admin = 0 AND tenant_id != 9999 AND deleted_at IS NULL
                GROUP BY sector
                ORDER BY count DESC
                LIMIT 6
            ");
            $industryRows = $industryStmt->fetchAll(\PDO::FETCH_ASSOC);

            // 7. System Audit & Security Telemetry (system_logs by Severity)
            $securityStmt = $this->db->query("
                SELECT UPPER(COALESCE(severity, 'INFO')) AS sev, COUNT(*) AS count
                FROM system_logs
                GROUP BY UPPER(COALESCE(severity, 'INFO'))
            ");
            $rawSecurity = $securityStmt->fetchAll(\PDO::FETCH_KEY_PAIR);
            $securityLogs = [
                'INFO'     => (int)($rawSecurity['INFO'] ?? 0),
                'WARNING'  => (int)($rawSecurity['WARNING'] ?? 0),
                'CRITICAL' => (int)($rawSecurity['CRITICAL'] ?? 0)
            ];

            return [
                'total_applications' => $totalApplications,
                'recent_jobs'        => $recentJobs,
                'total_tenants'      => $activeTenants,
                'top_tenants_chart'  => $topTenantsData,
                'velocity_chart'     => [
                    'labels' => $velocityLabels,
                    'jobs'   => $velocityJobs,
                    'apps'   => $velocityApps
                ],
                'global_funnel'      => $globalFunnel,
                'ai_engine'          => [
                    'scored_complete'    => $scoredComplete,
                    'queued_pending'     => $queuedPending,
                    'failed_count'       => $failedCount,
                    'platform_avg_score' => (float)($aiHealth['platform_avg_score'] ?? 0),
                    'success_rate'       => $aiSuccessRate
                ],
                'industry_chart'     => [
                    'labels' => array_column($industryRows, 'sector'),
                    'data'   => array_map('intval', array_column($industryRows, 'count'))
                ],
                'security_telemetry' => $securityLogs
            ];
        } catch (\PDOException $e) {
            error_log("Admin Analytics Error: " . $e->getMessage());
            return [
                'total_applications' => 0,
                'recent_jobs'        => 0,
                'total_tenants'      => 0,
                'top_tenants_chart'  => [],
                'velocity_chart'     => ['labels' => [], 'jobs' => [], 'apps' => []],
                'global_funnel'      => ['reviewed' => 0, 'interviewing' => 0, 'hired' => 0, 'rejected' => 0],
                'ai_engine'          => ['scored_complete' => 0, 'queued_pending' => 0, 'failed_count' => 0, 'platform_avg_score' => 0, 'success_rate' => 100],
                'industry_chart'     => ['labels' => [], 'data' => []],
                'security_telemetry' => ['INFO' => 0, 'WARNING' => 0, 'CRITICAL' => 0]
            ];
        }
    }
}
