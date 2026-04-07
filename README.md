# MailCamp - Multi-tenant Email Campaign Manager

MailCamp is a powerful multi-tenant mail campaign manager built on the **Larafony Framework**. Each organization can configure its own SMTP settings, design custom email templates, manage recipients, and track campaign performance.

Built with modern PHP 8.5+ features including property hooks, attributes, and asymmetric visibility.

## Features

- **Multi-tenancy**: Support for multiple organizations with isolated data
- **SMTP Configuration**: Each organization configures its own SMTP settings (host, port, encryption, credentials)
- **Role-based Access Control**: Admin, Manager, and User roles with different permissions
- **Template Designer**: Create HTML email templates with variable placeholders ({{variable}})
- **Rich HTML Editor**: Build templates with a full HTML editor, merge tags, and inline image uploads
- **Campaign Management**: Create, schedule, and launch email campaigns
- **Multiple SMTP Accounts**: Save several SMTP connections and select the right sender per campaign
- **Recipient Import**: Import recipients via CSV files with custom data fields
- **Queue System**: Database-backed queue with throttling for deliverability
- **Tracking & Analytics**: Track email opens, clicks, bounces, and failures
- **Subscription Management**: Handle email subscriptions and unsubscribes

## Database Schema

The application uses the following core tables:

- `users` - User authentication with role-based access
- `organizations` - Multi-tenant organizations
- `smtp_settings` - SMTP configuration per organization
- `templates` - Reusable HTML templates with variables
- `campaigns` - Email campaign records, selected SMTP account, and lifecycle state
- `recipients` - Recipient lists for campaigns
- `queue_jobs` - Campaign-recipient send queue entries
- `messages` - Per-recipient send records
- `email_events` - Open, click, sent, bounce, and failure events
- `links` - Tracked click targets per message
- `bounces` - Bounce and DSN storage
- `webhooks` - Provider webhook payload storage
- `jobs` - Framework scheduler / queue worker jobs
- `failed_jobs` - Failed framework job storage
- `subscriptions` - Email subscription management

The app uses both a campaign queue (`queue_jobs`) and the framework scheduler queue (`jobs`). That distinction matters during local development.

## Installation

### Requirements

- **PHP 8.5 or higher** (required for property hooks and modern PHP features)
- MySQL 5.7 or higher (this is the documented and configured database path in the repo)
- Composer (for PHP dependencies)
- Web server (Apache/Nginx) or PHP built-in server

### Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/md-riaz/MailCamp-Larafony.git
   cd MailCamp-Larafony
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   ```
   
   Edit `.env` and configure your database and application settings:
   ```
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://localhost:8000

   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=mailcamp
   DB_USERNAME=root
   DB_PASSWORD=your_password

   QUEUE_DRIVER=database
   QUEUE_THROTTLE_EMAILS_PER_HOUR=100
   ```

   Notes:
   - `.env.example` currently includes the main app, database, session, and queue values you need for local setup.
   - The README previously mentioned `APP_KEY`, but the sample env file in this repo does not currently include it as a required setup step.

4. **Initialize the application**
   ```bash
   php bin/larafony app:init
   ```
   
   This will check your database connection and create the database if needed.

5. **Run migrations**
   ```bash
   php bin/larafony migrate
   ```

6. **Start the application**
   
   Using PHP built-in server:
   ```bash
   php -S localhost:8000 -t public
   ```
   
   Or configure your web server to point to the `public` directory.

7. **Start the queue worker** (in a separate terminal)
   ```bash
   php bin/larafony queue:work
   ```

8. **Run the scheduler trigger during local development**
   ```bash
   php bin/larafony schedule:run
   ```

   In production this would usually be triggered by cron. In local development, run it manually whenever you want due scheduled jobs to be enqueued.

9. **Access the application**
   
   Open your browser and navigate to `http://localhost:8000`

10. **[Optional] Load demo data**
   ```bash
   php bin/larafony app:seed
   ```
   
   This is the preferred project-level seeding command for local onboarding. The repository also contains `php run-database-seeder.php`, but new contributors should start with `php bin/larafony app:seed` unless they specifically need the manual seeder runner.

   This creates:
   - RBAC roles and permissions (admin, manager, user)
   - Demo organization with 3 users
   - 3 SMTP settings
   - 5 email templates
   - 5 campaigns with sample recipients

## Test Credentials

