<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

// 1. Load Environment Variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

if (!function_exists('app_env')) {
    function app_env(string $key, string $default = ''): string {
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return (string)$_ENV[$key];
        }
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return (string)$_SERVER[$key];
        }
        $val = getenv($key);
        if ($val !== false && $val !== '') {
            return (string)$val;
        }
        return $default;
    }
}

// 2. Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Global Helpers
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('time_ago')) {
    function time_ago($datetime) {
        $timestamp = strtotime((string)$datetime);
        if (!$timestamp) return 'just now';
        $diff = time() - $timestamp;
        if ($diff < 60) return 'just now';
        if ($diff < 3600) return floor($diff / 60) . ' mins ago';
        if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
        return floor($diff / 86400) . ' days ago';
    }
}

// 4. Define Constants
if (!defined('BASE_URL')) {
    define('BASE_URL', app_env('BASE_URL', 'https://postyourjobuk-web-gxfzh2bcb9azbadw.polandcentral-01.azurewebsites.net'));
}
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

// 4.1 Define Database Constants (Resilient across .env and Azure App Service / Key Vault)
if (!defined('DB_HOST')) define('DB_HOST', app_env('DB_HOST'));
if (!defined('DB_NAME')) define('DB_NAME', app_env('DB_NAME'));
if (!defined('DB_USER')) define('DB_USER', app_env('DB_USER'));
if (!defined('DB_PASS')) define('DB_PASS', app_env('DB_PASS'));
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

// 4.2 Define Azure Blob Storage Constants
if (!defined('AZURE_STORAGE_ACCOUNT_NAME')) define('AZURE_STORAGE_ACCOUNT_NAME', app_env('AZURE_STORAGE_ACCOUNT_NAME'));
if (!defined('AZURE_STORAGE_ACCOUNT_KEY')) define('AZURE_STORAGE_ACCOUNT_KEY', app_env('AZURE_STORAGE_ACCOUNT_KEY'));
if (!defined('AZURE_STORAGE_CONTAINER')) define('AZURE_STORAGE_CONTAINER', app_env('AZURE_STORAGE_CONTAINER', 'cv-storage'));
if (!defined('AZURE_STORAGE_API_VERSION')) define('AZURE_STORAGE_API_VERSION', app_env('AZURE_STORAGE_API_VERSION', '2023-11-03'));
