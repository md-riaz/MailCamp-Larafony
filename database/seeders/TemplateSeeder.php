<?php

declare(strict_types=1);

namespace App\Database\Seeders;

use App\Models\Organization;
use App\Models\Template;

class TemplateSeeder
{
    public function run(): void
    {
        echo "Seeding email templates...\n\n";
        
        // Get default organization
        $defaultOrg = Organization::query()->where('slug', '=', 'default-org')->first();
        
        if (!$defaultOrg) {
            echo "   ⚠ Default organization not found - please run UserSeeder first\n";
            return;
        }
        
        $templates = [
            [
                'name' => 'Welcome Email',
                'subject' => 'Welcome to {{company_name}}!',
                'html_content' => <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #4CAF50;">Welcome to {{company_name}}!</h1>
        <p>Hi {{first_name}},</p>
        <p>Thank you for joining us. We're excited to have you on board!</p>
        <p>Here's what you can expect:</p>
        <ul>
            <li>Access to our premium features</li>
            <li>Regular updates and newsletters</li>
            <li>24/7 customer support</li>
        </ul>
        <p>If you have any questions, feel free to reach out to our support team.</p>
        <p>Best regards,<br>The {{company_name}} Team</p>
    </div>
</body>
</html>
HTML,
                'variables' => json_encode(['company_name', 'first_name']),
                'is_active' => 1,
            ],
            [
                'name' => 'Newsletter - Monthly Update',
                'subject' => '📧 Monthly Newsletter - {{month}} {{year}}',
                'html_content' => <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Monthly Newsletter</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f4f4f4;">
        <div style="background-color: white; padding: 30px; border-radius: 8px;">
            <h1 style="color: #2196F3; margin-bottom: 20px;">{{month}} Newsletter</h1>
            <p>Dear {{first_name}},</p>
            <p>Here are the highlights from {{month}} {{year}}:</p>
            
            <h2 style="color: #555; font-size: 18px;">What's New</h2>
            <p>{{whats_new}}</p>
            
            <h2 style="color: #555; font-size: 18px;">Upcoming Events</h2>
            <p>{{upcoming_events}}</p>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
                <p style="font-size: 12px; color: #888;">
                    You're receiving this because you subscribed to our newsletter.
                    <a href="{{unsubscribe_url}}" style="color: #2196F3;">Unsubscribe</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
HTML,
                'variables' => json_encode(['first_name', 'month', 'year', 'whats_new', 'upcoming_events', 'unsubscribe_url']),
                'is_active' => 1,
            ],
            [
                'name' => 'Product Announcement',
                'subject' => '🎉 Exciting News: {{product_name}} is Here!',
                'html_content' => <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Product Announcement</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px; border-radius: 8px; text-align: center;">
            <h1 style="margin: 0; font-size: 32px;">🎉 Big News!</h1>
            <p style="font-size: 18px; margin: 20px 0 0 0;">Introducing {{product_name}}</p>
        </div>
        
        <div style="padding: 30px; background-color: white;">
            <p>Hi {{first_name}},</p>
            <p>We're thrilled to announce the launch of <strong>{{product_name}}</strong>!</p>
            <p>{{product_description}}</p>
            
            <div style="margin: 30px 0; text-align: center;">
                <a href="{{cta_url}}" style="display: inline-block; padding: 15px 30px; background-color: #667eea; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
                    {{cta_text}}
                </a>
            </div>
            
            <p>Thank you for being a valued member of our community.</p>
            <p>Best regards,<br>The Team</p>
        </div>
    </div>
</body>
</html>
HTML,
                'variables' => json_encode(['first_name', 'product_name', 'product_description', 'cta_url', 'cta_text']),
                'is_active' => 1,
            ],
            [
                'name' => 'Password Reset',
                'subject' => 'Reset Your Password',
                'html_content' => <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Password Reset</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #FF9800;">Password Reset Request</h1>
        <p>Hi {{username}},</p>
        <p>We received a request to reset your password. Click the button below to create a new password:</p>
        
        <div style="margin: 30px 0; text-align: center;">
            <a href="{{reset_url}}" style="display: inline-block; padding: 15px 30px; background-color: #FF9800; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
                Reset Password
            </a>
        </div>
        
        <p>If you didn't request this, you can safely ignore this email.</p>
        <p>This link will expire in {{expiry_hours}} hours.</p>
        
        <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #888;">
            For security reasons, do not share this email with anyone.
        </p>
    </div>
</body>
</html>
HTML,
                'variables' => json_encode(['username', 'reset_url', 'expiry_hours']),
                'is_active' => 1,
            ],
            [
                'name' => 'Order Confirmation',
                'subject' => 'Order Confirmation #{{order_number}}',
                'html_content' => <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #4CAF50;">Order Confirmed! 🎉</h1>
        <p>Hi {{customer_name}},</p>
        <p>Thank you for your order! We've received it and will process it shortly.</p>
        
        <div style="background-color: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <h2 style="margin-top: 0; color: #555;">Order Details</h2>
            <p><strong>Order Number:</strong> #{{order_number}}</p>
            <p><strong>Order Date:</strong> {{order_date}}</p>
            <p><strong>Total Amount:</strong> \${{total_amount}}</p>
        </div>
        
        <p>You'll receive another email when your order ships.</p>
        
        <div style="margin: 30px 0; text-align: center;">
            <a href="{{order_url}}" style="display: inline-block; padding: 15px 30px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
                View Order
            </a>
        </div>
        
        <p>Thank you for shopping with us!</p>
        <p>Best regards,<br>Customer Service Team</p>
    </div>
</body>
</html>
HTML,
                'variables' => json_encode(['customer_name', 'order_number', 'order_date', 'total_amount', 'order_url']),
                'is_active' => 1,
            ],
        ];
        
        $count = 0;
        foreach ($templates as $templateData) {
            $template = new Template();
            $template->organization_id = (int)$defaultOrg->id;
            $template->name = $templateData['name'];
            $template->subject = $templateData['subject'];
            $template->html_content = $templateData['html_content'];
            $template->variables = $templateData['variables'];
            $template->is_active = $templateData['is_active'];
            $template->save();
            $count++;
            echo "   ✓ {$templateData['name']} created (ID: {$template->id})\n";
        }
        
        echo "\n✓ {$count} email templates seeded successfully!\n";
    }
}
