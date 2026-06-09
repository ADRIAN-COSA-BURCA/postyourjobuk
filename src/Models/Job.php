<?php
namespace App\Models;

use App\Core\BaseModel;
use PDO;

class Job extends BaseModel {
    
    protected $table = 'jobs';
    protected $primaryKey = 'job_id';

    /**
     * Get all active jobs for public job board.
     * Note: Tenant status is validated via JOINs for security.
     */
    public function getActiveJobs(?int $limit = null): array {
        $sql = "
            SELECT j.*, t.company_name, t.logo_url,
                   (SELECT COUNT(*) FROM applicants WHERE job_id = j.job_id) as applicant_count
            FROM {$this->table} j
            INNER JOIN tenants t ON j.tenant_id = t.tenant_id
            WHERE j.status = 'active' 
            AND t.is_active = 1 
            AND t.status = 'active'
            ORDER BY j.created_at DESC
        ";
        
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get all jobs for a specific tenant.
     */
    public function getTenantJobs(int $tenantId, ?string $status = null): array {
        $sql = "
            SELECT j.*, 
                   (SELECT COUNT(*) FROM applicants WHERE job_id = j.job_id) as applicant_count,
                   (SELECT IFNULL(MAX(ai_score), 0) FROM applicants WHERE job_id = j.job_id) as top_score
            FROM {$this->table} j
            WHERE j.tenant_id = ?
        ";
        
        $params = [$tenantId];
        
        if ($status) {
            $sql .= " AND j.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY j.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Create new job.
     * Refactored to assume data is already validated by the Controller.
     */
    public function createJob(array $data): ?int {
        $data['status'] = $data['status'] ?? 'active';
        $data['employment_type'] = $data['employment_type'] ?? 'full-time';
        
        return $this->create($data);
    }
    
    /**
     * Toggle status securely by verifying ownership (Zero-Trust Pattern).
     */
    public function toggleStatusSecure(int $tenantId, int $jobId): bool {
        $job = $this->find($jobId);
        if (!$job || (int)$job['tenant_id'] !== $tenantId) {
            return false;
        }
        
        $newStatus = ($job['status'] === 'active') ? 'inactive' : 'active';
        return $this->update($jobId, ['status' => $newStatus]);
    }
    
    /**
     * Search active jobs by keyword — Hardened with Cascade Protection rules
     */
    public function search($keyword, $limit = 20) {
        $cleanLimit = (int)$limit;
        
        $sql = "
            SELECT 
                j.*,
                t.company_name,
                t.logo_url
            FROM {$this->table} j
            INNER JOIN tenants t ON j.tenant_id = t.tenant_id
            WHERE j.status = 'active'
            AND t.is_active = 1
            AND t.status = 'active'
            AND (
                j.title LIKE ?
                OR j.description LIKE ?
                OR j.requirements LIKE ?
                OR j.location LIKE ?
            )
            ORDER BY j.created_at DESC
            LIMIT {$cleanLimit}
        ";
        
        $searchTerm = '%' . $keyword . '%';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get job statistics for tenant
     */
    public function getStats($tenantId) {
        $sql = "
            SELECT 
                COUNT(*) as total_jobs,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_jobs,
                SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_jobs,
                (SELECT COUNT(*) FROM applicants WHERE tenant_id = ?) as total_applicants
            FROM {$this->table}
            WHERE tenant_id = ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$tenantId, (int)$tenantId]);
        $row = $stmt->fetch();
        return $row ? $row : ['total_jobs' => 0, 'active_jobs' => 0, 'inactive_jobs' => 0, 'total_applicants' => 0];
    }
	
    public function getPostCountLastHour(int $tenantId): int {
        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE tenant_id = ? 
                AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return (int)$stmt->fetchColumn();
    }
    
    protected function beforeCreate(&$data) {}
    protected function afterCreate($id) {}
    protected function beforeDelete($id) {}
}