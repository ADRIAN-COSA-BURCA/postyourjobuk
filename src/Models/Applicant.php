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