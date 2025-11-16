# Database Seeders

This directory contains database seeders for populating the MailCamp application with sample data.

## Available Seeders

### 1. RbacSeeder
Seeds the Role-Based Access Control (RBAC) system with:
- **Roles**: admin, manager, user
- **Permissions**: 14 permissions for campaigns, templates, SMTP, users, and organization management
- **Role-Permission Assignments**: Appropriate permissions for each role

### 2. UserSeeder
Creates sample users, organizations, and profiles:
- 1 Default Organization
- 3 Users (admin, manager, regular user) with profiles
- Role assignments for each user
- Test credentials for authentication

### 3. SmtpSettingSeeder
Creates sample SMTP configurations:
- Default SMTP server (active)
- Gmail SMTP example (inactive)
- SendGrid SMTP example (inactive)

### 4. TemplateSeeder
Creates 5 email templates:
- Welcome Email
- Monthly Newsletter
- Product Announcement
- Password Reset
- Order Confirmation

Each template includes:
- HTML content with variables
- Subject line with variables
- Variable definitions (JSON)

### 5. CampaignSeeder
Creates 5 sample campaigns with different statuses:
- Completed campaigns with sent/failed counts
- Active campaigns in "sending" status
- Scheduled campaigns for future dates
- Draft campaigns
- Sample recipients for each campaign

### 6. DatabaseSeeder (Main Seeder)
Orchestrates all seeders in the correct order and provides a summary.

## Usage

### Run All Seeders
```bash
php run-database-seeder.php
```

### Run Specific Seeder
```bash
php run-database-seeder.php RbacSeeder
php run-database-seeder.php UserSeeder
php run-database-seeder.php TemplateSeeder
php run-database-seeder.php CampaignSeeder
php run-database-seeder.php SmtpSettingSeeder
```

## Seeding Order (Dependencies)

The seeders must be run in this order to respect dependencies:

1. **RbacSeeder** - No dependencies
2. **UserSeeder** - Depends on RbacSeeder (for role assignments)
3. **SmtpSettingSeeder** - Depends on UserSeeder (for organization)
4. **TemplateSeeder** - Depends on UserSeeder (for organization)
5. **CampaignSeeder** - Depends on TemplateSeeder and UserSeeder

The `DatabaseSeeder` automatically runs them in the correct order.

## Test Credentials

After running the seeders, you can log in with these credentials:

| Role    | Email                    | Password  |
|---------|--------------------------|-----------|
| Admin   | admin@example.com        | password  |
| Manager | manager@example.com      | password  |
| User    | user@example.com         | password  |

## Data Created

### Roles & Permissions
- 3 roles with hierarchical permissions
- 14 permissions covering all major features
- Proper role-permission mappings

### Organizations
- 1 default organization (slug: `default-org`)
- All users belong to this organization

### Users
- 3 users with different roles
- Each user has a profile with name and organization
- Passwords are hashed with Argon2id

### SMTP Settings
- 3 example SMTP configurations
- Only the default is active
- **Important**: Update credentials before actual use

### Templates
- 5 professional email templates
- HTML content with CSS styling
- Variable placeholders for personalization
- JSON variable definitions

### Campaigns
- 5 campaigns in various states:
  - `completed`: Fully sent campaigns
  - `sending`: Currently processing
  - `scheduled`: Waiting for scheduled time
  - `draft`: Not yet scheduled
- Sample recipients with different statuses

## Resetting Data

To reset and reseed the database:

```bash
# Drop and recreate tables
php bin/larafony migrate:fresh

# Run seeders
php run-database-seeder.php
```

## Customization

### Adding New Seeders

1. Create a new seeder class in `database/seeders/`:

```php
<?php

declare(strict_types=1);

namespace App\Database\Seeders;

class MyCustomSeeder
{
    public function run(): void
    {
        // Your seeding logic here
    }
}
```

2. Add it to `DatabaseSeeder.php` in the `$seeders` array:

```php
$seeders = [
    RbacSeeder::class,
    UserSeeder::class,
    // ... other seeders
    MyCustomSeeder::class,  // Add here
];
```

### Modifying Seed Data

Edit the respective seeder file to change the sample data. For example:

- **Change user credentials**: Edit `UserSeeder.php`
- **Add more templates**: Edit `TemplateSeeder.php`
- **Modify permissions**: Edit `RbacSeeder.php`

## Production Use

**⚠️ Warning**: These seeders create sample data with default passwords and test credentials.

**Do NOT use these seeders in production without:**
1. Changing all default passwords
2. Updating SMTP credentials with real values
3. Removing or modifying test data
4. Implementing proper security measures

## Troubleshooting

### Error: "Organization not found"
Run `UserSeeder` first, as it creates the default organization.

### Error: "Templates not found"
Run `TemplateSeeder` before `CampaignSeeder`.

### Error: "Roles not found"
Run `RbacSeeder` first.

### Database Connection Error
Check your database configuration in `config/database.php`.

### Permission Denied
Ensure the database user has proper permissions to insert data.

## Notes

- All timestamps use the system's current time with relative offsets
- Passwords are automatically hashed using Argon2id
- Sample recipient emails use `@example.com` domain
- Variable placeholders in templates use `{{variable_name}}` syntax
- Failed recipients include sample error messages

## Support

For issues or questions about seeders:
1. Check the console output for specific error messages
2. Verify database migrations are up to date
3. Ensure all dependencies are installed
4. Check that the database is accessible

## License

These seeders are part of the MailCamp project and follow the same license.
