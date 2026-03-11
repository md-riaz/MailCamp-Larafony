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
        Schema::create('messages', function ($table) {
            $table->id();
            $table->bigInteger('campaign_id')->unsigned(true);
            $table->bigInteger('subscriber_id')->unsigned(true)->nullable(true);
            $table->bigInteger('recipient_id')->unsigned(true)->nullable(true);
            $table->enum('status', ['queued', 'sending', 'sent', 'delivered', 'bounced', 'failed', 'opened', 'clicked', 'unsubscribed', 'complained'])->default('queued');
            $table->string('provider_message_id', 255)->nullable(true);
            $table->string('subject', 255)->nullable(true);
            $table->timestamp('sent_at')->nullable(true);
            $table->timestamp('delivered_at')->nullable(true);
            $table->timestamp('created_at')->nullable(false)->current();
            $table->timestamp('updated_at')->nullable(false)->current()->currentOnUpdate();

            $table->index('campaign_id');
            $table->index('subscriber_id');
            $table->index('recipient_id');
            $table->index('status');
            $table->index('sent_at');
            $table->index('created_at');
            $table->index('provider_message_id');
            $table->unique(['campaign_id', 'provider_message_id'], 'messages_campaign_provider_unique');
        }) |> Schema::execute(...);

        Schema::execute('ALTER TABLE `messages` ADD CONSTRAINT `fk_messages_campaign_id` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns`(`id`) ON DELETE CASCADE');
        Schema::execute('ALTER TABLE `messages` ADD CONSTRAINT `fk_messages_subscriber_id` FOREIGN KEY (`subscriber_id`) REFERENCES `subscriptions`(`id`) ON DELETE SET NULL');
        Schema::execute('ALTER TABLE `messages` ADD CONSTRAINT `fk_messages_recipient_id` FOREIGN KEY (`recipient_id`) REFERENCES `recipients`(`id`) ON DELETE SET NULL');

        Schema::create('email_events', function ($table) {
            $table->id();
            $table->bigInteger('message_id')->unsigned(true);
            $table->bigInteger('campaign_id')->unsigned(true);
            $table->bigInteger('subscriber_id')->unsigned(true)->nullable(true);
            $table->string('event_type', 64);
            $table->timestamp('timestamp')->nullable(false)->current();
            $table->string('provider_message_id', 255)->nullable(true);
            $table->string('ip_address', 45)->nullable(true);
            $table->string('user_agent', 1024)->nullable(true);
            $table->json('metadata')->nullable(true);
            $table->timestamp('created_at')->nullable(false)->current();
            $table->timestamp('updated_at')->nullable(false)->current()->currentOnUpdate();

            $table->index('message_id');
            $table->index('campaign_id');
            $table->index('subscriber_id');
            $table->index('event_type');
            $table->index('timestamp');
            $table->index('created_at');
            $table->index('provider_message_id');
            $table->index(['campaign_id', 'event_type', 'timestamp'], 'email_events_campaign_event_timestamp_idx');
        }) |> Schema::execute(...);

        Schema::execute('ALTER TABLE `email_events` ADD CONSTRAINT `fk_email_events_message_id` FOREIGN KEY (`message_id`) REFERENCES `messages`(`id`) ON DELETE CASCADE');
        Schema::execute('ALTER TABLE `email_events` ADD CONSTRAINT `fk_email_events_campaign_id` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns`(`id`) ON DELETE CASCADE');
        Schema::execute('ALTER TABLE `email_events` ADD CONSTRAINT `fk_email_events_subscriber_id` FOREIGN KEY (`subscriber_id`) REFERENCES `subscriptions`(`id`) ON DELETE SET NULL');

        Schema::create('links', function ($table) {
            $table->id();
            $table->bigInteger('message_id')->unsigned(true);
            $table->bigInteger('campaign_id')->unsigned(true);
            $table->bigInteger('subscriber_id')->unsigned(true)->nullable(true);
            $table->string('url', 2048);
            $table->string('url_hash', 64)->nullable(true);
            $table->integer('click_count')->unsigned(true)->default(0);
            $table->timestamp('last_clicked_at')->nullable(true);
            $table->timestamp('created_at')->nullable(false)->current();
            $table->timestamp('updated_at')->nullable(false)->current()->currentOnUpdate();

            $table->index('message_id');
            $table->index('campaign_id');
            $table->index('subscriber_id');
            $table->index('created_at');
            $table->index(['campaign_id', 'subscriber_id'], 'links_campaign_subscriber_idx');
            $table->unique(['message_id', 'url_hash'], 'links_message_url_hash_unique');
        }) |> Schema::execute(...);

        Schema::execute('ALTER TABLE `links` ADD CONSTRAINT `fk_links_message_id` FOREIGN KEY (`message_id`) REFERENCES `messages`(`id`) ON DELETE CASCADE');
        Schema::execute('ALTER TABLE `links` ADD CONSTRAINT `fk_links_campaign_id` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns`(`id`) ON DELETE CASCADE');
        Schema::execute('ALTER TABLE `links` ADD CONSTRAINT `fk_links_subscriber_id` FOREIGN KEY (`subscriber_id`) REFERENCES `subscriptions`(`id`) ON DELETE SET NULL');

        Schema::create('bounces', function ($table) {
            $table->id();
            $table->bigInteger('message_id')->unsigned(true);
            $table->bigInteger('campaign_id')->unsigned(true);
            $table->bigInteger('subscriber_id')->unsigned(true)->nullable(true);
            $table->string('provider_message_id', 255)->nullable(true);
            $table->enum('bounce_type', ['hard', 'soft', 'blocked', 'domain_error', 'unknown'])->default('unknown');
            $table->string('smtp_code', 32)->nullable(true);
            $table->text('bounce_reason')->nullable(true);
            $table->json('metadata')->nullable(true);
            $table->timestamp('bounced_at')->nullable(false)->current();
            $table->timestamp('created_at')->nullable(false)->current();
            $table->timestamp('updated_at')->nullable(false)->current()->currentOnUpdate();

            $table->index('message_id');
            $table->index('campaign_id');
            $table->index('subscriber_id');
            $table->index('provider_message_id');
            $table->index('bounced_at');
            $table->index('created_at');
            $table->index('bounce_type');
        }) |> Schema::execute(...);

        Schema::execute('ALTER TABLE `bounces` ADD CONSTRAINT `fk_bounces_message_id` FOREIGN KEY (`message_id`) REFERENCES `messages`(`id`) ON DELETE CASCADE');
        Schema::execute('ALTER TABLE `bounces` ADD CONSTRAINT `fk_bounces_campaign_id` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns`(`id`) ON DELETE CASCADE');
        Schema::execute('ALTER TABLE `bounces` ADD CONSTRAINT `fk_bounces_subscriber_id` FOREIGN KEY (`subscriber_id`) REFERENCES `subscriptions`(`id`) ON DELETE SET NULL');

        Schema::create('webhooks', function ($table) {
            $table->id();
            $table->bigInteger('campaign_id')->unsigned(true)->nullable(true);
            $table->bigInteger('message_id')->unsigned(true)->nullable(true);
            $table->bigInteger('subscriber_id')->unsigned(true)->nullable(true);
            $table->string('provider', 64);
            $table->string('event_type', 64)->nullable(true);
            $table->string('provider_message_id', 255)->nullable(true);
            $table->string('signature', 255)->nullable(true);
            $table->string('idempotency_key', 255)->nullable(true);
            $table->enum('processing_status', ['pending', 'processed', 'failed', 'duplicate'])->default('pending');
            $table->json('payload')->nullable(false);
            $table->json('headers')->nullable(true);
            $table->timestamp('processed_at')->nullable(true);
            $table->timestamp('created_at')->nullable(false)->current();
            $table->timestamp('updated_at')->nullable(false)->current()->currentOnUpdate();

            $table->index('campaign_id');
            $table->index('message_id');
            $table->index('subscriber_id');
            $table->index('event_type');
            $table->index('provider_message_id');
            $table->index('created_at');
            $table->index('processing_status');
            $table->unique('idempotency_key');
            $table->index(['provider', 'created_at'], 'webhooks_provider_created_idx');
        }) |> Schema::execute(...);

        Schema::execute('ALTER TABLE `webhooks` ADD CONSTRAINT `fk_webhooks_campaign_id` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns`(`id`) ON DELETE SET NULL');
        Schema::execute('ALTER TABLE `webhooks` ADD CONSTRAINT `fk_webhooks_message_id` FOREIGN KEY (`message_id`) REFERENCES `messages`(`id`) ON DELETE SET NULL');
        Schema::execute('ALTER TABLE `webhooks` ADD CONSTRAINT `fk_webhooks_subscriber_id` FOREIGN KEY (`subscriber_id`) REFERENCES `subscriptions`(`id`) ON DELETE SET NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('webhooks') |> Schema::execute(...);
        Schema::dropIfExists('bounces') |> Schema::execute(...);
        Schema::dropIfExists('links') |> Schema::execute(...);
        Schema::dropIfExists('email_events') |> Schema::execute(...);
        Schema::dropIfExists('messages') |> Schema::execute(...);
    }
};
