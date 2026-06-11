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

        // 1. FIXED: Added '/job' to public routes so candidates can view postings
        $publicRoutes = ['/login', '/login/authenticate', '/', '/index.php', '/job'];
        $isAdminRoute = str_starts_with($currentPath, '/admin');

        if (in_array($currentPath, $publicRoutes) || $isAdminRoute) {
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
        extract($data);
        
        $containerRoot = '/var/www/html'; 
        $view = ltrim($view, '/');
        $contentFile = $containerRoot . '/src/views/' . $view . '.php';

        if (!file_exists($contentFile)) {
            die("DOCKER PATH ERROR: The system cannot find the view file. <br>" .
                "Attempted path: " . htmlspecialchars($contentFile) . "<br>" .
                "Current working directory: " . htmlspecialchars(getcwd()));
        }

        ob_start();
        require $contentFile;
        $content = ob_get_clean();

        $layoutPath = $containerRoot . '/src/views/layouts/main.php';
        
        if (file_exists($layoutPath)) {
            // FIXED: Set both variable names to maximize support across your layout pages
            $view_content = $content; 
            require $layoutPath;
        } else {
            echo $content;
        }
    }
    
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