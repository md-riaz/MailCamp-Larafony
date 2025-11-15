<?php

declare(strict_types=1);

namespace App\Models;

use Larafony\Framework\Database\ORM\Attributes\HasMany;
use Larafony\Framework\Database\ORM\Attributes\HasOne;
use Larafony\Framework\Database\ORM\Model;

class Organization extends Model
{
    public string $table { get => 'organizations'; }

    public array $fillable = ['name', 'slug', 'domain', 'is_active'];

    public ?string $name {
        get => $this->name ?? null;
        set {
            $this->name = $value;
            $this->markPropertyAsChanged('name');
        }
    }

    public ?string $slug {
        get => $this->slug ?? null;
        set {
            $this->slug = $value;
            $this->markPropertyAsChanged('slug');
        }
    }

    public ?string $domain {
        get => $this->domain ?? null;
        set {
            $this->domain = $value;
            $this->markPropertyAsChanged('domain');
        }
    }

    public int $is_active {
        get => $this->is_active ?? 1;
        set {
            $this->is_active = $value;
            $this->markPropertyAsChanged('is_active');
        }
    }

    // HasMany relationship: Organization has many Users
    #[HasMany(
        related: User::class,
        foreign_key: 'organization_id',
        local_key: 'id'
    )]
    public array $users { get => $this->relations->getRelation('users'); }

    // HasOne relationship: Organization has one SmtpSetting
    // #[HasOne(
    //     related: SmtpSetting::class,
    //     foreign_key: 'organization_id',
    //     local_key: 'id'
    // )]
    public ?SmtpSetting $smtpSettings { get => $this->relations->getRelation('smtpSettings'); }

    // HasMany relationship: Organization has many Templates
    #[HasMany(
        related: Template::class,
        foreign_key: 'organization_id',
        local_key: 'id'
    )]
    public array $templates { get => $this->relations->getRelation('templates'); }

    // HasMany relationship: Organization has many Campaigns
    #[HasMany(
        related: Campaign::class,
        foreign_key: 'organization_id',
        local_key: 'id'
    )]
    public array $campaigns { get => $this->relations->getRelation('campaigns'); }

    // HasMany relationship: Organization has many Subscriptions
    #[HasMany(
        related: Subscription::class,
        foreign_key: 'organization_id',
        local_key: 'id'
    )]
    public array $subscriptions { get => $this->relations->getRelation('subscriptions'); }

    /**
     * Generate slug from name
     */
    public static function generateSlug(string $name): string
    {
        return strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $name));
    }
}
