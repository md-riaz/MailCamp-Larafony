<?php

namespace App\Models;

use App\Database\Model;

/**
 * QueueJob Model
 * Database-backed queue for batch email sending with throttling
 */
class QueueJob extends Model
{
    protected $table = 'queue_jobs';
    protected $fillable = [
        'organization_id', 'campaign_id', 'recipient_id', 'payload',
        'attempts', 'status', 'available_at', 'reserved_at', 'completed_at'
    ];
    
    /**
     * Get the organization this job belongs to
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    
    /**
     * Get the campaign this job belongs to
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
    
    /**
     * Get the recipient for this job
     */
    public function recipient()
    {
        return $this->belongsTo(Recipient::class);
    }
    
    /**
     * Get payload data
     */
    public function getPayload()
    {
        return json_decode($this->payload, true);
    }
    
    /**
     * Set payload data
     */
    public function setPayload($data)
    {
        $this->payload = json_encode($data);
    }
    
    /**
     * Mark job as processing
     */
    public function markAsProcessing()
    {
        $this->status = 'processing';
        $this->reserved_at = date('Y-m-d H:i:s');
        $this->attempts++;
    }
    
    /**
     * Mark job as completed
     */
    public function markAsCompleted()
    {
        $this->status = 'completed';
        $this->completed_at = date('Y-m-d H:i:s');
    }
    
    /**
     * Mark job as failed
     */
    public function markAsFailed()
    {
        $this->status = 'failed';
        $this->completed_at = date('Y-m-d H:i:s');
    }
}
