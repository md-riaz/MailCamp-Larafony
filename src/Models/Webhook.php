<?php

declare(strict_types=1);

namespace App\Models;

use Larafony\Framework\Database\ORM\Model;

class Webhook extends Model
{
    public string $table { get => 'webhooks'; }

    public array $fillable = [
        'campaign_id', 'message_id', 'subscriber_id', 'provider', 'event_type',
        'provider_message_id', 'signature', 'idempotency_key', 'processing_status',
        'payload', 'headers', 'processed_at',
    ];

    public ?int $campaign_id { get => $this->campaign_id ?? null; set { $this->campaign_id = $value; $this->markPropertyAsChanged('campaign_id'); } }
    public ?int $message_id { get => $this->message_id ?? null; set { $this->message_id = $value; $this->markPropertyAsChanged('message_id'); } }
    public ?int $subscriber_id { get => $this->subscriber_id ?? null; set { $this->subscriber_id = $value; $this->markPropertyAsChanged('subscriber_id'); } }
    public ?string $provider { get => $this->provider ?? 'smtp'; set { $this->provider = $value; $this->markPropertyAsChanged('provider'); } }
    public ?string $event_type { get => $this->event_type ?? null; set { $this->event_type = $value; $this->markPropertyAsChanged('event_type'); } }
    public ?string $provider_message_id { get => $this->provider_message_id ?? null; set { $this->provider_message_id = $value; $this->markPropertyAsChanged('provider_message_id'); } }
    public ?string $signature { get => $this->signature ?? null; set { $this->signature = $value; $this->markPropertyAsChanged('signature'); } }
    public ?string $idempotency_key { get => $this->idempotency_key ?? null; set { $this->idempotency_key = $value; $this->markPropertyAsChanged('idempotency_key'); } }
    public ?string $processing_status { get => $this->processing_status ?? 'pending'; set { $this->processing_status = $value; $this->markPropertyAsChanged('processing_status'); } }
    public ?string $payload { get => $this->payload ?? null; set { $this->payload = $value; $this->markPropertyAsChanged('payload'); } }
    public ?string $headers { get => $this->headers ?? null; set { $this->headers = $value; $this->markPropertyAsChanged('headers'); } }
    public ?string $processed_at { get => $this->processed_at ?? null; set { $this->processed_at = $value; $this->markPropertyAsChanged('processed_at'); } }
}