After running the seeder, you can login with:

| Role    | Email                  | Password |
|---------|------------------------|----------|
| Admin   | admin@example.com      | password |
| Manager | manager@example.com    | password |
| User    | user@example.com       | password |

**Note:** Change these passwords in production!

## Usage

### First Time Setup

1. **Register**: Create an account at `/register`. The first user becomes an admin for their organization.

2. **Configure SMTP**: Navigate to SMTP Settings and configure at least one active email server/account.

3. **Create a Campaign**: The current campaign creation flow supports inline content authoring. You provide a campaign name, email subject, HTML body, and select the SMTP account to send from.

4. **Optionally save the content as a reusable template**: Campaign creation can store the inline HTML as a reusable template for later campaigns.

5. **Edit campaign settings if needed**: The edit screen lets you change the selected template, SMTP account, and optional `scheduled_at` value.

6. **Import Recipients**: Upload a CSV file with recipient data (`email`, `name`, and any custom fields you want to use in templates).

7. **Launch Campaign**: Launching a campaign creates due queue entries. Sending then progresses through the queue/scheduler flow described below.

8. **Monitor**: Track opens, clicks, bounces, and failures in the campaign dashboard.

### Template Variables

Templates support variable placeholders using double curly braces:
- `{{name}}` - Recipient name
- `{{email}}` - Recipient email
- Custom variables from CSV import

Example template:
```html
<h1>Hello {{name}}!</h1>
<p>This is a personalized email for {{email}}</p>
```

### Tracking

MailCamp automatically tracks:
- **Opens**: Via invisible tracking pixel
- **Clicks**: Via tracked links
- **Bounces**: Via SMTP feedback
- **Unsubscribes**: Via unsubscribe links

### First Successful Local Run Checklist

A fresher developer should be able to verify local setup with this checklist:

- `composer install` completes successfully
- `.env` is created from `.env.example`
- MySQL is running and the configured database is reachable
- `php bin/larafony migrate` completes without errors
- `php -S localhost:8000 -t public` starts the app locally
- `/register` and `/login` pages load in the browser
- `php bin/larafony app:seed` creates demo data successfully (optional, but recommended for onboarding)
- You can sign in with one of the seeded demo accounts
- At least one active SMTP setting exists in the UI
- A campaign can be created, recipients can be imported, and launch creates queue entries
- `php bin/larafony schedule:run` enqueues due framework jobs
- `php bin/larafony queue:work --once` can execute at least one queued framework job in local testing

If all of the above work, the local environment is in good enough shape for day-to-day development.

### Queue and Scheduler Flow

MailCamp currently uses two queue-related layers:

- `queue_jobs` - campaign-recipient work created when a campaign is launched
- `jobs` - framework scheduler jobs processed by `queue:work`

Typical local flow:

1. Launch a campaign from the UI
2. Campaign recipients are queued into `queue_jobs`
3. `schedule:run` enqueues the framework sender job
4. `queue:work` executes the sender job
5. The sender job drains due campaign queue entries and creates `messages` / `email_events`

For local development, the safest mental model is:
- `schedule:run` decides what scheduled framework jobs should be queued now
- `queue:work` actually executes those framework jobs
- campaign delivery work is tracked separately in `queue_jobs`

### Queue Throttling

The queue worker respects throttling limits configured in `.env`:
```
QUEUE_THROTTLE_EMAILS_PER_HOUR=100
```

This is intended to improve deliverability by avoiding overly aggressive send rates.

## Architecture

### Larafony Framework Structure

MailCamp follows the **Larafony Framework** architecture with modern PHP 8.5+ features:

```
MailCamp-Larafony/
├── src/
│   ├── Controllers/      # Attribute-based routing controllers
│   ├── Models/           # ORM models with property hooks
│   ├── DTOs/             # Data Transfer Objects with validation
│   ├── Middleware/       # Request middleware
│   └── Console/          # Console commands
├── bootstrap/
│   ├── app.php          # Application bootstrap
│   └── console.php      # Console bootstrap
├── bin/
│   └── larafony         # Console entry point
├── config/              # Configuration files
├── database/
│   ├── migrations/      # Database migrations
│   └── seeders/         # Database seeders
├── public/              # Web root
│   └── index.php        # Application entry point
├── resources/
│   └── views/           # Blade templates
└── storage/             # Logs and cache
```

