<?php
namespace App\Core;

class SecurityLogger {
    public static function logAlert(string $eventType, int $tenantId, string $description): void {
        try {
            // Since getInstance() returns the raw PDO instance, we interact with it directly
            $pdo = Database::getInstance();

            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

            $sql = "INSERT INTO security_audit_logs (event_type, tenant_id, ip_address, user_agent, description, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                strtoupper($eventType), 
                $tenantId, 
                $ip, 
                substr($ua, 0, 255), 
                $description
            ]);

        } catch (\Exception $e) {
            error_log("Security Logging Engine Fault: " . $e->getMessage());
        }
    }
}