<?php
namespace App\Core;

abstract class BaseController {
    protected $db;

    public function __construct() {
        // Enforce "Zero-Trust" - no Controller can be accessed without an active, valid session
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['tenant_id'])) {
            Helpers::redirect('/login');
        }
        
        // Initialize DB here so all controllers have access to $this->db
        $this->db = \App\Core\Database::getInstance();
    }

    protected function render(string $view, array $data = []) {
        extract($data);
        require_once __DIR__ . "/../views/{$view}.php";
    }
	
	protected function isLoggedIn(): bool {
    return isset($_SESSION['tenant_id']);
}
}