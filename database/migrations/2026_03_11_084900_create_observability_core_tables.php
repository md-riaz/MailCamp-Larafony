<?php

declare(strict_types=1);

use Larafony\Framework\Database\Base\Migrations\Migration;
use Larafony\Framework\Database\Schema;

/**
 * Migration: Create observability core tables
 * Adds messages, email events, links, bounces, and webhook storage.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->attempt(<<<'SQL'
CREATE TABLE IF NOT EXISTS `messages` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `campaign_id` INT(11) NOT NULL,
  `subscriber_id` INT(11) NULL,
  `recipient_id` INT(11) NULL,
  `status` VARCHAR(32) NOT NULL DEFAULT 'queued',
  `provider_message_id` VARCHAR(255) NULL,
  `subject` VARCHAR(255) NULL,
  `sent_at` TIMESTAMP NULL DEFAULT NULL,
  `delivered_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `messages_campaign_id_idx` (`campaign_id`),
  KEY `messages_subscriber_id_idx` (`subscriber_id`),
  KEY `messages_recipient_id_idx` (`recipient_id`),
  KEY `messages_status_idx` (`status`),
  KEY `messages_sent_at_idx` (`sent_at`),
  KEY `messages_created_at_idx` (`created_at`),
  KEY `messages_provider_message_id_idx` (`provider_message_id`),
  UNIQUE KEY `messages_campaign_provider_unique` (`campaign_id`,`provider_message_id`),
  CONSTRAINT `fk_messages_campaign_id` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_messages_subscriber_id` FOREIGN KEY (`subscriber_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_messages_recipient_id` FOREIGN KEY (`recipient_id`) REFERENCES `recipients` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        $this->attempt(<<<'SQL'
CREATE TABLE IF NOT EXISTS `email_events` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `message_id` INT(11) NOT NULL,
  `campaign_id` INT(11) NOT NULL,
  `subscriber_id` INT(11) NULL,
  `event_type` VARCHAR(64) NOT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `provider_message_id` VARCHAR(255) NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` VARCHAR(1024) NULL,
  `metadata` JSON NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `email_events_message_id_idx` (`message_id`),
  KEY `email_events_campaign_id_idx` (`campaign_id`),
  KEY `email_events_subscriber_id_idx` (`subscriber_id`),
  KEY `email_events_event_type_idx` (`event_type`),
  KEY `email_events_timestamp_idx` (`timestamp`),
  KEY `email_events_created_at_idx` (`created_at`),
  KEY `email_events_provider_message_id_idx` (`provider_message_id`),
  KEY `email_events_campaign_event_timestamp_idx` (`campaign_id`,`event_type`,`timestamp`),
  CONSTRAINT `fk_email_events_message_id` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_email_events_campaign_id` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_email_events_subscriber_id` FOREIGN KEY (`subscriber_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        $this->attempt(<<<'SQL'
CREATE TABLE IF NOT EXISTS `links` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `message_id` INT(11) NOT NULL,
  `campaign_id` INT(11) NOT NULL,
  `subscriber_id` INT(11) NULL,
  `url` VARCHAR(2048) NOT NULL,
  `url_hash` VARCHAR(64) NULL,
  `click_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `last_clicked_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `links_message_id_idx` (`message_id`),
  KEY `links_campaign_id_idx` (`campaign_id`),
  KEY `links_subscriber_id_idx` (`subscriber_id`),
  KEY `links_created_at_idx` (`created_at`),
  KEY `links_campaign_subscriber_idx` (`campaign_id`,`subscriber_id`),
  UNIQUE KEY `links_message_url_hash_unique` (`message_id`,`url_hash`),
  CONSTRAINT `fk_links_message_id` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_links_campaign_id` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_links_subscriber_id` FOREIGN KEY (`subscriber_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        $this->attempt(<<<'SQL'
CREATE TABLE IF NOT EXISTS `bounces` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `message_id` INT(11) NOT NULL,
  `campaign_id` INT(11) NOT NULL,
  `subscriber_id` INT(11) NULL,
  `provider_message_id` VARCHAR(255) NULL,
  `bounce_type` VARCHAR(32) NOT NULL DEFAULT 'unknown',
  `smtp_code` VARCHAR(32) NULL,
  `bounce_reason` TEXT NULL,
  `metadata` JSON NULL,
  `bounced_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `bounces_message_id_idx` (`message_id`),
  KEY `bounces_campaign_id_idx` (`campaign_id`),
  KEY `bounces_subscriber_id_idx` (`subscriber_id`),
  KEY `bounces_provider_message_id_idx` (`provider_message_id`),
  KEY `bounces_bounced_at_idx` (`bounced_at`),
  KEY `bounces_created_at_idx` (`created_at`),
  KEY `bounces_bounce_type_idx` (`bounce_type`),
  CONSTRAINT `fk_bounces_message_id` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bounces_campaign_id` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bounces_subscriber_id` FOREIGN KEY (`subscriber_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        $this->attempt(<<<'SQL'
CREATE TABLE IF NOT EXISTS `webhooks` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `campaign_id` INT(11) NULL,
  `message_id` INT(11) NULL,
  `subscriber_id` INT(11) NULL,
  `provider` VARCHAR(64) NOT NULL,
  `event_type` VARCHAR(64) NULL,
  `provider_message_id` VARCHAR(255) NULL,
  `signature` VARCHAR(255) NULL,
  `idempotency_key` VARCHAR(255) NULL,
  `processing_status` VARCHAR(32) NOT NULL DEFAULT 'pending',
  `payload` JSON NOT NULL,
  `headers` JSON NULL,
  `processed_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `webhooks_campaign_id_idx` (`campaign_id`),
  KEY `webhooks_message_id_idx` (`message_id`),
  KEY `webhooks_subscriber_id_idx` (`subscriber_id`),
  KEY `webhooks_event_type_idx` (`event_type`),
  KEY `webhooks_provider_message_id_idx` (`provider_message_id`),
  KEY `webhooks_created_at_idx` (`created_at`),
  KEY `webhooks_processing_status_idx` (`processing_status`),
  UNIQUE KEY `webhooks_idempotency_key_unique` (`idempotency_key`),
  KEY `webhooks_provider_created_idx` (`provider`,`created_at`),
  CONSTRAINT `fk_webhooks_campaign_id` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_webhooks_message_id` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_webhooks_subscriber_id` FOREIGN KEY (`subscriber_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('webhooks') |> Schema::execute(...);
        Schema::dropIfExists('bounces') |> Schema::execute(...);
        Schema::dropIfExists('links') |> Schema::execute(...);
        Schema::dropIfExists('email_events') |> Schema::execute(...);
        Schema::dropIfExists('messages') |> Schema::execute(...);
    }

    private function attempt(string $sql): void
    {
        try {
            Schema::execute($sql);
        } catch (\Throwable) {
            // idempotent best effort for environments already partially migrated
        }
    }
};
