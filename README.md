# MailCamp - Multi-tenant Email Campaign Manager

MailCamp is a powerful multi-tenant mail campaign manager built on Larafony MVC framework. Each organization can configure its own SMTP settings, design custom email templates, manage recipients, and track campaign performance.

## Features

- **Multi-tenancy**: Support for multiple organizations with isolated data
- **SMTP Configuration**: Each organization configures its own SMTP settings (host, port, encryption, credentials)
- **Role-based Access Control**: Admin, Manager, and User roles with different permissions
- **Template Designer**: Create HTML email templates with variable placeholders ({{variable}})
- **Campaign Management**: Create, schedule, and launch email campaigns
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

- PHP 7.4 or higher
- MySQL 5.7 or higher
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
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=mailcamp
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

4. **Run migrations**
   ```bash
   php cli/migrate.php
   ```

5. **Start the application**
   
   Using PHP built-in server:
   ```bash
   php -S localhost:8000 -t public
   ```
   
   Or configure your web server to point to the `public` directory.

6. **Start the queue worker** (in a separate terminal)
   ```bash
   php cli/queue-worker.php
   ```

7. **Access the application**
   
   Open your browser and navigate to `http://localhost:8000`

## Usage

### First Time Setup

1. **Register**: Create an account at `/register`. The first user becomes an admin for their organization.

2. **Configure SMTP**: Navigate to SMTP Settings and configure your email server credentials.

3. **Create Templates**: Design email templates with HTML and use variables like `{{name}}`, `{{email}}`, etc.

4. **Create Campaign**: Create a new campaign and select a template.

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

### MVC Structure

```
MailCamp-Larafony/
├── app/
│   ├── Controllers/      # Request handlers
│   ├── Models/           # Database models
│   ├── Middleware/       # Request middleware
│   └── Workers/          # Background workers
├── config/               # Configuration files
├── database/
│   ├── migrations/       # Database migrations
│   └── seeds/            # Database seeders
├── public/               # Web root
├── resources/
│   ├── views/            # View templates
│   └── assets/           # CSS, JS, images
├── routes/               # Route definitions
├── storage/              # Logs and cache
└── cli/                  # CLI scripts
```

### Key Components

- **AuthController**: Handles user registration, login, and role-based access
- **CampaignController**: Manages campaign creation, recipient import, and launching
- **TemplateController**: Template CRUD operations with variable parsing
- **SmtpSettingController**: SMTP configuration management
- **QueueWorker**: Background worker for processing email queue with throttling
- **TrackingController**: Handles open/click tracking and unsubscribes

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
