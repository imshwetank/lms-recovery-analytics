# Configuration Documentation

## Overview
This document describes all configuration options available in the LMS Recovery Analytics system. All settings can be controlled through environment variables with sensible defaults provided.

## Configuration Sections

### Application Settings
```php
'app' => [
    'name'      => getenv('APP_NAME'),       // Application name
    'debug'     => getenv('APP_DEBUG'),      // Enable/disable debug mode
    'timezone'  => getenv('APP_TIMEZONE'),   // Application timezone
    'url'       => getenv('APP_URL'),        // Base URL of application
    'port'      => getenv('APP_PORT'),       // Application port
]
```

### Time Configuration
```php
'time' => [
    'formats' => [
        'datetime'         => 'Y-m-d H:i:s',     // Default datetime format
        'date'            => 'Y-m-d',            // Default date format
        'time'            => 'H:i:s',            // Default time format
        'datetime_display'=> 'd M Y, h:i A',     // User-friendly datetime
        'date_display'    => 'd M Y'             // User-friendly date
    ]
]
```

### Database Configuration
```php
'database' => [
    'db1' => [
        'host'  => getenv('DB_1_HOST'),     // Primary database host
        'name'  => getenv('DB_1_NAME'),     // Database name
        'user'  => getenv('DB_1_USER'),     // Database user
        'pass'  => getenv('DB_1_PASS'),     // Database password
        'email' => getenv('DB_1_EMAIL')     // Associated email
    ],
    'db2' => [
        // Secondary database settings
    ]
]
```

### Email Configuration
```php
'email' => [
    'host'      => getenv('SMTP_HOST'),     // SMTP server host
    'port'      => getenv('SMTP_PORT'),     // SMTP server port
    'username'  => getenv('SMTP_USERNAME'), // SMTP username
    'password'  => getenv('SMTP_PASSWORD'), // SMTP password
    'from'      => getenv('SMTP_FROM'),     // Default from email
    'from_name' => getenv('SMTP_FROM_NAME') // Default from name
]
```

### Security Configuration
```php
'security' => [
    'session_lifetime' => 120,              // Session timeout in minutes
    'encryption_key'   => '32-char-key',    // Encryption key for sensitive data
    'password_algo'    => PASSWORD_BCRYPT,  // Password hashing algorithm
    'password_cost'    => 12               // Password hashing cost
]
```

### Error Configuration
```php
'error' => [
    'reporting' => E_ALL,                   // PHP error reporting level
    'display'   => true,                    // Show errors on screen
    'log'       => true,                    // Log errors to file
    'log_path'  => '/logs/error.log'       // Error log file path
]
```

### Libraries & Helpers
```php
'libraries' => [
    'database' => true,                     // Enable database library
    'email'    => true,                     // Enable email library
    'session'  => true,                     // Enable session library
    'auth'     => true                      // Enable auth library
],
'helpers' => [
    'url'    => true,                       // Enable URL helper
    'form'   => true,                       // Enable form helper
    'date'   => true,                       // Enable date helper
    'string' => true                        // Enable string helper
]
```

## Usage Examples

### Getting Configuration Values
```php
$config = require 'config.php';

// Application settings
$app_name = $config['app']['name'];
$debug_mode = $config['app']['debug'];

// Database settings
$db_host = $config['database']['db1']['host'];
$db_name = $config['database']['db1']['name'];

// Email settings
$smtp_host = $config['email']['host'];
$smtp_port = $config['email']['port'];
```

### Using Helper Functions
```php
// Date/Time formatting
echo format_datetime();           // Current datetime in default format
echo format_date('2024-12-11');  // Format specific date
echo display_datetime();         // User-friendly datetime display

// URL helper
echo base_url('dashboard');     // Get full URL to dashboard
redirect('login');              // Redirect to login page
```

### Error Handling
```php
if ($config['app']['debug']) {
    error_reporting($config['error']['reporting']);
    ini_set('display_errors', $config['error']['display']);
}
```

## Environment Variables
All configuration can be controlled through environment variables. Copy `.env.example` to `.env` and adjust values as needed. The system provides sensible defaults if environment variables are not set.

## Important Notes
1. Never commit `.env` file to version control
2. Always set `APP_DEBUG=false` in production
3. Use strong values for security settings in production
4. Keep timezone consistent across all servers
5. Regularly rotate encryption and API keys
