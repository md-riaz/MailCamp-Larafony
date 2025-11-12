<?php

namespace App\Models;

use App\Database\Model;

/**
 * Log Model
 * Tracks email opens, clicks, and failures
 */
class Log extends Model
{
    protected $table = 'logs';
    protected $fillable = [
        'organization_id', 'campaign_id', 'recipient_id',
        'event_type', 'event_data', 'user_agent', 'ip_address'
    ];
    
    /**
     * Get the organization this log belongs to
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    
    /**
     * Get the campaign this log belongs to
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
    
    /**
     * Get the recipient this log belongs to
     */
    public function recipient()
    {
        return $this->belongsTo(Recipient::class);
    }
    
    /**
     * Get event data
     */
    public function getEventData()
    {
        return json_decode($this->event_data, true);
    }
    
    /**
     * Set event data
     */
    public function setEventData($data)
    {
        $this->event_data = json_encode($data);
    }
    
    /**
     * Create log entry
     */
    public static function createLog($organization_id, $campaign_id, $recipient_id, $event_type, $data = [])
    {
        $log = new self();
        $log->organization_id = $organization_id;
        $log->campaign_id = $campaign_id;
        $log->recipient_id = $recipient_id;
        $log->event_type = $event_type;
        $log->setEventData($data);
        $log->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $log->ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        return $log;
    }
}
