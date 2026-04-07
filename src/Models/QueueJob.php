<?php

declare(strict_types=1);

namespace App\Models;

use Larafony\Framework\Database\ORM\Attributes\BelongsTo;
use Larafony\Framework\Database\ORM\Model;

class QueueJob extends Model
{
    public string $table { get => 'queue_jobs'; }

    public array $fillable = [
        'organization_id', 'campaign_id', 'recipient_id', 'payload', 'status',
        'attempts', 'available_at', 'reserved_at', 'completed_at'
    ];

    public ?int $organization_id {
        get => $this->organization_id ?? null;
        set {
            $this->organization_id = $value;
            $this->markPropertyAsChanged('organization_id');
        }
    }

    public ?int $campaign_id {
        get => $this->campaign_id ?? null;
        set {
            $this->campaign_id = $value;
            $this->markPropertyAsChanged('campaign_id');
        }
    }

    public ?int $recipient_id {
        get => $this->recipient_id ?? null;
        set {
            $this->recipient_id = $value;
            $this->markPropertyAsChanged('recipient_id');
        }
    }

    public ?string $payload {
        get => $this->payload ?? null;
        set {
            $this->payload = $value;
            $this->markPropertyAsChanged('payload');
        }
    }

    public ?string $status {
        get => $this->status ?? 'pending';
        set {
            $this->status = $value;
            $this->markPropertyAsChanged('status');
        }
    }

    public ?int $attempts {
        get => $this->attempts ?? 0;
        set {
            $this->attempts = $value;
            $this->markPropertyAsChanged('attempts');
        }
    }

    public ?string $available_at {
        get => $this->available_at ?? null;
        set {
            $this->available_at = $value;
            $this->markPropertyAsChanged('available_at');
        }
    }

    public ?string $reserved_at {
        get => $this->reserved_at ?? null;
        set {
            $this->reserved_at = $value;
            $this->markPropertyAsChanged('reserved_at');
        }
    }

    public ?string $completed_at {
        get => $this->completed_at ?? null;
        set {
            $this->completed_at = $value;
            $this->markPropertyAsChanged('completed_at');
        }
    }

    #[BelongsTo(
        related: Campaign::class,
        foreign_key: 'campaign_id',
        local_key: 'id'
    )]
    public ?Campaign $campaign { get => $this->relations->getRelation('campaign'); }

    #[BelongsTo(
        related: Recipient::class,
        foreign_key: 'recipient_id',
        local_key: 'id'
    )]
    public ?Recipient $recipient { get => $this->relations->getRelation('recipient'); }
}
