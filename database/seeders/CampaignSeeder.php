<?php

declare(strict_types=1);

namespace App\Database\Seeders;

use App\Models\Campaign;
use App\Models\Organization;
use App\Models\Recipient;
use App\Models\Template;
use App\Models\User;

class CampaignSeeder
{
    public function run(): void
    {
        echo "Seeding campaigns...\n\n";
        
        // Get default organization and admin user
        $defaultOrg = Organization::query()->where('slug', '=', 'default-org')->first();
        $adminUser = User::query()->where('email', '=', 'admin@example.com')->first();
        
        if (!$defaultOrg) {
            echo "   ⚠ Default organization not found - please run UserSeeder first\n";
            return;
        }
        
        if (!$adminUser) {
            echo "   ⚠ Admin user not found - please run UserSeeder first\n";
            return;
        }
        
        // Get templates
        $welcomeTemplate = Template::query()->where('name', '=', 'Welcome Email')->first();
        $newsletterTemplate = Template::query()->where('name', '=', 'Newsletter - Monthly Update')->first();
        
        if (!$welcomeTemplate || !$newsletterTemplate) {
            echo "   ⚠ Templates not found - please run TemplateSeeder first\n";
            return;
        }
        
        $campaigns = [
            [
                'name' => 'Welcome Campaign - Q4 2024',
                'template_id' => $welcomeTemplate->id,
                'status' => 'sent',
                'total_recipients' => 150,
                'sent_count' => 148,
                'failed_count' => 2,
                'scheduled_at' => date('Y-m-d H:i:s', strtotime('-30 days')),
                'started_at' => date('Y-m-d H:i:s', strtotime('-30 days')),
                'completed_at' => date('Y-m-d H:i:s', strtotime('-30 days')),
            ],
            [
                'name' => 'Monthly Newsletter - November 2024',
                'template_id' => $newsletterTemplate->id,
                'status' => 'sent',
                'total_recipients' => 500,
                'sent_count' => 495,
                'failed_count' => 5,
                'scheduled_at' => date('Y-m-d H:i:s', strtotime('-15 days')),
                'started_at' => date('Y-m-d H:i:s', strtotime('-15 days')),
                'completed_at' => date('Y-m-d H:i:s', strtotime('-15 days')),
            ],
            [
                'name' => 'Black Friday Promotion',
                'template_id' => $newsletterTemplate->id,
                'status' => 'sending',
                'total_recipients' => 1000,
                'sent_count' => 650,
                'failed_count' => 10,
                'scheduled_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'started_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'completed_at' => null,
            ],
            [
                'name' => 'Holiday Special - December 2024',
                'template_id' => $welcomeTemplate->id,
                'status' => 'scheduled',
                'total_recipients' => 800,
                'sent_count' => 0,
                'failed_count' => 0,
                'scheduled_at' => date('Y-m-d H:i:s', strtotime('+7 days')),
                'started_at' => null,
                'completed_at' => null,
            ],
            [
                'name' => 'New Year Newsletter',
                'template_id' => $newsletterTemplate->id,
                'status' => 'draft',
                'total_recipients' => 0,
                'sent_count' => 0,
                'failed_count' => 0,
                'scheduled_at' => null,
                'started_at' => null,
                'completed_at' => null,
            ],
        ];
        
        $count = 0;
        foreach ($campaigns as $campaignData) {
            $campaign = new Campaign();
            $campaign->organization_id = (int)$defaultOrg->id;
            $campaign->template_id = $campaignData['template_id'];
            $campaign->name = $campaignData['name'];
            $campaign->status = $campaignData['status'];
            $campaign->total_recipients = $campaignData['total_recipients'];
            $campaign->sent_count = $campaignData['sent_count'];
            $campaign->failed_count = $campaignData['failed_count'];
            $campaign->scheduled_at = $campaignData['scheduled_at'];
            $campaign->started_at = $campaignData['started_at'];
            $campaign->completed_at = $campaignData['completed_at'];
            $campaign->created_by = (int)$adminUser->id;
            $campaign->save();
            $count++;
            echo "   ✓ {$campaignData['name']} created (ID: {$campaign->id}, Status: {$campaignData['status']})\n";
            
            // Add sample recipients for campaigns with recipients
            if ($campaignData['total_recipients'] > 0) {
                $this->createSampleRecipients((int)$campaign->id, (int)$defaultOrg->id, $campaignData);
            }
        }
        
        echo "\n✓ {$count} campaigns seeded successfully!\n";
    }
    
    private function createSampleRecipients(int $campaignId, int $organizationId, array $campaignData): void
    {
        $totalRecipients = min($campaignData['total_recipients'], 10); // Limit to 10 sample recipients
        $sentCount = $campaignData['sent_count'];
        $failedCount = $campaignData['failed_count'];
        
        for ($i = 1; $i <= $totalRecipients; $i++) {
            $recipient = new Recipient();
            $recipient->organization_id = $organizationId;
            $recipient->campaign_id = $campaignId;
            $recipient->email = "recipient{$i}@example.com";
            $recipient->name = "Test User {$i}";
            $recipient->custom_data = json_encode([
                'first_name' => "User{$i}",
                'last_name' => "Test",
                'company' => "Example Corp",
            ]);
            
            // Determine status based on campaign data
            if ($i <= $failedCount) {
                $recipient->status = 'failed';
                $recipient->sent_at = date('Y-m-d H:i:s', strtotime($campaignData['started_at'] ?? 'now'));
            } elseif ($i <= ($sentCount > 0 ? min($sentCount, $totalRecipients) : 0)) {
                $recipient->status = 'sent';
                $recipient->sent_at = date('Y-m-d H:i:s', strtotime($campaignData['started_at'] ?? 'now'));
            } else {
                $recipient->status = 'pending';
            }
            
            $recipient->save();
        }
    }
}
