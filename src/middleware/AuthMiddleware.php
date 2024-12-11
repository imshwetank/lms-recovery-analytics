<?php

namespace Middleware;

class AuthMiddleware extends Middleware {
    public function handle() {
        // Check if path is excluded from authentication
        if ($this->isExcluded($this->getRequestPath())) {
            return $this->success();
        }

        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            return $this->redirect('/login');
        }

        // Check if user exists and is active
        $user = $this->getUserById($_SESSION['user_id']);
        if (!$user || !$user['is_active']) {
            unset($_SESSION['user_id']);
            return $this->redirect('/login');
        }

        return $this->success();
    }

    private function getUserById($userId) {
        // TODO: Implement user retrieval from database
        // This is just a placeholder
        return [
            'id' => $userId,
            'is_active' => true
        ];
    }
}
