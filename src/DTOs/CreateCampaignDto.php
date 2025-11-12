<?php

declare(strict_types=1);

namespace App\DTOs;

use Larafony\Framework\Validation\Attributes\IsValidated;

class CreateCampaignDto
{
    #[IsValidated]
    public protected(set) string $name {
        get => $this->name;
        set => $this->name = $value;
    }

    #[IsValidated]
    public protected(set) int $template_id {
        get => $this->template_id;
        set => $this->template_id = $value;
    }
}
