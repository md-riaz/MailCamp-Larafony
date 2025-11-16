<?php

declare(strict_types=1);

namespace App\Database\Seeders;

/**
 * Database Seeder
 * 
 * Main seeder that orchestrates all other seeders in the correct order.
 * Run this file to seed the entire database with sample data.
 * 
 * Usage: php bin/larafony db:seed
 */
class DatabaseSeeder
{
    public function run(): void
    {
        echo "=====================================\n";
        echo "   MailCamp Database Seeder\n";
        echo "=====================================\n\n";
        
        $startTime = microtime(true);
        
        // Seed in correct order (respecting dependencies)
        $seeders = [
            RbacSeeder::class,        // 1. Roles and Permissions first
            UserSeeder::class,         // 2. Users and Organizations (with role assignment)
            SmtpSettingSeeder::class,  // 3. SMTP Settings
            TemplateSeeder::class,     // 4. Email Templates
            CampaignSeeder::class,     // 5. Campaigns (depends on templates, users, org)
        ];
        
        foreach ($seeders as $seederClass) {
            $seeder = new $seederClass();
            $seeder->run();
            echo "\n";
        }
        
        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);
        
        echo "=====================================\n";
        echo "   ✓ All seeders completed!\n";
        echo "   Duration: {$duration} seconds\n";
        echo "=====================================\n\n";
        
        $this->displaySummary();
    }
    
    private function displaySummary(): void
    {
        echo "Summary:\n";
        echo "--------\n";
        echo "• 3 Roles: admin, manager, user\n";
        echo "• 14 Permissions for campaign and template management\n";
        echo "• 1 Organization: Default Organization\n";
        echo "• 3 Users with profiles and role assignments\n";
        echo "• 3 SMTP Settings (1 active, 2 inactive examples)\n";
        echo "• 5 Email Templates (welcome, newsletter, etc.)\n";
        echo "• 5 Campaigns with different statuses\n";
        echo "• Sample recipients for active campaigns\n\n";
        
        echo "Test Credentials:\n";
        echo "-----------------\n";
        echo "Admin:   admin@example.com / password\n";
        echo "Manager: manager@example.com / password\n";
        echo "User:    user@example.com / password\n\n";
        
        echo "Next Steps:\n";
        echo "-----------\n";
        echo "1. Update SMTP settings with real credentials\n";
        echo "2. Test email sending functionality\n";
        echo "3. Create custom templates for your needs\n";
        echo "4. Start creating real campaigns!\n\n";
    }
}
