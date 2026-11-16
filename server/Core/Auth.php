<?php
namespace App\Core;

use PDO;

class Auth {
    public static function checkSessionFingerprint(): bool {
        if (!isset($_SESSION['user_agent'], $_SESSION['client_ip_hash'])) {
            return false;
        }
        $currentIpHash = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        return (($_SERVER['HTTP_USER_AGENT'] ?? '') === $_SESSION['user_agent'] && $currentIpHash === $_SESSION['client_ip_hash']);
    }

    public static function checkAdminSessionFingerprint(): bool {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            return false;
        }
        $savedAgent    = $_SESSION['admin_user_agent'] ?? '';
        $savedIpHash   = $_SESSION['admin_ip_hash'] ?? '';
        $currentAgent  = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $currentIpHash = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        return ($savedAgent === $currentAgent && $savedIpHash === $currentIpHash);
    }

    public static function isTenantActive(): bool {
        $db = Database::getInstance();
        $sql = "SELECT status, deleted_at FROM tenants WHERE tenant_id = ? LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([$_SESSION['tenant_id'] ?? 0]);
        $tenant = $stmt->fetch(PDO::FETCH_ASSOC);

        return ($tenant && $tenant['status'] === 'active' && $tenant['deleted_at'] === null);
    }
}
