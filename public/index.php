<?php

declare(strict_types=1);

define('APPLICATION_START', microtime(true));
require_once __DIR__ . '/../vendor/autoload.php';

// Normalize REQUEST_URI when app is mounted under a subpath (e.g. /mailcamp)
$envFile = __DIR__ . '/../.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if ($line === '' || str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v);
        if (!array_key_exists($k, $_ENV)) {
            $_ENV[$k] = $v;
            putenv("{$k}={$v}");
        }
    }
}

$appUrlPath = parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '';
$appUrlPath = rtrim($appUrlPath, '/');
if ($appUrlPath !== '' && isset($_SERVER['REQUEST_URI']) && str_starts_with($_SERVER['REQUEST_URI'], $appUrlPath)) {
    $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], strlen($appUrlPath)) ?: '/';
}

// Bootstrap the application
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Run the application
$app->run();
