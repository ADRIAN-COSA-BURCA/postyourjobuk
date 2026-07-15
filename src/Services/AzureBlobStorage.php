<?php
declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class AzureBlobStorage
{
    private string $accountName;
    private string $accountKey;
    private string $container;
    private string $baseUrl;
    private string $apiVersion = '2023-11-03';
    private int $timeout = 60;

    public function __construct()
    {
        $this->accountName = defined('AZURE_STORAGE_ACCOUNT_NAME') ? trim((string) AZURE_STORAGE_ACCOUNT_NAME) : '';
        $this->accountKey = defined('AZURE_STORAGE_ACCOUNT_KEY') ? trim((string) AZURE_STORAGE_ACCOUNT_KEY) : '';
        $this->container = defined('AZURE_STORAGE_CONTAINER') ? trim((string) AZURE_STORAGE_CONTAINER) : 'cv-storage';
        $this->baseUrl = defined('AZURE_STORAGE_BASE_URL') && AZURE_STORAGE_BASE_URL
            ? rtrim((string) AZURE_STORAGE_BASE_URL, '/')
            : ($this->accountName !== '' ? 'https://' . $this->accountName . '.blob.core.windows.net' : '');
    }

    public function isConfigured(): bool
    {
        return $this->accountName !== ''
            && $this->accountKey !== ''
            && $this->container !== ''
            && $this->baseUrl !== '';
    }

    public function getContainer(): string
    {
        return $this->container;
    }

    public function buildApplicantBlobName(int $tenantId, int $jobId, string $filename): string
    {
        $safeFilename = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($filename));
        return 'tenant-' . $tenantId . '/job-' . $jobId . '/' . $safeFilename;
    }

    public function uploadFile(string $localFilePath, string $blobName, ?string $contentType = null): array
    {
        $this->assertConfigured();

        if (!is_file($localFilePath) || !file_exists($localFilePath)) {
            throw new RuntimeException('Local file not found for Azure Blob upload.');
        }

        $content = file_get_contents($localFilePath);
        if ($content === false) {
            throw new RuntimeException('Failed to read local file before Azure upload.');
        }

        $blobName = $this->normalizeBlobName($blobName);
        $contentType = $contentType ?: $this->detectMimeType($localFilePath);
        $contentLength = strlen($content);
        $path = '/' . $this->container . '/' . $blobName;
        $url = $this->baseUrl . $path;
        $date = gmdate('D, d M Y H:i:s') . ' GMT';

        $headers = [
            'x-ms-blob-type: BlockBlob',
            'x-ms-date: ' . $date,
            'x-ms-version: ' . $this->apiVersion,
            'Content-Type: ' . $contentType,
            'Content-Length: ' . $contentLength,
            'Authorization: ' . $this->buildSharedKeyAuthorization('PUT', $path, [
                'Content-Length' => (string) $contentLength,
                'Content-Type' => $contentType,
                'x-ms-blob-type' => 'BlockBlob',
                'x-ms-date' => $date,
                'x-ms-version' => $this->apiVersion,
            ]),
        ];

        $response = $this->sendRequest('PUT', $url, $headers, $content);
        if ($response['status'] < 200 || $response['status'] >= 300) {
            throw new RuntimeException('Azure blob upload failed with HTTP ' . $response['status'] . ': ' . $response['body']);
        }

        return [
            'blob_name' => $blobName,
            'url' => $url,
            'storage_path' => 'blob://' . $this->container . '/' . $blobName,
            'content_type' => $contentType,
            'size' => $contentLength,
        ];
    }

    public function downloadBlob(string $storagePath): array
    {
        $this->assertConfigured();

        $blobName = $this->extractBlobName($storagePath);
        $path = '/' . $this->container . '/' . $blobName;
        $url = $this->baseUrl . $path;
        $date = gmdate('D, d M Y H:i:s') . ' GMT';

        $headers = [
            'x-ms-date: ' . $date,
            'x-ms-version: ' . $this->apiVersion,
            'Authorization: ' . $this->buildSharedKeyAuthorization('GET', $path, [
                'x-ms-date' => $date,
                'x-ms-version' => $this->apiVersion,
            ]),
        ];

        $response = $this->sendRequest('GET', $url, $headers);
        if ($response['status'] === 404) {
            return [];
        }
        if ($response['status'] < 200 || $response['status'] >= 300) {
            throw new RuntimeException('Azure blob download failed with HTTP ' . $response['status'] . ': ' . $response['body']);
        }

        return [
            'blob_name' => $blobName,
            'storage_path' => 'blob://' . $this->container . '/' . $blobName,
            'content' => $response['body'],
            'content_type' => $response['content_type'] ?: 'application/octet-stream',
            'content_length' => $response['content_length'] ?? strlen($response['body']),
        ];
    }

    public function deleteBlob(string $storagePath): bool
    {
        $this->assertConfigured();

        $blobName = $this->extractBlobName($storagePath);
        $path = '/' . $this->container . '/' . $blobName;
        $url = $this->baseUrl . $path;
        $date = gmdate('D, d M Y H:i:s') . ' GMT';

        $headers = [
            'x-ms-date: ' . $date,
            'x-ms-version: ' . $this->apiVersion,
            'Authorization: ' . $this->buildSharedKeyAuthorization('DELETE', $path, [
                'x-ms-date' => $date,
                'x-ms-version' => $this->apiVersion,
            ]),
        ];

        $response = $this->sendRequest('DELETE', $url, $headers);
        if ($response['status'] === 404) {
            return false;
        }
        if ($response['status'] < 200 || $response['status'] >= 300) {
            throw new RuntimeException('Azure blob delete failed with HTTP ' . $response['status'] . ': ' . $response['body']);
        }

        return true;
    }

    private function assertConfigured(): void
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Azure Blob Storage credentials/configuration are incomplete.');
        }
    }

    private function normalizeBlobName(string $blobName): string
    {
        $blobName = ltrim(str_replace('\\', '/', trim($blobName)), '/');
        return preg_replace('#/+#', '/', $blobName);
    }

    private function extractBlobName(string $storagePath): string
    {
        if (strpos($storagePath, 'blob://') === 0) {
            $withoutScheme = substr($storagePath, 7);
            $parts = explode('/', $withoutScheme, 2);
            if (count($parts) === 2) {
                return $this->normalizeBlobName($parts[1]);
            }
        }

        if (preg_match('#^https?://#i', $storagePath)) {
            $parsed = parse_url($storagePath);
            $path = $parsed['path'] ?? '';
            $prefix = '/' . $this->container . '/';
            if (strpos($path, $prefix) === 0) {
                return $this->normalizeBlobName(substr($path, strlen($prefix)));
            }
        }

        return $this->normalizeBlobName($storagePath);
    }

    private function detectMimeType(string $filePath): string
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = finfo_file($finfo, $filePath);
                finfo_close($finfo);
                if (is_string($mime) && $mime !== '') {
                    return $mime;
                }
            }
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $map = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'txt' => 'text/plain',
        ];

        return $map[$extension] ?? 'application/octet-stream';
    }

    private function buildSharedKeyAuthorization(string $method, string $canonicalPath, array $headers): string
    {
        $contentLength = $headers['Content-Length'] ?? '';
        if (in_array($method, ['GET', 'DELETE', 'HEAD'], true)) {
            $contentLength = '';
        }
        $contentType = $headers['Content-Type'] ?? '';

        $xmsHeaders = [];
        foreach ($headers as $name => $value) {
            $lower = strtolower($name);
            if (strpos($lower, 'x-ms-') === 0) {
                $xmsHeaders[$lower] = trim((string) $value);
            }
        }
        ksort($xmsHeaders);

        $canonicalizedHeaders = '';
        foreach ($xmsHeaders as $name => $value) {
            $canonicalizedHeaders .= $name . ':' . $value . "\n";
        }

        $canonicalizedResource = '/' . $this->accountName . $canonicalPath;
        $stringToSign = implode("\n", [
            strtoupper($method),
            '',
            '',
            $contentLength,
            '',
            $contentType,
            '',
            '',
            '',
            '',
            '',
            '',
            $canonicalizedHeaders . $canonicalizedResource,
        ]);

        $decodedKey = base64_decode($this->accountKey, true);
        if ($decodedKey === false) {
            throw new RuntimeException('Invalid Azure storage account key format.');
        }

        $signature = base64_encode(hash_hmac('sha256', $stringToSign, $decodedKey, true));
        return 'SharedKey ' . $this->accountName . ':' . $signature;
    }

    private function sendRequest(string $method, string $url, array $headers, ?string $body = null): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $raw = curl_exec($ch);
        if ($raw === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('Azure cURL request failed: ' . $error);
        }

        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $contentType = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        $headerText = substr($raw, 0, $headerSize);
        $responseBody = substr($raw, $headerSize);
        $contentLength = null;

        foreach (explode("\r\n", $headerText) as $line) {
            if (stripos($line, 'Content-Length:') === 0) {
                $contentLength = (int) trim(substr($line, 15));
                break;
            }
        }

        return [
            'status' => $status,
            'headers' => $headerText,
            'body' => $responseBody,
            'content_type' => $contentType,
            'content_length' => $contentLength,
        ];
    }
}