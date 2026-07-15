<?php
namespace App\Models;

use App\Core\BaseModel;
use PDO;

class Job extends BaseModel {
    
    protected $table = 'jobs';
    protected $primaryKey = 'job_id';

    /**
     * Get all active jobs for public job board.
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

    public function createJob(array $data): ?int {
        $data['status'] = $data['status'] ?? 'active';
        $data['employment_type'] = $data['employment_type'] ?? 'full-time';
        
        return $this->create($data);
    }

    /**
     * Update a job securely, ensuring it belongs to the tenant.
     */
    public function updateJobSecure(int $tenantId, int $jobId, array $data): bool {
        if (!$this->belongsToTenant($jobId, $tenantId)) {
            return false;
        }
        return $this->update($jobId, $data);
    }

    /**
     * Delete a job securely, ensuring it belongs to the tenant.
     */
    public function deleteJobSecure(int $tenantId, int $jobId): bool {
        if (!$this->belongsToTenant($jobId, $tenantId)) {
            return false;
        }
        return $this->delete($jobId);
    }
    
    public function toggleStatusSecure(int $tenantId, int $jobId): bool {
        $job = $this->find($jobId);
        if (!$job || (int)$job['tenant_id'] !== $tenantId) {
            return false;
        }
        
        $newStatus = ($job['status'] === 'active') ? 'inactive' : 'active';
        return $this->update($jobId, ['status' => $newStatus]);
    }
    
    public function search($keyword, $limit = 20) {
        $trimmed = trim((string)$keyword);
        if ($trimmed === '') {
            return [];
        }

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
        
        $searchTerm = '%' . $trimmed . '%';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
    
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
    
    /**
     * Get a single job by ID with its associated tenant details.
     */
    public function getJobWithTenant(int $jobId) {
    // We update the SELECT clause to pull all the new profile fields from 't' (tenants)
    $sql = "
        SELECT 
            j.*, 
            t.company_name, 
            t.contact_person, 
            t.email as company_email, 
            t.phone_number, 
            t.website_url, 
            t.industry, 
            t.company_address 
        FROM jobs j
        JOIN tenants t ON j.tenant_id = t.tenant_id
        WHERE j.job_id = ?
    ";
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$jobId]);
    return $stmt->fetch();
}
    
    public function getPostCountLastHour(int $tenantId): int {
        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE tenant_id = ? 
                AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Security check: Verify if a job belongs to a specific tenant.
     */
    public function belongsToTenant(int $jobId, int $tenantId): bool {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE job_id = ? AND tenant_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$jobId, $tenantId]);
        return (int)$stmt->fetchColumn() > 0;
    }
    
    protected function beforeCreate(&$data) {}
    protected function afterCreate($id) {}
    protected function beforeDelete($id) {}
}