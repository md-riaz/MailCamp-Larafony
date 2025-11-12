<?php

namespace App\Models;

/**
 * Subscription Model
 * Manages email subscription preferences
 */
class Subscription
{
    protected $table = 'subscriptions';
    protected $fillable = [
        'organization_id', 'email', 'name', 'status',
        'subscription_date', 'unsubscribe_date', 'unsubscribe_token'
    ];
    
    /**
     * Get the organization this subscription belongs to
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    
    /**
     * Generate unsubscribe token
     */
    public static function generateToken()
    {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Unsubscribe
     */
    public function unsubscribe()
    {
        $this->status = 'unsubscribed';
        $this->unsubscribe_date = date('Y-m-d H:i:s');
    }
    
    /**
     * Resubscribe
     */
    public function resubscribe()
    {
        $this->status = 'subscribed';
        $this->unsubscribe_date = null;
    }
    
    /**
     * Mark as bounced
     */
    public function markAsBounced()
    {
        $this->status = 'bounced';
    }
    
    /**
     * Check if subscribed
     */
    public function isSubscribed()
    {
        return $this->status === 'subscribed';
    }
}
