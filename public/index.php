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
// bootstrap.php already handles composer loading and session initialization
require_once __DIR__ . '/../bootstrap.php';

// Custom autoloader fallback if Composer mapping isn't fully updated yet
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

// Instantiate the centralized routing engine
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

// =========================================================================
// 4. DISPATCH ENGINE RUNTIME EXECUTOR
// =========================================================================
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);