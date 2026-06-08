<?php
namespace App\Core;

class Auth {
    // Moved session validation logic here
    public static function checkSessionFingerprint(): bool {
        if (!isset($_SESSION['user_agent'], $_SESSION['client_ip_hash'])) return false;
        
        $currentIpHash = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        return ($_SERVER['HTTP_USER_AGENT'] === $_SESSION['user_agent'] && $currentIpHash === $_SESSION['client_ip_hash']);
    }

    public static function isTenantActive(): bool {
        $db = Database::getInstance();
        $sql = "SELECT status, deleted_at FROM tenants WHERE tenant_id = ? LIMIT 1";
        $tenant = $db->fetchOne($sql, [$_SESSION['tenant_id'] ?? 0]);
        
        return ($tenant && $tenant['status'] === 'active' && $tenant['deleted_at'] === null);
    }
}