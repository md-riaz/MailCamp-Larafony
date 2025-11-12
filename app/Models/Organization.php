<?php

namespace App\Models;

/**
 * Organization Model
 * Handles multi-tenancy for different organizations
 */
class Organization
{
    protected $table = 'organizations';
    protected $fillable = ['name', 'slug', 'domain', 'is_active'];
    
    /**
     * Get users belonging to this organization
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
    
    /**
     * Get SMTP settings for this organization
     */
    public function smtpSettings()
    {
        return $this->hasOne(SmtpSetting::class);
    }
    
    /**
     * Get templates for this organization
     */
    public function templates()
    {
        return $this->hasMany(Template::class);
    }
    
    /**
     * Get campaigns for this organization
     */
    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }
    
    /**
     * Get subscriptions for this organization
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
    
    /**
     * Generate slug from name
     */
    public static function generateSlug($name)
    {
        return strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $name));
    }
}
