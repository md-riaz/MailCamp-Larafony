<?php

declare(strict_types=1);

use Larafony\Framework\Database\Base\Migrations\Migration;
use Larafony\Framework\Database\Schema;

/**
 * Migration: Create queue_jobs table
 * Database-backed queue for batch email sending with throttling
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_jobs', function ($table) {
            $table->id();
            $table->bigInteger('organization_id')->unsigned(true);
            $table->bigInteger('campaign_id')->unsigned(true);
            $table->bigInteger('recipient_id')->unsigned(true);
            $table->text('payload')->nullable(false);
            $table->integer('attempts')->default(0);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed']);
            $table->timestamp('available_at')->nullable(false);
            $table->timestamp('reserved_at')->nullable(true);
            $table->timestamp('completed_at')->nullable(true);
            $table->timestamp('created_at')->current();
            
            $table->index('status');
            $table->index('available_at');
            $table->index('campaign_id');
        }) |> Schema::execute(...);
    }
    
    public function down(): void
    {
        Schema::dropIfExists('queue_jobs') |> Schema::execute(...);
    }
};
