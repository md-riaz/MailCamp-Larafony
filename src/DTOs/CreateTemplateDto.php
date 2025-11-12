<?php

declare(strict_types=1);

namespace App\DTOs;

use Larafony\Framework\Validation\Attributes\IsValidated;

class CreateTemplateDto
{
    #[IsValidated]
    public protected(set) string $name {
        get => $this->name;
        set => $this->name = $value;
    }

    #[IsValidated]
    public protected(set) string $subject {
        get => $this->subject;
        set => $this->subject = $value;
    }

    #[IsValidated]
    public protected(set) string $html_content {
        get => $this->html_content;
        set => $this->html_content = $value;
    }
}
