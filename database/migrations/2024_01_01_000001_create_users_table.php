<?php

declare(strict_types=1);

use Larafony\Framework\Database\Base\Migrations\Migration;
use Larafony\Framework\Database\Schema;

/**
 * Migration: Create users table
 * Base authentication table - follows framework standards
 * Only contains authentication-related fields
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function ($table) {
            $table->id();
            $table->string('email', 100)->nullable(false);
            $table->string('username', 50)->nullable(true);
            $table->string('password')->nullable(false);
            $table->string('remember_token', 100)->nullable(true);
            $table->string('password_reset_token', 100)->nullable(true);
            $table->timestamp('password_reset_expires')->nullable(true);
            $table->timestamp('email_verified_at')->nullable(true);
            $table->integer('is_active', 'TINYINT')->length(1)->default(1);
            $table->timestamp('last_login_at')->nullable(true);
            $table->timestamps();
            
            $table->unique('email');
            $table->unique('username');
            $table->index('is_active');
        }) |> Schema::execute(...);
    }
    
    public function down(): void
    {
        Schema::dropIfExists('users') |> Schema::execute(...);
    }
};
