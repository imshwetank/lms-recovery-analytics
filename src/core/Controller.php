<?php

namespace Core;

use Core\View;
use Core\Session;
use Core\Middleware\MiddlewareHandler;
use Core\Validation\Validator;

class Controller {
    protected $middleware;
    protected $view;
    protected $session;

    public function __construct() {
        $this->middleware = new MiddlewareHandler();
        $this->view = new View();
        $this->session = new Session();
    }

    protected function render($view, $data = []) {
        return $this->view->render($view, $data);
    }

    protected function json($data) {
        header('Content-Type: application/json');
        return json_encode($data);
    }

    protected function redirect($url) {
        header("Location: {$url}");
        exit;
    }

    protected function setError($message) {
        $_SESSION['flash_error'] = $message;
    }

    protected function setSuccess($message) {
        $_SESSION['flash_success'] = $message;
    }

    protected function getError() {
        $error = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_error']);
        return $error;
    }

    protected function getSuccess() {
        $success = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_success']);
        return $success;
    }

    protected function validate($data, $rules) {
        $validator = new Validator($data, $rules);
        return $validator->validate();
    }
}
