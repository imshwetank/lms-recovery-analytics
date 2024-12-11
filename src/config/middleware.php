<?php

/**
 * Middleware Configuration
 * Define which routes require which middleware
 */

return [
    // Public routes (no middleware)
    'public' => [
        '/',
        '/about',
        '/contact',
        '/auth/login',
        '/auth/register',
        '/auth/forgot-password',
        '/auth/reset-password'
    ],

    // Routes that require authentication
    'auth' => [
        '/dashboard',
        '/profile',
        '/settings',
        '/charts/*'
    ],

    // Routes that require API authentication
    'api' => [
        '/api/*'
    ],

    // Global middleware (applied to all routes)
    'global' => [
        'csrf' => getenv('CSRF_PROTECTION') !== 'false'
    ]
];
