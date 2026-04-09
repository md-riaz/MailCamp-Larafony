<?php

declare(strict_types=1);

namespace App\Console\Commands;

final class CreateAudienceCommand
{
    public function handle(string $name, ?string $description = null): void
    {
        $service = new \App\Services\AudienceService();
        $audience = $service->createAudience(name: $name, description: $description);

        echo sprintf("Audience '%s' created successfully with ID %d\n", $audience->name, $audience->id);
    }
}
