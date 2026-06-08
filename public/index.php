<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../bootstrap.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path === '/' || $path === '/index.php') {
    $controller = new \App\Controllers\HomeController();
    $controller->index();
} else {
    echo "404 Not Found";
}