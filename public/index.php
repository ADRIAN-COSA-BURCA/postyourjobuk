<?php
// 1. PRODUCTION-READY ERROR HANDLING
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// 2. SESSION INITIALIZATION (Must be the very first step)
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// 3. CORE BOOTSTRAP & AUTOLOADER
require_once __DIR__ . '/../bootstrap.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $base_dir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});

// 4. PREPARE REQUEST CONTEXT
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$cleanPath = ($requestUri === '/') ? '/' : rtrim($requestUri, '/');

// 5. AUTHENTICATION MIDDLEWARE
$allowedPublicPaths = ['/', '/index.php', '/about', '/contact', '/vision', '/terms', '/login', '/login/authenticate', '/admin/login', '/apply', '/job'];
$isPublic = false;

foreach ($allowedPublicPaths as $route) {
    if ($cleanPath === $route || str_starts_with($cleanPath, $route . '/')) {
        $isPublic = true;
        break;
    }
}

// DEBUG: Log status after variables are set
error_log("DEBUG: Path: " . $cleanPath . " | Session Tenant: " . ($_SESSION['tenant_id'] ?? 'NONE'));

if (!$isPublic && !isset($_SESSION['tenant_id']) && !isset($_SESSION['admin_logged_in'])) {
    header('Location: /login');
    exit;
}

// 6. ROUTER & DISPATCH
$router = new \App\Core\Router();

// =========================================================================
// 3. REGISTER APPLICATION ROUTE MAPS
// =========================================================================

// --- Admin Portal Core & Authentication Routes ---
$router->add('GET',  '/admin',               [\App\Controllers\AdminController::class, 'index']);
$router->add('GET',  '/admin/index.php',     [\App\Controllers\AdminController::class, 'index']);
$router->add('GET',  '/admin/logs',          [\App\Controllers\AuditController::class, 'index']);
$router->add('GET',  '/admin/login',         [\App\Controllers\AdminController::class, 'login']);
$router->add('POST', '/admin/login',         [\App\Controllers\AdminController::class, 'authenticateAdmin']);
$router->add('GET',  '/admin/logout',        [\App\Controllers\AdminController::class, 'logout']);

// --- Admin Tenant Management Operations ---
$router->add('GET',  '/admin/view-tenant',      [\App\Controllers\AdminController::class, 'viewTenant']);
$router->add('POST', '/admin/suspend-tenant',   [\App\Controllers\AdminController::class, 'suspendTenant']);
$router->add('POST', '/admin/activate-tenant',  [\App\Controllers\AdminController::class, 'activateTenant']);
$router->add('POST', '/admin/tenant/create', [\App\Controllers\AdminController::class, 'createTenant']);

$router->add('GET',  '/admin/tenant/edit', [\App\Controllers\AdminController::class, 'editTenant']);
$router->post('/admin/tenant/edit', [\App\Controllers\AdminController::class, 'editTenant']);


// --- Public Base Routes ---
$router->add('GET',  '/',                       [\App\Controllers\HomeController::class, 'index']);
$router->add('GET',  '/index.php',              [\App\Controllers\HomeController::class, 'index']);

$router->add('GET',  '/about',                  [\App\Controllers\HomeController::class, 'about']);
$router->add('GET',  '/contact',                [\App\Controllers\HomeController::class, 'contact']);
$router->add('GET',  '/vision',                 [\App\Controllers\HomeController::class, 'vision']);
$router->add('GET',  '/terms',                  [\App\Controllers\HomeController::class, 'terms']);

// --- Recruiter Authentication Identity Routes ---
$router->add('GET',  '/login',                  [\App\Controllers\AuthController::class, 'login']);
$router->add('POST', '/login/authenticate',     [\App\Controllers\AuthController::class, 'authenticate']);
$router->add('GET',  '/logout',                 [\App\Controllers\AuthController::class, 'logout']);

// --- Protected Workspace Tenant Routes ---
$router->add('GET',  '/dashboard',              [\App\Controllers\DashboardController::class, 'index']);
$router->add('GET',  '/job',                    [\App\Controllers\JobController::class, 'show']);

// --- Job Creation Lifecycle Management Routes ---
$router->add('GET',  '/jobs/create',            [\App\Controllers\JobController::class, 'create']);
$router->add('POST', '/jobs/store',             [\App\Controllers\JobController::class, 'store']);
$router->add('GET',  '/jobs/edit',              [\App\Controllers\JobController::class, 'edit']);
$router->add('POST', '/jobs/update',            [\App\Controllers\JobController::class, 'update']);
$router->add('GET',  '/jobs/delete',            [\App\Controllers\JobController::class, 'delete']);

// --- Applicant Tracking System (ATS) Routes ---
$router->add('GET',  '/applicants',             [\App\Controllers\ApplicantController::class, 'index']);
$router->add('POST', '/apply',                  [\App\Controllers\ApplicantController::class, 'store']);
$router->add('POST', '/applicant/delete',       [\App\Controllers\ApplicantController::class, 'delete']); 
$router->add('POST', '/applicant/delete-all',   [\App\Controllers\ApplicantController::class, 'deleteAll']);

// --- Profile View & Secure Access Handlers ---
$router->add('GET', '/applicant/view',          [\App\Controllers\ApplicantController::class, 'view']);
$router->add('GET', '/applicant/download',      [\App\Controllers\ApplicantController::class, 'download']);


// =========================================================================
// 4. CONTEXT-AWARE AUTHENTICATION BYPASS MIDDLEWARE
// =========================================================================
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$cleanPath = ($requestUri === '/') ? '/' : rtrim($requestUri, '/');

$allowedPublicPaths = ['/', '/index.php', '/about', '/contact', '/vision', '/terms', '/login', '/login/authenticate', '/admin/login', '/apply', '/job'];

$isPublic = false;
foreach ($allowedPublicPaths as $route) {
    if ($cleanPath === $route || str_starts_with($cleanPath, $route . '/')) {
        $isPublic = true;
        break;
    }
}

// DEBUG: Log the exact state before the check
error_log("DEBUG: Middleware Checking Path: $cleanPath | Session: " . json_encode($_SESSION));

// FIX: Ensure we are checking for the presence of the session keys correctly
$isAuthenticated = isset($_SESSION['tenant_id']) || isset($_SESSION['admin_logged_in']);

if (!$isPublic && !$isAuthenticated) {
    error_log("DEBUG: Access Denied. Redirecting to /login");
    header('Location: /login');
    exit;
}

// 6. DISPATCH
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);