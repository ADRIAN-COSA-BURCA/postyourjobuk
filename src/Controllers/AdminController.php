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
        $this->render('admin/login', ['pageTitle' => 'Administrator Portal Secure Login']);
    }

    public function authenticateAdmin() {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $this->db->prepare("SELECT * FROM tenants WHERE email = ? AND is_super_admin = 1 AND status = 'active' LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        // DIAGNOSTIC BLOCK: If it fails, this will tell you exactly why
        if (!$admin) {
            $_SESSION['error_message'] = "DEBUG: User not found or not a super admin.";
            Helpers::redirect('/admin/login');
            return;
        }

        if (!password_verify($password, $admin['password_hash'])) {
            $_SESSION['error_message'] = "DEBUG: Password verification failed. Hash in DB: " . substr($admin['password_hash'], 0, 10) . "...";
            Helpers::redirect('/admin/login');
            return;
        }

        
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email'] = $admin['email'];
		
		// ADD THESE TWO LINES TO ENABLE THE ZERO-TRUST CHECKS
$_SESSION['admin_user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
$_SESSION['admin_ip_hash'] = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

        Helpers::redirect('/admin');
    }
	
	
	public function logout() {
    // 1. Determine redirect path before destroying session
    $redirect = '/';
    if (isset($_SESSION['admin_logged_in'])) {
        $redirect = '/admin/login';
    } elseif (isset($_SESSION['tenant_id'])) {
        $redirect = '/login'; // Or your tenant login path
    }

    // 2. Clear session data
    $_SESSION = [];

    // 3. Destroy session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();

    // 4. Redirect
    Helpers::redirect($redirect);
}

    public function index() {
        AdminAuth::check(); // Redirects to login if not authenticated

        $adminModel = new Admin();
        $stats = $adminModel->getSystemStats();
        
        $stmt = $this->db->query("SELECT tenant_id, company_name, status FROM tenants WHERE is_super_admin = 0");
        $tenants = $stmt->fetchAll();
        
        SecurityLogger::logAlert("ACCESS_DASHBOARD", 0, "Admin accessed the System Dashboard.");
        
        $this->render('admin/dashboard', [
            'pageTitle' => 'Admin Control Panel',
            'stats' => $stats,
            'tenants' => $tenants
        ]);
    }

    public function viewTenant($id) {
        AdminAuth::check(); 
        $tenantId = (int)$id;

        $stmt = $this->db->prepare("SELECT * FROM tenants WHERE tenant_id = ? AND is_super_admin = 0");
        $stmt->execute([$tenantId]);
        $tenant = $stmt->fetch();

        if (!$tenant) {
            $_SESSION['error_message'] = "Tenant not found.";
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

    // ... suspendTenant and activateTenant methods remain unchanged ...
}