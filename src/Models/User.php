<?php

declare(strict_types=1);

namespace App\Models;

use Larafony\Framework\Database\ORM\Attributes\BelongsTo;
use Larafony\Framework\Database\ORM\Attributes\HasMany;
use Larafony\Framework\Database\ORM\Entities\User as Authenticable;

class User extends Authenticable
{
    public string $table { get => 'users'; }

    public array $fillable = ['organization_id', 'name', 'email', 'password', 'role', 'is_active'];
    public array $hidden = ['password'];

    public ?int $organization_id {
        get => $this->organization_id ?? null;
        set {
            $this->organization_id = $value;
            $this->markPropertyAsChanged('organization_id');
        }
    }

    public ?string $name {
        get => $this->name ?? null;
        set {
            $this->name = $value;
            $this->markPropertyAsChanged('name');
        }
    }

    public ?string $email {
        get => $this->email ?? null;
        set {
            $this->email = $value;
            $this->markPropertyAsChanged('email');
        }
    }

    public ?string $password {
        get => $this->password ?? null;
        set {
            $this->password = $value;
            $this->markPropertyAsChanged('password');
        }
    }

    public ?string $role {
        get => $this->role ?? null;
        set {
            $this->role = $value;
            $this->markPropertyAsChanged('role');
        }
    }

    public ?bool $is_active {
        get => $this->is_active ?? true;
        set {
            $this->is_active = $value;
            $this->markPropertyAsChanged('is_active');
        }
    }

    // BelongsTo relationship: User belongs to Organization
    #[BelongsTo(
        related: Organization::class,
        foreign_key: 'organization_id',
        local_key: 'id'
    )]
    public ?Organization $organization { get => $this->relations->getRelation('organization'); }

    // HasMany relationship: User has many Campaigns
    #[HasMany(
        related: Campaign::class,
        foreign_key: 'created_by',
        local_key: 'id'
    )]
    public array $campaigns { get => $this->relations->getRelation('campaigns'); }

    /**
     * Check if user has admin role
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user has manager or admin role
     */
    public function isManager(): bool
    {
        return in_array($this->role, ['admin', 'manager']);
    }

    /**
     * Hash password before saving
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Verify password
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }
}
