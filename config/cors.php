<?php

return [
    'allowed_origins' => array_map('trim', explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? '*')),
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
    'max_age'         => 86400,
];
