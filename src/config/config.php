<?php
// Start session based on environment setting
if (getenv('APP_SESSION_ENABLED') !== 'false' && session_status() === PHP_SESSION_NONE) {
    session_start();
}

return [
    // Application Configuration
    'app' => [
        // APP_NAME="My Application"                     -> 'My Application'
        // APP_NAME not set                             -> 'LMS Recovery Analytics'
        'name' => getenv('APP_NAME') ?: 'LMS Recovery Analytics',

        // APP_DEBUG=true                               -> true
        // APP_DEBUG=false                              -> false
        // APP_DEBUG not set                            -> false
        'debug' => getenv('APP_DEBUG') === 'true',

        // APP_TIMEZONE="America/New_York"              -> 'America/New_York'
        // APP_TIMEZONE not set                         -> 'Asia/Kolkata'
        'timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Kolkata',

        // APP_URL="https://myapp.com"                  -> 'https://myapp.com'
        // APP_URL not set                              -> 'http://localhost/lms-recovery-analytics'
        'url' => getenv('APP_URL') ?: 'http://localhost/lms-recovery-analytics',

        // APP_PORT=8080                                -> 8080
        // APP_PORT not set                             -> 80
        'port' => getenv('APP_PORT') ?: 80,

        // DEFAULT_TIME_FORMAT="H:i:s d-m-Y"            -> 'H:i:s d-m-Y'     -> '15:30:45 11-12-2024'
        // DEFAULT_TIME_FORMAT not set                  -> 'Y-m-d H:i:s'     -> '2024-12-11 15:30:45'
        'default_time_format' => getenv('DEFAULT_TIME_FORMAT') ?: 'Y-m-d H:i:s',

        // DEFAULT_DATE_FORMAT="d-m-Y"                  -> 'd-m-Y'           -> '11-12-2024'
        // DEFAULT_DATE_FORMAT not set                  -> 'Y-m-d'           -> '2024-12-11'
        'default_date_format' => getenv('DEFAULT_DATE_FORMAT') ?: 'Y-m-d'
    ],

    // Time Configuration
    'time' => [
        'formats' => [
            // DATETIME_FORMAT="d-m-Y H:i:s"            -> '11-12-2024 15:30:45'
            // DATETIME_FORMAT not set                  -> '2024-12-11 15:30:45'
            'datetime' => getenv('DATETIME_FORMAT') ?: 'Y-m-d H:i:s',

            // DATE_FORMAT="d-m-Y"                      -> '11-12-2024'
            // DATE_FORMAT not set                      -> '2024-12-11'
            'date' => getenv('DATE_FORMAT') ?: 'Y-m-d',

            // TIME_FORMAT="h:i A"                      -> '03:30 PM'
            // TIME_FORMAT not set                      -> '15:30:45'
            'time' => getenv('TIME_FORMAT') ?: 'H:i:s',

            // DATETIME_DISPLAY_FORMAT="D, d M Y h:i A" -> 'Wed, 11 Dec 2024 03:30 PM'
            // DATETIME_DISPLAY_FORMAT not set          -> '11 Dec 2024, 03:30 PM'
            'datetime_display' => getenv('DATETIME_DISPLAY_FORMAT') ?: 'd M Y, h:i A',

            // DATE_DISPLAY_FORMAT="D, d M Y"           -> 'Wed, 11 Dec 2024'
            // DATE_DISPLAY_FORMAT not set              -> '11 Dec 2024'
            'date_display' => getenv('DATE_DISPLAY_FORMAT') ?: 'd M Y'
        ],

        // TIME_ERROR_IF_INVALID=false                  -> false (silent fail)
        // TIME_ERROR_IF_INVALID not set               -> true (throws error)
        'error_if_invalid' => getenv('TIME_ERROR_IF_INVALID') ?: true
    ],

    // Session Configuration
    'session' => [
        // APP_SESSION_ENABLED=false                    -> false
        // APP_SESSION_ENABLED not set                  -> true
        'enabled' => getenv('APP_SESSION_ENABLED') !== 'false',

        // APP_SESSION_LIFETIME=3600                    -> 3600
        // APP_SESSION_LIFETIME not set                 -> 7200
        'lifetime' => (int)(getenv('APP_SESSION_LIFETIME') ?: 7200),

        // APP_SESSION_REGENERATE=600                   -> 600
        // APP_SESSION_REGENERATE not set               -> 300
        'regenerate' => (int)(getenv('APP_SESSION_REGENERATE') ?: 300),

        // APP_SESSION_EXPIRE_ON_CLOSE=true            -> true
        // APP_SESSION_EXPIRE_ON_CLOSE not set         -> false
        'expire_on_close' => getenv('APP_SESSION_EXPIRE_ON_CLOSE') === 'true',

        // APP_SESSION_COOKIE_NAME="my_session"        -> 'my_session'
        // APP_SESSION_COOKIE_NAME not set             -> 'lms_session'
        'cookie_name' => getenv('APP_SESSION_COOKIE_NAME') ?: 'lms_session',

        // APP_SESSION_COOKIE_SECURE=true              -> true
        // APP_SESSION_COOKIE_SECURE not set           -> false in development, true in production
        'cookie_secure' => getenv('APP_SESSION_COOKIE_SECURE') === 'true' || 
                          (getenv('APP_ENV') === 'production' && getenv('APP_SESSION_COOKIE_SECURE') !== 'false'),

        // APP_SESSION_COOKIE_HTTPONLY=false           -> false
        // APP_SESSION_COOKIE_HTTPONLY not set         -> true
        'cookie_httponly' => getenv('APP_SESSION_COOKIE_HTTPONLY') !== 'false'
    ],

    // Database Configuration
    'database' => [
        'default' => getenv('DB_CONNECTION') ?: 'mysql'
    ],

    // Mail Configuration
    'mail' => [
        'driver' => getenv('MAIL_DRIVER') ?: 'smtp',
        'host' => getenv('MAIL_HOST') ?: 'smtp.gmail.com',
        'port' => getenv('MAIL_PORT') ?: 587,
        'username' => getenv('MAIL_USERNAME'),
        'password' => getenv('MAIL_PASSWORD'),
        'encryption' => getenv('MAIL_ENCRYPTION') ?: 'tls'
    ],

    // Cache Configuration
    'cache' => [
        'driver' => getenv('CACHE_DRIVER') ?: 'file',
        'prefix' => getenv('CACHE_PREFIX') ?: 'lms_'
    ],

    // Security Configuration
    'security' => [
        'encryption_key' => getenv('APP_KEY'),
        'cipher' => 'AES-256-CBC',
        'password_hash_algo' => PASSWORD_BCRYPT,
        'password_hash_options' => ['cost' => 12]
    ],

    // Logging Configuration
    'logging' => [
        'default' => getenv('LOG_CHANNEL') ?: 'file',
        'level' => getenv('LOG_LEVEL') ?: 'debug'
    ],

    // Language Configuration
    'language' => [
        // APP_DEFAULT_LANGUAGE="fr"                   -> 'fr'
        // APP_DEFAULT_LANGUAGE not set                -> 'en'
        'default' => getenv('APP_DEFAULT_LANGUAGE') ?: 'en',

        // Available languages
        'available' => ['en', 'hi'], // English and Hindi

        // LANGUAGE_AUTO_DETECT=true                   -> true
        // LANGUAGE_AUTO_DETECT=false                  -> false
        // LANGUAGE_AUTO_DETECT not set                -> true
        'auto_detect' => getenv('LANGUAGE_AUTO_DETECT') ?: true
    ],

    // Load middleware configuration
    'middleware' => require __DIR__ . '/middleware.php',

    // Libraries Configuration
    'libraries' => [
        // LIBRARIES_ENABLED=false                      -> false
        // LIBRARIES_ENABLED not set                   -> true
        'enabled' => getenv('LIBRARIES_ENABLED') ?: false,  // Global switch for all libraries

        'items' => [
            'database' => true,
            'email' => true,
            'session' => true,
            'auth' => true,
        ],

        // LIBRARY_ERROR_IF_NOT_FOUND=false            -> false (silent fail)
        // LIBRARY_ERROR_IF_NOT_FOUND not set         -> true (throws error)
        'error_if_not_found' => getenv('LIBRARY_ERROR_IF_NOT_FOUND') ?: true // Whether to throw error if library not found
    ],

    // Helpers Configuration
    'helpers' => [
        // HELPERS_ENABLED=false                       -> false
        // HELPERS_ENABLED not set                    -> true
        'enabled' => getenv('HELPERS_ENABLED') !== 'false',  // Global switch for all helpers

        'items' => [
            'url' => true,
            'form' => true,
            'date' => true,
            'string' => true,
        ],

        // HELPER_ERROR_IF_NOT_FOUND=false            -> false (silent fail)
        // HELPER_ERROR_IF_NOT_FOUND not set         -> true (throws error)
        'error_if_not_found' => getenv('HELPER_ERROR_IF_NOT_FOUND') ?: true // Whether to throw error if helper not found
    ]
];
