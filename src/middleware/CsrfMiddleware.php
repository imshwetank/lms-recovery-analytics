<?php

namespace Middleware;

class CsrfMiddleware extends Middleware {
    public function handle() {
        // Skip for excluded paths
        if ($this->isExcluded($this->getRequestPath())) {
            return $this->success();
        }

        // Only check CSRF for POST, PUT, DELETE requests
        if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE'])) {
            return $this->success();
        }

        // Generate token if not exists
        if (!isset($_SESSION['csrf_token'])) {
            $this->generateToken();
        }

        // Check if token is expired
        if ($this->isTokenExpired()) {
            $this->generateToken();
            return $this->error('CSRF token expired');
        }

        // Get token from request
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        // Validate token
        if (!$token || !$this->validateToken($token)) {
            return $this->error('Invalid CSRF token');
        }

        return $this->success();
    }

    private function generateToken() {
        $token = bin2hex(random_bytes($this->config['token_length'] / 2));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();
        return $token;
    }

    private function validateToken($token) {
        return isset($_SESSION['csrf_token']) && 
               hash_equals($_SESSION['csrf_token'], $token);
    }

    private function isTokenExpired() {
        if (!isset($_SESSION['csrf_token_time'])) {
            return true;
        }

        $lifetime = $this->config['token_lifetime'] * 60; // Convert minutes to seconds
        return (time() - $_SESSION['csrf_token_time']) > $lifetime;
    }
}
