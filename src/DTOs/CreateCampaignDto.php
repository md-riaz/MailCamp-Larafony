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
    #[MinLength(3)]
    public protected(set) string $subject;

    #[IsValidated]
    #[MinLength(10)]
    public protected(set) string $html_content;

    public protected(set) bool $save_as_template = false;

    #[IsValidated]
    public protected(set) int $smtp_setting_id;
}
