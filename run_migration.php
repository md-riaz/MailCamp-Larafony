<?php

require 'vendor/autoload.php';
require 'bootstrap/console.php';

use Larafony\Framework\Database\Base\Migrations\MigrationExecutor;
use Larafony\Framework\Database\Base\Migrations\MigrationRepository;
use Larafony\Framework\Database\Base\Migrations\MigrationResolver;

$container = require 'bootstrap/console.php';

// Resolve necessary classes
$resolver = $container->get(MigrationResolver::class);
$repository = $container->get(MigrationRepository::class);
$executor = $container->get(MigrationExecutor::class);

// Create migrations table if missing
$repository->createMigrationsTable();

// Get pending migrations
$migrations = array_diff($resolver->getMigrationFiles(), $repository->getRan());

if (empty($migrations)) {
    echo "Nothing to migrate.\n";
    exit(0);
}

// Run migrations
foreach ($migrations as $migration) {
    echo "Running migration: {$migration}\n";
    $executor->executeMigration($migration, 'up');
    echo "Migrated: {$migration}\n";
}

exit(0);