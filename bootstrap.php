<?php
// bootstrap.php
require_once __DIR__ . '/vendor/autoload.php';

// 1. Load Environment Variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// 2. Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Global Helpers
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' mins ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    return floor($diff / 86400) . ' days ago';
}

// 4. Define Constants
define('BASE_URL', $_ENV['BASE_URL'] ?? 'http://localhost');
define('ROOT_PATH', __DIR__); // Essential for finding your SSL certificate

// 4.1 Define Database Constants (These map to your .env file)
define('DB_HOST', $_ENV['DB_HOST']);
define('DB_NAME', $_ENV['DB_NAME']);
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);
define('DB_CHARSET', 'utf8mb4');

// 5. Initialize Database
// We use the full path to match your folder structure
require_once __DIR__ . '/config/Database.php';

// Optional: Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);