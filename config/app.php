<?php

return [
    'name' => getenv('APP_NAME') ?: 'MailCamp',
    'env' => getenv('APP_ENV') ?: 'production',
    'debug' => (bool) getenv('APP_DEBUG') ?: false,
    'url' => getenv('APP_URL') ?: 'http://localhost',
    
    'queue' => [
        'driver' => getenv('QUEUE_DRIVER') ?: 'database',
        'throttle_per_hour' => (int) getenv('QUEUE_THROTTLE_EMAILS_PER_HOUR') ?: 100,
    ],
    
    'session' => [
        'driver' => getenv('SESSION_DRIVER') ?: 'file',
        'lifetime' => (int) getenv('SESSION_LIFETIME') ?: 120,
    ],
];
