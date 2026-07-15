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
        // Count total non-admin tenants
        $totalStmt = $this->db->query("SELECT COUNT(*) FROM tenants WHERE is_super_admin = 0");
        $totalTenants = (int)$totalStmt->fetchColumn();

        // Count ONLY active tenants
        $activeStmt = $this->db->query("SELECT COUNT(*) FROM tenants WHERE is_super_admin = 0 AND status = 'active'");
        $activeTenants = (int)$activeStmt->fetchColumn();

        $jobStmt = $this->db->query("SELECT COUNT(*) FROM jobs");
        $totalJobs = (int)$jobStmt->fetchColumn();

        return [
            'total_tenants'    => $totalTenants,
            'active_tenants'   => $activeTenants, // Now correctly reflects the DB
            'total_jobs'       => $totalJobs
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
	
	public function getAnalyticsData(): array {
    try {
        // 1. Existing Stats
        $appStmt = $this->db->query("SELECT COUNT(*) FROM applicants");
        $totalApplications = (int)$appStmt->fetchColumn();

        $recentJobsStmt = $this->db->query("SELECT COUNT(*) FROM jobs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $recentJobs = (int)$recentJobsStmt->fetchColumn();

        $tenantsStmt = $this->db->query("SELECT COUNT(*) FROM tenants WHERE is_super_admin = 0");
        $totalTenants = (int)$tenantsStmt->fetchColumn();

        // 2. Growth Data
        $growthStmt = $this->db->query("
            SELECT DATE(created_at) as date, COUNT(job_id) as count 
            FROM jobs 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
            GROUP BY DATE(created_at) 
            ORDER BY date ASC
        ");
        $growthData = $growthStmt->fetchAll(\PDO::FETCH_ASSOC);

        // 3. FIX: Fetch Top Tenants Data
        $topTenantsStmt = $this->db->query("
            SELECT t.company_name, COUNT(j.job_id) as job_count 
            FROM tenants t
            LEFT JOIN jobs j ON t.tenant_id = j.tenant_id
            WHERE t.is_super_admin = 0
            GROUP BY t.tenant_id
            ORDER BY job_count DESC
            LIMIT 5
        ");
        $topTenantsData = $topTenantsStmt->fetchAll(\PDO::FETCH_ASSOC);

        // 4. Return array now contains the defined variable
        return [
            'total_applications' => $totalApplications,
            'recent_jobs' => $recentJobs,
            'total_tenants' => $totalTenants,
            'growth_chart' => $growthData,
            'top_tenants_chart' => $topTenantsData, // No longer undefined
            'pie_data' => [
                'jobs' => $recentJobs, 
                'applicants' => $totalApplications
            ]
        ];
    } catch (\PDOException $e) {
        error_log("Analytics Error: " . $e->getMessage());
        return [
            'total_applications' => 0, 
            'recent_jobs' => 0, 
            'total_tenants' => 0, 
            'growth_chart' => [], 
            'top_tenants_chart' => [], // Added missing key here for safety
            'pie_data' => ['jobs' => 0, 'applicants' => 0]
        ];
    }
}
}