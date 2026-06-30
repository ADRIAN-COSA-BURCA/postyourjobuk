<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Recruiter;
use App\Core\Helpers;
use App\Core\AuditLogger;

class AuthController extends BaseController {
    
    public function login() {
        $error = $_SESSION['error_message'] ?? null;
        unset($_SESSION['error_message']); 

        $this->render('auth/login', [
            'errorMessage' => $error
        ]);
    }

    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helpers::redirect('/login');
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $recruiterModel = new Recruiter();
        $user = $recruiterModel->findByEmail($email);
        
        // Security check for account status
        if ($user) {
            if (($user['deleted_at'] ?? null) !== null || ($user['status'] ?? '') === 'suspended') {
                // Pass Company Name as userLabel
                AuditLogger::log('TENANT_LOGIN_FAILED', $user['company_name'], $user['tenant_id'], 'tenant', $user['tenant_id'], ['email' => $email, 'reason' => 'account_suspended']);
                $_SESSION['error_message'] = "Access Denied: Account state invalid.";
                Helpers::redirect('/login');
                return;
            }
        } else {
            // No user found, use 'Guest' as label
            AuditLogger::log('TENANT_LOGIN_FAILED', 'Guest', 0, 'tenant', 0, ['email' => $email, 'reason' => 'user_not_found']);
            $_SESSION['error_message'] = "Invalid credentials.";
            Helpers::redirect('/login');
            return;
        }

        // Verify credentials
        if (password_verify($password, $user['password_hash'] ?? '')) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_regenerate_id(true);
            }
            
            $_SESSION['tenant_id'] = $user['tenant_id'];
            $_SESSION['tenant_profile'] = $user; 
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            $_SESSION['client_ip_hash'] = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
            
            $recruiterModel->updateLastLogin($user['tenant_id']);
            
            // Pass Company Name as userLabel
            AuditLogger::log('TENANT_LOGIN_SUCCESS', $user['company_name'], $user['tenant_id'], 'tenant', $user['tenant_id'], ['email' => $email]);
            
            Helpers::redirect('/dashboard');
        } else {
            // Pass Company Name as userLabel
            AuditLogger::log('TENANT_LOGIN_FAILED', $user['company_name'], $user['tenant_id'], 'tenant', $user['tenant_id'], ['email' => $email, 'reason' => 'invalid_password']);
            $_SESSION['error_message'] = "Invalid credentials.";
            Helpers::redirect('/login');
        }
    }

    public function logout() {
        // Capture identity BEFORE destroying the session
        $userLabel = $_SESSION['tenant_profile']['company_name'] ?? 'Unknown Tenant';
        $tenantId = $_SESSION['tenant_id'] ?? 0;
        
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        // Pass the captured label to the logger
        AuditLogger::log('TENANT_LOGOUT', $userLabel, $tenantId, 'tenant', $tenantId, ['status' => 'logged_out']);
        
        Helpers::redirect('/login');
    }
}