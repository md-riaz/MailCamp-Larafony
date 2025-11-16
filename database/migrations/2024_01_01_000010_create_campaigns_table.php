<?php

declare(strict_types=1);

use Larafony\Framework\Database\Base\Migrations\Migration;
use Larafony\Framework\Database\Schema;

/**
 * Migration: Create campaigns table
 * Manages email campaigns
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function ($table) {
            $table->id();
            $table->bigInteger('organization_id')->unsigned(true);
            $table->bigInteger('template_id')->unsigned(true);
            $table->string('name');
            $table->enum('status', ['draft', 'scheduled', 'sending', 'sent', 'paused', 'cancelled']);
            $table->timestamp('scheduled_at')->nullable(true);
            $table->timestamp('started_at')->nullable(true);
            $table->timestamp('completed_at')->nullable(true);
            $table->integer('total_recipients')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->bigInteger('created_by')->unsigned(true);
            $table->timestamps();
            
            $table->index('organization_id');
            $table->index('status');
            $table->index('scheduled_at');
        }) |> Schema::execute(...);
    }
    
    public function down(): void
    {
        Schema::dropIfExists('campaigns') |> Schema::execute(...);
    }
};
