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
                    error_log("Router Error: Controller $controller not found.");
                    http_response_code(500);
                    $this->renderErrorPage(500, 'System Controller Unavailable');
                    return;
                }

                $instance = new $controller();
                
                if (!method_exists($instance, $action)) {
                    error_log("Router Error: Action $action not found in $controller.");
                    http_response_code(500);
                    $this->renderErrorPage(500, 'System Action Unavailable');
                    return;
                }

                // Call the action without forcing a global ID parameter
                return $instance->$action();
            }
        }

        http_response_code(404);
        $this->renderErrorPage(404, '404 - Page Not Found');
    }

    /**
     * Render a branded error page within the application layout
     */
    protected function renderErrorPage(int $statusCode, string $title) {
        $pageTitle = $title;
        $root = defined('ROOT_PATH') ? ROOT_PATH : (dirname(__DIR__, 2));
        $viewFile = $root . '/src/views/errors/' . $statusCode . '.php';

        if (file_exists($viewFile)) {
            ob_start();
            include $viewFile;
            $view_content = ob_get_clean();

            $layoutFile = $root . '/src/views/layouts/main.php';
            if (file_exists($layoutFile)) {
                include $layoutFile;
                return;
            }
            echo $view_content;
            return;
        }

        echo "<h2>Error {$statusCode}</h2><p>" . htmlspecialchars($title) . "</p>";
    }
}