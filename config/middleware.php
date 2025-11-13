<?php

declare(strict_types=1);

use App\Middleware\AuthMiddleware;

return [
    'before_global' => [
       // Add global middleware that runs before request handling
    ],
    'global' => [
        // Add global middleware
    ],
    'after_global' => [
        // Add global middleware that runs after request handling
    ],
];
