# MailCamp API Documentation

## Overview

MailCamp uses a traditional MVC web interface. This document outlines the available routes and their functionality.

## Authentication

All routes except `/login` and `/register` require authentication via session cookies.

### Login
- **URL**: `/login`
- **Method**: `POST`
- **Parameters**:
  - `email` (string, required)
  - `password` (string, required)
- **Response**: Redirect to `/dashboard` on success

### Register
- **URL**: `/register`
- **Method**: `POST`
- **Parameters**:
  - `name` (string, required)
  - `email` (string, required)
  - `password` (string, required)
  - `organization_name` (string, required)
- **Response**: Redirect to `/dashboard`, user created as admin

### Logout
- **URL**: `/logout`
- **Method**: `GET`
- **Response**: Redirect to `/login`

## Dashboard

### View Dashboard
- **URL**: `/dashboard` or `/`
- **Method**: `GET`
- **Response**: Dashboard with statistics and recent campaigns

## SMTP Settings

### View SMTP Settings
- **URL**: `/smtp-settings`
- **Method**: `GET`
- **Access**: Admin only
- **Response**: SMTP configuration form

### Save SMTP Settings
- **URL**: `/smtp-settings`
- **Method**: `POST`
- **Access**: Admin only
- **Parameters**:
  - `host` (string, required)
  - `port` (integer, required)
  - `encryption` (enum: tls|ssl|none, required)
  - `username` (string, required)
  - `password` (string, required if new)
  - `from_email` (string, required)
  - `from_name` (string, required)
- **Response**: Redirect to `/smtp-settings` with success message

### Test SMTP Connection
- **URL**: `/smtp-settings/test`
- **Method**: `POST`
- **Access**: Admin only
- **Response**: JSON with success/error message

## Templates

### List Templates
- **URL**: `/templates`
- **Method**: `GET`
- **Response**: List of all templates for organization

### Create Template Form
- **URL**: `/templates/create`
- **Method**: `GET`
- **Response**: Template creation form

### Store Template
- **URL**: `/templates`
- **Method**: `POST`
- **Parameters**:
  - `name` (string, required)
  - `subject` (string, required)
  - `html_content` (text, required)
- **Response**: Redirect to `/templates` with success message

### Edit Template Form
- **URL**: `/templates/:id/edit`
- **Method**: `GET`
- **Response**: Template edit form

### Update Template
- **URL**: `/templates/:id`
- **Method**: `POST`
- **Parameters**:
  - `name` (string, required)
  - `subject` (string, required)
  - `html_content` (text, required)
- **Response**: Redirect to `/templates` with success message

### Delete Template
- **URL**: `/templates/:id`
- **Method**: `DELETE` (via POST with `_method=DELETE`)
- **Response**: Redirect to `/templates` with success message

## Campaigns

### List Campaigns
- **URL**: `/campaigns`
- **Method**: `GET`
- **Response**: List of all campaigns for organization

### Create Campaign Form
- **URL**: `/campaigns/create`
- **Method**: `GET`
- **Response**: Campaign creation form

### Store Campaign
- **URL**: `/campaigns`
- **Method**: `POST`
- **Parameters**:
  - `name` (string, required)
  - `template_id` (integer, required)
- **Response**: Redirect to campaign detail page

### View Campaign
- **URL**: `/campaigns/:id`
- **Method**: `GET`
- **Response**: Campaign details with statistics

### Import Recipients
- **URL**: `/campaigns/:id/recipients`
- **Method**: `POST`
- **Parameters**:
  - `recipients_file` (file, required) - CSV format
- **CSV Format**: `email,name,custom_field1,custom_field2,...`
- **Response**: Redirect to campaign detail with import count

### Launch Campaign
- **URL**: `/campaigns/:id/launch`
- **Method**: `POST`
- **Response**: Redirect to campaign detail, jobs queued

## Tracking

### Track Email Open
- **URL**: `/track/open/:campaign_id/:recipient_id/:token`
- **Method**: `GET`
- **Response**: 1x1 transparent GIF image
- **Side Effect**: Logs open event, updates recipient

### Track Link Click
- **URL**: `/track/click/:campaign_id/:recipient_id/:token`
- **Method**: `GET`
- **Parameters**:
  - `url` (query string, required) - Original URL
- **Response**: Redirect to original URL
- **Side Effect**: Logs click event, updates recipient

### Unsubscribe
- **URL**: `/unsubscribe/:token`
- **Method**: `GET`
- **Parameters**:
  - `campaign_id` (query string, optional)
  - `recipient_id` (query string, optional)
- **Response**: Unsubscribe confirmation message
- **Side Effect**: Updates subscription status, logs event

## Database Schema

### Users Table
```sql
- id (INT, PRIMARY KEY)
- organization_id (INT, FOREIGN KEY)
- name (VARCHAR)
- email (VARCHAR, UNIQUE)
- password (VARCHAR, hashed)
- role (ENUM: admin|manager|user)
- is_active (BOOLEAN)
- created_at, updated_at (TIMESTAMP)
```

