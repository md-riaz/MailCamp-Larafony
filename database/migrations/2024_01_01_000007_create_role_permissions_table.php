<?php

declare(strict_types=1);

use Larafony\Framework\Database\Base\Migrations\Migration;
use Larafony\Framework\Database\Schema;

/**
 * Migration: Create role_permissions table
 * Links roles to permissions (many-to-many)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_permissions', function ($table) {
            $table->id();
            $table->bigInteger('role_id')->unsigned(true);
            $table->bigInteger('permission_id')->unsigned(true);
            $table->timestamp('created_at')->current();
            
            $table->index('role_id');
            $table->index('permission_id');
            $table->unique(['role_id', 'permission_id']);
        }) |> Schema::execute(...);
    }
    
    public function down(): void
    {
        Schema::dropIfExists('role_permissions') |> Schema::execute(...);
    }
};
