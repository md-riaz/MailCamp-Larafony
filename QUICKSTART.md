# Quick Start Guide

Get MailCamp up and running in 5 minutes!

## Prerequisites

- PHP 7.4+ with PDO and MySQL extensions
- MySQL 5.7+
- Composer (optional, for dependencies)

## Installation

### 1. Clone and Setup

```bash
# Clone the repository
git clone https://github.com/md-riaz/MailCamp-Larafony.git
cd MailCamp-Larafony

# Install dependencies (if using composer)
composer install

# Or manually install PHPMailer
# Download from: https://github.com/PHPMailer/PHPMailer
# Extract to: vendor/phpmailer/phpmailer/
```

### 2. Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Edit configuration
nano .env
```

Set your database credentials:
```env
DB_HOST=127.0.0.1
DB_DATABASE=mailcamp
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. Create Database and Run Migrations

```bash
# Create database (via MySQL CLI)
mysql -u root -p
CREATE DATABASE mailcamp;
EXIT;

# Run migrations
php cli/migrate.php
```

### 4. Start the Application

```bash
# Start PHP development server
php -S localhost:8000 -t public

# In another terminal, start the queue worker
php cli/queue-worker.php
```

### 5. Access the Application

Open your browser and navigate to: http://localhost:8000

## First Steps

### 1. Register Your Organization

- Click "Register here" on the login page
- Fill in your details:
  - Full Name
  - Email Address
  - Password
  - Organization Name
- Click "Register"
- You'll be automatically logged in as admin

### 2. Configure SMTP Settings

- Navigate to "SMTP Settings" in the menu
- Enter your email server details:
  - SMTP Host (e.g., smtp.gmail.com)
  - SMTP Port (587 for TLS, 465 for SSL)
  - Encryption (TLS recommended)
  - Username (your email)
  - Password (your email password or app password)
  - From Email (sender email)
  - From Name (sender name)
- Click "Save Settings"
- Optionally click "Test Connection" to verify

**Gmail Users**: Use an [App Password](https://support.google.com/accounts/answer/185833) instead of your regular password.

### 3. Create Your First Email Template

- Go to "Templates" → "Create New Template"
- Enter:
  - Template Name: "Welcome Email"
  - Subject: "Welcome {{name}}!"
  - HTML Content:
```html
<html>
<body>
    <h1>Hello {{name}}!</h1>
    <p>Welcome to our newsletter. Your email is {{email}}.</p>
    <p>Best regards,<br>The Team</p>
</body>
</html>
```
- Click "Create Template"

### 4. Create a Campaign

- Go to "Campaigns" → "Create New Campaign"
- Enter:
  - Campaign Name: "Welcome Campaign"
  - Select your template
- Click "Create Campaign"

### 5. Import Recipients

On the campaign detail page:

1. Create a CSV file (recipients.csv):
```csv
email,name
john@example.com,John Doe
jane@example.com,Jane Smith
```

2. Click "Import Recipients"
3. Upload your CSV file
4. Click "Import Recipients"

### 6. Launch Campaign

- Review your campaign details
- Click "Launch Campaign"
- The queue worker will process emails based on your throttle settings

### 7. Monitor Results

- View campaign statistics:
  - Total sent
  - Failed emails
  - Open rates
  - Click rates
- Check the "Logs" for detailed tracking

## Common SMTP Providers

### Gmail
```
Host: smtp.gmail.com
Port: 587
Encryption: TLS
Username: your-email@gmail.com
Password: your-app-password
```

### Outlook/Office365
```
Host: smtp.office365.com
Port: 587
Encryption: TLS
Username: your-email@outlook.com
Password: your-password
```

### SendGrid
```
Host: smtp.sendgrid.net
Port: 587
Encryption: TLS
Username: apikey
Password: your-sendgrid-api-key
```

### Mailgun
```
Host: smtp.mailgun.org
Port: 587
Encryption: TLS
Username: your-mailgun-username
Password: your-mailgun-password
```

### Amazon SES
```
Host: email-smtp.us-east-1.amazonaws.com
Port: 587
Encryption: TLS
Username: your-ses-smtp-username
Password: your-ses-smtp-password
```

## Template Variables

Use these in your email templates and subjects:

- `{{name}}` - Recipient's name
- `{{email}}` - Recipient's email
- Any custom fields from your CSV import

Example CSV with custom fields:
```csv
email,name,company,position
john@example.com,John Doe,Acme Corp,Manager
```

Then use in template:
```html
<p>Hello {{name}} from {{company}}!</p>
<p>Your position: {{position}}</p>
```

## Troubleshooting

### "Database connection failed"
- Check your `.env` file has correct database credentials
- Ensure MySQL is running: `sudo systemctl status mysql`
- Verify database exists: `mysql -u root -p -e "SHOW DATABASES;"`

### "SMTP connection failed"
- Verify your SMTP credentials
- Check firewall isn't blocking SMTP ports
- For Gmail, enable "Less secure app access" or use App Password
- Test connection with the "Test Connection" button

### "Queue worker not processing"
- Ensure queue worker is running: `php cli/queue-worker.php`
- Check for errors in the worker output
- Verify SMTP settings are configured

### "Emails not sending"
- Check queue_jobs table: `SELECT * FROM queue_jobs WHERE status='failed';`
- Review logs table: `SELECT * FROM logs WHERE event_type='failed';`
- Verify throttle settings aren't too restrictive

### "Permission denied on storage"
```bash
chmod -R 775 storage
chown -R www-data:www-data storage  # Linux/Mac
```

## Tips

1. **Test with yourself first**: Add your own email as recipient to test
2. **Start with low throttle**: Begin with 10-20 emails per hour, increase gradually
3. **Monitor deliverability**: Check spam scores and bounce rates
4. **Use unsubscribe links**: Add `{{unsubscribe_url}}` to templates
5. **Segment your lists**: Create separate campaigns for different audiences

## Next Steps

- Read the full [README.md](README.md) for detailed features
- Review [SECURITY.md](SECURITY.md) before production deployment
- Check [DEPLOYMENT.md](DEPLOYMENT.md) for production setup
- Join our community for support

## Getting Help

- **Documentation**: Check README.md and other .md files
- **Issues**: Open an issue on GitHub
- **Email**: Contact support@example.com

Happy campaigning! 🚀
