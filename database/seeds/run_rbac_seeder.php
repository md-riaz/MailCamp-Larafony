#!/usr/bin/env php
<?php

/**
 * RBAC Seeder Runner
 * Seeds roles and permissions for the RBAC system
 */

declare(strict_types=1);

// Load environment variables from .env
$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Bootstrap minimal dependencies
require __DIR__ . '/../../vendor/autoload.php';

// Create minimal application with only database provider
$app = \Larafony\Framework\Web\Application::instance(base_path: dirname(__DIR__, 2));
$app->withServiceProviders([
    Larafony\Framework\Config\ServiceProviders\ConfigServiceProvider::class,
    Larafony\Framework\Database\ServiceProviders\DatabaseServiceProvider::class,
]);

// Run the seeder
$seeder = new App\Database\Seeders\RbacSeeder();
$seeder->run();

echo "\nDone!\n";
