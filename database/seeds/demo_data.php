#!/usr/bin/env php
<?php

/**
 * Demo Data Seeder
 * Creates sample data for testing MailCamp
 * 
 * WARNING: This will create test data in your database.
 * Only run on development/testing environments!
 * 
 * Usage: php database/seeds/demo_data.php
 */

require_once __DIR__ . '/../../config/database.php';

$config = require __DIR__ . '/../../config/database.php';
$dbConfig = $config['connections'][$config['default']];

try {
    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Creating demo data...\n\n";
    
    // 1. Create demo organization
    echo "1. Creating demo organization...\n";
    $stmt = $pdo->prepare("
        INSERT INTO organizations (name, slug, domain, is_active) 
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE name=name
    ");
    $stmt->execute(['Demo Company', 'demo-company', 'demo.example.com', 1]);
    
    $orgId = $pdo->lastInsertId();
    if (!$orgId) {
        // Organization might already exist
        $stmt = $pdo->prepare("SELECT id FROM organizations WHERE slug = ?");
        $stmt->execute(['demo-company']);
        $orgId = $stmt->fetchColumn();
    }
    echo "   ✓ Organization created (ID: {$orgId})\n";
    
    // 2. Create demo users
    echo "\n2. Creating demo users...\n";
    $users = [
        ['Demo Admin', 'admin@demo.example.com', 'admin123', 'admin'],
        ['Demo Manager', 'manager@demo.example.com', 'manager123', 'manager'],
        ['Demo User', 'user@demo.example.com', 'user123', 'user'],
    ];
    
    foreach ($users as $user) {
        $stmt = $pdo->prepare("
            INSERT INTO users (organization_id, name, email, password, role, is_active) 
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE password=VALUES(password), role=VALUES(role), is_active=VALUES(is_active)
        ");
        $stmt->execute([
            $orgId,
            $user[0],
            $user[1],
            password_hash($user[2], PASSWORD_ARGON2ID),
            $user[3],
            1
        ]);
        echo "   ✓ {$user[0]} ({$user[1]}) - Password: {$user[2]}\n";
    }
    
    // Get admin user ID
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND organization_id = ?");
    $stmt->execute(['admin@demo.example.com', $orgId]);
    $adminId = $stmt->fetchColumn();
    
    // 3. Create SMTP settings (using example values - won't actually work)
    echo "\n3. Creating SMTP settings...\n";
    $stmt = $pdo->prepare("
        INSERT INTO smtp_settings (
            organization_id, host, port, encryption, username, password, 
            from_email, from_name, is_active
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE host=VALUES(host)
    ");
    $stmt->execute([
        $orgId,
        'smtp.example.com',
        587,
        'tls',
        'demo@example.com',
        base64_encode('demo-password'),
        'noreply@demo.example.com',
        'Demo Company',
        1
    ]);
    echo "   ✓ SMTP settings created (needs real credentials to work)\n";
    
    // 4. Create demo templates
    echo "\n4. Creating demo templates...\n";
    $templates = [
        [
            'Welcome Email',
            'Welcome to {{company}}, {{name}}!',
            '<html><body><h1>Hello {{name}}!</h1><p>Welcome to {{company}}. We are excited to have you with us.</p><p>Your email is: {{email}}</p><p>Best regards,<br>The Team</p></body></html>'
        ],
        [
            'Newsletter',
            'Monthly Newsletter - {{month}}',
            '<html><body><h1>Newsletter for {{month}}</h1><p>Hi {{name}},</p><p>Here is our latest newsletter with exciting updates!</p><p>Stay tuned for more.</p><p><small><a href="{{unsubscribe_url}}">Unsubscribe</a></small></p></body></html>'
        ],
        [
            'Product Announcement',
            'New Product Launch: {{product_name}}',
            '<html><body><h1>Introducing {{product_name}}!</h1><p>Dear {{name}},</p><p>We are thrilled to announce the launch of {{product_name}}.</p><p>Learn more at {{product_url}}</p><p>Thanks,<br>{{company}}</p></body></html>'
        ],
    ];
    
    $templateIds = [];
    foreach ($templates as $template) {
        $stmt = $pdo->prepare("
            INSERT INTO templates (
                organization_id, name, subject, html_content, variables, is_active
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        // Extract variables
        preg_match_all('/\{\{([^}]+)\}\}/', $template[1] . ' ' . $template[2], $matches);
        $variables = json_encode(array_unique($matches[1]));
        
        $stmt->execute([
            $orgId,
            $template[0],
            $template[1],
            $template[2],
            $variables,
            1
        ]);
        
        $templateIds[] = $pdo->lastInsertId();
        echo "   ✓ {$template[0]} template created\n";
    }
    
    // 5. Create demo campaign
    echo "\n5. Creating demo campaign...\n";
    $stmt = $pdo->prepare("
        INSERT INTO campaigns (
            organization_id, template_id, name, status, 
            total_recipients, sent_count, failed_count, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $orgId,
        $templateIds[0],
        'Welcome Campaign',
        'draft',
        0,
        0,
        0,
        $adminId
    ]);
    
    $campaignId = $pdo->lastInsertId();
    echo "   ✓ Welcome Campaign created (ID: {$campaignId})\n";
    
    // 6. Create demo recipients
    echo "\n6. Creating demo recipients...\n";
    $recipients = [
        ['john.doe@example.com', 'John Doe'],
        ['jane.smith@example.com', 'Jane Smith'],
        ['bob.johnson@example.com', 'Bob Johnson'],
        ['alice.williams@example.com', 'Alice Williams'],
        ['charlie.brown@example.com', 'Charlie Brown'],
    ];
    
    foreach ($recipients as $recipient) {
        $stmt = $pdo->prepare("
            INSERT INTO recipients (
                organization_id, campaign_id, email, name, 
                custom_data, status
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $customData = json_encode([
            'company' => 'Demo Company',
            'position' => 'Customer'
        ]);
        
        $stmt->execute([
            $orgId,
            $campaignId,
            $recipient[0],
            $recipient[1],
            $customData,
            'pending'
        ]);
        echo "   ✓ {$recipient[1]} ({$recipient[0]})\n";
    }
    
    // Update campaign recipient count
    $stmt = $pdo->prepare("UPDATE campaigns SET total_recipients = ? WHERE id = ?");
    $stmt->execute([count($recipients), $campaignId]);
    
    // 7. Create demo subscriptions
    echo "\n7. Creating demo subscriptions...\n";
    foreach ($recipients as $recipient) {
        $stmt = $pdo->prepare("
            INSERT INTO subscriptions (
                organization_id, email, name, status, unsubscribe_token
            ) VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE email=email
        ");
        $stmt->execute([
            $orgId,
            $recipient[0],
            $recipient[1],
            'subscribed',
            bin2hex(random_bytes(32))
        ]);
    }
    echo "   ✓ {count($recipients)} subscriptions created\n";
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "Demo data created successfully!\n";
    echo str_repeat("=", 60) . "\n\n";
    
    echo "Login Credentials:\n";
    echo "  Admin:   admin@demo.example.com / admin123\n";
    echo "  Manager: manager@demo.example.com / manager123\n";
    echo "  User:    user@demo.example.com / user123\n\n";
    
    echo "Next Steps:\n";
    echo "1. Log in with admin credentials\n";
    echo "2. Configure real SMTP settings in 'SMTP Settings'\n";
    echo "3. Review the 'Welcome Campaign' on the Campaigns page\n";
    echo "4. Launch the campaign to test email sending\n";
    echo "5. Monitor results on the campaign detail page\n\n";
    
    echo "Note: The SMTP settings created are placeholders.\n";
    echo "      You need to configure real SMTP credentials before sending emails.\n\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
