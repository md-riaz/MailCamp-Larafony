<?php

namespace App\Models;

/**
 * User Model
 * Handles user authentication with role-based access
 */
class User
{
    protected $table = 'users';
    protected $fillable = ['organization_id', 'name', 'email', 'password', 'role', 'is_active'];
    protected $hidden = ['password'];
    
    /**
     * Get the organization that the user belongs to
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    
    /**
     * Get campaigns created by this user
     */
    public function campaigns()
    {
        return $this->hasMany(Campaign::class, 'created_by');
    }
    
    /**
     * Check if user has admin role
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }
    
    /**
     * Check if user has manager or admin role
     */
    public function isManager()
    {
        return in_array($this->role, ['admin', 'manager']);
    }
    
    /**
     * Hash password before saving
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }
    
    /**
     * Verify password
     */
    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }
}
