<?php

declare(strict_types=1);

namespace App\Models;

use Larafony\Framework\Database\ORM\Attributes\BelongsTo;
use Larafony\Framework\Database\ORM\Model;

class QueueJob extends Model
{
    public string $table { get => 'queue_jobs'; }

    public array $fillable = [
        'campaign_id', 'recipient_id', 'status', 'attempts', 
        'error_message', 'scheduled_at', 'processed_at'
    ];

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

    public ?string $error_message {
        get => $this->error_message ?? null;
        set {
            $this->error_message = $value;
            $this->markPropertyAsChanged('error_message');
        }
    }

    public ?string $scheduled_at {
        get => $this->scheduled_at ?? null;
        set {
            $this->scheduled_at = $value;
            $this->markPropertyAsChanged('scheduled_at');
        }
    }

    public ?string $processed_at {
        get => $this->processed_at ?? null;
        set {
            $this->processed_at = $value;
            $this->markPropertyAsChanged('processed_at');
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
