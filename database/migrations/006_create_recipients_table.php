<?php

/**
 * Migration: Create recipients table
 * Stores recipient lists for campaigns
 */
class CreateRecipientsTable
{
    public function up()
    {
        return "
            CREATE TABLE IF NOT EXISTS recipients (
                id INT AUTO_INCREMENT PRIMARY KEY,
                organization_id INT NOT NULL,
                campaign_id INT NOT NULL,
                email VARCHAR(255) NOT NULL,
                name VARCHAR(255),
                custom_data TEXT,
                status ENUM('pending', 'sent', 'failed', 'bounced', 'unsubscribed') DEFAULT 'pending',
                sent_at TIMESTAMP NULL,
                opened_at TIMESTAMP NULL,
                clicked_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
                FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
                INDEX idx_campaign (campaign_id),
                INDEX idx_email (email),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
    }
    
    public function down()
    {
        return "DROP TABLE IF EXISTS recipients;";
    }
}
