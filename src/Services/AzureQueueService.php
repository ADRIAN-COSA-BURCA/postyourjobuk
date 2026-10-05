<?php
namespace App\Services;

use MicrosoftAzure\Storage\Queue\QueueRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;

class AzureQueueService {
    private $queueClient;
    private $queueName = 'ai-processing-queue';

    public function __construct() {
        // 1. Try direct connection string first (Most reliable with Azure App Service / Key Vault)
        $connectionString = getenv('AZURE_STORAGE_CONNECTION_STRING') ?: ($_ENV['AZURE_STORAGE_CONNECTION_STRING'] ?? '');

        // 2. Fallback to manual assembly if connection string isn't explicitly set
        if (empty($connectionString)) {
            $accountName = getenv('AZURE_STORAGE_ACCOUNT_NAME') ?: ($_ENV['AZURE_STORAGE_ACCOUNT_NAME'] ?? '');
            $accountKey  = getenv('AZURE_STORAGE_ACCOUNT_KEY') ?: ($_ENV['AZURE_STORAGE_ACCOUNT_KEY'] ?? '');
            
            if (!empty($accountName) && !empty($accountKey)) {
                $connectionString = "DefaultEndpointsProtocol=https;AccountName={$accountName};AccountKey={$accountKey};EndpointSuffix=core.windows.net";
            }
        }

        if (!empty($connectionString)) {
            try {
                $this->queueClient = QueueRestProxy::createQueueService($connectionString);
            } catch (\Exception $e) {
                error_log("Azure Queue Initialization Exception: " . $e->getMessage());
            }
        } else {
            error_log("Azure Queue Error: No storage credentials found in environment.");
        }
    }

    /**
     * Securely pushes the candidate record pointer into the storage queue for background processing
     */
    public function dispatchJob(int $applicantId): bool {
        if (!$this->queueClient) {
            error_log("Azure Queue Error: Connection client uninitialized. Check storage connection string settings.");
            return false;
        }

        try {
            $payload = json_encode([
                'applicant_id' => $applicantId,
                'timestamp'    => time()
            ]);
            
            $this->queueClient->createMessage($this->queueName, $payload);
            return true;
        } catch (ServiceException $e) {
            error_log("Azure Queue Dispatch Failure [Code: " . $e->getCode() . "]: " . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            error_log("Azure Queue General Error: " . $e->getMessage());
            return false;
        }
    }
}