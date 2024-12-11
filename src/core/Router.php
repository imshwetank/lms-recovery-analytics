<?php

namespace Core;

class Router {
    private $routes = [];
    private static $instance = null;
    private $middleware;

    private function __construct() {
        $this->loadRoutes();
        $this->middleware = require BASEPATH . '/src/config/middleware.php';
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadRoutes() {
        // Load home routes first (for the root URL)
        $this->loadRouteFile('view_home.php', '');

        // Load API routes
        $this->loadRouteFile('api_auth.php', '/api/auth');
        $this->loadRouteFile('api_charts.php', '/api/charts');

        // Load view routes
        $this->loadRouteFile('view_auth.php', '');
        $this->loadRouteFile('view_charts.php', '');
    }

    private function loadRouteFile($file, $prefix = '') {
        $routes = require BASEPATH . '/src/routes/' . $file;
        foreach ($routes as $route => $handler) {
            list($method, $path) = explode(' ', $route);
            $this->routes[$method][$prefix . $path] = $handler;
        }
    }

    public function dispatch($method, $uri) {
        // Remove query string
        $uri = parse_url($uri, PHP_URL_PATH);
        
        // Handle trailing slashes
        $uri = rtrim($uri, '/');
        if (empty($uri)) $uri = '/';

        // Check if route exists
        if (!isset($this->routes[$method])) {
            throw new \Exception("Method not allowed", 405);
        }

        // Check if this is a public route
        $isPublicRoute = $this->isPublicRoute($uri);
        
        // If not public route, check authentication
        if (!$isPublicRoute && !$this->isAuthenticated($uri)) {
            // For API routes, return JSON response
            if (strpos($uri, '/api/') === 0) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }
            // For web routes, redirect to login
            header('Location: /auth/login');
            exit;
        }

        // Find matching route
        foreach ($this->routes[$method] as $route => $handler) {
            $pattern = $this->convertRouteToRegex($route);
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove full match
                return $this->handleRoute($handler, $matches);
            }
        }

        throw new \Exception("Route not found", 404);
    }

    private function isPublicRoute($uri) {
        foreach ($this->middleware['public'] as $publicPath) {
            // Convert wildcards to regex pattern
            $pattern = str_replace('*', '.*', $publicPath);
            $pattern = '#^' . $pattern . '$#';
            if (preg_match($pattern, $uri)) {
                return true;
            }
        }
        return false;
    }

    private function isAuthenticated($uri) {
        // For API routes
        if (strpos($uri, '/api/') === 0) {
            return $this->isApiAuthenticated();
        }
        // For web routes
        return isset($_SESSION['user']);
    }

    private function isApiAuthenticated() {
        $headers = getallheaders();
        $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
        // Implement your API token validation logic here
        return false; // For now, return false until we implement proper API auth
    }

    private function convertRouteToRegex($route) {
        // Convert route parameters to regex pattern
        $pattern = preg_replace('/\{([a-zA-Z]+)\}/', '([^/]+)', $route);
        return '#^' . $pattern . '$#';
    }

    private function handleRoute($handler, $params) {
        list($controller, $method) = $handler;
        $controllerClass = "App\\Controllers\\{$controller}";
        
        if (!class_exists($controllerClass)) {
            throw new \Exception("Controller not found: {$controllerClass}");
        }

        $instance = new $controllerClass();
        if (!method_exists($instance, $method)) {
            throw new \Exception("Method not found: {$method}");
        }

        return call_user_func_array([$instance, $method], $params);
    }

    public function generateUrl($name, $params = []) {
        // TODO: Implement URL generation for named routes
        return '';
    }

    // RESTful helper methods
    public function get($path, $handler) {
        $this->routes['GET'][$path] = $handler;
    }

    public function post($path, $handler) {
        $this->routes['POST'][$path] = $handler;
    }

    public function put($path, $handler) {
        $this->routes['PUT'][$path] = $handler;
    }

    public function delete($path, $handler) {
        $this->routes['DELETE'][$path] = $handler;
    }

    public function patch($path, $handler) {
        $this->routes['PATCH'][$path] = $handler;
    }

    public function options($path, $handler) {
        $this->routes['OPTIONS'][$path] = $handler;
    }

    public function any($path, $handler) {
        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'];
        foreach ($methods as $method) {
            $this->routes[$method][$path] = $handler;
        }
    }

    public function resource($path, $controller) {
        $this->get($path, [$controller, 'index']);
        $this->get($path . '/create', [$controller, 'create']);
        $this->post($path, [$controller, 'store']);
        $this->get($path . '/{id}', [$controller, 'show']);
        $this->get($path . '/{id}/edit', [$controller, 'edit']);
        $this->put($path . '/{id}', [$controller, 'update']);
        $this->delete($path . '/{id}', [$controller, 'destroy']);
    }

    public function apiResource($path, $controller) {
        $this->get($path, [$controller, 'index']);
        $this->post($path, [$controller, 'store']);
        $this->get($path . '/{id}', [$controller, 'show']);
        $this->put($path . '/{id}', [$controller, 'update']);
        $this->delete($path . '/{id}', [$controller, 'destroy']);
    }
}
