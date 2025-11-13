<?php

declare(strict_types=1);

use Larafony\Framework\Config\Environment\EnvReader;
use Larafony\Framework\Storage\Session\Handlers\FileSessionHandler;
use Larafony\Framework\Storage\Session\Handlers\DatabaseSessionHandler;

return [
    /*
    |--------------------------------------------------------------------------
    | Session Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the session handler used by the application.
    | Supported: FileSessionHandler, DatabaseSessionHandler
    |
    */
    'handler' => EnvReader::read('SESSION_DRIVER') === 'database'
        ? DatabaseSessionHandler::class
        : FileSessionHandler::class,

    /*
    |--------------------------------------------------------------------------
    | Session File Storage Path
    |--------------------------------------------------------------------------
    |
    | When using the file session handler, this path determines where
    | session files will be stored. Only used with FileSessionHandler.
    |
    */
    'path' => EnvReader::read('SESSION_PATH', sys_get_temp_dir() . '/sessions'),

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the session cookie settings for your application.
    |
    */
    'cookie_params' => [
        'lifetime'  => (int) EnvReader::read('SESSION_LIFETIME', '120'),
        'path'      => '/',
        'domain'    => EnvReader::read('SESSION_DOMAIN', null) ?: '',
        'secure'    => (bool) EnvReader::read('SESSION_SECURE_COOKIE', 'false'),
        'httponly'  => true,
        'samesite'  => EnvReader::read('SESSION_SAME_SITE', 'lax'),
    ],

];
