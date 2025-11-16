<?php

declare(strict_types=1);

use Larafony\Framework\Database\Base\Migrations\Migration;
use Larafony\Framework\Database\Schema;

/**
 * Migration: Create user_profiles table
 * Stores application-specific user data (organization, name, etc.)
 * Separated from auth table to follow framework architecture
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function ($table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned(true);
            $table->bigInteger('organization_id')->unsigned(true);
            $table->string('name')->nullable(false);
            $table->timestamps();
            
            $table->unique('user_id');
            $table->index('organization_id');
        }) |> Schema::execute(...);
    }
    
    public function down(): void
    {
        Schema::dropIfExists('user_profiles') |> Schema::execute(...);
    }
};
