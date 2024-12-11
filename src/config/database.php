<?php

return [
    'default' => env('DB_DEFAULT', 'db1'),

    'connections' => [
        'db1' => [
            'driver' => 'mysql',
            'host' => env('DB_1_HOST', 'localhost'),
            'database' => env('DB_1_NAME', 'database1'),
            'username' => env('DB_1_USER', 'root'),
            'password' => env('DB_1_PASS', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'email' => env('DB_1_EMAIL', ''),
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],
        
        'db2' => [
            'driver' => 'mysql',
            'host' => env('DB_2_HOST', 'localhost'),
            'database' => env('DB_2_NAME', 'database2'),
            'username' => env('DB_2_USER', 'root'),
            'password' => env('DB_2_PASS', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'email' => env('DB_2_EMAIL', ''),
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],
    ],
];
