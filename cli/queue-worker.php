#!/usr/bin/env php
<?php

/**
 * Queue Worker CLI Tool
 * Run: php cli/queue-worker.php
 */

// Autoload classes
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Load configuration
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

use App\Workers\QueueWorker;

echo "Starting MailCamp Queue Worker...\n";

try {
    $worker = new QueueWorker();
    $worker->process();
} catch (Exception $e) {
    echo "Worker error: " . $e->getMessage() . "\n";
    exit(1);
}
