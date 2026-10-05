<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Recruiter;
use App\Core\Helpers;
use App\Core\AuditLogger;
use App\Core\Database; 

class AuthController extends BaseController {
    
    public function login() {
        $error = $_SESSION['error_message'] ?? null;
        $success = $_SESSION['success_message'] ?? null;
        unset($_SESSION['error_message'], $_SESSION['success_message']); 

        $this->render('auth/login', [
            'errorMessage' => $error,
            'successMessage' => $success
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
                AuditLogger::log('TENANT_LOGIN_FAILED', $user['company_name'], 'WARNING', $user['tenant_id'], 'tenant', $user['tenant_id'], ['email' => $email, 'reason' => 'account_suspended']);
                $_SESSION['error_message'] = "Access Denied: Account state invalid.";
                Helpers::redirect('/login');
                return;
            }
        } else {
            // No user found, use 'Guest' as label with WARNING severity
            AuditLogger::log('TENANT_LOGIN_FAILED', 'Guest', 'WARNING', 0, 'tenant', 0, ['email' => $email, 'reason' => 'user_not_found']);
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
            
            if (empty($_SESSION['csrf_token'])) {$_SESSION['csrf_token'] = bin2hex(random_bytes(32));}
            
            $recruiterModel->updateLastLogin($user['tenant_id']);
            
            // Success logged as INFO
            AuditLogger::log('TENANT_LOGIN_SUCCESS', $user['company_name'], 'INFO', $user['tenant_id'], 'tenant', $user['tenant_id'], ['email' => $email]);
            
            Helpers::redirect('/dashboard');
        } else {
            // Invalid password logged as WARNING
            AuditLogger::log('TENANT_LOGIN_FAILED', $user['company_name'], 'WARNING', $user['tenant_id'], 'tenant', $user['tenant_id'], ['email' => $email, 'reason' => 'invalid_password']);
            $_SESSION['error_message'] = "Invalid credentials.";
            Helpers::redirect('/login');
        }
    }

    public function logout() {
        $userLabel = $_SESSION['tenant_profile']['company_name'] ?? 'Unknown Tenant';
        $tenantId = $_SESSION['tenant_id'] ?? 0;
        
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        // Logout logged as INFO
        AuditLogger::log('TENANT_LOGOUT', $userLabel, 'INFO', $tenantId, 'tenant', $tenantId, ['status' => 'logged_out']);
        
        Helpers::redirect('/login');
    }

    // ==========================================
    // PASSWORD RESET METHODS
    // ==========================================

    public function showResetForm() {
        $error = $_SESSION['error_message'] ?? null;
        unset($_SESSION['error_message']); 

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $this->render('auth/reset-password', [
            'errorMessage' => $error
        ]);
    }

    public function handleResetSubmit() {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            AuditLogger::log('CSRF_FAILURE', 'System', 'CRITICAL', 0, 'system', 0, ['action' => 'password_reset']);
            die("Invalid security token.");
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helpers::redirect('/reset-password');
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $recoveryCode = trim($_POST['recovery_code'] ?? '');
        $newPassword = $_POST['new_password'] ?? '';

        if (empty($email) || empty($recoveryCode) || empty($newPassword)) {
            $_SESSION['error_message'] = "All fields are required.";
            Helpers::redirect('/reset-password');
            return;
        }

        $recruiterModel = new Recruiter();
        $user = $recruiterModel->findByEmail($email);

        if (!$user) {
            AuditLogger::log('PASSWORD_RESET_FAILED', 'Guest', 'WARNING', 0, 'tenant', 0, ['email' => $email, 'reason' => 'user_not_found']);
            $_SESSION['error_message'] = "Invalid email address or recovery code.";
            Helpers::redirect('/reset-password');
            return;
        }

        // Security check for account status
        if (($user['deleted_at'] ?? null) !== null || ($user['status'] ?? '') === 'suspended') {
            AuditLogger::log('PASSWORD_RESET_FAILED', $user['company_name'], 'WARNING', $user['tenant_id'], 'tenant', $user['tenant_id'], ['email' => $email, 'reason' => 'account_suspended']);
            $_SESSION['error_message'] = "Access Denied: Account state invalid.";
            Helpers::redirect('/reset-password');
            return;
        }

        // Validate the recovery code (case-insensitive for better UX)
        if (strtoupper($user['recovery_code'] ?? '') !== strtoupper($recoveryCode)) {
            AuditLogger::log('PASSWORD_RESET_FAILED', $user['company_name'], 'WARNING', $user['tenant_id'], 'tenant', $user['tenant_id'], ['email' => $email, 'reason' => 'invalid_recovery_code']);
            $_SESSION['error_message'] = "Invalid email address or recovery code.";
            Helpers::redirect('/reset-password');
            return;
        }

        // Hash the new password securely
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        // Update the database securely using the Database singleton
        $stmt = $this->db->prepare("UPDATE tenants SET password_hash = ? WHERE tenant_id = ?");
        $stmt->execute([$hashedPassword, $user['tenant_id']]);

        // Log success
        AuditLogger::log('PASSWORD_RESET_SUCCESS', $user['company_name'], 'INFO', $user['tenant_id'], 'tenant', $user['tenant_id'], ['email' => $email]);

        // Redirect to login with a success message
        $_SESSION['success_message'] = "Password updated successfully! You can now log in.";
        Helpers::redirect('/login');
    }

