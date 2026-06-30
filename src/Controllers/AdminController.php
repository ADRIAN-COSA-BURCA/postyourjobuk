<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Admin;
use App\Models\Recruiter;
use App\Core\Helpers;
use App\Core\AuditLogger; // Integrated AuditLogger
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
            AuditLogger::log('ADMIN_LOGIN_FAILED', 'System', 0, 'admin', 0, ['email' => $email, 'reason' => 'user not found']);
            $_SESSION['error_message'] = "Admin user not found or inactive.";
            Helpers::redirect('/admin/login');
            return;
        }

        if (!password_verify($password, $admin['password_hash'])) {
            AuditLogger::log('ADMIN_LOGIN_FAILED', 'Admin: ' . $email, 0, 'admin', 0, ['email' => $email, 'reason' => 'invalid password']);
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

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        AuditLogger::log('ADMIN_LOGIN_SUCCESS', 'Admin: ' . $admin['email'], 0, 'admin', 0, ['email' => $email]);
        Helpers::redirect('/admin');
    }
    
    public function logout() {
        $email = $_SESSION['admin_email'] ?? 'unknown';
        $userLabel = 'Admin: ' . $email;
        
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
        AuditLogger::log('ADMIN_LOGOUT', $userLabel, 0, 'admin', 0, ['email' => $email]);
        Helpers::redirect('/admin/login');
    }

    public function index() {
        AdminAuth::check(); 

        $adminModel = new Admin();
        $stats = $adminModel->getSystemStats();
        
        $sort = $_GET['sort'] ?? 'company_name';
        $order = ($_GET['order'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
        $filter = $_GET['filter'] ?? 'all';
        
        $allowedSort = ['company_name', 'status', 'created_at'];
        $sort = in_array($sort, $allowedSort) ? $sort : 'company_name';
        $allowedFilters = ['all', 'active', 'suspended'];
        $filter = in_array($filter, $allowedFilters) ? $filter : 'all';

        $whereClause = "is_super_admin = 0 AND deleted_at IS NULL";
        $params = [];

        if ($filter !== 'all') {
            $whereClause .= " AND status = :status";
            $params['status'] = $filter;
        }

        $sql = "SELECT tenant_id, company_name, status, created_at 
                FROM tenants 
                WHERE $whereClause 
                ORDER BY $sort $order";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $tenants = $stmt->fetchAll();
        
        $this->render('admin/dashboard', [
            'stats' => $stats,
            'tenants' => $tenants,
            'currentSort' => $sort,
            'currentOrder' => $order,
            'currentFilter' => $filter
        ]);
    }
    
    public function createTenant() {
        AdminAuth::check();

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error_message'] = "Security token validation failed.";
            Helpers::redirect('/admin');
            return;
        }

        if (empty($_POST['company_name']) || empty($_POST['email']) || empty($_POST['password'])) {
            $_SESSION['error_message'] = "Company name, email, and password are required.";
            Helpers::redirect('/admin');
            return;
        }

        $recruiterModel = new Recruiter();
        $data = [
            'company_name'   => $_POST['company_name'],
            'contact_person' => $_POST['contact_person'] ?? null,
            'email'          => $_POST['email'],
            'phone_number'   => $_POST['phone_number'] ?? null,
            'website_url'    => $_POST['website_url'] ?? null,
            'company_address'=> $_POST['company_address'] ?? null,
            'industry'       => $_POST['industry'] ?? null,
            'password_hash'  => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'status'         => 'active',
            'is_active'      => 1,
            'is_super_admin' => 0,
            'created_at'     => date('Y-m-d H:i:s')
        ];

        if ($recruiterModel->create($data)) {
            $newId = $this->db->lastInsertId();
            $adminLabel = 'Admin: ' . ($_SESSION['admin_email'] ?? 'Unknown');
            AuditLogger::log('TENANT_CREATED', $adminLabel, 0, 'tenant', $newId, ['company' => $_POST['company_name']]);
            $_SESSION['success_message'] = "Tenant created successfully.";
        } else {
            $_SESSION['error_message'] = "Creation failed.";
        }
        
        Helpers::redirect('/admin');
    }

    public function viewTenant() {
        AdminAuth::check(); 
        
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

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error_message'] = "Security token validation failed.";
            Helpers::redirect('/admin');
            return;
        }

        $tenantId = (int)($_GET['id'] ?? 0);

        $stmt = $this->db->prepare("SELECT is_super_admin FROM tenants WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        $target = $stmt->fetch();

        if (!$target || (int)$target['is_super_admin'] === 1) {
            $_SESSION['error_message'] = "Action restricted: Cannot modify core administrative properties.";
            Helpers::redirect('/admin');
            return;
        }

        $stmt = $this->db->prepare("UPDATE tenants SET status = 'suspended', is_active = 0 WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);

        $adminLabel = 'Admin: ' . ($_SESSION['admin_email'] ?? 'Unknown');
        AuditLogger::log('TENANT_SUSPEND', $adminLabel, 0, 'tenant', $tenantId, ['status' => 'suspended']);
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

        $adminLabel = 'Admin: ' . ($_SESSION['admin_email'] ?? 'Unknown');
        AuditLogger::log('TENANT_ACTIVATE', $adminLabel, 0, 'tenant', $tenantId, ['status' => 'active']);
        $_SESSION['success_message'] = "Tenant workspace activated successfully.";
        Helpers::redirect('/admin/view-tenant?id=' . $tenantId);
    }
    
    public function editTenant() {
        AdminAuth::check();
        $tenantId = (int)($_GET['id'] ?? 0);

        $stmt = $this->db->prepare("SELECT * FROM tenants WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        $tenant = $stmt->fetch();

        if (!$tenant) {
            $_SESSION['error_message'] = "Tenant not found.";
            Helpers::redirect('/admin');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
                $_SESSION['error_message'] = "Security token validation failed.";
                Helpers::redirect('/admin/view-tenant?id=' . $tenantId);
                return;
            }

            $allowed = ['company_name', 'contact_person', 'phone_number', 'website_url', 'company_address', 'industry'];
            $data = [];
            $fields = [];

            foreach ($_POST as $key => $value) {
                if (in_array($key, $allowed)) {
                    $data[$key] = $value;
                    $fields[] = "$key = :$key";
                }
            }
            $data['updated_at'] = date('Y-m-d H:i:s');
            $fields[] = "updated_at = :updated_at";
            $data['tenant_id'] = $tenantId;

            $sql = "UPDATE tenants SET " . implode(', ', $fields) . " WHERE tenant_id = :tenant_id";
            
            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($data)) {
                $adminLabel = 'Admin: ' . ($_SESSION['admin_email'] ?? 'Unknown');
                AuditLogger::log('TENANT_UPDATED', $adminLabel, 0, 'tenant', $tenantId, ['company' => $data['company_name']]);
                $_SESSION['success_message'] = "Tenant details updated successfully.";
            } else {
                $_SESSION['error_message'] = "Update failed.";
            }
            Helpers::redirect('/admin/view-tenant?id=' . $tenantId);
            return;
        }

        $this->render('admin/edit_tenant', ['tenant' => $tenant]);
    }
}