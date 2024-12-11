<?php

/**
 * Authentication API Routes
 * Prefix: /api/auth
 */

return [
    'POST /login' => ['AuthController', 'apiLogin'],
    'POST /logout' => ['AuthController', 'apiLogout'],
    'POST /refresh' => ['AuthController', 'apiRefreshToken'],
    'GET /user' => ['AuthController', 'apiGetUser'],
    'PUT /user' => ['AuthController', 'apiUpdateUser'],
    'POST /forgot-password' => ['AuthController', 'apiForgotPassword'],
    'POST /reset-password' => ['AuthController', 'apiResetPassword']
];
