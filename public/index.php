<?php
// 1. PRODUCTION-READY ERROR HANDLING
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// 2. SESSION & SECURITY HEADERS (Moved to the very top)
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => false, // Set to true if using HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Prevent browser caching immediately
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

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

// 4. PREPARE REQUEST CONTEXT (Normalized to match Router)
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$cleanPath = ($requestUri === '/') ? '/' : rtrim($requestUri, '/');

// 5. AUTHENTICATION & ROLE-BASED MIDDLEWARE
$allowedPublicPaths = [
    '/', '/index.php', '/about', '/contact', '/vision', '/terms', 
    '/login', '/login/authenticate', '/admin/login', '/apply', '/job',
    '/reset-password', '/reset-password/submit', '/api/fetch-news', '/api/refresh-news',
    '/auth/google', '/auth/google/callback' // ADDED for Google OAuth
];

$isPublic = in_array($cleanPath, $allowedPublicPaths);
if (!$isPublic) {
    error_log("DEBUG: Middleware blocked access to: " . $cleanPath);
}

// 6. VIEW CONTEXT
$viewContext = 'public';
if (str_starts_with($cleanPath, '/admin')) {
    $viewContext = 'admin';
} elseif (str_starts_with($cleanPath, '/dashboard') || str_starts_with($cleanPath, '/jobs') || str_starts_with($cleanPath, '/applicants')) {
    $viewContext = 'tenant';
} elseif (str_contains($cleanPath, '/login')) {
    $viewContext = 'login';
}
$GLOBALS['viewContext'] = $viewContext;

// 7. ROUTER INITIALIZATION
$router = new \App\Core\Router();

// =========================================================================
// 3. REGISTER APPLICATION ROUTE MAPS
// =========================================================================

// --- Admin Portal Core & Authentication Routes ---
$router->add('GET',  '/admin',               [\App\Controllers\AdminController::class, 'index']);
$router->add('GET',  '/admin/index.php',     [\App\Controllers\AdminController::class, 'index']);
$router->add('GET',  '/admin/analytics',     [\App\Controllers\AdminController::class, 'analytics']);
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

// --- Background Task Routes ---
$router->add('GET', '/api/fetch-news', [\App\Controllers\NewsController::class, 'fetchFeeds']);
$router->add('GET', '/api/refresh-news', [\App\Controllers\HomeController::class, 'refreshNews']);

// --- Recruiter Authentication Identity Routes ---
$router->add('GET',  '/login',                  [\App\Controllers\AuthController::class, 'login']);
$router->add('POST', '/login/authenticate',     [\App\Controllers\AuthController::class, 'authenticate']);
$router->add('GET',  '/logout',                 [\App\Controllers\AuthController::class, 'logout']);

// Password Reset Routes
$router->add('GET',  '/reset-password', [\App\Controllers\AuthController::class, 'showResetForm']);
$router->add('POST', '/reset-password/submit', [\App\Controllers\AuthController::class, 'handleResetSubmit']);

// --- Google OAuth Routes (ADDED) ---
$router->add('GET',  '/auth/google',            [\App\Controllers\AuthController::class, 'googleLogin']);
$router->add('GET',  '/auth/google/callback',   [\App\Controllers\AuthController::class, 'googleCallback']);

// --- Protected Workspace Tenant Routes ---
$router->add('GET',  '/dashboard',              [\App\Controllers\DashboardController::class, 'index']);
$router->add('GET',  '/dashboard/analytics',    [\App\Controllers\DashboardController::class, 'analytics']);
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

// 5.5 PREVENT PAGE CACHING (Crucial for Logout functionality)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 6. DISPATCH
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);