<?php

declare(strict_types=1);

namespace App\Models;

use Larafony\Framework\Database\ORM\Attributes\BelongsTo;
use Larafony\Framework\Database\ORM\Attributes\HasMany;
use Larafony\Framework\Database\ORM\Model;

class Recipient extends Model
{
    public string $table { get => 'recipients'; }

    public array $fillable = [
        'organization_id', 'campaign_id', 'email', 'name', 
        'custom_data', 'status', 'sent_at', 'opened_at', 'clicked_at'
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

    public ?string $custom_data {
        get => $this->custom_data ?? null;
        set {
            $this->custom_data = $value;
            $this->markPropertyAsChanged('custom_data');
        }
    }

    public ?string $status {
        get => $this->status ?? 'pending';
        set {
            $this->status = $value;
            $this->markPropertyAsChanged('status');
        }
    }

    public ?string $sent_at {
        get => $this->sent_at ?? null;
        set {
            $this->sent_at = $value;
            $this->markPropertyAsChanged('sent_at');
        }
    }

    public ?string $opened_at {
        get => $this->opened_at ?? null;
        set {
            $this->opened_at = $value;
            $this->markPropertyAsChanged('opened_at');
        }
    }

    public ?string $clicked_at {
        get => $this->clicked_at ?? null;
        set {
            $this->clicked_at = $value;
            $this->markPropertyAsChanged('clicked_at');
        }
    }

    #[BelongsTo(
        related: Organization::class,
        foreign_key: 'organization_id',
        local_key: 'id'
    )]
    public ?Organization $organization { get => $this->relations->getRelation('organization'); }

    #[BelongsTo(
        related: Campaign::class,
        foreign_key: 'campaign_id',
        local_key: 'id'
    )]
    public ?Campaign $campaign { get => $this->relations->getRelation('campaign'); }

    #[HasMany(
        related: Log::class,
        foreign_key: 'recipient_id',
        local_key: 'id'
    )]
    public array $logs { get => $this->relations->getRelation('logs'); }

    public function getCustomData(): array
    {
        return json_decode($this->custom_data, true) ?: [];
    }

    public function setCustomData(array $data): void
    {
        $this->custom_data = json_encode($data);
    }

    public function markAsSent(): void
    {
        $this->status = 'sent';
        $this->sent_at = date('Y-m-d H:i:s');
    }

    public function markAsOpened(): void
    {
        if (!$this->opened_at) {
            $this->opened_at = date('Y-m-d H:i:s');
        }
    }

    public function markAsClicked(): void
    {
        if (!$this->clicked_at) {
            $this->clicked_at = date('Y-m-d H:i:s');
        }
    }
}
