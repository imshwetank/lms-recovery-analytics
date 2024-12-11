<?php

namespace Core;

class Session {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public function has($key) {
        return isset($_SESSION[$key]);
    }

    public function remove($key) {
        unset($_SESSION[$key]);
    }

    public function clear() {
        session_destroy();
    }

    public function setFlash($key, $message) {
        $_SESSION['flash_' . $key] = $message;
    }

    public function getFlash($key) {
        $message = $_SESSION['flash_' . $key] ?? null;
        unset($_SESSION['flash_' . $key]);
        return $message;
    }

    public function hasFlash($key) {
        return isset($_SESSION['flash_' . $key]);
    }

    public function regenerate() {
        session_regenerate_id(true);
    }
}
