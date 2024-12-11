<?php

namespace Core;

class MiddlewareHandler {
    private $config;
    private $middlewares = [];

    public function __construct($config) {
        $this->config = $config['middleware'];
        $this->loadMiddlewares();
    }

    private function loadMiddlewares() {
        // If middleware is globally disabled, return
        if (!$this->config['enabled']) {
            return;
        }

        // Load middleware in specified order
        foreach ($this->config['load'] as $name) {
            if (!isset($this->config['middleware'][$name])) {
                continue;
            }

            $middlewareConfig = $this->config['middleware'][$name];
            if (!$middlewareConfig['enabled']) {
                continue;
            }

            $className = '\\Middleware\\' . ucfirst($name) . 'Middleware';
            if (class_exists($className)) {
                $this->middlewares[] = new $className($middlewareConfig);
            }
        }
    }

    public function handle() {
        foreach ($this->middlewares as $middleware) {
            $result = $middleware->handle();
            
            if (!$result['status']) {
                if (!empty($result['redirect'])) {
                    header('Location: ' . $result['redirect']);
                    exit;
                }
                
                if (!empty($result['message'])) {
                    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                        // AJAX request
                        header('Content-Type: application/json');
                        http_response_code(401);
                        echo json_encode(['error' => $result['message']]);
                    } else {
                        // Regular request
                        http_response_code(401);
                        echo $result['message'];
                    }
                    exit;
                }
            }
        }
    }
}
