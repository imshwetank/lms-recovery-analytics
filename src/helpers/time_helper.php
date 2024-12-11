<?php

if (!function_exists('init_timezone')) {
    function init_timezone() {
        $config = require __DIR__ . '/../config/config.php';
        date_default_timezone_set($config['app']['timezone']);
    }
}

if (!function_exists('format_datetime')) {
    function format_datetime($datetime = null, $format = null) {
        $config = require __DIR__ . '/../config/config.php';
        
        if ($datetime === null) {
            $datetime = new DateTime();
        } elseif (is_string($datetime)) {
            $datetime = new DateTime($datetime);
        }

        if ($format === null) {
            $format = $config['time']['formats']['datetime'];
        }

        return $datetime->format($format);
    }
}

if (!function_exists('format_date')) {
    function format_date($date = null, $format = null) {
        $config = require __DIR__ . '/../config/config.php';
        
        if ($format === null) {
            $format = $config['time']['formats']['date'];
        }

        return format_datetime($date, $format);
    }
}

if (!function_exists('format_time')) {
    function format_time($time = null, $format = null) {
        $config = require __DIR__ . '/../config/config.php';
        
        if ($format === null) {
            $format = $config['time']['formats']['time'];
        }

        return format_datetime($time, $format);
    }
}

if (!function_exists('display_datetime')) {
    function display_datetime($datetime = null) {
        $config = require __DIR__ . '/../config/config.php';
        return format_datetime($datetime, $config['time']['formats']['datetime_display']);
    }
}

if (!function_exists('display_date')) {
    function display_date($date = null) {
        $config = require __DIR__ . '/../config/config.php';
        return format_datetime($date, $config['time']['formats']['date_display']);
    }
}

// Initialize timezone when helper is loaded
init_timezone();
