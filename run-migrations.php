#!/usr/bin/env php
<?php

/**
 * Simple Migration Runner
 * Runs all migrations in database/migrations folder
 */

declare(strict_types=1);

echo "MailCamp - Database Migration Runner\n";
echo "=====================================\n\n";

// Load environment variables
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

// Database configuration
$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$database = $_ENV['DB_DATABASE'] ?? 'mailcamp';
$username = $_ENV['DB_USERNAME'] ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? '';

echo "Database Configuration:\n";
echo "  Host: $host:$port\n";
echo "  Database: $database\n";
echo "  Username: $username\n\n";

try {
    // Connect to MySQL (without database first)
    $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Connected to MySQL server\n\n";
    
    // Create database if it doesn't exist
    echo "Checking if database '$database' exists...\n";
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$database'");
    
    if ($stmt->rowCount() === 0) {
        echo "Creating database '$database'...\n";
        $pdo->exec("CREATE DATABASE `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "✓ Database created\n\n";
    } else {
        echo "✓ Database exists\n\n";
    }
    
    // Connect to the specific database
    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create migrations table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_migration (migration)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Get already run migrations
    $stmt = $pdo->query("SELECT migration FROM migrations");
    $ranMigrations = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get current batch number
    $stmt = $pdo->query("SELECT COALESCE(MAX(batch), 0) + 1 as next_batch FROM migrations");
    $batch = (int)$stmt->fetchColumn();
    
    // Load and run migrations
    $migrationsDir = __DIR__ . '/database/migrations';
    $files = glob($migrationsDir . '/*.php');
    sort($files);
    
    $newMigrationsRun = 0;
    
    echo "Running migrations...\n";
    echo "-------------------\n\n";
    
    foreach ($files as $file) {
        $migrationName = basename($file, '.php');
        
        // Skip if already run
        if (in_array($migrationName, $ranMigrations)) {
            echo "⊘ Skipped: $migrationName (already run)\n";
            continue;
        }
        
        echo "→ Running: $migrationName\n";
        
        // Load the migration file
        require_once $file;
        
        // Extract class name from file
        $className = str_replace('.php', '', basename($file));
        $className = implode('', array_map('ucfirst', explode('_', substr($className, 4)))); // Remove number prefix
        
        // Instantiate and run
        if (class_exists($className)) {
            $migration = new $className();
            $sql = $migration->up();
            
            // Execute the migration
            $pdo->exec($sql);
            
            // Record the migration
            $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
            $stmt->execute([$migrationName, $batch]);
            
            echo "✓ Completed: $migrationName\n\n";
            $newMigrationsRun++;
        } else {
            echo "⚠ Warning: Class $className not found in $file\n\n";
        }
    }
    
    echo "-------------------\n";
    echo "Migration Summary:\n";
    echo "  Total migrations found: " . count($files) . "\n";
    echo "  Already run: " . count($ranMigrations) . "\n";
    echo "  New migrations run: $newMigrationsRun\n";
    echo "  Current batch: $batch\n\n";
    
    if ($newMigrationsRun > 0) {
        echo "✓ All migrations completed successfully!\n";
    } else {
        echo "✓ Database is up to date!\n";
    }
    
} catch (PDOException $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
