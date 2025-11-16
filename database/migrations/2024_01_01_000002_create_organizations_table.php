<?php

declare(strict_types=1);

use Larafony\Framework\Database\Base\Migrations\Migration;
use Larafony\Framework\Database\Schema;

/**
 * Migration: Create organizations table
 * Handles multi-tenancy for different organizations
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function ($table) {
            $table->id();
            $table->string('name')->nullable(false);
            $table->string('slug')->nullable(false);
            $table->string('domain')->nullable(true);
            $table->integer('is_active', 'TINYINT')->length(1)->default(1);
            $table->timestamps();
            
            $table->unique('slug');
            $table->index('is_active');
        }) |> Schema::execute(...);
    }
    
    public function down(): void
    {
        Schema::dropIfExists('organizations') |> Schema::execute(...);
    }
};
