<?php
namespace App\Models;

use App\Core\BaseModel;
use Exception;
use Throwable;

class Applicant extends BaseModel {
    protected $table = 'applicants';

    // Refactored store path logic: Uses dependency injection of the storage service
    public function createApplication(array $data, array $cvFile) {
        $this->validateFile($cvFile);
        
        // This keeps logic clean: The model focuses on the database, 
        // while the Storage service handles the Azure/Local distinction.
        $storagePath = $this->container->get('storage')->store($cvFile, $data['tenant_id'], $data['job_id']);
        
        $data['cv_storage_path'] = $storagePath;
        return $this->create($data);
    }

    public function deleteWithCVIsolated(int $tenantId, int $applicantId): bool {
        $applicant = $this->getApplicantSecure($tenantId, $applicantId);
        if (!$applicant) return false;

        $this->db->beginTransaction();
        try {
            // Delete child records...
            $this->db->executeQuery("DELETE FROM applicant_ai_analysis WHERE applicant_id = ?", [$applicantId]);
            $this->db->executeQuery("DELETE FROM {$this->table} WHERE applicant_id = ? AND tenant_id = ?", [$applicantId, $tenantId]);
            
            $this->db->commit();
            
            // Trigger storage cleanup
            $this->container->get('storage')->delete($applicant['cv_storage_path']);
            return true;
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}