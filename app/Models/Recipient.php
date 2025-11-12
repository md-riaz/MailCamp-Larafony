<?php

namespace App\Models;

/**
 * Recipient Model
 * Stores recipient lists for campaigns
 */
class Recipient
{
    protected $table = 'recipients';
    protected $fillable = [
        'organization_id', 'campaign_id', 'email', 'name', 
        'custom_data', 'status', 'sent_at', 'opened_at', 'clicked_at'
    ];
    
    /**
     * Get the organization this recipient belongs to
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    
    /**
     * Get the campaign this recipient belongs to
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
    
    /**
     * Get logs for this recipient
     */
    public function logs()
    {
        return $this->hasMany(Log::class);
    }
    
    /**
     * Parse custom data
     */
    public function getCustomData()
    {
        return json_decode($this->custom_data, true) ?: [];
    }
    
    /**
     * Set custom data
     */
    public function setCustomData($data)
    {
        $this->custom_data = json_encode($data);
    }
    
    /**
     * Mark as sent
     */
    public function markAsSent()
    {
        $this->status = 'sent';
        $this->sent_at = date('Y-m-d H:i:s');
    }
    
    /**
     * Mark as opened
     */
    public function markAsOpened()
    {
        if (!$this->opened_at) {
            $this->opened_at = date('Y-m-d H:i:s');
        }
    }
    
    /**
     * Mark as clicked
     */
    public function markAsClicked()
    {
        if (!$this->clicked_at) {
            $this->clicked_at = date('Y-m-d H:i:s');
        }
    }
}
