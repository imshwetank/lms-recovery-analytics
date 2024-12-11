<?php
namespace Core;

class Loader {
    private $config;
    private static $instance = null;

    private function __construct() {
        $this->config = require __DIR__ . '/../config/config.php';
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function library($library) {
        // Check if libraries are globally enabled
        if (!$this->config['libraries']['enabled']) {
            if ($this->config['libraries']['error_if_not_found']) {
                throw new \Exception("All libraries are disabled globally");
            }
            return null;
        }

        // Check if specific library is enabled
        if (!isset($this->config['libraries']['items'][$library]) || 
            !$this->config['libraries']['items'][$library]) {
            if ($this->config['libraries']['error_if_not_found']) {
                throw new \Exception("Library {$library} is not enabled in configuration");
            }
            return null;
        }

        // Try to load the library
        $class = "\\Libraries\\" . ucfirst($library);
        if (class_exists($class)) {
            return $class::getInstance();
        }

        if ($this->config['libraries']['error_if_not_found']) {
            throw new \Exception("Library {$library} class not found");
        }
        return null;
    }

    public function helper($helper) {
        // Check if helpers are globally enabled
        if (!$this->config['helpers']['enabled']) {
            if ($this->config['helpers']['error_if_not_found']) {
                throw new \Exception("All helpers are disabled globally");
            }
            return false;
        }

        // Check if specific helper is enabled
        if (!isset($this->config['helpers']['items'][$helper]) || 
            !$this->config['helpers']['items'][$helper]) {
            if ($this->config['helpers']['error_if_not_found']) {
                throw new \Exception("Helper {$helper} is not enabled in configuration");
            }
            return false;
        }

        // Try to load the helper
        $helper_file = __DIR__ . "/../helpers/{$helper}_helper.php";
        if (file_exists($helper_file)) {
            require_once $helper_file;
            return true;
        }

        if ($this->config['helpers']['error_if_not_found']) {
            throw new \Exception("Helper {$helper} file not found");
        }
        return false;
    }

    public function language($key, $lang = null) {
        if (!$lang) {
            $lang = $this->config['language']['default'];
        }
        
        $lang_file = __DIR__ . "/../language/{$lang}/common.php";
        if (file_exists($lang_file)) {
            $strings = require $lang_file;
            return $strings[$key] ?? $key;
        }
        return $key;
    }
}
