<?php

// Define base path
define('BASEPATH', dirname(__DIR__));

// Load composer autoloader
require_once BASEPATH . '/vendor/autoload.php';

// Load helper functions
require_once BASEPATH . '/src/helpers/functions.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(BASEPATH);
$dotenv->load();

// Start session
session_start();

// Load configuration
require_once BASEPATH . '/src/config/config.php';

// Get the request method and URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

try {
    // Dispatch the request and echo the response
    $router = Core\Router::getInstance();
    $response = $router->dispatch($method, $uri);
    echo $response;
} catch (Exception $e) {
    // Handle errors
    if ($e->getCode() === 404) {
        http_response_code(404);
        require BASEPATH . '/src/views/errors/404.php';
    } else {
        http_response_code(500);
        require BASEPATH . '/src/views/errors/500.php';
    }
}
