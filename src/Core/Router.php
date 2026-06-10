<?php
namespace App\Core;

class Router {
    protected array $routes = [];

    /**
     * Register an application route map
     */
    public function add(string $method, string $path, array $callback) {
        // Ensure registered paths are normalized (no trailing slashes, except for root)
        $normalizedPath = ($path !== '/' && substr($path, -1) === '/') ? rtrim($path, '/') : $path;
        
        $this->routes[] = [
            'method'   => strtoupper($method),
            'path'     => $normalizedPath,
            'callback' => $callback
        ];
    }

    /**
     * Dispatch the current HTTP request to the matching controller action
     */
    public function dispatch(string $uri, string $method) {
        // 1. Isolate the clean URL path component from any attached query string parameters
        $cleanUri = parse_url($uri, PHP_URL_PATH);

        // 2. Normalize trailing slashes (e.g., turn "/admin/" into "/admin" for a clean match)
        if ($cleanUri !== '/' && substr($cleanUri, -1) === '/') {
            $cleanUri = rtrim($cleanUri, '/');
        }

        // 3. Automatically grab any parameter ID keys passed over the request scope
        $id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0);

        // Ensure method is uppercase for safety
        $method = strtoupper($method);

        foreach ($this->routes as $route) {
            // Compare the normalized URL pattern path against the active HTTP request method
            if ($route['path'] === $cleanUri && $route['method'] === $method) {
                [$controller, $action] = $route['callback'];

                // Check if the controller class exists (handles Linux case-sensitivity issues)
                if (!class_exists($controller)) {
                    http_response_code(500);
                    echo "<h2>Internal Server Error</h2>";
                    echo "<b>Reason:</b> Controller Class <code>{$controller}</code> not found.<br>";
                    echo "<b>Check:</b> Verify your namespace and make sure the file inside <code>src/Controllers/</code> matches capitalization exactly.";
                    return;
                }

                $instance = new $controller();

                // Check if the target method exists inside that controller instance
                if (!method_exists($instance, $action)) {
                    http_response_code(500);
                    echo "<h2>Internal Server Error</h2>";
                    echo "<b>Reason:</b> Action method <code>{$action}</code> does not exist on controller <code>{$controller}</code>.";
                    return;
                }

                // 4. Dynamically execute the action, passing the extracted ID down as a parameter
                return $instance->$action($id);
            }
        }

        // Fallback catch-all error handling if no route match is found
        http_response_code(404);
        echo "<h2>404 Not Found</h2>";
        echo "The framework router could not find a registered match for path: <code>" . htmlspecialchars($cleanUri) . "</code> [" . htmlspecialchars($method) . "]";
    }
}