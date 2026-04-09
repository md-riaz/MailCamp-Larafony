<?php

declare(strict_types=1);

use Larafony\Framework\Database\Base\Migrations\Migration;
use Larafony\Framework\Database\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smtp_unsubscriptions', function ($table) {
            $table->id();

            $table->bigInteger('subscription_id')->unsigned(true)->nullable(true);
            $table->bigInteger('smtp_setting_id')->unsigned(true);
            $table->string('email')->nullable(false);
            $table->timestamp('unsubscribed_at')->current();
            $table->timestamps();

            $table->unique(['smtp_setting_id', 'email']);

            $table->index('subscription_id');
            $table->index('smtp_setting_id');
            $table->index('email');
        }) |> Schema::execute(...);
    }

    public function down(): void
    {
        Schema::dropIfExists('smtp_unsubscriptions') |> Schema::execute(...);
    }
};
