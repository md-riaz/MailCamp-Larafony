<?php

declare(strict_types=1);

use Larafony\Framework\Database\Base\Migrations\Migration;
use Larafony\Framework\Database\Schema;

/**
 * Migration: Create subscriptions table
 * Manages email subscription preferences
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function ($table) {
            $table->id();
            $table->bigInteger('organization_id')->unsigned(true);
            $table->string('email')->nullable(false);
            $table->string('name')->nullable(true);
            $table->enum('status', ['subscribed', 'unsubscribed', 'bounced']);
            $table->timestamp('subscription_date')->current();
            $table->timestamp('unsubscribe_date')->nullable(true);
            $table->string('unsubscribe_token', 64)->nullable(true);
            $table->timestamps();
            
            $table->unique(['organization_id', 'email']);
            $table->unique('unsubscribe_token');
            $table->index('organization_id');
            $table->index('email');
            $table->index('status');
        }) |> Schema::execute(...);
    }
    
    public function down(): void
    {
        Schema::dropIfExists('subscriptions') |> Schema::execute(...);
    }
};
