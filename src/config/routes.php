<?php

return [
    // Auth routes
    '/login' => [
        'controller' => 'Auth',
        'method' => 'showLoginForm'
    ],
    '/auth/login' => [
        'controller' => 'Auth',
        'method' => 'login'
    ],
    '/verify' => [
        'controller' => 'Auth',
        'method' => 'showVerifyForm'
    ],
    '/auth/verify' => [
        'controller' => 'Auth',
        'method' => 'verify'
    ],
    '/logout' => [
        'controller' => 'Auth',
        'method' => 'logout'
    ],

    // Chart routes
    '/charts' => [
        'controller' => 'Chart',
        'method' => 'index'
    ],
    '/charts/data' => [
        'controller' => 'Chart',
        'method' => 'getData'
    ],

    // Custom routes with parameters
    '/charts/branch/(:num)' => [
        'controller' => 'Chart',
        'method' => 'getBranchData',
        'params' => ['$1']
    ],
    '/charts/type/(:any)' => [
        'controller' => 'Chart',
        'method' => 'getTypeData',
        'params' => ['$1']
    ]
];
