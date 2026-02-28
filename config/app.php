<?php

declare(strict_types=1);

use Larafony\Framework\Config\Environment\EnvReader;

return [
    'name' => EnvReader::read('APP_NAME', 'MailCamp'),
    'env' => EnvReader::read('APP_ENV', 'production'),
    'debug' => (bool) EnvReader::read('APP_DEBUG', false),
    'url' => EnvReader::read('APP_URL', 'http://localhost'),
    'key' => EnvReader::read('APP_KEY', null),
];
