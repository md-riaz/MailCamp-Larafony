<?php

declare(strict_types=1);

use Larafony\Framework\Database\Base\Migrations\Migration;
use Larafony\Framework\Database\Schema;

/**
 * Migration: Create smtp_settings table
 * Stores SMTP configuration per organization
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smtp_settings', function ($table) {
            $table->id();
            $table->bigInteger('organization_id')->unsigned(true);
            $table->string('host')->nullable(false);
            $table->integer('port')->default(587);
            $table->enum('encryption', ['none', 'ssl', 'tls']);
            $table->string('username')->nullable(false);
            $table->string('password')->nullable(false);
            $table->string('from_email')->nullable(false);
            $table->string('from_name')->nullable(false);
            $table->integer('is_active', 'TINYINT')->length(1)->default(1);
            $table->timestamps();
            
            $table->index('organization_id');
            $table->index('is_active');
        }) |> Schema::execute(...);
    }
    
    public function down(): void
    {
        Schema::dropIfExists('smtp_settings') |> Schema::execute(...);
    }
};
