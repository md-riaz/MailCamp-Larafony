<?php

declare(strict_types=1);

namespace App\Database\Seeders;

use Larafony\Framework\Database\ORM\Entities\Permission;
use Larafony\Framework\Database\ORM\Entities\Role;

class RbacSeeder
{
    public function run(): void
    {
        echo "Seeding RBAC data...\n\n";
        
        // 1. Create roles
        echo "1. Creating roles...\n";
        $adminRole = new Role();
        $adminRole->name = 'admin';
        $adminRole->description = 'Full system administrator access';
        $adminRole->save();
        echo "   ✓ admin role created\n";
        
        $managerRole = new Role();
        $managerRole->name = 'manager';
        $managerRole->description = 'Organization manager with limited admin capabilities';
        $managerRole->save();
        echo "   ✓ manager role created\n";
        
        $userRole = new Role();
        $userRole->name = 'user';
        $userRole->description = 'Regular user with basic access';
        $userRole->save();
        echo "   ✓ user role created\n";
        
        // 2. Create permissions
        echo "\n2. Creating permissions...\n";
        $permissions = [
            ['campaigns.view', 'View campaigns'],
            ['campaigns.create', 'Create campaigns'],
            ['campaigns.edit', 'Edit campaigns'],
            ['campaigns.delete', 'Delete campaigns'],
            ['campaigns.launch', 'Launch campaigns'],
            ['templates.view', 'View templates'],
            ['templates.create', 'Create templates'],
            ['templates.edit', 'Edit templates'],
            ['templates.delete', 'Delete templates'],
            ['smtp.view', 'View SMTP settings'],
            ['smtp.manage', 'Manage SMTP settings'],
            ['users.view', 'View users'],
            ['users.manage', 'Manage users'],
            ['organization.manage', 'Manage organization settings'],
        ];
        
        $permissionObjects = [];
        foreach ($permissions as $perm) {
            $permission = new Permission();
            $permission->name = $perm[0];
            $permission->description = $perm[1];
            $permission->save();
            $permissionObjects[$perm[0]] = $permission;
            echo "   ✓ {$perm[0]} permission created\n";
        }
        
        // 3. Assign permissions to roles
        echo "\n3. Assigning permissions to roles...\n";
        
        // Admin gets all permissions
        echo "   Admin role gets all permissions\n";
        foreach ($permissionObjects as $permission) {
            $adminRole->addPermission($permission);
        }
        
        // Manager gets most permissions except user/org management
        echo "   Manager role gets limited permissions\n";
        $managerPerms = [
            'campaigns.view', 'campaigns.create', 'campaigns.edit', 'campaigns.delete', 'campaigns.launch',
            'templates.view', 'templates.create', 'templates.edit', 'templates.delete',
            'smtp.view', 'users.view'
        ];
        foreach ($managerPerms as $permName) {
            if (isset($permissionObjects[$permName])) {
                $managerRole->addPermission($permissionObjects[$permName]);
            }
        }
        
        // Regular user gets basic permissions
        echo "   User role gets basic permissions\n";
        $userPerms = [
            'campaigns.view', 'campaigns.create',
            'templates.view', 'templates.create'
        ];
        foreach ($userPerms as $permName) {
            if (isset($permissionObjects[$permName])) {
                $userRole->addPermission($permissionObjects[$permName]);
            }
        }
        
        echo "\n✓ RBAC data seeded successfully!\n";
    }
}
