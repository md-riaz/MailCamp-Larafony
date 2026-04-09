<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use Larafony\Framework\Database\Base\Migrations\Migration;

return new class extends Migration
{
    /**
     * run migration.
     */
    public function up(): void
    {
        Larafony\Framework\Database\Schema::execute("CREATE TABLE audiences (id BIGINT AUTO_INCREMENT, name VARCHAR(255) NOT NULL, description TEXT, created_at DATETIME NULL, updated_at DATETIME NULL, PRIMARY KEY (id)) ENGINE=InnoDB;");
            $table->id();
            $table->string('name')->nullable(false);
            $table->text('description')->nullable(true);
            $table->timestamps();
        });
    }

    /**
     * rollback migration.
     */
    public function down(): void
    {
        Larafony\Framework\Database\Schema::dropIfExists('audiences');
    }
};