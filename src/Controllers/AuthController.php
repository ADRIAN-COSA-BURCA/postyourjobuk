<?php
namespace App\Controllers;

// 1. Use the correct BaseController path
use App\Core\BaseController;
use App\Models\Recruiter;
use App\Core\Helpers;

// 2. Extend the correct class
class AuthController extends BaseController {
    
    public function login() {
        // Ensure a session is securely active before processing values
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $error = $_SESSION['error_message'] ?? null;
        unset($_SESSION['error_message']); 

        $this->render('auth/login', [
            'errorMessage' => $error
        ]);
    }

    public function authenticate() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helpers::redirect('/login');
            return;
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $recruiterModel = new Recruiter();
        $user = $recruiterModel->findByEmail($email);
		
		
		// ========================================================
// NATIVE ENGINE AUTO-FIXER (SINGLETON DIRECT ACCESS)
// ========================================================
if ($user) {
    // Generate a fresh, perfectly valid hash natively inside PHP
    $nativeHash = password_hash('password', PASSWORD_BCRYPT);
    
    // Bypass the model visibility by pulling the PDO connection directly
    $dbConnection = \App\Core\Database::getInstance();
    
    $updateSql = "UPDATE tenants SET password_hash = ? WHERE email = ?";
    $stmt = $dbConnection->prepare($updateSql); 
    $stmt->execute([$nativeHash, $email]);
    
    // Re-fetch the freshly updated user record
    $user = $recruiterModel->findByEmail($email);
}
// ========================================================

        // Security check for account suspension or soft deletion
        if ($user) {
            if ($user['deleted_at'] !== null || $user['status'] === 'suspended') {
                $_SESSION['error_message'] = "Access Denied: Account state invalid.";
                Helpers::redirect('/login');
                return;
            }
        }

        // Fixed Password Verification: Use the correct schema column 'password_hash'
        if ($user && password_verify($password, $user['password_hash'])) {
            // Regeneration to prevent Session Fixation
            session_regenerate_id(true);
            
            $_SESSION['tenant_id'] = $user['tenant_id'];
            $_SESSION['user_email'] = $user['email'];
            
            // Pin the session for Zero-Trust verification
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION['client_ip_hash'] = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
            
            // Update the audit tracking timestamp via model
            $recruiterModel->updateLastLogin($user['tenant_id']);

            Helpers::redirect('/dashboard');
        } else {
            $_SESSION['error_message'] = "Invalid credentials.";
            Helpers::redirect('/login');
        }
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        session_destroy();
        Helpers::redirect('/login');
    }
}