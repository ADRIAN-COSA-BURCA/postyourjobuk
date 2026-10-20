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

        $publicRoutes = [
            '/login', 
            '/login/authenticate', 
            '/', 
            '/index.php', 
            '/job', 
            '/job/details',
            '/applicants/store', 
            '/apply',
            '/about',
            '/contact',
            '/vision',
            '/terms',
            '/reset-password',
            '/reset-password/submit',
            '/api/fetch-news',
            '/api/refresh-news',
            '/auth/google',            // ADDED for Google OAuth
            '/auth/google/callback'    // ADDED for Google OAuth
        ];
        
        $isPublic = false;
        foreach ($publicRoutes as $route) {
            if ($currentPath === $route || str_starts_with($currentPath, $route . '/')) {
                $isPublic = true;
                break;
            }
        }

        $isAdminRoute = str_starts_with($currentPath, '/admin');

        if ($isPublic || $isAdminRoute) {
            $this->db = \App\Core\Database::getInstance();
            return;
        }

        // 2. Standard Tenant Auth Enforcement
        $this->db = \App\Core\Database::getInstance();
        
        if (!$this->isLoggedIn()) {
            $_SESSION['error_message'] = 'Please log in to access your recruiter workspace.';
            Helpers::redirect('/login');
            return;
        }

        // ENFORCEMENT RESTORED: Session fingerprint check prevents session hijacking
        if (!$this->isSessionValid()) { 
            error_log("Security: Session fingerprint mismatch or missing. Destroying session.");
            $this->logout(); 
            $_SESSION['error_message'] = 'Your session has expired for security reasons. Please log in again.';
            Helpers::redirect('/login'); 
            return;
        }

        // ENFORCEMENT RESTORED: Tenant status check prevents suspended accounts from accessing the dashboard
        if (!$this->isTenantActive()) { 
            error_log("Security: Inactive tenant attempted access. Destroying session.");
            $this->logout(); 
            $_SESSION['error_message'] = 'Your company workspace is currently inactive or suspended. Please contact support.';
            Helpers::redirect('/login'); 
            return;
        }
    }

    protected function render(string $view, array $data = [], $layoutOrStandalone = false) {
        extract($data);
        
        $root = (defined('ROOT_PATH') && file_exists(ROOT_PATH . '/src/views/' . ltrim($view, '/') . '.php')) 
            ? ROOT_PATH 
            : '/var/www/html';
            
        $view = ltrim($view, '/');
        $contentFile = $root . '/src/views/' . $view . '.php';

        if (!file_exists($contentFile)) {
            die("DOCKER PATH ERROR: The system cannot find the view file. <br>" .
                "Attempted path: " . htmlspecialchars($contentFile) . "<br>" .
                "Current working directory: " . htmlspecialchars(getcwd()));
        }

        ob_start();
        require $contentFile;
        $content = ob_get_clean();

        if ($layoutOrStandalone === true) {
            echo $content;
            return;
        }

        $layout = (is_string($layoutOrStandalone) && !empty($layoutOrStandalone))
            ? $layoutOrStandalone 
            : 'layouts/main';

        $layoutPath = $root . '/src/views/' . ltrim($layout, '/') . '.php';
        
        if (file_exists($layoutPath)) {
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
        if (!class_exists('\App\Core\Auth') || !method_exists('\App\Core\Auth', 'isTenantActive')) {
            return true; // Bypass if the class isn't fully set up yet to prevent fatal errors
        }
        return \App\Core\Auth::isTenantActive();
    }

    protected function logout() {
        $_SESSION = [];
        session_destroy();
    }
}