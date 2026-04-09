<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Audience;

final class AudienceService
{
    public function createAudience(string $name, ?string $description = null): Audience
    {
        $audience = new Audience();
        $audience->name = $name;
        $audience->description = $description;
        $audience->save();

        return $audience;
    }

    public function listAudiences(): array
    {
        return Audience::query()->orderBy('name')->get()->toArray();
    }

    public function addRecipientToAudience(int $audienceId, int $recipientId): void
    {
        // Placeholder: Logic for linking mailing recipients to audiences
    }
}
