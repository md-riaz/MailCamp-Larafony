<?php

declare(strict_types=1);

use Larafony\Framework\Config\Environment\EnvReader;
use Larafony\Framework\DebugBar\Middleware\InjectDebugBar;

$afterGlobal = [];

if (EnvReader::read('APP_DEBUG', 'false') === 'true') {
    $afterGlobal[] = InjectDebugBar::class;
}

return [
    'before_global' => [
       // Add global middleware that runs before request handling
    ],
    'global' => [
        // Add global middleware
    ],
    'after_global' => $afterGlobal,
];
