<?php

return [
    'name' => 'LMS Recovery Analytics',
    'debug' => true,
    'url' => 'http://localhost/lms-recovery-analytics',
    'timezone' => 'Asia/Kolkata',
    'locale' => 'en',
    'key' => 'your-secret-key-here',
    
    // Session configuration
    'session' => [
        'lifetime' => 120,
        'path' => '/',
        'domain' => null,
        'secure' => false,
        'httponly' => true,
    ],
    
    // View configuration
    'view' => [
        'path' => BASEPATH . '/src/views',
        'cache' => BASEPATH . '/storage/cache',
        'extension' => '.php'
    ],
    
    // Error handling
    'errors' => [
        'display' => true,
        'log' => true,
        'debug' => true
    ],
    
    // Middleware configuration
    'middleware' => [
        'global' => [
            'Core\Middleware\SessionMiddleware',
            'Core\Middleware\CsrfMiddleware'
        ],
        'api' => [
            'Core\Middleware\ApiMiddleware'
        ]
    ]
];
