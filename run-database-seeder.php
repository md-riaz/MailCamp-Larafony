<?php

declare(strict_types=1);

/**
 * Database Seeder Runner
 * 
 * This file provides a convenient way to run database seeders.
 * 
 * Usage:
 *   php run-database-seeder.php              # Run all seeders
 *   php run-database-seeder.php RbacSeeder   # Run specific seeder
 *   php run-database-seeder.php UserSeeder   # Run specific seeder
 */

require_once __DIR__ . '/vendor/autoload.php';

use Larafony\Framework\Foundation\Application;

// Bootstrap the application
$app = require_once __DIR__ . '/bootstrap/app.php';

// Get seeder name from command line argument
$seederName = $argv[1] ?? 'DatabaseSeeder';

// Build full seeder class name
$seederClass = "App\\Database\\Seeders\\{$seederName}";

// Check if seeder class exists
if (!class_exists($seederClass)) {
    echo "Error: Seeder class '{$seederClass}' not found.\n\n";
    echo "Available seeders:\n";
    echo "  - DatabaseSeeder (runs all seeders)\n";
    echo "  - RbacSeeder\n";
    echo "  - UserSeeder\n";
    echo "  - SmtpSettingSeeder\n";
    echo "  - TemplateSeeder\n";
    echo "  - CampaignSeeder\n\n";
    echo "Usage: php run-database-seeder.php [SeederName]\n";
    exit(1);
}

echo "Running {$seederName}...\n\n";

try {
    // Create and run the seeder
    $seeder = new $seederClass();
    $seeder->run();
    
    echo "\n✓ Seeding completed successfully!\n";
    exit(0);
} catch (Throwable $e) {
    echo "\n✗ Seeding failed!\n";
    echo "Error: {$e->getMessage()}\n";
    echo "File: {$e->getFile()}:{$e->getLine()}\n\n";
    echo "Stack trace:\n{$e->getTraceAsString()}\n";
    exit(1);
}
