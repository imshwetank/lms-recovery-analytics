<?php

namespace Core\Middleware;

use Core\Session;

class AuthMiddleware {
    public function handle() {
        $session = new Session();
        if (!$session->get('user')) {
            header('Location: /auth');
            exit;
        }
    }
}
