<?php

declare(strict_types=1);

use Larafony\Framework\Database\Base\Migrations\Migration;
use Larafony\Framework\Database\Schema;

/**
 * Migration: Extend observability core for real data wiring
 *
 * Adds organization-level denormalization and query indexes so dashboard,
 * events APIs, and provider ingestion can query observability data without
 * expensive multi-table joins on every request.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->addColumnIfMissing('messages', 'organization_id BIGINT UNSIGNED NULL AFTER `campaign_id`');
        $this->addColumnIfMissing('messages', 'provider VARCHAR(64) NULL AFTER `status`');
        $this->addColumnIfMissing('messages', 'recipient_email VARCHAR(255) NULL AFTER `recipient_id`');

        $this->addColumnIfMissing('email_events', 'organization_id BIGINT UNSIGNED NULL AFTER `campaign_id`');
        $this->addColumnIfMissing('email_events', 'recipient_id BIGINT UNSIGNED NULL AFTER `subscriber_id`');
        $this->addColumnIfMissing('email_events', 'provider VARCHAR(64) NULL AFTER `event_type`');

        $this->addColumnIfMissing('links', 'organization_id BIGINT UNSIGNED NULL AFTER `campaign_id`');
        $this->addColumnIfMissing('links', 'recipient_id BIGINT UNSIGNED NULL AFTER `subscriber_id`');

        $this->addColumnIfMissing('bounces', 'organization_id BIGINT UNSIGNED NULL AFTER `campaign_id`');
        $this->addColumnIfMissing('bounces', 'recipient_id BIGINT UNSIGNED NULL AFTER `subscriber_id`');
        $this->addColumnIfMissing('bounces', 'provider VARCHAR(64) NULL AFTER `provider_message_id`');

        $this->addColumnIfMissing('webhooks', 'organization_id BIGINT UNSIGNED NULL AFTER `campaign_id`');
        $this->addColumnIfMissing('webhooks', 'recipient_id BIGINT UNSIGNED NULL AFTER `subscriber_id`');

        Schema::execute('UPDATE `messages` m INNER JOIN `campaigns` c ON c.`id` = m.`campaign_id` SET m.`organization_id` = c.`organization_id` WHERE m.`organization_id` IS NULL');
        Schema::execute('UPDATE `messages` m INNER JOIN `recipients` r ON r.`id` = m.`recipient_id` SET m.`recipient_email` = r.`email` WHERE m.`recipient_email` IS NULL');
        Schema::execute("UPDATE `messages` SET `provider` = 'smtp' WHERE `provider` IS NULL");

        Schema::execute('UPDATE `email_events` e INNER JOIN `campaigns` c ON c.`id` = e.`campaign_id` SET e.`organization_id` = c.`organization_id` WHERE e.`organization_id` IS NULL');
        Schema::execute('UPDATE `email_events` e INNER JOIN `messages` m ON m.`id` = e.`message_id` SET e.`recipient_id` = m.`recipient_id` WHERE e.`recipient_id` IS NULL');
        Schema::execute('UPDATE `email_events` e INNER JOIN `messages` m ON m.`id` = e.`message_id` SET e.`provider` = m.`provider` WHERE e.`provider` IS NULL');
        Schema::execute("UPDATE `email_events` SET `provider` = 'smtp' WHERE `provider` IS NULL");

        Schema::execute('UPDATE `links` l INNER JOIN `campaigns` c ON c.`id` = l.`campaign_id` SET l.`organization_id` = c.`organization_id` WHERE l.`organization_id` IS NULL');
        Schema::execute('UPDATE `links` l INNER JOIN `messages` m ON m.`id` = l.`message_id` SET l.`recipient_id` = m.`recipient_id` WHERE l.`recipient_id` IS NULL');

        Schema::execute('UPDATE `bounces` b INNER JOIN `campaigns` c ON c.`id` = b.`campaign_id` SET b.`organization_id` = c.`organization_id` WHERE b.`organization_id` IS NULL');
        Schema::execute('UPDATE `bounces` b INNER JOIN `messages` m ON m.`id` = b.`message_id` SET b.`recipient_id` = m.`recipient_id` WHERE b.`recipient_id` IS NULL');
        Schema::execute('UPDATE `bounces` b INNER JOIN `messages` m ON m.`id` = b.`message_id` SET b.`provider` = m.`provider` WHERE b.`provider` IS NULL');
        Schema::execute("UPDATE `bounces` SET `provider` = 'smtp' WHERE `provider` IS NULL");

        Schema::execute('UPDATE `webhooks` w INNER JOIN `campaigns` c ON c.`id` = w.`campaign_id` SET w.`organization_id` = c.`organization_id` WHERE w.`organization_id` IS NULL');
        Schema::execute('UPDATE `webhooks` w INNER JOIN `messages` m ON m.`id` = w.`message_id` SET w.`recipient_id` = m.`recipient_id` WHERE w.`recipient_id` IS NULL');

        Schema::execute('ALTER TABLE `messages` MODIFY `organization_id` BIGINT UNSIGNED NOT NULL');
        Schema::execute('ALTER TABLE `messages` MODIFY `provider` VARCHAR(64) NOT NULL DEFAULT "smtp"');
        Schema::execute('ALTER TABLE `email_events` MODIFY `organization_id` BIGINT UNSIGNED NOT NULL');
        Schema::execute('ALTER TABLE `email_events` MODIFY `provider` VARCHAR(64) NOT NULL DEFAULT "smtp"');
        Schema::execute('ALTER TABLE `links` MODIFY `organization_id` BIGINT UNSIGNED NOT NULL');
        Schema::execute('ALTER TABLE `bounces` MODIFY `organization_id` BIGINT UNSIGNED NOT NULL');
        Schema::execute('ALTER TABLE `bounces` MODIFY `provider` VARCHAR(64) NOT NULL DEFAULT "smtp"');
        Schema::execute('ALTER TABLE `webhooks` MODIFY `organization_id` BIGINT UNSIGNED NOT NULL');

        $this->addIndexIfMissing('messages', 'messages_organization_id_idx', '(`organization_id`)');
        $this->addIndexIfMissing('messages', 'messages_org_campaign_status_id_idx', '(`organization_id`, `campaign_id`, `status`, `id`)');
        $this->addIndexIfMissing('messages', 'messages_org_provider_message_idx', '(`organization_id`, `provider_message_id`)');
        $this->addIndexIfMissing('messages', 'messages_campaign_recipient_idx', '(`campaign_id`, `recipient_id`)');
        $this->addIndexIfMissing('messages', 'messages_campaign_subscriber_idx', '(`campaign_id`, `subscriber_id`)');
        $this->addIndexIfMissing('messages', 'messages_recipient_email_idx', '(`recipient_email`)');

        $this->addIndexIfMissing('email_events', 'email_events_organization_id_idx', '(`organization_id`)');
        $this->addIndexIfMissing('email_events', 'email_events_org_event_timestamp_idx', '(`organization_id`, `event_type`, `timestamp`)');
        $this->addIndexIfMissing('email_events', 'email_events_message_event_timestamp_idx', '(`message_id`, `event_type`, `timestamp`)');
        $this->addIndexIfMissing('email_events', 'email_events_recipient_event_timestamp_idx', '(`recipient_id`, `event_type`, `timestamp`)');
        $this->addIndexIfMissing('email_events', 'email_events_provider_provider_message_idx', '(`provider`, `provider_message_id`)');

        $this->addIndexIfMissing('links', 'links_organization_id_idx', '(`organization_id`)');
        $this->addIndexIfMissing('links', 'links_message_created_idx', '(`message_id`, `created_at`)');
        $this->addIndexIfMissing('links', 'links_campaign_click_count_idx', '(`campaign_id`, `click_count`)');
        $this->addIndexIfMissing('links', 'links_recipient_last_clicked_idx', '(`recipient_id`, `last_clicked_at`)');

        $this->addIndexIfMissing('bounces', 'bounces_organization_id_idx', '(`organization_id`)');
        $this->addIndexIfMissing('bounces', 'bounces_org_type_bounced_idx', '(`organization_id`, `bounce_type`, `bounced_at`)');
        $this->addIndexIfMissing('bounces', 'bounces_message_bounced_idx', '(`message_id`, `bounced_at`)');
        $this->addIndexIfMissing('bounces', 'bounces_provider_provider_message_idx', '(`provider`, `provider_message_id`)');

        $this->addIndexIfMissing('webhooks', 'webhooks_organization_id_idx', '(`organization_id`)');
        $this->addIndexIfMissing('webhooks', 'webhooks_org_status_created_idx', '(`organization_id`, `processing_status`, `created_at`)');
        $this->addIndexIfMissing('webhooks', 'webhooks_provider_event_created_idx', '(`provider`, `event_type`, `created_at`)');
        $this->addIndexIfMissing('webhooks', 'webhooks_message_created_idx', '(`message_id`, `created_at`)');

        $this->addForeignKeyIfMissing('messages', 'fk_messages_organization_id', 'ALTER TABLE `messages` ADD CONSTRAINT `fk_messages_organization_id` FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`) ON DELETE CASCADE');
        $this->addForeignKeyIfMissing('email_events', 'fk_email_events_organization_id', 'ALTER TABLE `email_events` ADD CONSTRAINT `fk_email_events_organization_id` FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`) ON DELETE CASCADE');
        $this->addForeignKeyIfMissing('email_events', 'fk_email_events_recipient_id', 'ALTER TABLE `email_events` ADD CONSTRAINT `fk_email_events_recipient_id` FOREIGN KEY (`recipient_id`) REFERENCES `recipients`(`id`) ON DELETE SET NULL');
        $this->addForeignKeyIfMissing('links', 'fk_links_organization_id', 'ALTER TABLE `links` ADD CONSTRAINT `fk_links_organization_id` FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`) ON DELETE CASCADE');
        $this->addForeignKeyIfMissing('links', 'fk_links_recipient_id', 'ALTER TABLE `links` ADD CONSTRAINT `fk_links_recipient_id` FOREIGN KEY (`recipient_id`) REFERENCES `recipients`(`id`) ON DELETE SET NULL');
        $this->addForeignKeyIfMissing('bounces', 'fk_bounces_organization_id', 'ALTER TABLE `bounces` ADD CONSTRAINT `fk_bounces_organization_id` FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`) ON DELETE CASCADE');
        $this->addForeignKeyIfMissing('bounces', 'fk_bounces_recipient_id', 'ALTER TABLE `bounces` ADD CONSTRAINT `fk_bounces_recipient_id` FOREIGN KEY (`recipient_id`) REFERENCES `recipients`(`id`) ON DELETE SET NULL');
        $this->addForeignKeyIfMissing('webhooks', 'fk_webhooks_organization_id', 'ALTER TABLE `webhooks` ADD CONSTRAINT `fk_webhooks_organization_id` FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`) ON DELETE CASCADE');
        $this->addForeignKeyIfMissing('webhooks', 'fk_webhooks_recipient_id', 'ALTER TABLE `webhooks` ADD CONSTRAINT `fk_webhooks_recipient_id` FOREIGN KEY (`recipient_id`) REFERENCES `recipients`(`id`) ON DELETE SET NULL');
    }

    public function down(): void
    {
        $this->dropForeignKeyIfExists('webhooks', 'fk_webhooks_recipient_id');
        $this->dropForeignKeyIfExists('webhooks', 'fk_webhooks_organization_id');
        $this->dropForeignKeyIfExists('bounces', 'fk_bounces_recipient_id');
        $this->dropForeignKeyIfExists('bounces', 'fk_bounces_organization_id');
        $this->dropForeignKeyIfExists('links', 'fk_links_recipient_id');
        $this->dropForeignKeyIfExists('links', 'fk_links_organization_id');
        $this->dropForeignKeyIfExists('email_events', 'fk_email_events_recipient_id');
        $this->dropForeignKeyIfExists('email_events', 'fk_email_events_organization_id');
        $this->dropForeignKeyIfExists('messages', 'fk_messages_organization_id');

        foreach ([
            ['webhooks', 'webhooks_message_created_idx'],
            ['webhooks', 'webhooks_provider_event_created_idx'],
            ['webhooks', 'webhooks_org_status_created_idx'],
            ['webhooks', 'webhooks_organization_id_idx'],
            ['bounces', 'bounces_provider_provider_message_idx'],
            ['bounces', 'bounces_message_bounced_idx'],
            ['bounces', 'bounces_org_type_bounced_idx'],
            ['bounces', 'bounces_organization_id_idx'],
            ['links', 'links_recipient_last_clicked_idx'],
            ['links', 'links_campaign_click_count_idx'],
            ['links', 'links_message_created_idx'],
            ['links', 'links_organization_id_idx'],
            ['email_events', 'email_events_provider_provider_message_idx'],
            ['email_events', 'email_events_recipient_event_timestamp_idx'],
            ['email_events', 'email_events_message_event_timestamp_idx'],
            ['email_events', 'email_events_org_event_timestamp_idx'],
            ['email_events', 'email_events_organization_id_idx'],
            ['messages', 'messages_recipient_email_idx'],
            ['messages', 'messages_campaign_subscriber_idx'],
            ['messages', 'messages_campaign_recipient_idx'],
            ['messages', 'messages_org_provider_message_idx'],
            ['messages', 'messages_org_campaign_status_id_idx'],
            ['messages', 'messages_organization_id_idx'],
        ] as [$table, $index]) {
            $this->dropIndexIfExists($table, $index);
        }

        $this->dropColumnIfExists('webhooks', 'recipient_id');
        $this->dropColumnIfExists('webhooks', 'organization_id');
        $this->dropColumnIfExists('bounces', 'provider');
        $this->dropColumnIfExists('bounces', 'recipient_id');
        $this->dropColumnIfExists('bounces', 'organization_id');
        $this->dropColumnIfExists('links', 'recipient_id');
        $this->dropColumnIfExists('links', 'organization_id');
        $this->dropColumnIfExists('email_events', 'provider');
        $this->dropColumnIfExists('email_events', 'recipient_id');
        $this->dropColumnIfExists('email_events', 'organization_id');
        $this->dropColumnIfExists('messages', 'recipient_email');
        $this->dropColumnIfExists('messages', 'provider');
        $this->dropColumnIfExists('messages', 'organization_id');
    }

    private function addColumnIfMissing(string $table, string $definition): void
    {
        Schema::execute(sprintf('ALTER TABLE `%s` ADD COLUMN IF NOT EXISTS %s', $table, $definition));
    }

    private function dropColumnIfExists(string $table, string $column): void
    {
        Schema::execute(sprintf('ALTER TABLE `%s` DROP COLUMN IF EXISTS `%s`', $table, $column));
    }

    private function addIndexIfMissing(string $table, string $name, string $columns): void
    {
        $this->attempt(sprintf('ALTER TABLE `%s` ADD INDEX IF NOT EXISTS `%s` %s', $table, $name, $columns));
    }

    private function dropIndexIfExists(string $table, string $name): void
    {
        $this->attempt(sprintf('ALTER TABLE `%s` DROP INDEX IF EXISTS `%s`', $table, $name));
    }

    private function addForeignKeyIfMissing(string $table, string $constraint, string $sql): void
    {
        $this->attempt($sql);
    }

    private function dropForeignKeyIfExists(string $table, string $constraint): void
    {
        $this->attempt(sprintf('ALTER TABLE `%s` DROP FOREIGN KEY `%s`', $table, $constraint));
    }

    private function attempt(string $sql): void
    {
        try {
            Schema::execute($sql);
        } catch (\Throwable) {
            // Best-effort migration helpers: tolerate already-exists / already-dropped cases.
        }
    }
};
