<?php

declare(strict_types=1);

namespace App\Models;

use Larafony\Framework\Database\ORM\Attributes\BelongsTo;
use Larafony\Framework\Database\ORM\Model;

class Subscription extends Model
{
    public string $table { get => 'subscriptions'; }

    public array $fillable = [
        'email', 'name', 'status', 'unsubscribe_token',
        'subscription_date', 'unsubscribe_date'
    ];

    
        get => $this->organization_id ?? null;
        set {
            $this->organization_id = $value;
            $this->markPropertyAsChanged('organization_id');
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

    public ?string $status {
        get => $this->status ?? 'subscribed';
        set {
            $this->status = $value;
            $this->markPropertyAsChanged('status');
        }
    }

    public ?string $unsubscribe_token {
        get => $this->unsubscribe_token ?? null;
        set {
            $this->unsubscribe_token = $value;
            $this->markPropertyAsChanged('unsubscribe_token');
        }
    }

    public ?string $subscription_date {
        get => $this->subscription_date ?? null;
        set {
            $this->subscription_date = $value;
            $this->markPropertyAsChanged('subscription_date');
        }
    }

    public ?string $unsubscribe_date {
        get => $this->unsubscribe_date ?? null;
        set {
            $this->unsubscribe_date = $value;
            $this->markPropertyAsChanged('unsubscribe_date');
        }
    }

    #[BelongsTo(
        related: Organization::class,
        foreign_key: 'organization_id',
        local_key: 'id'
    )]
    public ?Organization $organization { get => $this->relations->getRelation('organization'); }

    public static function generateToken(int $organizationId, int $smtpId, string $email): string
    {
        $payload = [
            'smtpId' => $smtpId,
            'email' => strtolower(trim($email)),
        ];

        return rtrim(strtr(base64_encode(json_encode($payload, JSON_UNESCAPED_SLASHES)), '+/', '-_'), '=');
    }

    /**
     * @return array{organizationId:int,smtpId:int,email:string}|null
     */
    public static function decodeToken(string $token): ?array
    {
        $padded = str_pad(strtr($token, '-_', '+/'), (int) ceil(strlen($token) / 4) * 4, '=', STR_PAD_RIGHT);
        $decoded = json_decode((string) base64_decode($padded, true), true);
        if (!is_array($decoded)) {
            return null;
        }

        $organizationId = (int) ($decoded['organizationId'] ?? 0);
        $smtpId = (int) ($decoded['smtpId'] ?? 0);
        $email = strtolower(trim((string) ($decoded['email'] ?? '')));
        if ($organizationId <= 0 || $smtpId <= 0 || $email === '') {
            return null;
        }

        return [
            'organizationId' => $organizationId,
            'smtpId' => $smtpId,
            'email' => $email,
        ];
    }



    public function ensureToken(int $organizationId, int $smtpId): void
    {
        $this->unsubscribe_token = self::generateToken($organizationId, $smtpId, (string) $this->email);
    }

    public function markGloballyUnsubscribed(): void
    {
        $this->status = 'unsubscribed';
        $this->unsubscribe_date = date('Y-m-d H:i:s');
        $this->save();
    }
}
