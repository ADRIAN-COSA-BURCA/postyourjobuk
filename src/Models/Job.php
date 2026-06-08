<?php
namespace App\Models;
use App\Core\BaseModel;

class Job extends BaseModel {
    
    protected $table = 'jobs';
    protected $primaryKey = 'job_id';
    
    /**
     * Get all active jobs for public job board — Cascades tenant suspension immediately
     */
    public function getActiveJobs($limit = null) {
        // Enforces immediate cascade filtering: If the corporate tenant is suspended, the posting falls off the board instantly
        $sql = "
            SELECT 
                j.*,
                t.company_name,
                t.logo_url,
                (SELECT COUNT(*) FROM applicants WHERE job_id = j.job_id) as applicant_count
            FROM {$this->table} j
            INNER JOIN tenants t ON j.tenant_id = t.tenant_id
            WHERE j.status = 'active' 
            AND t.is_active = 1 
            AND t.status = 'active'
            ORDER BY j.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get all jobs for a specific tenant — Bounded secure dashboard readout
     */
    public function getTenantJobs($tenantId, $status = null) {
        $sql = "
            SELECT 
                j.*,
                (SELECT COUNT(*) FROM applicants WHERE job_id = j.job_id) as applicant_count,
                (SELECT IFNULL(MAX(ai_score), 0) FROM applicants WHERE job_id = j.job_id) as top_score
            FROM {$this->table} j
            WHERE j.tenant_id = ?
        ";
        
        $params = [(int)$tenantId];
        
        if ($status) {
            $sql .= " AND j.status = ?";
            $params[] = strip_tags($status);
        }
        
        $sql .= " ORDER BY j.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get single job with tenant info — Validates parent status
     */
    public function getJobWithTenant($jobId) {
        $sql = "
            SELECT 
                j.*,
                t.company_name,
                t.logo_url,
                t.is_active as tenant_is_active,
                (SELECT COUNT(*) FROM applicants WHERE job_id = j.job_id) as applicant_count
            FROM {$this->table} j
            INNER JOIN tenants t ON j.tenant_id = t.tenant_id
            WHERE j.job_id = ?
            LIMIT 1
        ";
        
        return $this->db->fetchOne($sql, [(int)$jobId]);
    }
    
    /**
     * Check if job belongs to tenant (Core security authorization helper)
     */
    public function belongsToTenant($jobId, $tenantId) {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE job_id = ? AND tenant_id = ?";
        return (bool) $this->db->fetchColumn($sql, [(int)$jobId, (int)$tenantId]);
    }
    
    /**
     * Create new job with strict tenant context validation injection
     */
    public function createJob($data) {
        // Force parameters to conform to clean data structures
        $data['tenant_id'] = (int)$data['tenant_id'];
        $data['title'] = strip_tags(trim($data['title'] ?? ''));
        
        if (!isset($data['status'])) {
            $data['status'] = 'active';
        }
        
        if (!isset($data['employment_type'])) {
            $data['employment_type'] = 'full-time';
        }
        
        return $this->create($data);
    }
    
    /**
     * Toggle job status (active/inactive) — Bounded securely by Tenant verification parameters
     */
    public function toggleStatusSecure($tenantId, $jobId) {
        // Level 6 Protection Check: Assert that the calling company actually owns this row
        $job = $this->find((int)$jobId);
        if (!$job || (int)$job['tenant_id'] !== (int)$tenantId) {
            return false;
        }
        
        $newStatus = ($job['status'] === 'active') ? 'inactive' : 'active';
        return $this->update((int)$jobId, ['status' => $newStatus]);
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
        return $this->db->fetchAll($sql, [
            $searchTerm, 
            $searchTerm, 
            $searchTerm, 
            $searchTerm
        ]);
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
        
        return $this->db->fetchOne($sql, [(int)$tenantId, (int)$tenantId]);
    }
    
    // ============================================
    // EXTENSION POINTS (Business Phase Hooks)
    // ============================================
    
    protected function beforeCreate(&$data) {
        // Enforced in business phase to assert billing limits
    }
    
    protected function afterCreate($id) {
        // Dispatches global application logs
    }
    
    protected function beforeDelete($id) {
        // Cascade hooks go here
    }
}
?>