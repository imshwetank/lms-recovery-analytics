<?php

namespace Controllers;

use Core\Controller;
use Models\UserModel;

class AuthController extends Controller {
    private $model;

    public function __construct() {
        parent::__construct();
        $this->model = new UserModel();
    }

    public function index() {
        return $this->render('auth/login');
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('/auth');
        }

        $rules = [
            'username' => 'required|min:3',
            'password' => 'required|min:6'
        ];

        if (!$this->validate($_POST, $rules)) {
            $this->setError('Invalid username or password format');
            return $this->redirect('/auth');
        }

        $username = $_POST['username'];
        $password = $_POST['password'];

        if ($this->model->authenticate($username, $password)) {
            $this->session->set('user', $username);
            $this->setSuccess('Successfully logged in');
            return $this->redirect('/dashboard');
        }

        $this->setError('Invalid username or password');
        return $this->redirect('/auth');
    }

    public function logout() {
        $this->session->destroy();
        return $this->redirect('/auth');
    }
}
