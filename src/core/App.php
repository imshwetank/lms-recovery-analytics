<?php

namespace Core;

class App {
    protected static $instance = null;
    protected $config = [];
    protected $router;
    protected $middleware;

    private function __construct() {
        // Load base configuration
        $this->loadConfig();
        
        // Initialize components
        $this->router = new Router();
        $this->middleware = new MiddlewareHandler();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadConfig() {
        // Load all config files
        $configPath = BASEPATH . '/src/config/';
        $configFiles = glob($configPath . '*.php');
        
        foreach ($configFiles as $file) {
            $key = basename($file, '.php');
            $this->config[$key] = require $file;
        }
    }

    public function run() {
        try {
            // Run middleware
            $this->middleware->handle();
            
            // Dispatch route
            return $this->router->dispatch();
            
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    private function handleError($e) {
        if ($this->config['app']['debug'] ?? false) {
            return $this->renderDebugError($e);
        }
        return $this->renderProductionError($e);
    }

    private function renderDebugError($e) {
        ob_start();
        include BASEPATH . '/src/views/errors/debug.php';
        return ob_get_clean();
    }

    private function renderProductionError($e) {
        // Log error
        error_log($e->getMessage());
        
        ob_start();
        include BASEPATH . '/src/views/errors/production.php';
        return ob_get_clean();
    }

    public function getConfig($key = null) {
        if ($key === null) {
            return $this->config;
        }
        return $this->config[$key] ?? null;
    }
}
