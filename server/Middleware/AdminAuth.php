<?php
namespace App\Middleware;

use App\Core\Helpers;
use App\Core\Database;

class AdminAuth {
    /**
     * Check Super-Admin session for REST API endpoints (returns JSON 401/403 on failure)
     */
    public static function checkApi(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            Helpers::jsonResponse([
                'success'  => false,
                'error'    => 'Admin authentication required.',
                'redirect' => '/admin/login'
            ], 401);
        }

        $savedAgent    = $_SESSION['admin_user_agent'] ?? '';
        $savedIpHash   = $_SESSION['admin_ip_hash'] ?? '';
        $currentAgent  = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $currentIpHash = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

        if ($savedAgent !== $currentAgent || $savedIpHash !== $currentIpHash) {
            session_destroy();
            Helpers::jsonResponse([
                'success'  => false,
                'error'    => 'Admin session fingerprint mismatch. Please sign in again.',
                'redirect' => '/admin/login'
            ], 401);
        }
    }

    /**
     * Legacy check method aliasing checkApi() inside server/
     */
    public static function check(): void {
        self::checkApi();
    }

    public static function reverifyPassword(string $password): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_email'])) {
            return false;
        }

        $db = Database::getInstance();
        $admin = $db->prepare("SELECT password_hash FROM tenants WHERE email = ? AND is_super_admin = 1 LIMIT 1");
        $admin->execute([$_SESSION['admin_email']]);
        $result = $admin->fetch();

        return ($result && password_verify($password, $result['password_hash']));
    }
}
