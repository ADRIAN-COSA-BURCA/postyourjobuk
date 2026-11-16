<?php
namespace App\Core;

abstract class BaseController {
    protected $db;
    protected ?array $parsedBody = null;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = \App\Core\Database::getInstance();
    }

    /**
     * Send standardized JSON response and terminate execution
     */
    protected function json(array $payload, int $statusCode = 200): void {
        Helpers::jsonResponse($payload, $statusCode);
    }

    /**
     * Read unified request payload from JSON body or $_POST
     */
    protected function getBody(): array {
        if ($this->parsedBody !== null) {
            return $this->parsedBody;
        }
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $decoded = $raw ? json_decode($raw, true) : [];
            $this->parsedBody = is_array($decoded) ? $decoded : [];
            return $this->parsedBody;
        }
        if (!empty($_POST)) {
            $this->parsedBody = $_POST;
            return $this->parsedBody;
        }
        $raw = file_get_contents('php://input');
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $this->parsedBody = $decoded;
                return $this->parsedBody;
            }
        }
        $this->parsedBody = [];
        return $this->parsedBody;
    }

    /**
     * Enforce CSRF token validation for state-mutating API endpoints
     */
    protected function requireCsrf(string $violationEvent = 'CSRF_API_VIOLATION'): void {
        $body = $this->getBody();
        $token = $body['csrf_token'] ?? null;
        if (!Helpers::csrf_verify(is_string($token) ? $token : null)) {
            $tenantId = (int)($_SESSION['tenant_id'] ?? 0);
            SecurityLogger::logAlert($violationEvent, $tenantId, 'Invalid or missing CSRF token on API request.', 'WARNING');
            $this->json([
                'success' => false,
                'error'   => 'Security token validation failed. Please refresh the page and try again.'
            ], 403);
        }
    }

    /**
     * Enforce Recruiter Tenant Authentication + Zero-Trust Session Fingerprint + Active Status
     */
    protected function requireTenantAuth(): int {
        if (!$this->isLoggedIn()) {
            $this->json([
                'success'  => false,
                'error'    => 'Please log in to access your recruiter workspace.',
                'redirect' => '/login'
            ], 401);
        }

        if (!Auth::checkSessionFingerprint()) {
            error_log("Security: Session fingerprint mismatch or missing. Destroying session.");
            $this->destroySession();
            $this->json([
                'success'  => false,
                'error'    => 'Your session has expired for security reasons. Please log in again.',
                'redirect' => '/login'
            ], 401);
        }

        if (!Auth::isTenantActive()) {
            error_log("Security: Inactive tenant attempted access. Destroying session.");
            $this->destroySession();
            $this->json([
                'success'  => false,
                'error'    => 'Your company workspace is currently inactive or suspended. Please contact support.',
                'redirect' => '/login'
            ], 403);
        }

        return (int)$_SESSION['tenant_id'];
    }

    /**
     * Enforce Super-Admin Authentication + Zero-Trust Session Fingerprint
     */
    protected function requireAdminAuth(): void {
        \App\Middleware\AdminAuth::checkApi();
    }

    protected function isLoggedIn(): bool {
        return isset($_SESSION['tenant_id']) && (int)$_SESSION['tenant_id'] > 0;
    }

    protected function destroySession(): void {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}
