<?php

namespace Core\Middleware;

class MiddlewareHandler {
    private $middlewares = [];

    public function __construct() {
        $this->middlewares = [
            'auth' => new AuthMiddleware(),
            'api' => new ApiMiddleware()
        ];
    }

    public function handle(array $middleware = []) {
        foreach ($middleware as $name) {
            if (isset($this->middlewares[$name])) {
                $this->middlewares[$name]->handle();
            }
        }
    }
}
