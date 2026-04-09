<?php

declare(strict_types=1);

namespace App\Models;

use Larafony\Framework\Database\ORM\Model;

class RecipientSuppression extends Model
{
    public string $table { get => 'recipient_suppressions'; }

    public array $fillable = [
        'organization_id', 'subscription_id', 'email', 'reason', 'source', 'created_at',
    ];

    public ?int $organization_id { get => $this->organization_id ?? null; set { $this->organization_id = $value; $this->markPropertyAsChanged('organization_id'); } }
    public ?int $subscription_id { get => $this->subscription_id ?? null; set { $this->subscription_id = $value; $this->markPropertyAsChanged('subscription_id'); } }
    public ?string $email { get => $this->email ?? null; set { $this->email = $value; $this->markPropertyAsChanged('email'); } }
    public ?string $reason { get => $this->reason ?? null; set { $this->reason = $value; $this->markPropertyAsChanged('reason'); } }
    public ?string $source { get => $this->source ?? null; set { $this->source = $value; $this->markPropertyAsChanged('source'); } }
    public ?string $created_at { get => $this->created_at ?? null; set { $this->created_at = $value; $this->markPropertyAsChanged('created_at'); } }
}
