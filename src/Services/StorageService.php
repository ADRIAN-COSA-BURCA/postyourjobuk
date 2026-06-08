<?php
namespace App\Services;

class StorageService {
    private $isBlobConfigured = false;

    public function __construct() {
        // Logic to check if Azure credentials exist in .env
        $this->isBlobConfigured = !empty($_ENV['AZURE_STORAGE_ACCOUNT_NAME']);
    }

    public function store($file, $tenantId, $jobId) {
        if ($this->isBlobConfigured) {
            // Logic to call your AzureBlobStorage class
            return "blob://" . $tenantId . "/" . $jobId . "/" . $file['name'];
        }
        // Fallback to local storage logic...
        return "local://" . $tenantId . "/" . $jobId . "/" . $file['name'];
    }

    public function delete($path) {
        // Logic to delete based on the prefix (blob:// or local://)
    }
}