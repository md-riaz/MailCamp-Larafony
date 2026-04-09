<?php

declare(strict_types=1);

use Larafony\Framework\Database\Base\Migrations\Migration;
use Larafony\Framework\Database\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipient_suppressions', function ($table) {
            $table->id();
            $table->bigInteger('organization_id')->unsigned(true);
            $table->bigInteger('subscription_id')->unsigned(true)->nullable(true);
            $table->string('email')->nullable(false);
            $table->string('reason')->nullable(false);
            $table->string('source')->nullable(true);
            $table->timestamp('created_at')->current();

            $table->unique(['organization_id', 'email', 'reason']);
            $table->index('organization_id');
            $table->index('subscription_id');
            $table->index('email');
            $table->index('reason');
        }) |> Schema::execute(...);
    }

    public function down(): void
    {
        Schema::dropIfExists('recipient_suppressions') |> Schema::execute(...);
    }
};
