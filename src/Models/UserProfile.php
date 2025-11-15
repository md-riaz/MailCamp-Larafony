<?php

declare(strict_types=1);

namespace App\Models;

use Larafony\Framework\Database\ORM\Attributes\BelongsTo;
use Larafony\Framework\Database\ORM\Model;

class UserProfile extends Model
{
    public string $table { get => 'user_profiles'; }

    public array $fillable = ['user_id', 'organization_id', 'name'];

    public ?int $user_id {
        get => $this->user_id ?? null;
        set {
            $this->user_id = $value;
            $this->markPropertyAsChanged('user_id');
        }
    }

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

    // BelongsTo relationship: Profile belongs to User
    #[BelongsTo(
        related: User::class,
        foreign_key: 'user_id',
        local_key: 'id'
    )]
    public ?User $user { get => $this->relations->getRelation('user'); }

    // BelongsTo relationship: Profile belongs to Organization
    #[BelongsTo(
        related: Organization::class,
        foreign_key: 'organization_id',
        local_key: 'id'
    )]
    public ?Organization $organization { get => $this->relations->getRelation('organization'); }
}
