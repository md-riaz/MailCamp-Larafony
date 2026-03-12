<?php

declare(strict_types=1);

namespace App\Models;

use Larafony\Framework\Database\ORM\Model;

class Message extends Model
{
    public string $table { get => 'messages'; }

    public array $fillable = [
        'campaign_id', 'organization_id', 'subscriber_id', 'recipient_id', 'recipient_email',
        'status', 'provider', 'provider_message_id', 'subject', 'sent_at', 'delivered_at',
    ];

    public ?int $campaign_id { get => $this->campaign_id ?? null; set { $this->campaign_id = $value; $this->markPropertyAsChanged('campaign_id'); } }
    public ?int $organization_id { get => $this->organization_id ?? null; set { $this->organization_id = $value; $this->markPropertyAsChanged('organization_id'); } }
    public ?int $subscriber_id { get => $this->subscriber_id ?? null; set { $this->subscriber_id = $value; $this->markPropertyAsChanged('subscriber_id'); } }
    public ?int $recipient_id { get => $this->recipient_id ?? null; set { $this->recipient_id = $value; $this->markPropertyAsChanged('recipient_id'); } }
    public ?string $recipient_email { get => $this->recipient_email ?? null; set { $this->recipient_email = $value; $this->markPropertyAsChanged('recipient_email'); } }
    public ?string $status { get => $this->status ?? 'queued'; set { $this->status = $value; $this->markPropertyAsChanged('status'); } }
    public ?string $provider { get => $this->provider ?? 'smtp'; set { $this->provider = $value; $this->markPropertyAsChanged('provider'); } }
    public ?string $provider_message_id { get => $this->provider_message_id ?? null; set { $this->provider_message_id = $value; $this->markPropertyAsChanged('provider_message_id'); } }
    public ?string $subject { get => $this->subject ?? null; set { $this->subject = $value; $this->markPropertyAsChanged('subject'); } }
    public ?string $sent_at { get => $this->sent_at ?? null; set { $this->sent_at = $value; $this->markPropertyAsChanged('sent_at'); } }
    public ?string $delivered_at { get => $this->delivered_at ?? null; set { $this->delivered_at = $value; $this->markPropertyAsChanged('delivered_at'); } }
}
