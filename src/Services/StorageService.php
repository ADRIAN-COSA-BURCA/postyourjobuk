<?php
namespace App\Services;

use RuntimeException;

class StorageService {
    private AzureBlobStorage $azureStorage;
    private bool $isBlobConfigured = false;
    private string $localUploadDir;

    public function __construct() {
        // Instantiate our Azure Worker
        $this->azureStorage = new AzureBlobStorage();
        
        // Check if the worker says Azure is ready to go
        $this->isBlobConfigured = $this->azureStorage->isConfigured();
        
        // Set a fallback local directory inside the Docker container
        $this->localUploadDir = '/var/www/html/uploads/cvs/';
    }

    /**
     * Master method to store an uploaded CV file.
     */
    public function store(array $file, int $tenantId, int $jobId): string {
        // Validation: Did an actual file upload occur?
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException("No valid file provided for storage.");
        }

        // 1. PRIMARY STRATEGY: Try Azure Blob Storage
        if ($this->isBlobConfigured) {
            try {
                // Generate unique blob name
                $uniqueName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($file['name']));
                $blobName = $this->azureStorage->buildApplicantBlobName($tenantId, $jobId, $uniqueName);
                
                // Hand the temporary file to the Azure worker
                $result = $this->azureStorage->uploadFile($file['tmp_name'], $blobName, $file['type']);
                
                return $result['storage_path'];
            } catch (\Exception $e) {
                error_log("Azure Upload Failed: " . $e->getMessage() . ". Falling back to local storage.");
            }
        }

        // 2. FALLBACK STRATEGY: Local Docker File System
        return $this->storeLocal($file, $tenantId, $jobId);
    }

    /**
     * Master method to delete a CV file.
     */
    public function delete(string $path): bool {
        if (str_starts_with($path, 'blob://')) {
            if ($this->isBlobConfigured) {
                return $this->azureStorage->deleteBlob($path);
            }
            return false;
        }

        $realPath = str_replace('local://', '', $path);
        if (file_exists($realPath)) {
            return unlink($realPath);
        }
        
        return false;
    }

    /**
     * Internal helper to handle local file saves.
     */
    private function storeLocal(array $file, int $tenantId, int $jobId): string {
        $dir = $this->localUploadDir . "tenant-{$tenantId}/job-{$jobId}/";
        
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
                throw new RuntimeException("Failed to create directory: $dir");
            }
        }
        
        // ADDED: Timestamp prefix to ensure uniqueness and prevent overwriting
        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($file['name']));
        $uniqueName = time() . '_' . $safeName;
        $destination = $dir . $uniqueName;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return "local://" . $destination;
        }
        
        throw new RuntimeException("Local file system upload failed. Ensure /uploads/cvs is writable.");
    }
}