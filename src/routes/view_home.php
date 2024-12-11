<?php

/**
 * Home View Routes
 * These routes handle the main public pages
 */

return [
    'GET /' => ['HomeController', 'index'],
    'GET /about' => ['HomeController', 'about'],
    'GET /contact' => ['HomeController', 'contact']
];
