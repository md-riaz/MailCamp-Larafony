<?php

/**
 * Migration: Create logs table
 * Tracks email opens, clicks, and failures
 */
class CreateLogsTable
{
    public function up()
    {
        return "
            CREATE TABLE IF NOT EXISTS logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                organization_id INT NOT NULL,
                campaign_id INT NOT NULL,
                recipient_id INT NOT NULL,
                event_type ENUM('sent', 'opened', 'clicked', 'bounced', 'failed', 'unsubscribed') NOT NULL,
                event_data TEXT,
                user_agent TEXT,
                ip_address VARCHAR(45),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
                FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
                FOREIGN KEY (recipient_id) REFERENCES recipients(id) ON DELETE CASCADE,
                INDEX idx_campaign (campaign_id),
                INDEX idx_recipient (recipient_id),
                INDEX idx_event_type (event_type),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
    }
    
    public function down()
    {
        return "DROP TABLE IF EXISTS logs;";
    }
}
