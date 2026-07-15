<?php
namespace App\Models;

use App\Core\BaseModel;

class Applicant extends BaseModel {
    protected $table = 'applicants';
    protected $primaryKey = 'applicant_id';

    /**
     * Legacy support for createApplication
     */
    public function createApplication(array $data) {
        return $this->create($data);
    }

    /**
     * Ensure we always sanitize the data before it hits the DB
     */
    protected function beforeCreate(array &$data) {
        // Ensure standard fields are always present
        if (!isset($data['status'])) {
            $data['status'] = 'new';
        }
        
        // Ensure ai_score defaults to null if not provided, 
        // to avoid DB insert errors on strict SQL modes
        if (!isset($data['ai_score'])) {
            $data['ai_score'] = null;
        }
    }
	
	/**
     * Securely deletes an applicant and returns their CV storage path
     */
    public function deleteSecure(int $tenantId, int $applicantId): ?string {
        // First, fetch the storage path, ensuring the tenant actually owns this record
        $sql = "SELECT cv_storage_path FROM {$this->table} WHERE applicant_id = ? AND tenant_id = ?";
        $path = $this->db->fetchColumn($sql, [$applicantId, $tenantId]);

        // If the query returns a string (even an empty one), the tenant is authorized
        if ($path !== false) {
            $deleteSql = "DELETE FROM {$this->table} WHERE applicant_id = ? AND tenant_id = ?";
            $this->db->query($deleteSql, [$applicantId, $tenantId]);
            return (string)$path;
        }
        
        return null; // Unauthorized or record not found
    }
	
	
	/**
     * Securely deletes ALL applicants for a specific job and returns their CV paths
     */
    public function deleteAllForJobSecure(int $tenantId, int $jobId): array {
        // 1. Fetch all storage paths first so we know what to wipe from the cloud
        $sql = "SELECT cv_storage_path FROM {$this->table} WHERE tenant_id = ? AND job_id = ?";
        $paths = $this->db->fetchAll($sql, [$tenantId, $jobId]);
        
        // 2. Wipe the database records
        $deleteSql = "DELETE FROM {$this->table} WHERE tenant_id = ? AND job_id = ?";
        $this->db->query($deleteSql, [$tenantId, $jobId]);
        
        // 3. Flatten the array to just return a simple list of path strings
        return array_column($paths, 'cv_storage_path');
    }

    public function getJobApplicants(int $tenantId, int $jobId, string $sortBy = 'ai_score', string $order = 'DESC'): array {
        // Validate sort column to prevent SQL injection in the ORDER BY clause
        $allowedSorts = ['ai_score', 'applied_at', 'name'];
        $sortBy = in_array($sortBy, $allowedSorts) ? $sortBy : 'ai_score';
        $order = (strtoupper($order) === 'ASC') ? 'ASC' : 'DESC';

        $sql = "SELECT * FROM {$this->table} WHERE tenant_id = ? AND job_id = ? ORDER BY {$sortBy} {$order}";
        return $this->db->fetchAll($sql, [$tenantId, $jobId]);
    }

    public function getJobStats(int $tenantId, int $jobId): array {
        $sql = "SELECT 
                    COUNT(*) as total_applicants,
                    IFNULL(AVG(ai_score), 0) as average_score,
                    SUM(CASE WHEN ai_score >= 80 THEN 1 ELSE 0 END) as strong_matches,
                    SUM(CASE WHEN ai_score >= 50 AND ai_score < 80 THEN 1 ELSE 0 END) as moderate_matches
                FROM {$this->table} 
                WHERE tenant_id = ? AND job_id = ?";
        
        $result = $this->db->fetchOne($sql, [$tenantId, $jobId]);
        
        // Ensure we always return an array even if the result is empty
        return $result ?: [
            'total_applicants' => 0, 
            'average_score' => 0, 
            'strong_matches' => 0, 
            'moderate_matches' => 0
        ];
    }
}