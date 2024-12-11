<?php

namespace Core;

class Autoloader {
    public static function register() {
        spl_autoload_register(function ($class) {
            // Debug log
            error_log("Attempting to autoload class: " . $class);

            // Base directory for the application
            $baseDir = dirname(__DIR__);

            // Map namespaces to directories
            $namespaceMap = [
                'Core' => 'core',
                'Controllers' => 'controllers',
                'Models' => 'models',
                'Libraries' => 'libraries',
                'Middleware' => 'middleware',
                'Helpers' => 'helpers'
            ];

            // Get the namespace from the class name
            $parts = explode('\\', $class);
            $namespace = array_shift($parts);

            // If namespace is mapped, use the mapped directory
            if (isset($namespaceMap[$namespace])) {
                $directory = $namespaceMap[$namespace];
                $className = implode(DIRECTORY_SEPARATOR, $parts);
                $file = $baseDir . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR . $className . '.php';
            } else {
                // Default behavior for unmapped namespaces
                $file = $baseDir . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
            }

            // Debug log
            error_log("Looking for file: " . $file);

            // Check if file exists and require it
            if (file_exists($file)) {
                error_log("Found and loading file: " . $file);
                require_once $file;
                return true;
            }

            error_log("File not found: " . $file);
            return false;
        });
    }
}
