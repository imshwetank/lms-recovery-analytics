<?php

namespace Core\Middleware;

class ApiMiddleware {
    public function handle() {
        header('Content-Type: application/json');
    }
}
