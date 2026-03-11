<?php

declare(strict_types=1);

namespace App\Models;

use Larafony\Framework\Database\ORM\Model;

class EmailEvent extends Model
{
    public string $table { get => 'email_events'; }

    public array $fillable = [
        'message_id', 'campaign_id', 'subscriber_id', 'event_type', 'timestamp',
        'provider_message_id', 'ip_address', 'user_agent', 'metadata',
    ];

    public ?int $message_id { get => $this->message_id ?? null; set { $this->message_id = $value; $this->markPropertyAsChanged('message_id'); } }
    public ?int $campaign_id { get => $this->campaign_id ?? null; set { $this->campaign_id = $value; $this->markPropertyAsChanged('campaign_id'); } }
    public ?int $subscriber_id { get => $this->subscriber_id ?? null; set { $this->subscriber_id = $value; $this->markPropertyAsChanged('subscriber_id'); } }
    public ?string $event_type { get => $this->event_type ?? null; set { $this->event_type = $value; $this->markPropertyAsChanged('event_type'); } }
    public ?string $timestamp { get => $this->timestamp ?? null; set { $this->timestamp = $value; $this->markPropertyAsChanged('timestamp'); } }
    public ?string $provider_message_id { get => $this->provider_message_id ?? null; set { $this->provider_message_id = $value; $this->markPropertyAsChanged('provider_message_id'); } }
    public ?string $ip_address { get => $this->ip_address ?? null; set { $this->ip_address = $value; $this->markPropertyAsChanged('ip_address'); } }
    public ?string $user_agent { get => $this->user_agent ?? null; set { $this->user_agent = $value; $this->markPropertyAsChanged('user_agent'); } }
    public ?string $metadata { get => $this->metadata ?? null; set { $this->metadata = $value; $this->markPropertyAsChanged('metadata'); } }
}
