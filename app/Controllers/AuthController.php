<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Organization;

/**
 * AuthController
 * Handles user authentication (login/register) with role-based access
 */
class AuthController
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        include __DIR__ . '/../../resources/views/auth/login.php';
    }
    
    /**
     * Handle login
     */
    public function login()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            return $this->error('Email and password are required');
        }
        
        // Find user by email
        $user = User::findByEmail($email);
        
        if (!$user || !$user->verifyPassword($password)) {
            return $this->error('Invalid credentials');
        }
        
        if (!$user->is_active) {
            return $this->error('Account is inactive');
        }
        
        // Start session
        session_start();
        $_SESSION['user_id'] = $user->id;
        $_SESSION['organization_id'] = $user->organization_id;
        $_SESSION['role'] = $user->role;
        
        header('Location: /dashboard');
        exit;
    }
    
    /**
     * Show register form
     */
    public function showRegister()
    {
        include __DIR__ . '/../../resources/views/auth/register.php';
    }
    
    /**
     * Handle registration
     */
    public function register()
    {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $org_name = $_POST['organization_name'] ?? '';
        
        if (empty($name) || empty($email) || empty($password) || empty($org_name)) {
            return $this->error('All fields are required');
        }
        
        // Check if email exists
        if (User::findByEmail($email)) {
            return $this->error('Email already exists');
        }
        
        // Create organization
        $org = new Organization();
        $org->name = $org_name;
        $org->slug = Organization::generateSlug($org_name);
        $org->save();
        
        // Create user
        $user = new User();
        $user->organization_id = $org->id;
        $user->name = $name;
        $user->email = $email;
        $user->password = User::hashPassword($password);
        $user->role = 'admin'; // First user is admin
        $user->save();
        
        // Auto-login
        session_start();
        $_SESSION['user_id'] = $user->id;
        $_SESSION['organization_id'] = $user->organization_id;
        $_SESSION['role'] = $user->role;
        
        header('Location: /dashboard');
        exit;
    }
    
    /**
     * Handle logout
     */
    public function logout()
    {
        session_start();
        session_destroy();
        header('Location: /login');
        exit;
    }
    
    /**
     * Error handler
     */
    private function error($message)
    {
        $_SESSION['error'] = $message;
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
}
