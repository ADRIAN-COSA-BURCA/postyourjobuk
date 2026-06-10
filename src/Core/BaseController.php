<?php
namespace App\Core;

abstract class BaseController {
    protected $db;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $currentPath = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        if ($currentPath === '') $currentPath = '/';

        // 1. Bypass Tenant Auth for Admin and Public routes
        $publicRoutes = ['/login', '/login/authenticate', '/'];
        $isAdminRoute = str_starts_with($currentPath, '/admin');

        if (in_array($currentPath, $publicRoutes) || $isAdminRoute) {
            // Even if bypassing, we still need the database for Admin operations
            $this->db = \App\Core\Database::getInstance();
            return;
        }

        // 2. Standard Tenant Auth Enforcement
        $this->db = \App\Core\Database::getInstance();
        
        if (!$this->isLoggedIn()) {
            Helpers::redirect('/login');
            return;
        }
        if (!$this->isSessionValid()) { 
            $this->logout(); 
            Helpers::redirect('/login'); 
            return;
        }
        if (!$this->isTenantActive()) { 
            $this->logout(); 
            Helpers::redirect('/login'); 
            return;
        }
    }

    protected function render(string $view, array $data = []) {
    // 1. Prepare variables for the view
    extract($data);
    
    $viewsPath = dirname(__DIR__) . '/views';
    $contentFile = $viewsPath . '/' . $view . '.php';

    // 2. Security/Existence check for the view file
    if (!file_exists($contentFile)) {
        http_response_code(404);
        die("View file not found: {$view}");
    }

    // 3. Capture the specific view content into an output buffer
    ob_start();
    require $contentFile;
    $view_content = ob_get_clean();

    // 4. Load the Master Layout wrapper
    // This wrapper acts as the central point for <head>, CSS, and <footer>
    $layoutPath = $viewsPath . '/layouts/main.php';
    
    if (file_exists($layoutPath)) {
        require $layoutPath;
    } else {
        // Fallback: if main.php is missing, just render the raw view content
        echo $view_content;
    }
}

    // ... [Keep existing isLoggedIn, isSessionValid, isTenantActive, and logout methods] ...
    
    protected function isLoggedIn(): bool {
        return isset($_SESSION['tenant_id']);
    }

    private function isSessionValid(): bool {
        if (!isset($_SESSION['user_agent'], $_SESSION['client_ip_hash'])) {
            return false;
        }
        $currentIpHash = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        return ($_SERVER['HTTP_USER_AGENT'] === $_SESSION['user_agent'] && 
                $currentIpHash === $_SESSION['client_ip_hash']);
    }

    private function isTenantActive(): bool {
        return \App\Core\Auth::isTenantActive();
    }

    protected function logout() {
        $_SESSION = [];
        session_destroy();
    }
}