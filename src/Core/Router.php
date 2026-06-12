<?php
namespace App\Core;

class Router {
    protected array $routes = [];

    // Shorthand registration methods
    public function get(string $path, array $callback) { $this->add('GET', $path, $callback); }
    public function post(string $path, array $callback) { $this->add('POST', $path, $callback); }

    public function add(string $method, string $path, array $callback) {
    // If path is '/', keep it as '/'. Otherwise, rtrim trailing slashes.
    $normalizedPath = ($path === '/') ? '/' : rtrim($path, '/');
    
    $this->routes[] = [
        'method'   => strtoupper($method),
        'path'     => $normalizedPath,
        'callback' => $callback
    ];
}

    public function dispatch(string $uri, string $method) {
    // 1. Get clean path
    $cleanUri = parse_url($uri, PHP_URL_PATH);
    
    // 2. FORCE root to be '/'
    if ($cleanUri === '' || $cleanUri === null) {
        $cleanUri = '/';
    }

    // 3. Normalize: remove trailing slash for all paths EXCEPT root
    if ($cleanUri !== '/' && substr($cleanUri, -1) === '/') {
        $cleanUri = rtrim($cleanUri, '/');
    }

    $method = strtoupper($method);

    foreach ($this->routes as $route) {
        // Compare
        if ($route['path'] === $cleanUri && $route['method'] === $method) {
                [$controller, $action] = $route['callback'];

                if (!class_exists($controller)) {
                    die("Controller $controller not found.");
                }

                $instance = new $controller();
                
                if (!method_exists($instance, $action)) {
                    die("Action $action not found in $controller.");
                }

                // Call the action without forcing a global ID parameter
                return $instance->$action();
            }
        }

        http_response_code(404);
        echo "<h2>404 Not Found</h2>";
        echo "The framework router could not find a registered match for path: <code>" . htmlspecialchars($cleanUri) . "</code> [" . htmlspecialchars($method) . "]";
    }
}