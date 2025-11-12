<?php

declare(strict_types=1);

namespace App\DTOs;

use Larafony\Framework\Validation\Attributes\IsValidated;

class RegisterDto
{
    #[IsValidated]
    public protected(set) string $name {
        get => $this->name;
        set => $this->name = $value;
    }

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

    #[IsValidated]
    public protected(set) string $organization_name {
        get => $this->organization_name;
        set => $this->organization_name = $value;
    }
}
