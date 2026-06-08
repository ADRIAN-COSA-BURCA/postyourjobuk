<?php
namespace App\Core;

abstract class BaseController {
    protected $db;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // 1. Basic Session Presence
        if (!$this->isLoggedIn()) {
            Helpers::redirect('/login');
        }

        // 2. Zero-Trust Identity Pinning (Detect session hijacking)
        if (!$this->isSessionValid()) {
            SecurityLogger::logAlert("SESSION_HIJACK_ATTEMPT", $_SESSION['tenant_id'] ?? 0, "Fingerprint mismatch detected.");
            $this->logout();
            Helpers::redirect('/login');
        }

        // 3. Dynamic State Verification (Terminate if tenant is suspended/deleted)
        if (!$this->isTenantActive()) {
            SecurityLogger::logAlert("ACTIVE_SESSION_TERMINATION", $_SESSION['tenant_id'] ?? 0, "Suspended workspace access attempt.");
            $this->logout();
            Helpers::redirect('/login');
        }
        
        $this->db = \App\Core\Database::getInstance();
    }

    protected function render(string $view, array $data = []) {
        extract($data);
        
        $header = __DIR__ . "/../views/layouts/header.php";
        $footer = __DIR__ . "/../views/layouts/footer.php";
        $content = __DIR__ . "/../views/{$view}.php";

        // Defensive check: Ensure view exists
        if (!file_exists($content)) {
            throw new \Exception("View file not found: {$view}");
        }
        
        require_once $header;
        require_once $content;
        require_once $footer;
    }

    protected function isLoggedIn(): bool {
        return isset($_SESSION['tenant_id']);
    }

    private function isSessionValid(): bool {
        // Handle case where session might be active but fingerprints not set
        if (!isset($_SESSION['user_agent'], $_SESSION['client_ip_hash'])) return false;

        $currentIpHash = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        return ($_SERVER['HTTP_USER_AGENT'] === $_SESSION['user_agent'] && 
                $currentIpHash === $_SESSION['client_ip_hash']);
    }

    private function isTenantActive(): bool {
        $db = \App\Core\Database::getInstance();
        $sql = "SELECT status, deleted_at FROM tenants WHERE tenant_id = ? LIMIT 1";
        $tenant = $db->fetchOne($sql, [$_SESSION['tenant_id']]);
        
        return ($tenant && $tenant['status'] === 'active' && $tenant['deleted_at'] === null);
    }

    private function logout() {
        $_SESSION = [];
        session_destroy();
    }
}