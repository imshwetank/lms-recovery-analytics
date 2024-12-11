<?php

namespace Middleware;

abstract class Middleware {
    protected $config;
    protected $request;
    protected $response;

    public function __construct($config = null) {
        $this->config = $config;
        $this->request = $_SERVER;
        $this->response = [
            'status' => true,
            'message' => '',
            'redirect' => ''
        ];
    }

    abstract public function handle();

    protected function isExcluded($path) {
        if (!isset($this->config['exclude'])) {
            return false;
        }

        $currentPath = trim($path, '/');
        foreach ($this->config['exclude'] as $excludePath) {
            $excludePath = trim($excludePath, '/');
            if ($currentPath === $excludePath || strpos($currentPath, $excludePath) === 0) {
                return true;
            }
        }
        return false;
    }

    protected function getRequestPath() {
        $uri = $this->request['REQUEST_URI'];
        $position = strpos($uri, '?');
        if ($position !== false) {
            $uri = substr($uri, 0, $position);
        }
        return $uri;
    }

    protected function redirect($url) {
        $this->response['status'] = false;
        $this->response['redirect'] = $url;
        return $this->response;
    }

    protected function error($message) {
        $this->response['status'] = false;
        $this->response['message'] = $message;
        return $this->response;
    }

    protected function success() {
        return $this->response;
    }
}
