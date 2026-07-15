<?php
namespace App\Middleware;

use App\Core\Helpers;
use App\Core\Database;

class AdminAuth {
    public static function check() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // 1. Check if user is logged in
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            Helpers::redirect('/admin/login');
            exit;
        }
        
        // 2. Safely retrieve session data using null coalescing
        $savedAgent = $_SESSION['admin_user_agent'] ?? '';
        $savedIpHash = $_SESSION['admin_ip_hash'] ?? '';
        
        $currentAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $currentIpHash = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

        // 3. Compare safely
        if ($savedAgent !== $currentAgent || $savedIpHash !== $currentIpHash) {
            session_destroy();
            Helpers::redirect('/admin/login');
            exit;
        }
    }

    public static function reverifyPassword(string $password): bool {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['admin_email'])) {
            return false;
        }

        $db = Database::getInstance();
        // Use the connection method defined in your Database core
        $admin = $db->prepare("SELECT password_hash FROM tenants WHERE email = ? AND is_super_admin = 1 LIMIT 1");
        $admin->execute([$_SESSION['admin_email']]);
        $result = $admin->fetch();
        
        return ($result && password_verify($password, $result['password_hash']));
    }
}