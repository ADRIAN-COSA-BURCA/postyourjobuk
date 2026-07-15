<?php
namespace App\Services;

use MicrosoftAzure\Storage\Queue\QueueRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;

class AzureQueueService {
    private $queueClient;
    private $queueName = 'ai-processing-queue';

    public function __construct() {
        // Build the connection string dynamically using your existing cloud credential parameters
        $accountName = getenv('AZURE_STORAGE_ACCOUNT_NAME') ?: '';
        $accountKey  = getenv('AZURE_STORAGE_ACCOUNT_KEY') ?: '';
        
        if (empty($accountName) || empty($accountKey)) {
            $connectionString = getenv('AZURE_STORAGE_CONNECTION_STRING') ?: '';
        } else {
            $connectionString = "DefaultEndpointsProtocol=https;AccountName={$accountName};AccountKey={$accountKey};EndpointSuffix=core.windows.net";
        }

        if (!empty($connectionString)) {
            $this->queueClient = QueueRestProxy::createQueueService($connectionString);
        }
    }

    /**
     * Securely pushes the candidate record pointer into the storage queue for background processing
     */
    public function dispatchJob(int $applicantId): bool {
        if (!$this->queueClient) {
            error_log("Azure Queue Error: Connection string missing or uninitialized.");
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
            error_log("Azure Queue Dispatch Failure: " . $e->getMessage());
            return false;
        }
    }
}