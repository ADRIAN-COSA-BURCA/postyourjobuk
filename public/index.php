<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../bootstrap.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Public Routes
if ($path === '/' || $path === '/index.php') {
    (new \App\Controllers\HomeController())->index();
} 
// Authentication Routes
elseif ($path === '/login') {
    (new \App\Controllers\AuthController())->login();
} 
elseif ($path === '/login/authenticate') {
    (new \App\Controllers\AuthController())->authenticate();
} 
elseif ($path === '/logout') {
    (new \App\Controllers\AuthController())->logout();
} 


// Protected Routes
elseif ($path === '/dashboard') {
    (new \App\Controllers\DashboardController())->index();
}

elseif ($path === '/job') {
    $id = $_GET['id'] ?? null;
    (new \App\Controllers\JobController())->show($id);
}

// Fallback
else {
    http_response_code(404);
    echo "404 Not Found";
}