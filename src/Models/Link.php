<?php

declare(strict_types=1);

namespace App\Models;

use Larafony\Framework\Database\ORM\Model;

class Link extends Model
{
    public string $table { get => 'links'; }

    public array $fillable = [
        'message_id', 'campaign_id', 'organization_id', 'subscriber_id', 'recipient_id',
        'url', 'url_hash', 'click_count', 'last_clicked_at',
    ];

    public ?int $message_id { get => $this->message_id ?? null; set { $this->message_id = $value; $this->markPropertyAsChanged('message_id'); } }
    public ?int $campaign_id { get => $this->campaign_id ?? null; set { $this->campaign_id = $value; $this->markPropertyAsChanged('campaign_id'); } }
    public ?int $organization_id { get => $this->organization_id ?? null; set { $this->organization_id = $value; $this->markPropertyAsChanged('organization_id'); } }
    public ?int $subscriber_id { get => $this->subscriber_id ?? null; set { $this->subscriber_id = $value; $this->markPropertyAsChanged('subscriber_id'); } }
    public ?int $recipient_id { get => $this->recipient_id ?? null; set { $this->recipient_id = $value; $this->markPropertyAsChanged('recipient_id'); } }
    public ?string $url { get => $this->url ?? null; set { $this->url = $value; $this->markPropertyAsChanged('url'); } }
    public ?string $url_hash { get => $this->url_hash ?? null; set { $this->url_hash = $value; $this->markPropertyAsChanged('url_hash'); } }
    public ?int $click_count { get => $this->click_count ?? 0; set { $this->click_count = $value; $this->markPropertyAsChanged('click_count'); } }
    public ?string $last_clicked_at { get => $this->last_clicked_at ?? null; set { $this->last_clicked_at = $value; $this->markPropertyAsChanged('last_clicked_at'); } }
}
