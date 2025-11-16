<?php

declare(strict_types=1);

use Larafony\Framework\Database\Base\Migrations\Migration;
use Larafony\Framework\Database\Schema;

/**
 * Migration: Create recipients table
 * Stores recipient lists for campaigns
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipients', function ($table) {
            $table->id();
            $table->bigInteger('organization_id')->unsigned(true);
            $table->bigInteger('campaign_id')->unsigned(true);
            $table->string('email')->nullable(false);
            $table->string('name')->nullable(true);
            $table->text('custom_data')->nullable(true);
            $table->enum('status', ['pending', 'sent', 'failed', 'bounced', 'unsubscribed']);
            $table->timestamp('sent_at')->nullable(true);
            $table->timestamp('opened_at')->nullable(true);
            $table->timestamp('clicked_at')->nullable(true);
            $table->timestamps();
            
            $table->index('campaign_id');
            $table->index('email');
            $table->index('status');
        }) |> Schema::execute(...);
    }
    
    public function down(): void
    {
        Schema::dropIfExists('recipients') |> Schema::execute(...);
    }
};
