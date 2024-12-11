<?php

namespace Middleware;

class CorsMiddleware extends Middleware {
    public function handle() {
        // Always set CORS headers
        $this->setCorsHeaders();

        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        return $this->success();
    }

    private function setCorsHeaders() {
        // Allow Origins
        if (in_array('*', $this->config['allow_origins'])) {
            header('Access-Control-Allow-Origin: *');
        } else {
            $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
            if (in_array($origin, $this->config['allow_origins'])) {
                header('Access-Control-Allow-Origin: ' . $origin);
            }
        }

        // Allow Credentials
        if ($this->config['allow_credentials']) {
            header('Access-Control-Allow-Credentials: true');
        }

        // Allow Methods
        header('Access-Control-Allow-Methods: ' . implode(', ', $this->config['allow_methods']));

        // Allow Headers
        header('Access-Control-Allow-Headers: ' . implode(', ', $this->config['allow_headers']));

        // Expose Headers
        if (!empty($this->config['expose_headers'])) {
            header('Access-Control-Expose-Headers: ' . implode(', ', $this->config['expose_headers']));
        }

        // Max Age
        if ($this->config['max_age'] > 0) {
            header('Access-Control-Max-Age: ' . $this->config['max_age']);
        }
    }
}
