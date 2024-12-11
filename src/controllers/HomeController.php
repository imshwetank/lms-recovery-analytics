<?php

namespace App\Controllers;

use Core\Controller;

class HomeController extends Controller {
    public function index() {
        return $this->render('home/index', [
            'title' => 'Welcome to LMS Recovery Analytics',
            'user' => $this->session->get('user')
        ]);
    }

    public function about() {
        return $this->render('home/about', [
            'title' => 'About Us'
        ]);
    }

    public function contact() {
        return $this->render('home/contact', [
            'title' => 'Contact Us'
        ]);
    }
}
