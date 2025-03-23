<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', '/*'], // Including all routes if needed

    'allowed_methods' => ['*'], // Allowing all HTTP methods

    'allowed_origins' => ['*'], // You might want to specify trusted origins here for production

    'allowed_origins_patterns' => [], // Empty if no patterns needed

    'allowed_headers' => ['*'], // Allowing all headers

    'exposed_headers' => [], // No exposed headers

    'max_age' => 3600, // Cache preflight response for 1 hour

    'supports_credentials' => true, // Set to true if using cookies or authorization headers
];
