<?php

namespace App\Models;

use App\Database\Model;

/**
 * Campaign Model
 * Manages email campaigns
 */
class Campaign extends Model
{
    protected $table = 'campaigns';
    protected $fillable = [
        'organization_id', 'template_id', 'name', 'status', 
        'scheduled_at', 'started_at', 'completed_at',
        'total_recipients', 'sent_count', 'failed_count', 'created_by'
    ];
    
    /**
     * Get the organization this campaign belongs to
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    
    /**
     * Get the template used for this campaign
     */
    public function template()
    {
        return $this->belongsTo(Template::class);
    }
    
    /**
     * Get the user who created this campaign
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Get recipients for this campaign
     */
    public function recipients()
    {
        return $this->hasMany(Recipient::class);
    }
    
    /**
     * Get queue jobs for this campaign
     */
    public function queueJobs()
    {
        return $this->hasMany(QueueJob::class);
    }
    
    /**
     * Get logs for this campaign
     */
    public function logs()
    {
        return $this->hasMany(Log::class);
    }
    
    /**
     * Check if campaign can be started
     */
    public function canStart()
    {
        return in_array($this->status, ['draft', 'scheduled', 'paused']);
    }
    
    /**
     * Get campaign statistics
     */
    public function getStats()
    {
        return [
            'total_recipients' => $this->total_recipients,
            'sent_count' => $this->sent_count,
            'failed_count' => $this->failed_count,
            'open_rate' => $this->calculateOpenRate(),
            'click_rate' => $this->calculateClickRate(),
        ];
    }
    
    private function calculateOpenRate()
    {
        if ($this->sent_count == 0) return 0;
        // Count unique opens from logs
        return 0; // Placeholder - implement with actual log query
    }
    
    private function calculateClickRate()
    {
        if ($this->sent_count == 0) return 0;
        // Count unique clicks from logs
        return 0; // Placeholder - implement with actual log query
    }
}
