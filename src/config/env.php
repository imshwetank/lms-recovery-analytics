<?php

require_once __DIR__ . '/../../vendor/autoload.php';

// Load environment variables from .env file
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
    
    // Log successful loading of environment variables
    error_log("Environment variables loaded successfully");
    
} catch (Exception $e) {
    error_log("Error loading environment variables: " . $e->getMessage());
    die("Error loading environment configuration. Please check error logs.");
}

// Validate required environment variables
$required_vars = [
    'SMTP_HOST',
    'SMTP_USERNAME',
    'SMTP_PASSWORD',
    'SMTP_PORT',
    'SMTP_FROM',
    'SMTP_FROM_NAME'
];

foreach ($required_vars as $var) {
    if (!isset($_ENV[$var])) {
        error_log("Missing required environment variable: $var");
        die("Missing required environment variable: $var");
    }
}

// Additional database configuration validation
$dbIndex = 1;
$hasValidDb = false;

while (isset($_ENV["DB_{$dbIndex}_EMAIL"])) {
    $prefix = "DB_{$dbIndex}_";
    $required_db_vars = ['HOST', 'NAME', 'USER', 'PASS', 'EMAIL'];
    $valid = true;
    
    foreach ($required_db_vars as $var) {
        if (!isset($_ENV[$prefix . $var])) {
            error_log("Missing database configuration: {$prefix}{$var}");
            $valid = false;
            break;
        }
    }
    
    if ($valid) {
        $hasValidDb = true;
    }
    
    $dbIndex++;
}

if (!$hasValidDb) {
    error_log("No valid database configurations found");
    die("No valid database configurations found in environment variables");
}

// Log successful configuration
error_log("Configuration loaded successfully with " . ($dbIndex - 1) . " database configurations");
