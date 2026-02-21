<?php

return [
    'name'    => $_ENV['APP_NAME'] ?? 'AurexAPI',
    'env'     => $_ENV['APP_ENV']  ?? 'production',
    'debug'   => ($_ENV['APP_DEBUG'] ?? 'false') === 'true',
    'url'     => $_ENV['APP_URL']  ?? 'http://localhost',
    'version' => '1.0.0',
];
