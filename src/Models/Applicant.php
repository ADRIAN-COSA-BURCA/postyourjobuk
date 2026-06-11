<?php
namespace App\Models;

use App\Core\BaseModel;

class Applicant extends BaseModel {
    protected $table = 'applicants';
    protected $primaryKey = 'applicant_id';

    public function createApplication(array $data) {
        return $this->create($data);
    }

    public function getJobApplicants(int $tenantId, int $jobId, string $sortBy = 'ai_score', string $order = 'DESC'): array {
        $sql = "SELECT * FROM {$this->table} WHERE tenant_id = ? AND job_id = ? ORDER BY {$sortBy} {$order}";
        return $this->db->fetchAll($sql, [$tenantId, $jobId]);
    }

    // ADD THIS METHOD TO FIX THE ERROR
    public function getJobStats(int $tenantId, int $jobId): array {
        $sql = "SELECT 
                    COUNT(*) as total_applicants,
                    IFNULL(AVG(ai_score), 0) as average_score,
                    SUM(CASE WHEN ai_score >= 80 THEN 1 ELSE 0 END) as strong_matches,
                    SUM(CASE WHEN ai_score >= 50 AND ai_score < 80 THEN 1 ELSE 0 END) as moderate_matches
                FROM {$this->table} 
                WHERE tenant_id = ? AND job_id = ?";
        
        $result = $this->db->fetchOne($sql, [$tenantId, $jobId]);
        return $result ?: ['total_applicants' => 0, 'average_score' => 0, 'strong_matches' => 0, 'moderate_matches' => 0];
    }
}