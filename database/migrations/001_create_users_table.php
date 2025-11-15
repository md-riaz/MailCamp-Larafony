<?php

/**
 * Migration: Create users table
 * Base authentication table - follows framework standards
 * Only contains authentication-related fields
 */
class CreateUsersTable
{
    public function up()
    {
        return "
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(100) NOT NULL UNIQUE,
                username VARCHAR(50) NULL,
                password VARCHAR(255) NOT NULL,
                remember_token VARCHAR(100) NULL,
                password_reset_token VARCHAR(100) NULL,
                password_reset_expires TIMESTAMP NULL,
                email_verified_at TIMESTAMP NULL,
                is_active TINYINT(1) DEFAULT 1,
                last_login_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_email (email),
                UNIQUE KEY unique_username (username),
                INDEX idx_is_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
    }
    
    public function down()
    {
        return "DROP TABLE IF EXISTS users;";
    }
}
