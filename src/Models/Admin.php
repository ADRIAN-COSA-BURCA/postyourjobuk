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
            // Count total active tenants/workspaces
            $tenantStmt = $this->db->query("SELECT COUNT(*) FROM tenants WHERE is_super_admin = 0");
            $totalTenants = (int)$tenantStmt->fetchColumn();

            // Count total jobs listed globally across all tenants
            $jobStmt = $this->db->query("SELECT COUNT(*) FROM jobs");
            $totalJobs = (int)$jobStmt->fetchColumn();

            // Count total applicants across all job posts
            $applicantStmt = $this->db->query("SELECT COUNT(*) FROM applicants");
            $totalApplicants = (int)$applicantStmt->fetchColumn();

            return [
                'total_tenants'    => $totalTenants,
                'active_tenants'   => $totalTenants, // Added to fix the dashboard view lookup warning
                'total_jobs'       => $totalJobs,
                'total_applicants' => $totalApplicants
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
}