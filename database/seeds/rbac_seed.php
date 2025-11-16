<?php

declare(strict_types=1);

/**
 * RBAC Seeder
 * Seeds roles and permissions for the framework's RBAC system
 */

require_once __DIR__ . '/../../vendor/autoload.php';

$config = require __DIR__ . '/../../config/database.php';
$dbConfig = $config['connections'][$config['default']];

try {
    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Seeding RBAC data...\n\n";
    
    // 1. Create roles
    echo "1. Creating roles...\n";
    $roles = [
        ['admin', 'Full system administrator access'],
        ['manager', 'Organization manager with limited admin capabilities'],
        ['user', 'Regular user with basic access'],
    ];
    
    $roleIds = [];
    foreach ($roles as $role) {
        $stmt = $pdo->prepare("
            INSERT INTO roles (name, description, created_at, updated_at) 
            VALUES (?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE description = VALUES(description)
        ");
        $stmt->execute([$role[0], $role[1]]);
        
        $roleId = $pdo->lastInsertId();
        if (!$roleId) {
            $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
            $stmt->execute([$role[0]]);
            $roleId = $stmt->fetchColumn();
        }
        $roleIds[$role[0]] = $roleId;
        echo "   ✓ {$role[0]} role created (ID: {$roleId})\n";
    }
    
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
    
    $permissionIds = [];
    foreach ($permissions as $permission) {
        $stmt = $pdo->prepare("
            INSERT INTO permissions (name, description, created_at, updated_at) 
            VALUES (?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE description = VALUES(description)
        ");
        $stmt->execute([$permission[0], $permission[1]]);
        
        $permId = $pdo->lastInsertId();
        if (!$permId) {
            $stmt = $pdo->prepare("SELECT id FROM permissions WHERE name = ?");
            $stmt->execute([$permission[0]]);
            $permId = $stmt->fetchColumn();
        }
        $permissionIds[$permission[0]] = $permId;
        echo "   ✓ {$permission[0]} permission created\n";
    }
    
    // 3. Assign permissions to roles
    echo "\n3. Assigning permissions to roles...\n";
    
    // Admin gets all permissions
    echo "   Admin role:\n";
    foreach ($permissionIds as $name => $permId) {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO role_permissions (role_id, permission_id, created_at) 
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$roleIds['admin'], $permId]);
        echo "     ✓ {$name}\n";
    }
    
    // Manager gets most permissions except user/org management
    echo "\n   Manager role:\n";
    $managerPerms = [
        'campaigns.view', 'campaigns.create', 'campaigns.edit', 'campaigns.delete', 'campaigns.launch',
        'templates.view', 'templates.create', 'templates.edit', 'templates.delete',
        'smtp.view', 'users.view'
    ];
    foreach ($managerPerms as $perm) {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO role_permissions (role_id, permission_id, created_at) 
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$roleIds['manager'], $permissionIds[$perm]]);
        echo "     ✓ {$perm}\n";
    }
    
    // Regular user gets basic permissions
    echo "\n   User role:\n";
    $userPerms = [
        'campaigns.view', 'campaigns.create',
        'templates.view', 'templates.create'
    ];
    foreach ($userPerms as $perm) {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO role_permissions (role_id, permission_id, created_at) 
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$roleIds['user'], $permissionIds[$perm]]);
        echo "     ✓ {$perm}\n";
    }
    
    echo "\n✓ RBAC data seeded successfully!\n";
    
} catch (PDOException $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
