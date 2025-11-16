<?php

declare(strict_types=1);

use Larafony\Framework\Database\Base\Migrations\Migration;
use Larafony\Framework\Database\Schema;

/**
 * Migration: Create logs table
 * Tracks email opens, clicks, and failures
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logs', function ($table) {
            $table->id();
            $table->bigInteger('organization_id')->unsigned(true);
            $table->bigInteger('campaign_id')->unsigned(true);
            $table->bigInteger('recipient_id')->unsigned(true);
            $table->enum('event_type', ['sent', 'opened', 'clicked', 'bounced', 'failed', 'unsubscribed'])->nullable(false);
            $table->text('event_data')->nullable(true);
            $table->text('user_agent')->nullable(true);
            $table->string('ip_address', 45)->nullable(true);
            $table->timestamp('created_at')->current();
            
            $table->index('campaign_id');
            $table->index('recipient_id');
            $table->index('event_type');
            $table->index('created_at');
        }) |> Schema::execute(...);
    }
    
    public function down(): void
    {
        Schema::dropIfExists('logs') |> Schema::execute(...);
    }
};
