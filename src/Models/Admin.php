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
}