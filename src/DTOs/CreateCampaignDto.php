<?php

declare(strict_types=1);

namespace App\DTOs;

use Larafony\Framework\Validation\Attributes\IsValidated;
use Larafony\Framework\Validation\Attributes\MinLength;
use Larafony\Framework\Validation\FormRequest;

class CreateCampaignDto extends FormRequest
{
    #[IsValidated]
    #[MinLength(3)]
    public protected(set) string $name;

    #[IsValidated]
    public protected(set) int $template_id;
}
