<?php
namespace App\Core;

class Router {
    protected array $routes = [];

    public function get(string $path, array $callback): void {
        $this->add('GET', $path, $callback);
    }

    public function post(string $path, array $callback): void {
        $this->add('POST', $path, $callback);
    }

    public function put(string $path, array $callback): void {
        $this->add('PUT', $path, $callback);
    }

    public function patch(string $path, array $callback): void {
        $this->add('PATCH', $path, $callback);
    }

    public function delete(string $path, array $callback): void {
        $this->add('DELETE', $path, $callback);
    }

    public function add(string $method, string $path, array $callback): void {
        $normalizedPath = ($path === '/') ? '/' : rtrim($path, '/');
        $this->routes[] = [
            'method'   => strtoupper($method),
            'path'     => $normalizedPath,
            'callback' => $callback
        ];
    }

    public function dispatch(string $uri, string $method): void {
        $cleanUri = parse_url($uri, PHP_URL_PATH);
        if ($cleanUri === '' || $cleanUri === null) {
            $cleanUri = '/';
        }
        if ($cleanUri !== '/' && substr($cleanUri, -1) === '/') {
            $cleanUri = rtrim($cleanUri, '/');
        }

        $method = strtoupper($method);
        if ($method === 'OPTIONS') {
            Helpers::jsonResponse(['success' => true], 200);
        }

        $pathMatched = false;

        foreach ($this->routes as $route) {
            if ($route['path'] === $cleanUri) {
                $pathMatched = true;
                if ($route['method'] === $method) {
                    [$controller, $action] = $route['callback'];

                    if (!class_exists($controller)) {
                        error_log("Router Error: Controller $controller not found.");
                        Helpers::jsonResponse([
                            'success' => false,
                            'error'   => 'System Controller Unavailable'
                        ], 500);
                    }

                    $instance = new $controller();

                    if (!method_exists($instance, $action)) {
                        error_log("Router Error: Action $action not found in $controller.");
                        Helpers::jsonResponse([
                            'success' => false,
                            'error'   => 'System Action Unavailable'
                        ], 500);
                    }

                    $instance->$action();
                    return;
                }
            }
        }

        if ($pathMatched) {
            Helpers::jsonResponse([
                'success' => false,
                'error'   => "Method {$method} not allowed for {$cleanUri}"
            ], 405);
        }

        Helpers::jsonResponse([
            'success' => false,
            'error'   => 'Endpoint Not Found',
            'path'    => $cleanUri
        ], 404);
    }
}
