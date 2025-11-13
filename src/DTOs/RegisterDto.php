<?php

declare(strict_types=1);

namespace App\DTOs;

use Larafony\Framework\Validation\Attributes\Email;
use Larafony\Framework\Validation\Attributes\IsValidated;
use Larafony\Framework\Validation\Attributes\MinLength;
use Larafony\Framework\Validation\FormRequest;

class RegisterDto extends FormRequest
{
    #[IsValidated]
    public protected(set) string $name;

    #[IsValidated]
    #[Email]
    public protected(set) string $email;

    #[IsValidated]
    #[MinLength(6)]
    public protected(set) string $password;

    #[IsValidated]
    public protected(set) string $organization_name;
}
