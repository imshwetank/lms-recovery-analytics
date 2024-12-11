<?php

namespace Middleware;

class ApiMiddleware extends Middleware {
    public function handle() {
        // Check if path is excluded from API authentication
        if ($this->isExcluded($this->getRequestPath())) {
            return $this->success();
        }

        // Check for API token in headers
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? '';

        if (empty($token)) {
            return $this->error('API token is required');
        }

        // Remove 'Bearer ' from token if present
        $token = str_replace('Bearer ', '', $token);

        // Validate API token
        if (!$this->validateApiToken($token)) {
            return $this->error('Invalid API token');
        }

        return $this->success();
    }

    private function validateApiToken($token) {
        // TODO: Implement API token validation
        // This is just a placeholder
        return true;
    }
}
