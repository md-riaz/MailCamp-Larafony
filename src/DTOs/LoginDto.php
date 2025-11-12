<?php

declare(strict_types=1);

namespace App\DTOs;

use Larafony\Framework\Validation\Attributes\IsValidated;

class LoginDto
{
    #[IsValidated]
    public protected(set) string $email {
        get => $this->email;
        set => $this->email = filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : throw new \InvalidArgumentException('Invalid email');
    }

    #[IsValidated]
    public protected(set) string $password {
        get => $this->password;
        set => $this->password = $value;
    }
}
