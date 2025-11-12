<?php

declare(strict_types=1);

namespace App\Console;

use Larafony\Framework\Console\Attributes\Command;
use Larafony\Framework\Console\Commands\Command as BaseCommand;

#[Command(
    name: 'app:init',
    description: 'Initialize the MailCamp application'
)]
class InitCommand extends BaseCommand
{
    public function handle(): int
    {
        $this->info('MailCamp - Multi-tenant Email Campaign Manager');
        $this->info('==============================================');
        $this->newLine();

        // Check database connection
        $this->info('Checking database connection...');
        
        try {
            $config = require __DIR__ . '/../../config/database.php';
            $mysqlConfig = $config['connections']['mysql'];
            
            $dsn = sprintf(
                'mysql:host=%s;port=%s',
                $mysqlConfig['host'],
                $mysqlConfig['port']
            );
            
            $pdo = new \PDO(
                $dsn,
                $mysqlConfig['username'],
                $mysqlConfig['password']
            );
            
            $this->success('✓ Database connection successful');
            
            // Check if database exists
            $stmt = $pdo->query(
                "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA 
                 WHERE SCHEMA_NAME = '{$mysqlConfig['database']}'"
            );
            
            if ($stmt->rowCount() === 0) {
                $this->warn("Database '{$mysqlConfig['database']}' does not exist.");
                
                if ($this->confirm('Would you like to create it?', true)) {
                    $pdo->exec("CREATE DATABASE {$mysqlConfig['database']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $this->success("✓ Database '{$mysqlConfig['database']}' created successfully");
                }
            } else {
                $this->success("✓ Database '{$mysqlConfig['database']}' exists");
            }
            
        } catch (\PDOException $e) {
            $this->error('✗ Database connection failed: ' . $e->getMessage());
            return self::FAILURE;
        }
        
        $this->newLine();
        $this->info('Next steps:');
        $this->line('1. Run migrations: php bin/larafony migrate');
        $this->line('2. Start the server: php -S localhost:8000 -t public');
        $this->line('3. Visit http://localhost:8000');
        
        return self::SUCCESS;
    }
}
