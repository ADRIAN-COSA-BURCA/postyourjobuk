<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Auth;
use App\Core\Helpers;
use App\Core\AuditLogger;
use App\Models\Recruiter;

class AuthController extends BaseController {

    /**
     * GET /api/auth/session
     * Returns current authentication state, role, user metadata, and active CSRF token
     */
    public function session(): void {
        $isAdmin     = !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && Auth::checkAdminSessionFingerprint();
        $isRecruiter = !$isAdmin && $this->isLoggedIn() && Auth::checkSessionFingerprint() && Auth::isTenantActive();

        $role = 'guest';
        $user = null;

        if ($isAdmin) {
            $role = 'admin';
            $user = [
                'email' => $_SESSION['admin_email'] ?? 'admin@postyourjobuk.co.uk',
                'role'  => 'super_admin'
            ];
        } elseif ($isRecruiter) {
            $role = 'recruiter';
            $profile = $_SESSION['tenant_profile'] ?? [];
            $user = [
                'tenant_id'       => (int)$_SESSION['tenant_id'],
                'company_name'    => $profile['company_name'] ?? 'Recruiter',
                'contact_person'  => $profile['contact_person'] ?? '',
                'email'           => $profile['email'] ?? '',
                'phone_number'    => $profile['phone_number'] ?? '',
                'website_url'     => $profile['website_url'] ?? '',
                'company_address' => $profile['company_address'] ?? '',
                'industry'        => $profile['industry'] ?? ''
            ];
        }

        $flashSuccess = $_SESSION['success_message'] ?? null;
        $flashError   = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        $this->json([
            'success'       => true,
            'authenticated' => ($role !== 'guest'),
            'role'          => $role,
            'user'          => $user,
            'csrf_token'    => Helpers::csrf_token(),
            'flash_success' => $flashSuccess,
            'flash_error'   => $flashError
        ]);
    }