### Organizations Table
```sql
- id (INT, PRIMARY KEY)
- name (VARCHAR)
- slug (VARCHAR, UNIQUE)
- domain (VARCHAR)
- is_active (BOOLEAN)
- created_at, updated_at (TIMESTAMP)
```

### SMTP Settings Table
```sql
- id (INT, PRIMARY KEY)
- organization_id (INT, FOREIGN KEY)
- host, port, encryption (VARCHAR/INT/ENUM)
- username, password (VARCHAR, encrypted)
- from_email, from_name (VARCHAR)
- is_active (BOOLEAN)
- created_at, updated_at (TIMESTAMP)
```

### Templates Table
```sql
- id (INT, PRIMARY KEY)
- organization_id (INT, FOREIGN KEY)
- name, subject (VARCHAR)
- html_content (TEXT)
- variables (TEXT, JSON)
- is_active (BOOLEAN)
- created_at, updated_at (TIMESTAMP)
```

### Campaigns Table
```sql
- id (INT, PRIMARY KEY)
- organization_id (INT, FOREIGN KEY)
- template_id (INT, FOREIGN KEY)
- name (VARCHAR)
- status (ENUM: draft|scheduled|sending|sent|paused|cancelled)
- scheduled_at, started_at, completed_at (TIMESTAMP)
- total_recipients, sent_count, failed_count (INT)
- created_by (INT, FOREIGN KEY to users)
- created_at, updated_at (TIMESTAMP)
```

### Recipients Table
```sql
- id (INT, PRIMARY KEY)
- organization_id, campaign_id (INT, FOREIGN KEY)
- email, name (VARCHAR)
- custom_data (TEXT, JSON)
- status (ENUM: pending|sent|failed|bounced|unsubscribed)
- sent_at, opened_at, clicked_at (TIMESTAMP)
- created_at, updated_at (TIMESTAMP)
```

### Queue Jobs Table
```sql
- id (INT, PRIMARY KEY)
- organization_id, campaign_id, recipient_id (INT, FOREIGN KEY)
- payload (TEXT, JSON)
- attempts (INT)
- status (ENUM: pending|processing|completed|failed)
- available_at, reserved_at, completed_at (TIMESTAMP)
- created_at (TIMESTAMP)
```

### Logs Table
```sql
- id (INT, PRIMARY KEY)
- organization_id, campaign_id, recipient_id (INT, FOREIGN KEY)
- event_type (ENUM: sent|opened|clicked|bounced|failed|unsubscribed)
- event_data (TEXT, JSON)
- user_agent, ip_address (VARCHAR)
- created_at (TIMESTAMP)
```

### Subscriptions Table
```sql
- id (INT, PRIMARY KEY)
- organization_id (INT, FOREIGN KEY)
- email, name (VARCHAR)
- status (ENUM: subscribed|unsubscribed|bounced)
- subscription_date, unsubscribe_date (TIMESTAMP)
- unsubscribe_token (VARCHAR, UNIQUE)
- created_at, updated_at (TIMESTAMP)
```

## Template Variables

Templates support these built-in variables:
- `{{name}}` - Recipient name
- `{{email}}` - Recipient email
- `{{unsubscribe_url}}` - Unsubscribe link (auto-generated)

Custom variables from CSV import are also available.

## Error Codes

- **200**: Success
- **302**: Redirect (typical for form submissions)
- **404**: Not Found (invalid route or resource)
- **500**: Server Error (check logs)

## Rate Limiting

Configured via `.env`:
```
QUEUE_THROTTLE_EMAILS_PER_HOUR=100
```

The queue worker processes emails according to this limit to maintain deliverability.

## Security Notes

1. **Authentication**: Session-based, stored in PHP sessions
2. **Authorization**: Role-based (admin/manager/user)
3. **Multi-tenancy**: All queries filtered by organization_id
4. **CSRF**: Not implemented (add in production)
5. **Rate Limiting**: Not implemented (add in production)

## Queue Worker

Background process that sends emails:

```bash
php cli/queue-worker.php
```

- Processes pending jobs
- Respects throttle limits
- Auto-retries failed jobs (max 3 attempts)
- Updates campaign statistics
- Logs all events

## Integration Examples

### Custom Email Tracking

In your templates, tracking is automatic:
```html
<!-- Open tracking added automatically -->
<!-- Link tracking: use normal links -->
<a href="https://example.com">Click here</a>
```

### Webhook Integration

Currently not supported. Future enhancement could add:
- Bounce notifications
- Complaint notifications
- Delivery status

## Support

For API questions or issues:
- GitHub Issues: https://github.com/md-riaz/MailCamp-Larafony/issues
- Documentation: See README.md, QUICKSTART.md
