<?php

declare(strict_types=1);

namespace App\Models;

use Larafony\Framework\Database\ORM\Model;

class Bounce extends Model
{
    public string $table { get => 'bounces'; }

    public array $fillable = [
        'message_id', 'campaign_id', 'organization_id', 'subscriber_id', 'recipient_id',
        'provider_message_id', 'provider', 'bounce_type', 'smtp_code', 'bounce_reason',
        'metadata', 'bounced_at',
    ];

    public ?int $message_id { get => $this->message_id ?? null; set { $this->message_id = $value; $this->markPropertyAsChanged('message_id'); } }
    public ?int $campaign_id { get => $this->campaign_id ?? null; set { $this->campaign_id = $value; $this->markPropertyAsChanged('campaign_id'); } }
    public ?int $organization_id { get => $this->organization_id ?? null; set { $this->organization_id = $value; $this->markPropertyAsChanged('organization_id'); } }
    public ?int $subscriber_id { get => $this->subscriber_id ?? null; set { $this->subscriber_id = $value; $this->markPropertyAsChanged('subscriber_id'); } }
    public ?int $recipient_id { get => $this->recipient_id ?? null; set { $this->recipient_id = $value; $this->markPropertyAsChanged('recipient_id'); } }
    public ?string $provider_message_id { get => $this->provider_message_id ?? null; set { $this->provider_message_id = $value; $this->markPropertyAsChanged('provider_message_id'); } }
    public ?string $provider { get => $this->provider ?? 'smtp'; set { $this->provider = $value; $this->markPropertyAsChanged('provider'); } }
    public ?string $bounce_type { get => $this->bounce_type ?? 'unknown'; set { $this->bounce_type = $value; $this->markPropertyAsChanged('bounce_type'); } }
    public ?string $smtp_code { get => $this->smtp_code ?? null; set { $this->smtp_code = $value; $this->markPropertyAsChanged('smtp_code'); } }
    public ?string $bounce_reason { get => $this->bounce_reason ?? null; set { $this->bounce_reason = $value; $this->markPropertyAsChanged('bounce_reason'); } }
    public ?string $metadata { get => $this->metadata ?? null; set { $this->metadata = $value; $this->markPropertyAsChanged('metadata'); } }
    public ?string $bounced_at { get => $this->bounced_at ?? null; set { $this->bounced_at = $value; $this->markPropertyAsChanged('bounced_at'); } }
}
