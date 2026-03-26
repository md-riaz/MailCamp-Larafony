<?php

declare(strict_types=1);

use Larafony\Framework\Database\Base\Migrations\Migration;
use Larafony\Framework\Database\Schema;

/**
 * Migration: Add smtp_setting_id to campaigns
 * Allows campaigns to target a specific SMTP connection
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function ($table) {
            $table->bigInteger('smtp_setting_id')->unsigned(true)->nullable(true)->after('template_id');
            $table->index('smtp_setting_id');
        }) |> Schema::execute(...);
    }

    public function down(): void
    {
        Schema::table('campaigns', function ($table) {
            $table->dropColumn('smtp_setting_id');
        }) |> Schema::execute(...);
    }
};
