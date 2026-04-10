<?php

declare(strict_types=1);

namespace App\DTOs;

use Larafony\Framework\Validation\Attributes\Email;
use Larafony\Framework\Validation\Attributes\IsValidated;
use Larafony\Framework\Validation\Attributes\MinLength;
use Larafony\Framework\Validation\FormRequest;

class CreateSmtpSettingDto extends FormRequest
{
    #[IsValidated]
    #[MinLength(3)]
    public protected(set) string $host;

    #[IsValidated]
    public protected(set) string|int $port;

    #[IsValidated]
    public protected(set) string $encryption;

    #[IsValidated]
    public protected(set) string $username;

    #[IsValidated]
    #[MinLength(6)]
    public protected(set) string $password;

    #[IsValidated]
    #[Email]
    public protected(set) string $from_email;

    #[IsValidated]
    public protected(set) string $from_name;

    public protected(set) bool $is_active = false;
}
