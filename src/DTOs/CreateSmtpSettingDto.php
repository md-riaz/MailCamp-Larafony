<?php

declare(strict_types=1);

namespace App\DTOs;

use Larafony\Framework\Validation\Attributes\IsValidated;

class CreateSmtpSettingDto
{
    #[IsValidated]
    public protected(set) string $host {
        get => $this->host;
        set => $this->host = $value;
    }

    #[IsValidated]
    public protected(set) int $port {
        get => $this->port;
        set => $this->port = $value;
    }

    #[IsValidated]
    public protected(set) string $encryption {
        get => $this->encryption;
        set => $this->encryption = $value;
    }

    #[IsValidated]
    public protected(set) string $username {
        get => $this->username;
        set => $this->username = $value;
    }

    #[IsValidated]
    public protected(set) string $password {
        get => $this->password;
        set => $this->password = $value;
    }

    #[IsValidated]
    public protected(set) string $from_email {
        get => $this->from_email;
        set => $this->from_email = filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : throw new \InvalidArgumentException('Invalid email');
    }

    #[IsValidated]
    public protected(set) string $from_name {
        get => $this->from_name;
        set => $this->from_name = $value;
    }
}
