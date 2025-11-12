<?php

namespace App\Models;

/**
 * SmtpSetting Model
 * Stores SMTP configuration per organization
 */
class SmtpSetting
{
    protected $table = 'smtp_settings';
    protected $fillable = [
        'organization_id', 'host', 'port', 'encryption', 
        'username', 'password', 'from_email', 'from_name', 'is_active'
    ];
    protected $hidden = ['password'];
    
    /**
     * Get the organization this SMTP setting belongs to
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    
    /**
     * Encrypt password before saving
     */
    public static function encryptPassword($password)
    {
        // Simple encryption - in production use proper encryption
        return base64_encode($password);
    }
    
    /**
     * Decrypt password
     */
    public function decryptPassword()
    {
        return base64_decode($this->password);
    }
    
    /**
     * Validate SMTP settings
     */
    public function validate()
    {
        return !empty($this->host) && 
               !empty($this->port) && 
               !empty($this->username) && 
               !empty($this->password) &&
               !empty($this->from_email);
    }
}