    // ==========================================
    // GOOGLE OAUTH METHODS
    // ==========================================

    public function googleLogin() {
        $clientId = getenv('GOOGLE_CLIENT_ID') ?: $_ENV['GOOGLE_CLIENT_ID'] ?? '';
        $redirectUri = getenv('GOOGLE_REDIRECT_URI') ?: $_ENV['GOOGLE_REDIRECT_URI'] ?? 'https://postyourjobuk-web-gxfzh2bcb9azbadw.polandcentral-01.azurewebsites.net/auth/google/callback';

        if (empty($clientId)) {
            $_SESSION['error_message'] = "Google Authentication is not configured on this server.";
            Helpers::redirect('/login');
            return;
        }

        $params = [
            'client_id'     => $clientId,
            'redirect_uri'  => $redirectUri,
            'response_type' => 'code',
            'scope'         => 'email profile',
            'access_type'   => 'online',
            'prompt'        => 'select_account'
        ];

        $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
        Helpers::redirect($authUrl);
    }

    public function googleCallback() {
        $code = $_GET['code'] ?? null;
        $error = $_GET['error'] ?? null;

        if ($error || !$code) {
            $_SESSION['error_message'] = "Google login was cancelled or failed.";
            Helpers::redirect('/login');
            return;
        }

        $clientId = getenv('GOOGLE_CLIENT_ID') ?: $_ENV['GOOGLE_CLIENT_ID'] ?? '';
        $clientSecret = getenv('GOOGLE_CLIENT_SECRET') ?: $_ENV['GOOGLE_CLIENT_SECRET'] ?? '';
        $redirectUri = getenv('GOOGLE_REDIRECT_URI') ?: $_ENV['GOOGLE_REDIRECT_URI'] ?? 'https://postyourjobuk-web-gxfzh2bcb9azbadw.polandcentral-01.azurewebsites.net/auth/google/callback';

        // 1. Exchange authorization code for an access token
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri'  => $redirectUri,
            'grant_type'    => 'authorization_code',
            'code'          => $code
        ]));
        $tokenResponse = curl_exec($ch);
        curl_close($ch);

        $tokenData = json_decode($tokenResponse, true);
        if (empty($tokenData['access_token'])) {
            AuditLogger::log('OAUTH_TOKEN_EXCHANGE_FAILED', 'System', 'WARNING', 0, 'system', 0, ['response' => $tokenResponse]);
            $_SESSION['error_message'] = "Failed to authenticate with Google.";
            Helpers::redirect('/login');
            return;
        }

        // 2. Fetch the user's profile information from Google
        $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tokenData['access_token']]);
        $profileResponse = curl_exec($ch);
        curl_close($ch);

        $profileData = json_decode($profileResponse, true);
        if (empty($profileData['email'])) {
            $_SESSION['error_message'] = "Could not retrieve email address from Google.";
            Helpers::redirect('/login');
            return;
        }

        $googleId = $profileData['id'];
        $email = $profileData['email'];
        $name = $profileData['name'] ?? 'Google User';

        $recruiterModel = new Recruiter();
        
        // 3. Authenticate or Register the user
        $user = $recruiterModel->findByGoogleId($googleId);

        if (!$user) {
            // Check if they already have a password-based account with this email
            $user = $recruiterModel->findByEmail($email);
            
            if ($user) {
                // Link the new Google ID to their existing account
                $recruiterModel->linkGoogleAccount($user['tenant_id'], $googleId);
                $user = $recruiterModel->findByGoogleId($googleId); 
                AuditLogger::log('TENANT_ACCOUNT_LINKED_GOOGLE', $user['company_name'], 'INFO', $user['tenant_id'], 'tenant', $user['tenant_id'], ['email' => $email]);
            } else {
                // Completely new user: Auto-register them
                $newTenantData = [
                    'company_name' => $name . "'s Company", // Sensible default
                    'email'        => $email,
                    'status'       => 'active'
                ];
                $recruiterModel->create($newTenantData);
                $newTenantId = $recruiterModel->getLastInsertId();
                $recruiterModel->linkGoogleAccount($newTenantId, $googleId);
                
                $user = $recruiterModel->findByGoogleId($googleId);
                AuditLogger::log('TENANT_REGISTERED_GOOGLE', $newTenantData['company_name'], 'INFO', $newTenantId, 'tenant', $newTenantId, ['email' => $email]);
            }
        }

        // 4. Security check for account status
        if (($user['deleted_at'] ?? null) !== null || ($user['status'] ?? '') === 'suspended') {
            AuditLogger::log('TENANT_LOGIN_FAILED', $user['company_name'], 'WARNING', $user['tenant_id'], 'tenant', $user['tenant_id'], ['email' => $email, 'reason' => 'account_suspended_google']);
            $_SESSION['error_message'] = "Access Denied: Account state invalid.";
            Helpers::redirect('/login');
            return;
        }

        // 5. Establish the user session securely
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        
        $_SESSION['tenant_id'] = $user['tenant_id'];
        $_SESSION['tenant_profile'] = $user; 
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $_SESSION['client_ip_hash'] = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        $recruiterModel->updateLastLogin($user['tenant_id']);
        AuditLogger::log('TENANT_LOGIN_SUCCESS_GOOGLE', $user['company_name'], 'INFO', $user['tenant_id'], 'tenant', $user['tenant_id'], ['email' => $email]);
        
        Helpers::redirect('/dashboard');
    }
}