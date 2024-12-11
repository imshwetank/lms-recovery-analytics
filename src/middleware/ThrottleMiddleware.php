<?php

namespace Middleware;

class ThrottleMiddleware extends Middleware {
    private $cachePrefix = 'throttle_';

    public function handle() {
        // Skip for excluded paths
        if ($this->isExcluded($this->getRequestPath())) {
            return $this->success();
        }

        $key = $this->getThrottleKey();
        $maxAttempts = $this->config['max_attempts'];
        $decayMinutes = $this->config['decay_minutes'];

        // Get current attempts
        $attempts = $this->getAttempts($key);
        
        // Check if max attempts exceeded
        if ($attempts >= $maxAttempts) {
            return $this->error('Too many requests. Please try again later.');
        }

        // Increment attempts
        $this->incrementAttempts($key, $decayMinutes);

        // Set rate limit headers
        $this->setRateLimitHeaders($maxAttempts, $attempts + 1);

        return $this->success();
    }

    private function getThrottleKey() {
        return $this->cachePrefix . md5(
            $_SERVER['REMOTE_ADDR'] . '|' . 
            $_SERVER['REQUEST_URI']
        );
    }

    private function getAttempts($key) {
        return (int)apcu_fetch($key) ?: 0;
    }

    private function incrementAttempts($key, $decayMinutes) {
        $ttl = $decayMinutes * 60; // Convert to seconds
        
        if (apcu_exists($key)) {
            apcu_inc($key);
        } else {
            apcu_store($key, 1, $ttl);
        }
    }

    private function setRateLimitHeaders($limit, $remaining) {
        header('X-RateLimit-Limit: ' . $limit);
        header('X-RateLimit-Remaining: ' . max(0, $limit - $remaining));
    }
}
