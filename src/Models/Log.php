<?php

declare(strict_types=1);

namespace App\Models;

use Larafony\Framework\Database\ORM\Attributes\BelongsTo;
use Larafony\Framework\Database\ORM\Model;

class Log extends Model
{
    public string $table { get => 'logs'; }

    public array $fillable = [
        'campaign_id', 'recipient_id', 'type', 'data', 'ip_address', 'user_agent'
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

    public ?string $type {
        get => $this->type ?? null;
        set {
            $this->type = $value;
            $this->markPropertyAsChanged('type');
        }
    }

    public ?string $data {
        get => $this->data ?? null;
        set {
            $this->data = $value;
            $this->markPropertyAsChanged('data');
        }
    }

    public ?string $ip_address {
        get => $this->ip_address ?? null;
        set {
            $this->ip_address = $value;
            $this->markPropertyAsChanged('ip_address');
        }
    }

    public ?string $user_agent {
        get => $this->user_agent ?? null;
        set {
            $this->user_agent = $value;
            $this->markPropertyAsChanged('user_agent');
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
