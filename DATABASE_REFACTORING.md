# Database Architecture Refactoring

## Overview

The database structure has been refactored to align with the Larafony framework's intended architecture. The original design mixed authentication data with application-specific data in the `users` table, which goes against the framework's separation of concerns.

## Key Changes

### 1. Users Table (Authentication Only)
**Before:**
```sql
CREATE TABLE users (
    id INT PRIMARY KEY,
    organization_id INT,          -- Application data
    name VARCHAR(255),             -- Application data  
    email VARCHAR(255),
    password VARCHAR(255),
    role ENUM('admin','manager','user'),  -- Simple role field
    is_active BOOLEAN,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**After:**
```sql
CREATE TABLE users (
    id INT PRIMARY KEY,
    email VARCHAR(100) UNIQUE,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    remember_token VARCHAR(100),
    password_reset_token VARCHAR(100),
    password_reset_expires TIMESTAMP,
    email_verified_at TIMESTAMP,
    is_active TINYINT(1),
    last_login_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 2. New User Profiles Table (Application Data)
```sql
CREATE TABLE user_profiles (
    id INT PRIMARY KEY,
    user_id INT UNIQUE,           -- Links to users.id
    organization_id INT,
    name VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (organization_id) REFERENCES organizations(id)
);
```

### 3. Framework's RBAC System (Replaces Simple Role Field)

**New Tables:**
```sql
-- Roles table
CREATE TABLE roles (
    id INT PRIMARY KEY,
    name VARCHAR(100) UNIQUE,
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Permissions table  
CREATE TABLE permissions (
    id INT PRIMARY KEY,
    name VARCHAR(100) UNIQUE,
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- User-Role pivot table (many-to-many)
CREATE TABLE user_roles (
    id INT PRIMARY KEY,
    user_id INT,
    role_id INT,
    created_at TIMESTAMP,
    UNIQUE(user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Role-Permission pivot table (many-to-many)
CREATE TABLE role_permissions (
    id INT PRIMARY KEY,
    role_id INT,
    permission_id INT,
    created_at TIMESTAMP,
    UNIQUE(role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (permission_id) REFERENCES permissions(id)
);
```

## Model Changes

### User Model (App\Models\User)

**Before:**
```php
class User extends Authenticable
{
    public ?int $organization_id;
    public ?string $name;
    public ?string $role;
    
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
```

**After:**
```php
class User extends Authenticable
{
    // Relationships only - no direct application data
    
    public function profile(): ?UserProfile
    {
        // Lazy-loaded profile relationship
    }
    
    // Helper methods using profile
    public function getOrganizationId(): ?int
    {
        return $this->profile()?->organization_id;
    }
    
    public function getName(): ?string
    {
        return $this->profile()?->name;
    }
    
    // Using framework's RBAC
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');  // From framework
    }
    
    public function hasPermission(string $permission): bool
    {
        return parent::hasPermission($permission);  // From framework
    }
}
```

### New UserProfile Model

```php
class UserProfile extends Model
{
    public string $table = 'user_profiles';
    
    public ?int $user_id;
    public ?int $organization_id;
    public ?string $name;
    
    public function user(): User { }
    public function organization(): Organization { }
}
```

## Controller Changes

**Before:**
```php
$user = Auth::user();  // Returns base Authenticable
$org_id = $user->organization_id;  // Error: property doesn't exist
```

**After:**
```php
$user = User::query()->where('id', '=', Auth::id())->first();
$org_id = $user->getOrganizationId();  // Access via helper method
```

## Benefits

### 1. Framework Alignment
- Follows Larafony's intended architecture
- Uses framework's built-in RBAC system
- Properly extends framework entities

### 2. Separation of Concerns
- Authentication data separate from application data
- Clear boundaries between layers
- Easier to maintain and test

### 3. Flexibility
- Can have multiple profiles per user (future enhancement)
- Roles and permissions are flexible and cacheable
- Easy to add new application-specific fields without modifying auth table

### 4. Security
- Framework handles auth concerns (password resets, email verification, etc.)
- Application only deals with business logic
- Clear audit trail for role changes

### 5. RBAC Advantages Over Simple Role Field
- Users can have multiple roles
- Roles can have multiple permissions
- Permissions are cached for performance
- Fine-grained access control
- Role/permission changes don't require code changes

## Migration Path

1. Run new migrations to create updated tables
2. Migrate existing data:
   ```sql
   -- Move user profiles to new table
   INSERT INTO user_profiles (user_id, organization_id, name)
   SELECT id, organization_id, name FROM users;
   
   -- Create roles
   INSERT INTO roles (name, description) VALUES
   ('admin', 'Administrator'),
   ('manager', 'Manager'),
   ('user', 'Regular User');
   
   -- Migrate role assignments
   INSERT INTO user_roles (user_id, role_id)
   SELECT u.id, r.id
   FROM users u
   JOIN roles r ON r.name = u.role;
   ```

3. Update code to use new structure
4. Remove old columns from users table

## Framework Reference

The example_demo project shows the intended usage:

```php
// example_demo/src/Models/User.php
class User extends Authenticable
{
    // Only relationships, no direct properties
    #[HasMany(...)]
    public array $notes;
}
```

This refactoring brings our project in line with this pattern.
