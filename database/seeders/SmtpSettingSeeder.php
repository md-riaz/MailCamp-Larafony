<?php

declare(strict_types=1);

namespace App\Database\Seeders;

use App\Models\Organization;
use App\Models\SmtpSetting;

class SmtpSettingSeeder
{
    public function run(): void
    {
        echo "Seeding SMTP settings...\n\n";
        
        // Get default organization
        $defaultOrg = Organization::query()->where('slug', '=', 'default-org')->first();
        
        if (!$defaultOrg) {
            echo "   ⚠ Default organization not found - please run UserSeeder first\n";
            return;
        }
        
        $smtpSettings = [
            [
                'description' => 'Default SMTP Server',
                'host' => 'smtp.example.com',
                'port' => 587,
                'username' => 'noreply@example.com',
                'password' => 'encrypted_password_here',
                'encryption' => 'tls',
                'from_email' => 'noreply@example.com',
                'from_name' => 'Default Organization',
                'is_active' => 1,
            ],
            [
                'description' => 'Gmail SMTP',
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'username' => 'your-email@gmail.com',
                'password' => 'app_specific_password',
                'encryption' => 'tls',
                'from_email' => 'your-email@gmail.com',
                'from_name' => 'Your Name',
                'is_active' => 0,
            ],
            [
                'description' => 'SendGrid SMTP',
                'host' => 'smtp.sendgrid.net',
                'port' => 587,
                'username' => 'apikey',
                'password' => 'your_sendgrid_api_key',
                'encryption' => 'tls',
                'from_email' => 'verified@yourdomain.com',
                'from_name' => 'Your Company',
                'is_active' => 0,
            ],
        ];
        
        $count = 0;
        foreach ($smtpSettings as $settingData) {
            $smtp = new SmtpSetting();
            $smtp->organization_id = (int)$defaultOrg->id;
            $smtp->host = $settingData['host'];
            $smtp->port = $settingData['port'];
            $smtp->username = $settingData['username'];
            $smtp->password = $settingData['password'];
            $smtp->encryption = $settingData['encryption'];
            $smtp->from_email = $settingData['from_email'];
            $smtp->from_name = $settingData['from_name'];
            $smtp->is_active = $settingData['is_active'];
            $smtp->save();
            $count++;
            
            $status = $settingData['is_active'] ? '✓ Active' : '○ Inactive';
            echo "   {$status} {$settingData['description']} created (ID: {$smtp->id})\n";
        }
        
        echo "\n✓ {$count} SMTP settings seeded successfully!\n";
        echo "\n⚠ Note: Update SMTP credentials in the database before using them for actual email sending.\n";
    }
}
