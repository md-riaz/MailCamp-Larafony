#!/usr/bin/env php
<?php

/**
 * Demo Data Seeder Runner
 * Loads .env and runs the demo data seeder
 */

declare(strict_types=1);

// Load environment variables from .env
$envFile = __DIR__ . '/.env';
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

// Now run the seeder
require __DIR__ . '/database/seeds/demo_data.php';
