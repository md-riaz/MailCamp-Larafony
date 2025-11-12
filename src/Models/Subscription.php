<?php

declare(strict_types=1);

namespace App\Models;

use Larafony\Framework\Database\ORM\Attributes\BelongsTo;
use Larafony\Framework\Database\ORM\Model;

class Subscription extends Model
{
    public string $table { get => 'subscriptions'; }

    public array $fillable = [
        'organization_id', 'email', 'name', 'status', 'token', 
        'subscribed_at', 'unsubscribed_at'
    ];

    public ?int $organization_id {
        get => $this->organization_id ?? null;
        set {
            $this->organization_id = $value;
            $this->markPropertyAsChanged('organization_id');
        }
    }

    public ?string $email {
        get => $this->email ?? null;
        set {
            $this->email = $value;
            $this->markPropertyAsChanged('email');
        }
    }

    public ?string $name {
        get => $this->name ?? null;
        set {
            $this->name = $value;
            $this->markPropertyAsChanged('name');
        }
    }

    public ?string $status {
        get => $this->status ?? 'subscribed';
        set {
            $this->status = $value;
            $this->markPropertyAsChanged('status');
        }
    }

    public ?string $token {
        get => $this->token ?? null;
        set {
            $this->token = $value;
            $this->markPropertyAsChanged('token');
        }
    }

    public ?string $subscribed_at {
        get => $this->subscribed_at ?? null;
        set {
            $this->subscribed_at = $value;
            $this->markPropertyAsChanged('subscribed_at');
        }
    }

    public ?string $unsubscribed_at {
        get => $this->unsubscribed_at ?? null;
        set {
            $this->unsubscribed_at = $value;
            $this->markPropertyAsChanged('unsubscribed_at');
        }
    }

    #[BelongsTo(
        related: Organization::class,
        foreign_key: 'organization_id',
        local_key: 'id'
    )]
    public ?Organization $organization { get => $this->relations->getRelation('organization'); }

    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function unsubscribe(): void
    {
        $this->status = 'unsubscribed';
        $this->unsubscribed_at = date('Y-m-d H:i:s');
        $this->save();
    }
}
