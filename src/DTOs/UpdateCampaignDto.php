<?php

declare(strict_types=1);

namespace App\DTOs;

use Larafony\Framework\Validation\Attributes\IsValidated;
use Larafony\Framework\Validation\Attributes\MinLength;
use Larafony\Framework\Validation\FormRequest;

class UpdateCampaignDto extends FormRequest
{
    #[IsValidated]
    #[MinLength(3)]
    public protected(set) string $name;

    #[IsValidated]
    #[MinLength(3)]
    public protected(set) string $subject;

    #[IsValidated]
    #[MinLength(10)]
    public protected(set) string $html_content;

    #[IsValidated]
    public protected(set) string $smtp_setting_id;

    public protected(set) ?string $scheduled_at = null;
}
