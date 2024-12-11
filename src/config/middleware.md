# Middleware Documentation

## Overview
The middleware system provides a flexible way to handle HTTP requests before they reach your application's core logic. Each middleware can perform specific tasks such as authentication, CORS handling, CSRF protection, and rate limiting.

## Available Middleware

### 1. Authentication Middleware (AuthMiddleware)
Handles session-based authentication for web routes.

```php
// Configuration
'auth' => [
    'enabled' => true,
    'session_key' => 'user_id',
    'redirect_to' => '/login',
    'exclude' => [
        '/login',
        '/register',
        '/forgot-password'
    ]
]

// Usage in forms
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}
```

### 2. API Middleware (ApiMiddleware)
Handles token-based authentication for API routes.

```php
// Configuration
'api' => [
    'enabled' => true,
    'token_header' => 'Authorization',
    'token_prefix' => 'Bearer',
    'exclude' => [
        '/api/login',
        '/api/register'
    ]
]

// Usage in API calls
$headers = [
    'Authorization: Bearer your-token-here'
];
```

### 3. CORS Middleware (CorsMiddleware)
Handles Cross-Origin Resource Sharing headers.

```php
// Configuration
'cors' => [
    'enabled' => true,
    'allow_origins' => ['*'],
    'allow_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
    'allow_headers' => ['Content-Type', 'Authorization'],
    'allow_credentials' => false,
    'max_age' => 0
]
```

### 4. CSRF Middleware (CsrfMiddleware)
Protects against Cross-Site Request Forgery attacks.

```php
// Configuration
'csrf' => [
    'enabled' => true,
    'token_length' => 32,
    'token_lifetime' => 60 * 24, // 24 hours
    'exclude' => [
        '/api/*'
    ]
]

// Usage in forms
<form method="post">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <!-- form fields -->
</form>

// Usage in AJAX calls
$.ajax({
    url: '/api/endpoint',
    headers: {
        'X-CSRF-TOKEN': '<?php echo $_SESSION["csrf_token"]; ?>'
    }
});
```

### 5. Rate Limiting (ThrottleMiddleware)
Prevents abuse by limiting request frequency.

```php
// Configuration
'throttle' => [
    'enabled' => true,
    'max_attempts' => 60,
    'decay_minutes' => 1,
    'exclude' => [
        '/assets/*'
    ]
]
```

## Environment Variables
Configure middleware behavior using these environment variables:

```env
# Global Middleware Settings
MIDDLEWARE_ENABLED=true
MIDDLEWARE_ERROR_IF_NOT_FOUND=true

# Auth Middleware
AUTH_MIDDLEWARE_ENABLED=true
AUTH_SESSION_KEY=user_id
AUTH_REDIRECT_TO=/login

# API Middleware
API_MIDDLEWARE_ENABLED=true
API_TOKEN_HEADER=Authorization
API_TOKEN_PREFIX=Bearer

# CORS Settings
CORS_MIDDLEWARE_ENABLED=true
CORS_ALLOW_ORIGINS=*
CORS_ALLOW_METHODS=GET,POST,PUT,DELETE,OPTIONS
CORS_ALLOW_HEADERS=Content-Type,Authorization
CORS_ALLOW_CREDENTIALS=false
CORS_MAX_AGE=0

# CSRF Protection
CSRF_MIDDLEWARE_ENABLED=true
CSRF_TOKEN_LENGTH=32
CSRF_TOKEN_LIFETIME=1440

# Rate Limiting
THROTTLE_MIDDLEWARE_ENABLED=true
THROTTLE_MAX_ATTEMPTS=60
THROTTLE_DECAY_MINUTES=1
```

## Adding Custom Middleware

1. Create a new middleware class in `src/middleware/`:

```php
namespace Middleware;

class CustomMiddleware extends Middleware {
    public function handle() {
        // Your middleware logic here
        if ($someCondition) {
            return $this->error('Error message');
        }
        return $this->success();
    }
}
```

2. Add configuration in `src/config/middleware.php`:

```php
'middleware' => [
    'custom' => [
        'enabled' => true,
        // Your custom config options
    ]
]
```

3. Add to middleware load order:

```php
'load' => [
    'auth',
    'custom',  // Your new middleware
    'api'
]
```

## Error Handling

Middleware can return responses in these ways:

```php
// Redirect response
return $this->redirect('/login');

// Error response
return $this->error('Unauthorized access');

// Success response
return $this->success();
```

## Best Practices

1. **Order Matters**: Place authentication middleware before others
2. **Exclude Paths**: Use wildcards for excluding similar paths: `/api/*`
3. **Rate Limiting**: Set reasonable limits based on your server capacity
4. **CORS**: Be specific with allowed origins in production
5. **CSRF**: Always use for forms that modify data
6. **API Authentication**: Use strong token generation and validation

## Troubleshooting

1. **Middleware Not Running**:
   - Check if enabled in `.env`
   - Verify load order in config
   - Check for syntax errors

2. **CORS Issues**:
   - Verify allowed origins
   - Check if credentials are needed
   - Confirm required headers

3. **Rate Limiting Too Strict**:
   - Adjust max_attempts
   - Modify decay_minutes
   - Add to exclude paths

4. **CSRF Token Mismatch**:
   - Check token in session
   - Verify form/header submission
   - Check token lifetime
