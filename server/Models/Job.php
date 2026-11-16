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
            SELECT j.*, t.company_name, t.logo_url, t.industry,
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
                t.logo_url,
                t.industry
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
                OR t.company_name LIKE ?
                OR t.industry LIKE ?
            )
            ORDER BY j.created_at DESC
            LIMIT {$cleanLimit}
        ";
        
        $searchTerm = '%' . $trimmed . '%';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
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
     * Get applicant counts grouped by status for the Funnel Chart
     */
    public function getApplicantFunnelData(int $tenantId): array {
        $sql = "
            SELECT 
                status, 
                COUNT(*) as count 
            FROM applicants 
            WHERE tenant_id = ? 
            GROUP BY status
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        
        $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Map raw DB statuses cleanly into the 4 ATS funnel stages matching Chart.js colors:
        // ['#6366f1' (Reviewed), '#f59e0b' (Interviewing), '#10b981' (Hired), '#ef4444' (Rejected)]
        $funnel = [
            'reviewed'     => 0,
            'interviewing' => 0,
            'hired'        => 0,
            'rejected'     => 0
        ];

        foreach ($results as $statusKey => $cnt) {
            $s = strtolower(trim((string)$statusKey));
            $countVal = (int)$cnt;
            if ($s === 'interviewing') {
                $funnel['interviewing'] += $countVal;
            } elseif ($s === 'hired' || $s === 'offered') {
                $funnel['hired'] += $countVal;
            } elseif ($s === 'rejected') {
                $funnel['rejected'] += $countVal;
            } else {
                // 'new', 'pending', 'reviewed', 'applied', etc.
                $funnel['reviewed'] += $countVal;
            }
        }

        return $funnel;
    }

    public function getTrendData(int $tenantId): array {
        $sql = "
            SELECT DATE(created_at) as date, COUNT(*) as count 
            FROM applicants 
            WHERE tenant_id = ? 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // If fewer than 2 days have activity in the last 7 calendar days, fallback to the last 7 active application dates
        if (count($results) < 2) {
            $fallbackSql = "
                SELECT date, count FROM (
                    SELECT DATE(created_at) as date, COUNT(*) as count 
                    FROM applicants 
                    WHERE tenant_id = ? 
                    GROUP BY DATE(created_at)
                    ORDER BY date DESC
                    LIMIT 7
                ) sub ORDER BY date ASC
            ";
            $stmtFb = $this->db->prepare($fallbackSql);
            $stmtFb->execute([$tenantId]);
            $fbResults = $stmtFb->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($fbResults)) {
                $results = $fbResults;
            }
        }

        // Format for Chart.js
        $labels = [];
        $data = [];
        foreach ($results as $row) {
            $labels[] = date('M d', strtotime($row['date']));
            $data[] = (int)$row['count'];
        }
        
        return ['labels' => $labels, 'data' => $data];
    }

    public function getTopPerformingJobs(int $tenantId, int $limit = 6): array {
        $sql = "
            SELECT 
                j.title, 
                COUNT(a.applicant_id) as applicant_count,
                ROUND(IFNULL(AVG(a.ai_score), 0), 1) as avg_score,
                IFNULL(MAX(a.ai_score), 0) as max_score
            FROM jobs j
            LEFT JOIN applicants a ON j.job_id = a.job_id
            WHERE j.tenant_id = ?
            GROUP BY j.job_id, j.title
            ORDER BY applicant_count DESC, j.job_id DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get AI Match Score tier distribution for Recruiter Analytics Pie Chart
     */
    public function getScoreDistributionData(int $tenantId): array {
        $sql = "
            SELECT 
                SUM(CASE WHEN ai_score >= 80 THEN 1 ELSE 0 END) as strong_match,
                SUM(CASE WHEN ai_score >= 50 AND ai_score < 80 THEN 1 ELSE 0 END) as moderate_match,
                SUM(CASE WHEN ai_score IS NOT NULL AND ai_score < 50 THEN 1 ELSE 0 END) as low_match
            FROM applicants
            WHERE tenant_id = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'Strong Match (≥80%)'    => (int)($row['strong_match'] ?? 0),
            'Qualified (50%–79%)'    => (int)($row['moderate_match'] ?? 0),
            'Low Match (<50%)'       => (int)($row['low_match'] ?? 0),
        ];
    }

    /**
     * Get application volume grouped by Job Employment Type for Recruiter Analytics
     */
    public function getEmploymentTypeBreakdown(int $tenantId): array {
        $sql = "
            SELECT 
                COALESCE(NULLIF(TRIM(j.employment_type), ''), 'Full-time') as emp_type,
                COUNT(a.applicant_id) as app_count,
                COUNT(DISTINCT j.job_id) as job_count
            FROM jobs j
            LEFT JOIN applicants a ON j.job_id = a.job_id
            WHERE j.tenant_id = ?
            GROUP BY emp_type
            ORDER BY app_count DESC, job_count DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $labels = [];
        $data = [];
        foreach ($rows as $r) {
            $labels[] = ucfirst(str_replace('-', ' ', $r['emp_type']));
            // Use application count if > 0, else job count so new recruiters still see their portfolio breakdown
            $val = (int)$r['app_count'] > 0 ? (int)$r['app_count'] : (int)$r['job_count'];
            $data[] = $val;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Get top highest-scoring candidates across all jobs for the tenant
     */
    public function getTopCandidatesForTenant(int $tenantId, int $limit = 5): array {
        $sql = "
            SELECT 
                a.applicant_id,
                a.name,
                a.email,
                a.ai_score,
                a.status,
                a.created_at,
                a.job_id,
                j.title as job_title
            FROM applicants a
            INNER JOIN jobs j ON a.job_id = j.job_id
            WHERE a.tenant_id = ?
            ORDER BY a.ai_score DESC, a.created_at DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