### Modern PHP 8.5+ Features

**Property Hooks**
```php
public ?string $name {
    get => $this->name ?? null;
    set {
        $this->name = $value;
        $this->markPropertyAsChanged('name');
    }
}
```

**Attribute-Based Routing**
```php
#[Route('/campaigns', 'GET')]
public function index(ServerRequestInterface $request): ResponseInterface
{
    // Controller logic
}
```

**ORM Relationships with Attributes**
```php
#[BelongsTo(
    related: Organization::class,
    foreign_key: 'organization_id',
    local_key: 'id'
)]
public ?Organization $organization { 
    get => $this->relations->getRelation('organization'); 
}
```

**DTOs with Asymmetric Visibility**
```php
#[IsValidated]
public protected(set) string $email {
    get => $this->email;
    set => $this->email = filter_var($value, FILTER_VALIDATE_EMAIL) 
        ? $value 
        : throw new \InvalidArgumentException('Invalid email');
}
```

### Key Components

- **AuthController**: User authentication with Larafony Auth facade
- **CampaignController**: Campaign management with ORM relationships
- **TemplateController**: Template CRUD with variable parsing
- **SmtpSettingController**: SMTP configuration management
- **Models**: Active Record pattern with property hooks and attribute-based relationships
- **DTOs**: Type-safe request validation with property hooks
- **Middleware**: PSR-15 middleware for authentication and authorization

## Daily Developer Workflow

A typical local development session looks like this:

1. Pull the latest changes
2. Run migrations if the branch includes database updates
3. Start the web server
4. Start the queue worker when working on queued or scheduled features
5. Run `schedule:run` manually when testing scheduled behavior locally
6. Make changes
7. Re-test the relevant UI flow or console command

Common commands:

```bash
# Install dependencies
composer install

# Run migrations
php bin/larafony migrate

# Start local server
php -S localhost:8000 -t public

# Start worker
php bin/larafony queue:work

# Enqueue scheduled jobs that are due now
php bin/larafony schedule:run

# Seed local demo data
php bin/larafony app:seed
```

## Troubleshooting

### `php bin/larafony migrate` fails
- Check `.env` database values first
- Make sure MySQL is running
- Make sure the configured database exists or rerun `php bin/larafony app:init`
- Check whether an older migration is failing before the latest one runs

### The app loads but campaigns do not send
- Verify there is at least one active SMTP setting
- Verify the campaign template includes required variables such as `{{unsubscribe_url}}`
- Run `php bin/larafony schedule:run`
- Run `php bin/larafony queue:work`
- Check whether `queue_jobs` rows are being created after launch

### Scheduled campaigns or scheduled sender jobs do not progress
- Confirm the `jobs` table exists and migrations are up to date
- Run `php bin/larafony schedule:run` manually in local development
- Then run `php bin/larafony queue:work`

### Seeder command fails
- Make sure migrations ran first
- Make sure the database user can insert rows
- If needed, retry with a clean database using `php bin/larafony migrate:fresh`

### Login works but campaign creation/editing feels broken
- Check whether SMTP settings exist and are active
- Check whether seeded data is present if you expected demo templates/accounts
- Review browser/server output for validation failures around subject, HTML content, or SMTP selection

## Local Development Notes / Gotchas

- **PHP 8.5+ is required.** Older PHP versions will fail because the project uses modern language features such as property hooks.
- **MySQL is the documented setup path.** The repo config is currently centered on MySQL.
- **SMTP is required for realistic sending tests.** A campaign can be created without actually delivering mail, but real delivery flows depend on valid active SMTP settings.
- **Template variables matter.** Campaign validation expects required variables such as `{{unsubscribe_url}}` and recipient data fields to be present when needed.
- **Scheduler + worker are both part of the delivery flow.** If queued campaigns are not progressing, check both `schedule:run` and `queue:work`.
- **Seed data is for local/demo use only.** It includes default credentials and sample SMTP settings.

## Security

Current security-related behavior includes:
- Password hashing for user credentials
- Encrypted SMTP password storage
- Role-based access control for sensitive operations
- Prepared-statement-based database access through the framework
- Output escaping in rendered views

Areas that should still be verified before production use include CSRF coverage, deployment hardening, and a full security review of mail/webhook flows.

## Contributing

Contributions are welcome! Please follow these guidelines:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For issues and questions, please open an issue on GitHub.
