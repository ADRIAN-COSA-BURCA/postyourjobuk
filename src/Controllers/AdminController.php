<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Admin;
use App\Core\Helpers;
use App\Core\SecurityLogger;
use App\Middleware\AdminAuth;

class AdminController extends BaseController {

    public function __construct() {
        parent::__construct();
    }

    public function login() {
        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
            Helpers::redirect('/admin');
            return;
        }
        $this->render('admin/login', [
            'pageTitle' => 'Admin Login'
        ], 'layouts/login_layout');
    }

    public function authenticateAdmin() {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $this->db->prepare("SELECT * FROM tenants WHERE email = ? AND is_super_admin = 1 AND status = 'active' LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if (!$admin) {
            $_SESSION['error_message'] = "Admin user not found or inactive.";
            Helpers::redirect('/admin/login');
            return;
        }

        if (!password_verify($password, $admin['password_hash'])) {
            $_SESSION['error_message'] = "Invalid credentials.";
            Helpers::redirect('/admin/login');
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $_SESSION['admin_ip_hash'] = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

        // Generate high-fidelity security token
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        Helpers::redirect('/admin');
    }
	
    public function logout() {
        $redirect = '/';
        if (isset($_SESSION['admin_logged_in'])) {
            $redirect = '/admin/login';
        } elseif (isset($_SESSION['tenant_id'])) {
            $redirect = '/login';
        }

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
        Helpers::redirect($redirect);
    }

    public function index() {
        AdminAuth::check(); 

        $adminModel = new Admin();
        $stats = $adminModel->getSystemStats();
        
        // Exclude super admin records and hidden soft-deleted items
        $stmt = $this->db->query("SELECT tenant_id, company_name, status FROM tenants WHERE is_super_admin = 0 AND deleted_at IS NULL");
        $tenants = $stmt->fetchAll();
        
        SecurityLogger::logAlert("ACCESS_DASHBOARD", 0, "Admin accessed the System Dashboard.");
        
        // Ensure CSRF token is built for active console state modifications
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $this->render('admin/dashboard', [
            'pageTitle' => 'Admin Control Panel',
            'stats' => $stats,
            'tenants' => $tenants
        ]);
    }

    public function viewTenant() {
        AdminAuth::check(); 
        
        // Safely extract the ID from the global query parameters matching the router framework
        $tenantId = (int)($_GET['id'] ?? 0);

        if ($tenantId <= 0) {
            $_SESSION['error_message'] = "Invalid operational workspace ID.";
            Helpers::redirect('/admin');
            return;
        }

        $stmt = $this->db->prepare("SELECT * FROM tenants WHERE tenant_id = ? AND is_super_admin = 0 AND deleted_at IS NULL");
        $stmt->execute([$tenantId]);
        $tenant = $stmt->fetch();

        if (!$tenant) {
            $_SESSION['error_message'] = "Tenant workspace profile not found.";
            Helpers::redirect('/admin');
            return;
        }

        $stmt = $this->db->prepare(
            "SELECT job_id, title, status, created_at, 
            (SELECT COUNT(*) FROM applicants WHERE job_id = jobs.job_id) as applicant_count
            FROM jobs WHERE tenant_id = ? ORDER BY created_at DESC"
        );
        $stmt->execute([$tenantId]);
        $jobs = $stmt->fetchAll();

        $this->render('admin/view_tenant', [
            'pageTitle' => 'Viewing Tenant: ' . htmlspecialchars($tenant['company_name']),
            'tenant' => $tenant,
            'jobs' => $jobs
        ]);
    }

    public function suspendTenant() {
        AdminAuth::check();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helpers::redirect('/admin');
            return;
        }

        // Validate Security Handshake token to prevent cross-site automation exploits
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error_message'] = "Security token validation failed.";
            Helpers::redirect('/admin');
            return;
        }

        $tenantId = (int)($_GET['id'] ?? 0);

        // Verify entity state exists and is not a core infrastructure super admin
        $stmt = $this->db->prepare("SELECT is_super_admin FROM tenants WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        $target = $stmt->fetch();

        if (!$target || (int)$target['is_super_admin'] === 1) {
            $_SESSION['error_message'] = "Action restricted: Cannot modify core administrative properties.";
            Helpers::redirect('/admin');
            return;
        }

        // Apply Quarantine Posture to isolation node
        $stmt = $this->db->prepare("UPDATE tenants SET status = 'suspended', is_active = 0 WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);

        SecurityLogger::logAlert("ADMIN_USER_QUARANTINE", $tenantId, "Super Admin changed corporate account posture to: suspended");
        $_SESSION['success_message'] = "Tenant workspace suspended successfully.";
        Helpers::redirect('/admin/view-tenant?id=' . $tenantId);
    }

    public function activateTenant() {
        AdminAuth::check();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helpers::redirect('/admin');
            return;
        }

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error_message'] = "Security token validation failed.";
            Helpers::redirect('/admin');
            return;
        }

        $tenantId = (int)($_GET['id'] ?? 0);

        $stmt = $this->db->prepare("UPDATE tenants SET status = 'active', is_active = 1 WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);

        SecurityLogger::logAlert("ADMIN_USER_REHABILITATION", $tenantId, "Super Admin changed corporate account posture to: active");
        $_SESSION['success_message'] = "Tenant workspace activated successfully.";
        Helpers::redirect('/admin/view-tenant?id=' . $tenantId);
    }
}