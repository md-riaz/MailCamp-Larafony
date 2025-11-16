<?php

declare(strict_types=1);

use Larafony\Framework\Database\Base\Migrations\Migration;
use Larafony\Framework\Database\Schema;

/**
 * Migration: Create templates table
 * Stores HTML email templates with variable placeholders
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('templates', function ($table) {
            $table->id();
            $table->bigInteger('organization_id')->unsigned(true);
            $table->string('name')->nullable(false);
            $table->string('subject', 500)->nullable(false);
            $table->text('html_content')->nullable(false);
            $table->text('variables')->nullable(true);
            $table->integer('is_active', 'TINYINT')->length(1)->default(1);
            $table->timestamps();
            
            $table->index('organization_id');
            $table->index('is_active');
        }) |> Schema::execute(...);
    }
    
    public function down(): void
    {
        Schema::dropIfExists('templates') |> Schema::execute(...);
    }
};
