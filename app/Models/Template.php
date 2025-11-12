<?php

namespace App\Models;

/**
 * Template Model
 * Stores HTML email templates with variable placeholders
 */
class Template
{
    protected $table = 'templates';
    protected $fillable = [
        'organization_id', 'name', 'subject', 'html_content', 'variables', 'is_active'
    ];
    
    /**
     * Get the organization this template belongs to
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    
    /**
     * Get campaigns using this template
     */
    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }
    
    /**
     * Parse variables from template content
     */
    public function parseVariables()
    {
        preg_match_all('/\{\{([^}]+)\}\}/', $this->html_content, $matches);
        return array_unique($matches[1]);
    }
    
    /**
     * Replace variables in template
     */
    public function render($data = [])
    {
        $content = $this->html_content;
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        return $content;
    }
    
    /**
     * Render subject with variables
     */
    public function renderSubject($data = [])
    {
        $subject = $this->subject;
        foreach ($data as $key => $value) {
            $subject = str_replace('{{' . $key . '}}', $value, $subject);
        }
        return $subject;
    }
}
