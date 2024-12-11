<?php

namespace Controllers;

use Core\MiddlewareHandler;

class BaseController {
    protected $middleware;

    public function __construct() {
        $this->middleware = new MiddlewareHandler();
    }

    protected function render($view, $data = []) {
        // Extract data to make variables available in view
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        include __DIR__ . "/../views/{$view}.php";
        
        // Get the contents and clean the buffer
        $content = ob_get_clean();
        
        // Return the rendered content
        return $content;
    }

    protected function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    protected function redirect($url) {
        header("Location: {$url}");
        exit;
    }
}
