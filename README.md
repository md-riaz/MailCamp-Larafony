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

The application uses the following tables:

- `users` - User authentication with role-based access
- `organizations` - Multi-tenant organizations
- `smtp_settings` - SMTP configuration per organization
- `templates` - HTML email templates with variables
- `campaigns` - Email campaign management
- `recipients` - Recipient lists for campaigns
- `queue_jobs` - DB-backed queue for batch sending
- `logs` - Tracking for opens, clicks, and failures
- `subscriptions` - Email subscription management

## Installation

### Requirements

- **PHP 8.5 or higher** (required for property hooks and modern PHP features)
- MySQL 5.7 or higher / PostgreSQL / SQLite
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
   APP_KEY=your-app-key-here
   
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=mailcamp
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

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

8. **Access the application**
   
   Open your browser and navigate to `http://localhost:8000`

9. **[Optional] Load demo data**
   ```bash
   php bin/larafony app:seed
   ```
   
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

2. **Configure SMTP**: Navigate to SMTP Settings and configure your email server credentials.

3. **Create Templates**: Design email templates with HTML and use variables like `{{name}}`, `{{email}}`, etc.

4. **Create Campaign**: Create a new campaign, select a template, and pick which SMTP account to send from.

5. **Import Recipients**: Upload a CSV file with recipient data (email, name, custom fields).

6. **Launch Campaign**: Launch the campaign to add emails to the queue.

7. **Monitor**: Track opens, clicks, and failures in the campaign dashboard.

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

### Queue Throttling

The queue worker respects throttling limits configured in `.env`:
```
QUEUE_THROTTLE_EMAILS_PER_HOUR=100
```

This ensures good deliverability by not overwhelming email servers.

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

## Security

- Passwords are hashed using bcrypt
- SMTP passwords are encrypted before storage
- Role-based access control for sensitive operations
- CSRF protection (implement as needed)
- SQL injection protection via prepared statements
- XSS protection via output escaping

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
