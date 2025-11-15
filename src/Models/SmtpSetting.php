<?php

declare(strict_types=1);

namespace App\Models;

use Larafony\Framework\Database\ORM\Attributes\BelongsTo;
use Larafony\Framework\Database\ORM\Model;

class SmtpSetting extends Model
{
    public string $table { get => 'smtp_settings'; }

    public array $fillable = [
        'organization_id', 'host', 'port', 'encryption', 
        'username', 'password', 'from_email', 'from_name', 'is_active'
    ];
    public array $hidden = ['password'];

    public ?int $organization_id {
        get => $this->organization_id ?? null;
        set {
            $this->organization_id = $value;
            $this->markPropertyAsChanged('organization_id');
        }
    }

    public ?string $host {
        get => $this->host ?? null;
        set {
            $this->host = $value;
            $this->markPropertyAsChanged('host');
        }
    }

    public ?int $port {
        get => $this->port ?? null;
        set {
            $this->port = $value;
            $this->markPropertyAsChanged('port');
        }
    }

    public ?string $encryption {
        get => $this->encryption ?? null;
        set {
            $this->encryption = $value;
            $this->markPropertyAsChanged('encryption');
        }
    }

    public ?string $username {
        get => $this->username ?? null;
        set {
            $this->username = $value;
            $this->markPropertyAsChanged('username');
        }
    }

    public ?string $password {
        get => $this->password ?? null;
        set {
            $this->password = $value;
            $this->markPropertyAsChanged('password');
        }
    }

    public ?string $from_email {
        get => $this->from_email ?? null;
        set {
            $this->from_email = $value;
            $this->markPropertyAsChanged('from_email');
        }
    }

    public ?string $from_name {
        get => $this->from_name ?? null;
        set {
            $this->from_name = $value;
            $this->markPropertyAsChanged('from_name');
        }
    }

    public int $is_active {
        get => $this->is_active ?? 1;
        set {
            $this->is_active = $value;
            $this->markPropertyAsChanged('is_active');
        }
    }

    #[BelongsTo(
        related: Organization::class,
        foreign_key: 'organization_id',
        local_key: 'id'
    )]
    public ?Organization $organization { get => $this->relations->getRelation('organization'); }

    public static function encryptPassword(string $password): string
    {
        return base64_encode($password);
    }

    public function decryptPassword(): string
    {
        return base64_decode($this->password);
    }

    public function validate(): bool
    {
        return !empty($this->host) && 
               !empty($this->port) && 
               !empty($this->username) && 
               !empty($this->password) &&
               !empty($this->from_email);
    }
}
