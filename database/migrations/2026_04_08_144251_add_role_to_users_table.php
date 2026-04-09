<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use Larafony\Framework\Database\Base\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        $this->schema->table('users', function ($table) {
            $table->enum('role', ['Superadmin', 'Admin', 'Agent'])->default('Agent');
        });
    }

    /**
     * Rollback the migration.
     */
    public function down(): void
    {
        $this->schema->table('users', function ($table) {
            $table->dropColumn('role');
        });
    }
};