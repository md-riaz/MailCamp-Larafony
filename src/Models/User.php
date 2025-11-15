<?php

declare(strict_types=1);

namespace App\Models;

use Larafony\Framework\Database\ORM\Attributes\HasMany;
use Larafony\Framework\Database\ORM\Entities\User as Authenticable;

/**
 * User Model
 * 
 * Extends framework's Authenticable User entity.
 * Uses profile relationship for application-specific data.
 * Uses framework's RBAC system for roles and permissions.
 */
class User extends Authenticable
{
    public string $table { get => 'users'; }

    public array $fillable = ['email', 'username', 'password', 'is_active'];
    public array $hidden = ['password', 'remember_token', 'password_reset_token'];

    private ?UserProfile $_profile = null;

    /**
     * Get user's profile (lazy loaded)
     */
    public function profile(): ?UserProfile
    {
        if ($this->_profile === null && isset($this->id)) {
            $this->_profile = UserProfile::query()
                ->where('user_id', '=', $this->id)
                ->first();
        }
        return $this->_profile;
    }

    // HasMany relationship: User has many Campaigns
    #[HasMany(
        related: Campaign::class,
        foreign_key: 'created_by',
        local_key: 'id'
    )]
    public array $campaigns { get => $this->relations->getRelation('campaigns'); }

    /**
     * Get user's organization ID via profile
     */
    public function getOrganizationId(): ?int
    {
        return $this->profile()?->organization_id;
    }

    /**
     * Get user's name via profile
     */
    public function getName(): ?string
    {
        return $this->profile()?->name;
    }

    /**
     * Get user's organization via profile
     */
    public function getOrganization(): ?Organization
    {
        return $this->profile()?->organization;
    }

    /**
     * Check if user has admin role (using framework's RBAC)
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user has manager or admin role (using framework's RBAC)
     */
    public function isManager(): bool
    {
        return $this->hasRole('admin') || $this->hasRole('manager');
    }
}
