<?php

/**
 * Authentication View Routes
 * These routes handle the UI views for authentication
 */

return [
    'GET /login' => ['AuthController', 'showLoginForm'],
    'GET /register' => ['AuthController', 'showRegisterForm'],
    'GET /forgot-password' => ['AuthController', 'showForgotPasswordForm'],
    'GET /reset-password/{token}' => ['AuthController', 'showResetPasswordForm'],
    'GET /profile' => ['AuthController', 'showProfile'],
    'GET /settings' => ['AuthController', 'showSettings']
];
