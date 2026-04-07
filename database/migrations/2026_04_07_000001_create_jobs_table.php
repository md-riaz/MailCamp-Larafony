<?php

declare(strict_types=1);

use Larafony\Framework\Database\Base\Migrations\Migration;
use Larafony\Framework\Database\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function ($table) {
            $table->string('id', 255)->nullable(false);
            $table->text('payload');
            $table->text('queue')->nullable(true);
            $table->integer('attempts')->nullable(false)->default(0);
            $table->timestamp('reserved_at')->nullable(true);
            $table->timestamp('available_at')->nullable(false);
            $table->timestamp('created_at')->nullable(false);

            $table->primary('id');
            $table->index('available_at');
            $table->index(['queue', 'reserved_at']);
        }) |> Schema::execute(...);
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs') |> Schema::execute(...);
    }
};
