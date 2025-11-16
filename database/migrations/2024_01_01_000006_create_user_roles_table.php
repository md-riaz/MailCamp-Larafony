<?php

declare(strict_types=1);

use Larafony\Framework\Database\Base\Migrations\Migration;
use Larafony\Framework\Database\Schema;

/**
 * Migration: Create user_roles table
 * Links users to roles (many-to-many)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_roles', function ($table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned(true);
            $table->bigInteger('role_id')->unsigned(true);
            $table->timestamp('created_at')->current();
            
            $table->index('user_id');
            $table->index('role_id');
            $table->unique(['user_id', 'role_id']);
        }) |> Schema::execute(...);
    }
    
    public function down(): void
    {
        Schema::dropIfExists('user_roles') |> Schema::execute(...);
    }
};
