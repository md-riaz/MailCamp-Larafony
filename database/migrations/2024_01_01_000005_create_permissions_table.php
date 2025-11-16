<?php

declare(strict_types=1);

use Larafony\Framework\Database\Base\Migrations\Migration;
use Larafony\Framework\Database\Schema;

/**
 * Migration: Create permissions table
 * Part of framework's RBAC system
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function ($table) {
            $table->id();
            $table->string('name', 100)->nullable(false);
            $table->string('description')->nullable(true);
            $table->timestamps();
            
            $table->unique('name');
        }) |> Schema::execute(...);
    }
    
    public function down(): void
    {
        Schema::dropIfExists('permissions') |> Schema::execute(...);
    }
};
