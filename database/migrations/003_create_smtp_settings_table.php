<?php

/**
 * Migration: Create smtp_settings table
 * Stores SMTP configuration per organization
 */
class CreateSmtpSettingsTable
{
    public function up()
    {
        return "
            CREATE TABLE IF NOT EXISTS smtp_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                organization_id INT NOT NULL,
                host VARCHAR(255) NOT NULL,
                port INT NOT NULL DEFAULT 587,
                encryption ENUM('none', 'ssl', 'tls') DEFAULT 'tls',
                username VARCHAR(255) NOT NULL,
                password VARCHAR(255) NOT NULL,
                from_email VARCHAR(255) NOT NULL,
                from_name VARCHAR(255) NOT NULL,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
                INDEX idx_organization (organization_id),
                INDEX idx_is_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
    }
    
    public function down()
    {
        return "DROP TABLE IF EXISTS smtp_settings;";
    }
}
