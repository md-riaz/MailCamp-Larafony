<?php

/**
 * Migration: Create subscriptions table
 * Manages email subscription preferences
 */
class CreateSubscriptionsTable
{
    public function up()
    {
        return "
            CREATE TABLE IF NOT EXISTS subscriptions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                organization_id INT NOT NULL,
                email VARCHAR(255) NOT NULL,
                name VARCHAR(255),
                status ENUM('subscribed', 'unsubscribed', 'bounced') DEFAULT 'subscribed',
                subscription_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                unsubscribe_date TIMESTAMP NULL,
                unsubscribe_token VARCHAR(64) UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
                UNIQUE KEY unique_org_email (organization_id, email),
                INDEX idx_organization (organization_id),
                INDEX idx_email (email),
                INDEX idx_status (status),
                INDEX idx_token (unsubscribe_token)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
    }
    
    public function down()
    {
        return "DROP TABLE IF EXISTS subscriptions;";
    }
}
