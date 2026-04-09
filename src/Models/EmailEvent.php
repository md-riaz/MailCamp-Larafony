<?php

declare(strict_types=1);

namespace App\Models;

use Larafony\Framework\Database\ORM\Model;

class EmailEvent extends Model
{
    public string $table { get => 'email_events'; }

    public array $fillable = [
        'message_id', 'campaign_id', 'organization_id', 'subscriber_id', 'recipient_id',
        'event_type', 'provider', 'timestamp', 'provider_message_id', 'ip_address',
        'user_agent', 'metadata',
    ];

    public ?int $message_id { get => $this->message_id ?? null; set { $this->message_id = $value; $this->markPropertyAsChanged('message_id'); } }
    public ?int $campaign_id { get => $this->campaign_id ?? null; set { $this->campaign_id = $value; $this->markPropertyAsChanged('campaign_id'); } }
    public ?int $organization_id { get => $this->organization_id ?? null; set { $this->organization_id = $value; $this->markPropertyAsChanged('organization_id'); } }
    public ?int $subscriber_id { get => $this->subscriber_id ?? null; set { $this->subscriber_id = $value; $this->markPropertyAsChanged('subscriber_id'); } }
    public ?int $recipient_id { get => $this->recipient_id ?? null; set { $this->recipient_id = $value; $this->markPropertyAsChanged('recipient_id'); } }
    public ?string $event_type { get => $this->event_type ?? null; set { $this->event_type = $value; $this->markPropertyAsChanged('event_type'); } }
    public ?string $provider { get => $this->provider ?? 'smtp'; set { $this->provider = $value; $this->markPropertyAsChanged('provider'); } }
    public ?string $timestamp { get => $this->timestamp ?? null; set { $this->timestamp = $value; $this->markPropertyAsChanged('timestamp'); } }
    public ?string $provider_message_id { get => $this->provider_message_id ?? null; set { $this->provider_message_id = $value; $this->markPropertyAsChanged('provider_message_id'); } }
    public ?string $ip_address { get => $this->ip_address ?? null; set { $this->ip_address = $value; $this->markPropertyAsChanged('ip_address'); } }
    public ?string $user_agent { get => $this->user_agent ?? null; set { $this->user_agent = $value; $this->markPropertyAsChanged('user_agent'); } }
    public ?string $metadata { get => $this->metadata ?? null; set { $this->metadata = $value; $this->markPropertyAsChanged('metadata'); } }

    public function getMetadataArray(): array
    {
        return json_decode((string) $this->metadata, true) ?: [];
    }

    public function setMetadataArray(array $metadata): void
    {
        $this->metadata = json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
