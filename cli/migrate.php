#!/usr/bin/env php
<?php

/**
 * Database Migration CLI Tool
 * Run: php cli/migrate.php
 */

require_once __DIR__ . '/../config/database.php';

$config = require __DIR__ . '/../config/database.php';
$dbConfig = $config['connections'][$config['default']];

try {
    // Connect to database
    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    echo "Creating database if not exists: {$dbConfig['database']}\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS {$dbConfig['database']} CHARACTER SET {$dbConfig['charset']} COLLATE {$dbConfig['collation']}");
    $pdo->exec("USE {$dbConfig['database']}");
    
    // Get migration files
    $migrationPath = __DIR__ . '/../database/migrations/';
    $files = glob($migrationPath . '*.php');
    sort($files);
    
    echo "Running migrations...\n";
    
    foreach ($files as $file) {
        $filename = basename($file);
        echo "Running: {$filename}\n";
        
        require $file;
        
        // Extract class name from file
        preg_match('/\d+_(.+)\.php$/', $filename, $matches);
        $className = str_replace('_', '', ucwords($matches[1], '_'));
        $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $matches[1])));
        
        $migration = new $className();
        $sql = $migration->up();
        
        try {
            $pdo->exec($sql);
            echo "✓ {$filename} completed\n";
        } catch (PDOException $e) {
            echo "✗ {$filename} failed: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\nMigration completed!\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    exit(1);
}
