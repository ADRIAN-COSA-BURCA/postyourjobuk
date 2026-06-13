<?php
// =========================================================================
// 1. FORCE PHP TO DISPLAY ENVIRONMENT ERRORS DIRECTLY
// =========================================================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// =========================================================================
// 2. CORE BOOTSTRAP INITIALIZATION
// =========================================================================
require_once __DIR__ . '/../bootstrap.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

$router = new \App\Core\Router();

// =========================================================================
// 3. REGISTER APPLICATION ROUTE MAPS
// =========================================================================

// --- Admin Portal Core & Authentication Routes ---
$router->add('GET',  '/admin',               [\App\Controllers\AdminController::class, 'index']);
$router->add('GET',  '/admin/index.php',     [\App\Controllers\AdminController::class, 'index']);
$router->add('GET',  '/admin/login',         [\App\Controllers\AdminController::class, 'login']);
$router->add('POST', '/admin/login',         [\App\Controllers\AdminController::class, 'authenticateAdmin']);
$router->add('GET',  '/admin/logout',        [\App\Controllers\AdminController::class, 'logout']);

// --- Admin Tenant Management Operations ---
$router->add('GET',  '/admin/view-tenant',      [\App\Controllers\AdminController::class, 'viewTenant']);
$router->add('POST', '/admin/suspend-tenant',   [\App\Controllers\AdminController::class, 'suspendTenant']);
$router->add('POST', '/admin/activate-tenant',  [\App\Controllers\AdminController::class, 'activateTenant']);

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

// --- Profile View & Secure Access Handlers ---
$router->add('GET', '/applicant/view',          [\App\Controllers\ApplicantController::class, 'view']);
$router->add('GET', '/applicant/download',      [\App\Controllers\ApplicantController::class, 'download']);


// =========================================================================
// 4. CONTEXT-AWARE AUTHENTICATION BYPASS MIDDLEWARE
// =========================================================================
// Extract the URI path dynamically to bypass validation rules for non-authenticated public spaces
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$cleanPath = ($requestUri === '/') ? '/' : rtrim($requestUri, '/');

// Absolute non-guarded routes array whitelist
$allowedPublicPaths = [
    '/', 
    '/index.php', 
    '/about', 
    '/contact', 
    '/vision', 
    '/terms', 
    '/login', 
    '/login/authenticate',
    '/admin/login',
    '/apply'
];

// If no administrative session or tenant identity context exists, guard all paths outside our whitelist
if (!isset($_SESSION['tenant_id']) && !isset($_SESSION['admin_logged_in'])) {
    if (!in_array($cleanPath, $allowedPublicPaths)) {
        // Clear conflicting variables and bounce smoothly to workspace portal
        header('Location: /login');
        exit;
    }
}


// =========================================================================
// 5. DISPATCH ENGINE RUNTIME EXECUTOR
// =========================================================================
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);