<?php

declare(strict_types=1);

use Larafony\Framework\DebugBar\Middleware\InjectDebugBar;

return [
    'before_global' => [
       // Add global middleware that runs before request handling
    ],
    'global' => [
        // Add global middleware
    ],
    'after_global' => [
        InjectDebugBar::class,
    ],
];
