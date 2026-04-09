<?php

declare(strict_types=1);

namespace App\Models;

use Larafony\Framework\Database\ORM\Attributes\BelongsTo;
use Larafony\Framework\Database\ORM\Model;

class SmtpUnsubscription extends Model
{
    public string $table { get => 'smtp_unsubscriptions'; }

    public array $fillable = [
        'subscription_id', 'smtp_setting_id', 'email', 'unsubscribed_at',
    ];



    public ?int $subscription_id {
        get => $this->subscription_id ?? null;
        set {
            $this->subscription_id = $value;
            $this->markPropertyAsChanged('subscription_id');
        }
    }

    public ?int $smtp_setting_id {
        get => $this->smtp_setting_id ?? null;
        set {
            $this->smtp_setting_id = $value;
            $this->markPropertyAsChanged('smtp_setting_id');
        }
    }

    public ?string $email {
        get => $this->email ?? null;
        set {
            $this->email = $value;
            $this->markPropertyAsChanged('email');
        }
    }

    public ?string $unsubscribed_at {
        get => $this->unsubscribed_at ?? null;
        set {
            $this->unsubscribed_at = $value;
            $this->markPropertyAsChanged('unsubscribed_at');
        }
    }

    #[BelongsTo(
        related: Subscription::class,
        foreign_key: 'subscription_id',
        local_key: 'id'
    )]
    public ?Subscription $subscription { get => $this->relations->getRelation('subscription'); }
}