    /**
     * POST /api/auth/login
     */
    public function authenticate(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'Method not allowed.'], 405);
        }

        $body     = $this->getBody();
        $email    = trim((string)($body['email'] ?? ''));
        $password = (string)($body['password'] ?? '');

        if ($email === '' || $password === '') {
            $this->json(['success' => false, 'error' => 'Please enter both your email address and password.'], 400);
        }

        $recruiterModel = new Recruiter();
        $user = $recruiterModel->findByEmail($email);

        if ($user) {
            if (($user['deleted_at'] ?? null) !== null || ($user['status'] ?? '') === 'suspended') {
                AuditLogger::log('TENANT_LOGIN_FAILED', $user['company_name'], 'WARNING', $user['tenant_id'], 'tenant', $user['tenant_id'], ['email' => $email, 'reason' => 'account_suspended']);
                $this->json([
                    'success' => false,
                    'error'   => 'Your company account is currently suspended or inactive. Please contact support.'
                ], 403);
            }
        } else {
            AuditLogger::log('TENANT_LOGIN_FAILED', 'Guest', 'WARNING', 0, 'tenant', 0, ['email' => $email, 'reason' => 'user_not_found']);
            $this->json([
                'success' => false,
                'error'   => 'Incorrect email address or password. Please verify your credentials and try again.'
            ], 401);
        }

        if (password_verify($password, $user['password_hash'] ?? '')) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_regenerate_id(true);
            }

            unset($user['password_hash'], $user['recovery_code']);
            $_SESSION['tenant_id']      = (int)$user['tenant_id'];
            $_SESSION['tenant_profile'] = $user;
            $_SESSION['user_agent']     = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            $_SESSION['client_ip_hash'] = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }

            $recruiterModel->updateLastLogin($user['tenant_id']);
            AuditLogger::log('TENANT_LOGIN_SUCCESS', $user['company_name'], 'INFO', $user['tenant_id'], 'tenant', $user['tenant_id'], ['email' => $email]);

            $this->json([
                'success'    => true,
                'message'    => 'Logged in successfully.',
                'redirect'   => '/dashboard',
                'csrf_token' => $_SESSION['csrf_token'],
                'user'       => $user
            ]);
        }

        AuditLogger::log('TENANT_LOGIN_FAILED', $user['company_name'], 'WARNING', $user['tenant_id'], 'tenant', $user['tenant_id'], ['email' => $email, 'reason' => 'invalid_password']);
        $this->json([
            'success' => false,
            'error'   => 'Incorrect email address or password. Please verify your credentials and try again.'
        ], 401);
    }

    /**
     * POST|GET /api/auth/logout
     */
    public function logout(): void {
        $userLabel = $_SESSION['tenant_profile']['company_name'] ?? 'Unknown Tenant';
        $tenantId  = (int)($_SESSION['tenant_id'] ?? 0);

        $this->destroySession();
        AuditLogger::log('TENANT_LOGOUT', $userLabel, 'INFO', $tenantId, 'tenant', $tenantId, ['status' => 'logged_out']);

        $this->json([
            'success'  => true,
            'message'  => 'You have been signed out safely.',
            'redirect' => '/login'
        ]);
    }

    /**
     * POST /api/auth/reset-password
     */
    public function handleResetSubmit(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'Method not allowed.'], 405);
        }

        $this->requireCsrf('CSRF_PASSWORD_RESET_VIOLATION');

        $body         = $this->getBody();
        $email        = trim((string)($body['email'] ?? ''));
        $recoveryCode = trim((string)($body['recovery_code'] ?? ''));
        $newPassword  = (string)($body['new_password'] ?? '');

        if ($email === '' || $recoveryCode === '' || $newPassword === '') {
            $this->json(['success' => false, 'error' => 'All fields are required.'], 400);
        }

        if (mb_strlen($newPassword) < 8) {
            $this->json(['success' => false, 'error' => 'New password must be at least 8 characters long.'], 422);
        }

        $recruiterModel = new Recruiter();
        $user = $recruiterModel->findByEmail($email);

        if (!$user) {
            AuditLogger::log('PASSWORD_RESET_FAILED', 'Guest', 'WARNING', 0, 'tenant', 0, ['email' => $email, 'reason' => 'user_not_found']);
            $this->json(['success' => false, 'error' => 'Invalid email address or recovery code.'], 400);
        }

        if (($user['deleted_at'] ?? null) !== null || ($user['status'] ?? '') === 'suspended') {
            AuditLogger::log('PASSWORD_RESET_FAILED', $user['company_name'], 'WARNING', $user['tenant_id'], 'tenant', $user['tenant_id'], ['email' => $email, 'reason' => 'account_suspended']);
            $this->json(['success' => false, 'error' => 'Access Denied: Account state invalid.'], 403);
        }

        if (strtoupper($user['recovery_code'] ?? '') !== strtoupper($recoveryCode)) {
            AuditLogger::log('PASSWORD_RESET_FAILED', $user['company_name'], 'WARNING', $user['tenant_id'], 'tenant', $user['tenant_id'], ['email' => $email, 'reason' => 'invalid_recovery_code']);
            $this->json(['success' => false, 'error' => 'Invalid email address or recovery code.'], 400);
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE tenants SET password_hash = ? WHERE tenant_id = ?");
        $stmt->execute([$hashedPassword, $user['tenant_id']]);

        AuditLogger::log('PASSWORD_RESET_SUCCESS', $user['company_name'], 'INFO', $user['tenant_id'], 'tenant', $user['tenant_id'], ['email' => $email]);

        $this->json([
            'success'  => true,
            'message'  => 'Password updated successfully! You can now log in.',
            'redirect' => '/login'
        ]);
    }

    /**
     * GET /auth/google or /api/auth/google
     */
    public function googleLogin(): void {
        $clientId    = app_env('GOOGLE_CLIENT_ID');
        $redirectUri = app_env('GOOGLE_REDIRECT_URI', 'https://postyourjobuk-web-gxfzh2bcb9azbadw.polandcentral-01.azurewebsites.net/auth/google/callback');

        if ($clientId === '') {
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

    /**
     * GET /auth/google/callback or /api/auth/google/callback
     */
    public function googleCallback(): void {
        $code  = $_GET['code'] ?? null;
        $error = $_GET['error'] ?? null;

        if ($error || !$code) {
            $_SESSION['error_message'] = "Google login was cancelled or failed.";
            Helpers::redirect('/login');
            return;
        }

        $clientId     = app_env('GOOGLE_CLIENT_ID');
        $clientSecret = app_env('GOOGLE_CLIENT_SECRET');
        $redirectUri  = app_env('GOOGLE_REDIRECT_URI', 'https://postyourjobuk-web-gxfzh2bcb9azbadw.polandcentral-01.azurewebsites.net/auth/google/callback');

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

        $tokenData = json_decode((string)$tokenResponse, true);
        if (empty($tokenData['access_token'])) {
            AuditLogger::log('OAUTH_TOKEN_EXCHANGE_FAILED', 'System', 'WARNING', 0, 'system', 0, ['response' => $tokenResponse]);
            $_SESSION['error_message'] = "Failed to authenticate with Google.";
            Helpers::redirect('/login');
            return;
        }

        $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tokenData['access_token']]);
        $profileResponse = curl_exec($ch);
        curl_close($ch);

        $profileData = json_decode((string)$profileResponse, true);
        if (empty($profileData['email'])) {
            $_SESSION['error_message'] = "Could not retrieve email address from Google.";
            Helpers::redirect('/login');
            return;
        }

        $googleId = $profileData['id'];
        $email    = $profileData['email'];
        $name     = $profileData['name'] ?? 'Google User';

        $recruiterModel = new Recruiter();
        $user = $recruiterModel->findByGoogleId($googleId);

        if (!$user) {
            $user = $recruiterModel->findByEmail($email);
            if ($user) {
                $recruiterModel->linkGoogleAccount($user['tenant_id'], $googleId);
                $user = $recruiterModel->findByGoogleId($googleId);
                AuditLogger::log('TENANT_ACCOUNT_LINKED_GOOGLE', $user['company_name'], 'INFO', $user['tenant_id'], 'tenant', $user['tenant_id'], ['email' => $email]);
            } else {
                $newTenantData = [
                    'company_name' => $name . "'s Company",
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

        if (($user['deleted_at'] ?? null) !== null || ($user['status'] ?? '') === 'suspended') {
            AuditLogger::log('TENANT_LOGIN_FAILED', $user['company_name'], 'WARNING', $user['tenant_id'], 'tenant', $user['tenant_id'], ['email' => $email, 'reason' => 'account_suspended_google']);
            $_SESSION['error_message'] = "Access Denied: Account state invalid.";
            Helpers::redirect('/login');
            return;
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        unset($user['password_hash'], $user['recovery_code']);
        $_SESSION['tenant_id']      = (int)$user['tenant_id'];
        $_SESSION['tenant_profile'] = $user;
        $_SESSION['user_agent']     = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $_SESSION['client_ip_hash'] = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $recruiterModel->updateLastLogin($user['tenant_id']);
        AuditLogger::log('TENANT_LOGIN_SUCCESS_GOOGLE', $user['company_name'], 'INFO', $user['tenant_id'], 'tenant', $user['tenant_id'], ['email' => $email]);

        Helpers::redirect('/dashboard');
    }
}
