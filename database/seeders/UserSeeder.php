<?php

declare(strict_types=1);

namespace App\Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use App\Models\UserProfile;
use Larafony\Framework\Database\ORM\Entities\Role;

class UserSeeder
{
    public function run(): void
    {
        echo "Seeding users and organizations...\n\n";
        
        // 1. Create default organization
        echo "1. Creating default organization...\n";
        $defaultOrg = new Organization();
        $defaultOrg->name = 'Default Organization';
        $defaultOrg->slug = 'default-org';
        $defaultOrg->domain = 'example.com';
        $defaultOrg->is_active = 1;
        $defaultOrg->save();
        echo "   ✓ Default organization created (ID: {$defaultOrg->id})\n";
        
        // 2. Create admin user
        echo "\n2. Creating admin user...\n";
        $admin = new User();
        $admin->email = 'admin@example.com';
        $admin->username = 'admin';
        $admin->password = 'password'; // Auto-hashed with Argon2id
        $admin->is_active = 1;
        $admin->save();
        echo "   ✓ Admin user created (ID: {$admin->id})\n";
        
        // Create admin profile
        $adminProfile = new UserProfile();
        $adminProfile->user_id = (int)$admin->id;
        $adminProfile->organization_id = (int)$defaultOrg->id;
        $adminProfile->name = 'System Administrator';
        $adminProfile->save();
        echo "   ✓ Admin profile created\n";
        
        // 3. Create manager user
        echo "\n3. Creating manager user...\n";
        $manager = new User();
        $manager->email = 'manager@example.com';
        $manager->username = 'manager';
        $manager->password = 'password'; // Auto-hashed with Argon2id
        $manager->is_active = 1;
        $manager->save();
        echo "   ✓ Manager user created (ID: {$manager->id})\n";
        
        // Create manager profile
        $managerProfile = new UserProfile();
        $managerProfile->user_id = (int)$manager->id;
        $managerProfile->organization_id = (int)$defaultOrg->id;
        $managerProfile->name = 'Campaign Manager';
        $managerProfile->save();
        echo "   ✓ Manager profile created\n";
        
        // 4. Create regular user
        echo "\n4. Creating regular user...\n";
        $user = new User();
        $user->email = 'user@example.com';
        $user->username = 'user';
        $user->password = 'password'; // Auto-hashed with Argon2id
        $user->is_active = 1;
        $user->save();
        echo "   ✓ Regular user created (ID: {$user->id})\n";
        
        // Create user profile
        $userProfile = new UserProfile();
        $userProfile->user_id = (int)$user->id;
        $userProfile->organization_id = (int)$defaultOrg->id;
        $userProfile->name = 'Regular User';
        $userProfile->save();
        echo "   ✓ User profile created\n";
        
        // 5. Assign roles to users (if roles exist)
        echo "\n5. Assigning roles to users...\n";
        
        // Get roles
        $adminRole = Role::query()->where('name', '=', 'admin')->first();
        $managerRole = Role::query()->where('name', '=', 'manager')->first();
        $userRole = Role::query()->where('name', '=', 'user')->first();
        
        if ($adminRole) {
            $admin->addRole($adminRole);
            echo "   ✓ Admin role assigned to admin user\n";
        } else {
            echo "   ⚠ Admin role not found - please run RbacSeeder first\n";
        }
        
        if ($managerRole) {
            $manager->addRole($managerRole);
            echo "   ✓ Manager role assigned to manager user\n";
        } else {
            echo "   ⚠ Manager role not found - please run RbacSeeder first\n";
        }
        
        if ($userRole) {
            $user->addRole($userRole);
            echo "   ✓ User role assigned to regular user\n";
        } else {
            echo "   ⚠ User role not found - please run RbacSeeder first\n";
        }
        
        echo "\n✓ Users and organization seeded successfully!\n";
        echo "\nTest Credentials:\n";
        echo "  Admin:   admin@example.com / password\n";
        echo "  Manager: manager@example.com / password\n";
        echo "  User:    user@example.com / password\n";
    }
}
