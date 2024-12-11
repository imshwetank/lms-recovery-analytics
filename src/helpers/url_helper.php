<?php
if (!function_exists('base_url')) {
    function base_url($path = '') {
        $config = require __DIR__ . '/../config/config.php';
        $base_url = rtrim($config['app']['url'], '/');
        return $base_url . '/' . ltrim($path, '/');
    }
}

if (!function_exists('redirect')) {
    function redirect($path) {
        header('Location: ' . base_url($path));
        exit;
    }
}
