<?php

/**
 * Migration: Create queue_jobs table
 * Database-backed queue for batch email sending with throttling
 */
class CreateQueueJobsTable
{
    public function up()
    {
        return "
            CREATE TABLE IF NOT EXISTS queue_jobs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                organization_id INT NOT NULL,
                campaign_id INT NOT NULL,
                recipient_id INT NOT NULL,
                payload TEXT NOT NULL,
                attempts INT DEFAULT 0,
                status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
                available_at TIMESTAMP NOT NULL,
                reserved_at TIMESTAMP NULL,
                completed_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
                FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
                FOREIGN KEY (recipient_id) REFERENCES recipients(id) ON DELETE CASCADE,
                INDEX idx_status (status),
                INDEX idx_available (available_at),
                INDEX idx_campaign (campaign_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
    }
    
    public function down()
    {
        return "DROP TABLE IF EXISTS queue_jobs;";
    }
}
