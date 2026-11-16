<?php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

$projectRoot = dirname(__DIR__);
require_once $projectRoot . '/vendor/autoload.php';

// 1. Load Environment Variables from Root .env (if present)
$dotenv = Dotenv\Dotenv::createImmutable($projectRoot);
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

// 2. Secure Session Initialization (Same-Origin HttpOnly + Lax + Secure on HTTPS)
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Auto-seed CSRF token for all sessions
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 3. Global Helpers
if (!function_exists('e')) {
    function e($value): string {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('time_ago')) {
    function time_ago($datetime): string {
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
    define('ROOT_PATH', $projectRoot);
}
if (!defined('SERVER_PATH')) {
    define('SERVER_PATH', __DIR__);
}

// 4.1 Database Constants
if (!defined('DB_HOST')) define('DB_HOST', app_env('DB_HOST'));
if (!defined('DB_NAME')) define('DB_NAME', app_env('DB_NAME'));
if (!defined('DB_USER')) define('DB_USER', app_env('DB_USER'));
if (!defined('DB_PASS')) define('DB_PASS', app_env('DB_PASS'));
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

// 4.2 Azure Storage Constants
if (!defined('AZURE_STORAGE_ACCOUNT_NAME')) define('AZURE_STORAGE_ACCOUNT_NAME', app_env('AZURE_STORAGE_ACCOUNT_NAME'));
if (!defined('AZURE_STORAGE_ACCOUNT_KEY')) define('AZURE_STORAGE_ACCOUNT_KEY', app_env('AZURE_STORAGE_ACCOUNT_KEY'));
if (!defined('AZURE_STORAGE_CONTAINER')) define('AZURE_STORAGE_CONTAINER', app_env('AZURE_STORAGE_CONTAINER', 'cv-storage'));
if (!defined('AZURE_STORAGE_API_VERSION')) define('AZURE_STORAGE_API_VERSION', app_env('AZURE_STORAGE_API_VERSION', '2023-11-03'));

// 5. PSR-4 Autoloader for server/ Tier (App\ -> server/)
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
