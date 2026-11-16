<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

// Global Uncaught Exception Handler (Pure JSON)
set_exception_handler(function (\Throwable $e): void {
    error_log("API UNCAUGHT EXCEPTION: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    \App\Core\Helpers::jsonResponse([
        'success' => false,
        'error'   => 'An unexpected internal server error occurred.'
    ], 500);
});

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

$router = new \App\Core\Router();

// =========================================================================
// 1. HEALTH & SESSION DISCOVERY ROUTES
// =========================================================================
$router->get('/api/health', [\App\Controllers\AuthController::class, 'session']);
$router->get('/api/auth/session', [\App\Controllers\AuthController::class, 'session']);

// =========================================================================
// 2. PUBLIC PORTAL & CANDIDATE APPLICATION ROUTES
// =========================================================================
$router->get('/api/public/home', [\App\Controllers\HomeController::class, 'index']);
$router->post('/api/public/contact', [\App\Controllers\HomeController::class, 'contactSubmit']);
$router->get('/api/fetch-news', [\App\Controllers\NewsController::class, 'fetchFeeds']);
$router->get('/api/refresh-news', [\App\Controllers\HomeController::class, 'refreshNews']);
$router->post('/api/news/refresh', [\App\Controllers\HomeController::class, 'refreshNews']);

$router->get('/api/jobs/detail', [\App\Controllers\JobController::class, 'show']);
$router->post('/api/apply', [\App\Controllers\ApplicantController::class, 'store']);

// =========================================================================
// 3. RECRUITER AUTHENTICATION & OAUTH ROUTES
// =========================================================================
$router->post('/api/auth/login', [\App\Controllers\AuthController::class, 'authenticate']);
$router->post('/api/auth/logout', [\App\Controllers\AuthController::class, 'logout']);
$router->get('/api/auth/logout', [\App\Controllers\AuthController::class, 'logout']);
$router->post('/api/auth/reset-password', [\App\Controllers\AuthController::class, 'handleResetSubmit']);
$router->get('/api/auth/google', [\App\Controllers\AuthController::class, 'googleLogin']);
$router->get('/api/auth/google/callback', [\App\Controllers\AuthController::class, 'googleCallback']);
$router->get('/auth/google', [\App\Controllers\AuthController::class, 'googleLogin']);
$router->get('/auth/google/callback', [\App\Controllers\AuthController::class, 'googleCallback']);

// =========================================================================
// 4. RECRUITER WORKSPACE, JOB CRUD & ATS PIPELINE ROUTES
// =========================================================================
$router->get('/api/dashboard', [\App\Controllers\DashboardController::class, 'index']);
$router->get('/api/dashboard/analytics', [\App\Controllers\DashboardController::class, 'analytics']);

$router->post('/api/jobs/store', [\App\Controllers\JobController::class, 'store']);
$router->post('/api/jobs/update', [\App\Controllers\JobController::class, 'update']);
$router->post('/api/jobs/delete', [\App\Controllers\JobController::class, 'delete']);

$router->get('/api/applicants', [\App\Controllers\ApplicantController::class, 'index']);
$router->get('/api/applicants/detail', [\App\Controllers\ApplicantController::class, 'view']);
$router->get('/api/applicants/download', [\App\Controllers\ApplicantController::class, 'download']);
$router->get('/applicant/download', [\App\Controllers\ApplicantController::class, 'download']);
$router->post('/api/applicants/status', [\App\Controllers\ApplicantController::class, 'changeStatus']);
$router->post('/api/applicants/delete', [\App\Controllers\ApplicantController::class, 'delete']);
$router->post('/api/applicants/delete-all', [\App\Controllers\ApplicantController::class, 'deleteAll']);

// =========================================================================
// 5. SUPER-ADMIN GOVERNANCE, ANALYTICS, LOGS & FAIRNESS SUITE ROUTES
// =========================================================================
$router->post('/api/admin/login', [\App\Controllers\AdminController::class, 'authenticateAdmin']);
$router->post('/api/admin/logout', [\App\Controllers\AdminController::class, 'logout']);
$router->get('/api/admin/logout', [\App\Controllers\AdminController::class, 'logout']);
$router->get('/api/admin/dashboard', [\App\Controllers\AdminController::class, 'index']);
$router->get('/api/admin/analytics', [\App\Controllers\AdminController::class, 'analytics']);
$router->get('/api/admin/logs', [\App\Controllers\AuditController::class, 'index']);

$router->post('/api/admin/tenants/create', [\App\Controllers\AdminController::class, 'createTenant']);
$router->get('/api/admin/tenants/detail', [\App\Controllers\AdminController::class, 'viewTenant']);
$router->post('/api/admin/tenants/suspend', [\App\Controllers\AdminController::class, 'suspendTenant']);
$router->post('/api/admin/tenants/activate', [\App\Controllers\AdminController::class, 'activateTenant']);
$router->post('/api/admin/tenants/edit', [\App\Controllers\AdminController::class, 'editTenant']);

$router->get('/api/admin/fairness', [\App\Controllers\AdminController::class, 'fairness']);
$router->post('/api/admin/fairness/run', [\App\Controllers\AdminController::class, 'runFairnessAudit']);
$router->get('/api/admin/fairness/status', [\App\Controllers\AdminController::class, 'fairnessStatus']);
$router->get('/api/admin/fairness/detail', [\App\Controllers\AdminController::class, 'fairnessDetail']);
$router->get('/api/admin/fairness/certificate', [\App\Controllers\AdminController::class, 'fairnessCertificate']);

// Dispatch Request
$router->dispatch($_SERVER['REQUEST_URI'] ?? '/', $_SERVER['REQUEST_METHOD'] ?? 'GET');
