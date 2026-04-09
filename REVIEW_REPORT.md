# MailCamp-Larafony Code Review Report

**Date:** 2026-04-07  
**Scope:** Multi-SMTP campaign platform reliability, security, and production readiness  
**Reviewed:** All source files in `src/`, `database/migrations/`, `config/`, framework auth layer

---

## Architecture Summary

MailCamp is a custom PHP 8.4 framework ("Larafony") email campaign platform. The architecture:

- **Multi-tenant via organizations:** Users belong to organizations through `user_profiles`. SMTP settings, campaigns, templates, recipients, and observability data are scoped by `organization_id`.
- **Multi-SMTP:** Organizations can add multiple SMTP accounts (`smtp_settings`). Campaigns reference a specific SMTP account via `smtp_setting_id` (added in a later migration). Fallback logic picks the first active SMTP if the campaign's designated one is missing.
- **Campaign flow:** Draft -> Scheduled -> Sending -> Sent/Failed. Queue jobs (`queue_jobs`) are created per-recipient, processed by a CLI command (`app:send-queued-campaign-messages`). A `messages` table tracks per-recipient send status.
- **Observability:** Full event tracking pipeline via `email_events`, `bounces`, `links`, `webhooks` tables. Open/click tracking via pixel injection and link rewriting. Webhook ingestion for SMTP reports and third-party providers (SendGrid, SES, Mailgun).
- **Template system:** `{{variable}}` merge tags with `str_replace`. Templates are reusable across campaigns. CKEditor 4 integration with image upload.

### Strengths
- Good observability infrastructure with denormalized `organization_id` across all event tables for fast dashboard queries
- Comprehensive indexing strategy in migrations
- Webhook idempotency via `idempotency_key` with UNIQUE constraint
- Campaign safety service with bounce/complaint rate auto-pause thresholds
- Deliverability advisor with DNS checks (MX, SPF, DKIM, DMARC)
- Session fixation protection (`session_regenerate_id` on login)
- Upload validation (extension whitelist, size limit, random filenames)

---

## Critical Issues

### C1. SMTP Passwords Stored as Base64 (Not Encryption)
**File:** `src/Models/SmtpSetting.php`, lines 99-107  
**Impact:** Anyone with database read access (SQL injection, backup leak, shared hosting) gets all SMTP credentials instantly. `base64_encode` is encoding, not encryption.

```php
public static function encryptPassword(string $password): string
{
    return base64_encode($password); // NOT encryption
}
```

**Fix:** Use `openssl_encrypt`/`sodium_crypto_secretbox` with a key from env var. At minimum, use the framework's encryption service if one exists.

---

### C2. No CSRF Protection on Any Forms
**File:** All controllers accepting POST (every controller)  
**Impact:** All state-changing operations are vulnerable to cross-site request forgery. An attacker can craft a page that submits forms to create campaigns, add SMTP settings, upload files, or start campaign sends on behalf of a logged-in user.

No CSRF middleware, no CSRF tokens in forms, no `_token` field validation anywhere in the codebase. The session config has `samesite: Lax` which mitigates some vectors, but not all (e.g., top-level navigations, GET-to-POST escalation).

**Fix:** Implement CSRF token middleware. Every form must include a token; every POST/PUT/DELETE must validate it.

---

### C3. Template Rendering Has No Output Escaping (Stored XSS)
**File:** `src/Models/Template.php`, lines 87-103  
**Impact:** Merge variables are injected via raw `str_replace` into HTML content. If recipient `custom_data` contains malicious HTML/JS (e.g., `{"name": "<script>alert(1)</script>"}`), it gets embedded directly into the email body. While email clients often strip scripts, this is a stored XSS vector in the web preview and a potential phishing vector.

```php
public function render(array $data = []): string
{
    $content = $this->html_content;
    foreach ($data as $key => $value) {
        $content = str_replace('{{' . $key . '}}', $value, $content); // No escaping
    }
    return $content;
}
```

**Fix:** `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')` for each merge variable value before substitution.

---

### C4. Race Condition in Queue Job Processing
**File:** `src/Services/CampaignMessageLifecycleService.php`, lines 120-139  
**Impact:** Multiple workers running `sendQueuedMessages` simultaneously will both SELECT the same pending jobs, both mark them as processing, and both attempt to send the same emails. There is no row-level locking, no `SELECT ... FOR UPDATE`, and no atomic status transition.

```php
$queueJobs = $query->orderBy('available_at')->get(); // No locking
// ...
$queueJob->status = 'processing'; // Non-atomic update
$queueJob->save();
```

**Fix:** Use `SELECT ... FOR UPDATE SKIP LOCKED` or an atomic `UPDATE queue_jobs SET status='processing', reserved_at=NOW() WHERE status='pending' AND id=? LIMIT 1` to claim jobs.

---

### C5. `sent_count` / `failed_count` Subject to Race Conditions
**File:** `src/Services/CampaignMessageLifecycleService.php`, lines 242, 274  
**Impact:** Campaign counters are incremented via read-increment-write pattern. With concurrent workers, counts will be inaccurate.

```php
$campaign->sent_count = (int) $campaign->sent_count + 1; // Read-modify-write
$campaign->save();
```

**Fix:** Use `UPDATE campaigns SET sent_count = sent_count + 1 WHERE id = ?` (atomic SQL increment).

---

### C6. Image Upload Validates Extension Only, Not Content
**File:** `src/Controllers/TemplateController.php`, lines 174-182  
**Impact:** An attacker can upload a PHP webshell renamed to `.jpg`. If the web server is misconfigured to execute PHP in the uploads directory, this is remote code execution. Even without PHP execution, polyglot files (valid image headers + embedded scripts) can be served to exploit other vulnerabilities.

```php
$extension = strtolower(pathinfo($clientFilename, PATHINFO_EXTENSION));
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
// No MIME type check, no magic bytes check, no getimagesize() validation
```

**Fix:** Validate with `getimagesize()` or `finfo_file()` to confirm actual image content. Add `.htaccess` / nginx config to disable PHP execution in uploads directory.

---

### C7. Webhook Endpoints Accept Unsigned Payloads When Secret Not Set
**File:** `src/Services/WebhookSecurityService.php`, lines 22-30  
**Impact:** If `SMTP_REPORT_WEBHOOK_SECRET` env var is empty (which it is by default), all webhook payloads are accepted without any authentication. Anyone who discovers the webhook URL can inject fake bounce/delivery events, corrupt campaign metrics, and trigger recipient status changes.

```php
$secret = trim((string) (getenv('SMTP_REPORT_WEBHOOK_SECRET') ?: ''));
if ($secret !== '') {
    // ... signature validation
}
// If secret is empty, falls through to: 'unsigned_allowed'
```

**Fix:** Default to rejecting unsigned webhooks. Require the secret to be configured before accepting any webhook data.

---

## Important Issues

### I1. No Recipient Deduplication Within a Campaign
**File:** `src/Controllers/CampaignController.php`, line 540 (`storeRecipientsFromCsv`)  
**Impact:** If a CSV file contains the same email address twice, two recipients are created and both will receive the email. No `UNIQUE(campaign_id, email)` constraint exists on the `recipients` table.

**Fix:** Add `UNIQUE(campaign_id, email)` to `recipients` table. Deduplicate in the CSV import logic.

---

### I2. No Unsubscribe Check Before Sending
**File:** `src/Services/CampaignMessageLifecycleService.php`, lines 59-110 (queueCampaign) and 164-257 (sendQueuedMessage)  
**Impact:** Neither the queuing nor the sending process checks whether a recipient has unsubscribed (via `subscriptions` table) or has previously bounced. This violates CAN-SPAM/GDPR requirements and will damage sender reputation.

**Fix:** Before queuing, cross-reference recipients against `subscriptions` where `status = 'unsubscribed'` or `'bounced'` for the same organization.

---

### I3. No Rate Limiting Per SMTP Account
**File:** `src/Services/CampaignMessageLifecycleService.php`, lines 118-162  
**Impact:** The send loop fires as fast as possible with no throttling. SMTP providers have rate limits (e.g., 100/hour for Gmail, 500/hour for many shared hosts). Exceeding these causes mass failures, temporary bans, or permanent blocks.

**Fix:** Add configurable rate limits per SMTP setting (e.g., `max_per_hour`, `max_per_minute`). Throttle the send loop accordingly.

---

### I4. No SMTP Rotation or Failover
**File:** `src/Services/CampaignMessageLifecycleService.php`, lines 178-192  
**Impact:** If the designated SMTP fails (rate limited, credentials expired, server down), every remaining recipient in the campaign fails. There's no attempt to try another SMTP account.

**Fix:** Implement failover: on SMTP connection failure, try the next active SMTP for the organization. Optionally support round-robin distribution across SMTPs for large campaigns.

---

### I5. `SmtpReportController` Processes Payload Before Checking Security Result
**File:** `src/Controllers/SmtpReportController.php`, lines 34-44  
**Impact:** The service processes and persists the webhook data (`ingestWithSecurity`) before the controller checks whether security passed. The rejected webhook still gets stored in the database.

```php
$result = $this->service->ingestWithSecurity($raw, $security, $source); // Processes first
if (($security['ok'] ?? false) !== true) {
    return $this->json([...], 401); // Rejects after
}
```

**Fix:** Check `$security['ok']` before calling ingest, or ensure the service properly handles the rejection (it does store a "rejected" webhook, but this is still unnecessary DB writes for unauthenticated requests and could be a DoS vector).

---

### I6. Campaign Status Enum Mismatch Between Schema and Code
**File:**  
- Migration `2024_01_01_000010_create_campaigns_table.php`, line 21: `['draft', 'scheduled', 'sending', 'sent', 'paused', 'cancelled']`  
- `src/Controllers/CampaignController.php`, lines 58-64: `['draft', 'active', 'sending', 'sent', 'failed']`  
- `src/Services/CampaignMessageLifecycleService.php`, line 309: sets status to `'active'`  
- `src/Services/CampaignMessageLifecycleService.php`, line 314: sets status to `'failed'`  

**Impact:** The code writes `'active'` and `'failed'` statuses that don't exist in the database ENUM. MySQL will reject these writes with a data truncation error, silently set them to `''`, or throw depending on strict mode.

**Fix:** Align the database ENUM to include all statuses the code uses: `['draft', 'active', 'scheduled', 'sending', 'sent', 'paused', 'cancelled', 'failed']`.

---

### I7. No Foreign Keys on `campaigns`, `recipients`, `queue_jobs`, `smtp_settings` Tables
**File:** Migrations `000008`, `000010`, `000011`, `000012`  
**Impact:** No referential integrity. Orphaned records when organizations or campaigns are deleted. The observability tables (created later) have proper FKs, but the core tables don't.

Missing FKs:
- `campaigns.organization_id` -> `organizations.id`
- `campaigns.template_id` -> `templates.id`
- `campaigns.smtp_setting_id` -> `smtp_settings.id`
- `campaigns.created_by` -> `users.id`
- `recipients.organization_id` -> `organizations.id`
- `recipients.campaign_id` -> `campaigns.id`
- `queue_jobs.campaign_id` -> `campaigns.id`
- `queue_jobs.recipient_id` -> `recipients.id`
- `smtp_settings.organization_id` -> `organizations.id`

---

### I8. Provider Webhook Ingestion Only Normalizes, Never Persists
**File:** `src/Services/ProviderWebhookIngestionService.php`, lines 12-52  
**Impact:** The SendGrid/SES/Mailgun webhook endpoint normalizes the payload into a standard format but never creates `EmailEvent`, `Bounce`, `Message` updates, or `Webhook` records. The normalized data is returned in the HTTP response and discarded. These webhooks are effectively no-ops.

**Fix:** After normalizing, pass each event through the same persistence pipeline as `SmtpReportIngestionService` to update message statuses, create email events, and record bounces.

---

### I9. Subscription Model Field Name Mismatch
**File:** `src/Models/Subscription.php`, lines 14-16 vs migration `000014`, lines 22-24  
**Impact:** The model uses `token`, `subscribed_at`, `unsubscribed_at` but the schema has `unsubscribe_token`, `subscription_date`, `unsubscribe_date`. The `fillable` array references field names that don't match the database columns. Any save/fill operation on these fields silently fails.

---

### I10. No Max Retry Limit on Queue Jobs
**File:** `src/Services/CampaignMessageLifecycleService.php`, lines 131-139  
**Impact:** Failed queue jobs are marked as `failed` and never retried. Conversely, there's no maximum attempt limit checked before processing -- the `attempts` counter is incremented but never compared against a threshold. If a job somehow stays in `pending` status after a failure (e.g., process crash between status update and save), it will be retried indefinitely.

**Fix:** Add `MAX_ATTEMPTS` constant (e.g., 3). Skip jobs where `$queueJob->attempts >= MAX_ATTEMPTS` and mark them as permanently failed.

---

## Recommendations

### R1. Add Database Transactions Around Send Operations
The send flow (update queue job -> send email -> update message -> update recipient -> update campaign) spans 5+ individual saves with no transaction wrapping. A crash mid-sequence leaves data in an inconsistent state.

### R2. Batch Queue Job Fetching with LIMIT
**File:** `src/Services/CampaignMessageLifecycleService.php`, line 128  
Currently loads ALL pending jobs into memory at once. With 10,000+ recipients, this causes memory exhaustion. Add a `LIMIT` to the query and process in batches.

### R3. Add Email Validation on Recipient Import
**File:** `src/Controllers/CampaignController.php`, `storeRecipientsFromCsv`  
No validation that imported email addresses are syntactically valid. Invalid addresses waste SMTP quota and damage sender reputation.

### R4. Add List-Unsubscribe Header to Emails
**File:** `src/Services/CampaignMessageLifecycleService.php`, line 212  
The `Email` object is constructed without `List-Unsubscribe` or `List-Unsubscribe-Post` headers. These are required by Gmail/Yahoo as of 2024 for bulk senders.

### R5. Add Campaign Pause/Resume Functionality
The `canStart()` method accepts `'paused'` status, and the schema includes it, but there's no controller action to pause a running campaign. A mid-send pause would require marking remaining queue jobs as paused.

### R6. Add Template Preview with Sample Data
No endpoint exists to preview a rendered template with sample merge variables before launching a campaign.

### R7. Add Audit Logging
No audit trail for who created/modified campaigns, SMTP settings, or templates. Critical for multi-user organizations.

### R8. Add Health Check Endpoint
No health check route for monitoring. Should verify database connectivity and queue processing status.

### R9. Session Cookie Security
**File:** `config/session.php`, line 43  
The `secure` flag defaults to checking `$_SERVER['HTTPS']` which may not be set behind reverse proxies. Should also check `X-Forwarded-Proto` header.

### R10. Add Password Strength Validation on Registration
**File:** `src/Controllers/Auth/RegisterController.php`, line 70  
The password is saved directly from the DTO with no minimum length or complexity requirements.

### R11. Add Scheduled Campaign Auto-Start
The schema supports `scheduled_at` and the status `scheduled`, but there's no cron job or command that checks for campaigns past their scheduled time and starts them automatically.

### R12. Consider Moving Uploads to Object Storage
**File:** `src/Controllers/TemplateController.php`, line 191  
Uploads go to local filesystem (`public/uploads/`). For production with multiple web servers, this breaks. Use S3-compatible object storage.

### R13. Add Organization-Scoped Unique Constraint on SMTP Settings
Currently, nothing prevents duplicate SMTP configurations within the same organization. Consider unique on `(organization_id, host, port, username)`.

### R14. `$_POST` Direct Access
**File:** `src/Controllers/SmtpSettingController.php`, line 69  
Uses `$_POST['is_active']` directly instead of the DTO/PSR-7 request object. Inconsistent with the rest of the codebase and bypasses any middleware processing.

---

## Database Schema Issues Summary

| Table | Issue | Severity |
|-------|-------|----------|
| `campaigns` | ENUM missing `'active'` and `'failed'` statuses | **Critical** |
| `campaigns` | No FK on `organization_id`, `template_id`, `created_by` | Important |
| `campaigns` | `smtp_setting_id` added in separate migration, no FK | Important |
| `recipients` | No `UNIQUE(campaign_id, email)` for dedup | Important |
| `recipients` | No FK on `organization_id`, `campaign_id` | Important |
| `queue_jobs` | No FK on `campaign_id`, `recipient_id`, `organization_id` | Important |
| `smtp_settings` | No FK on `organization_id` | Important |
| `subscriptions` | Column names don't match model property names | **Critical** |
| `queue_jobs` | No compound index on `(status, available_at)` for queue polling | Minor |

---

## Files Reviewed

| File | Lines |
|------|-------|
| `src/Controllers/CampaignController.php` | 590 |
| `src/Controllers/SmtpSettingController.php` | 220 |
| `src/Controllers/TemplateController.php` | 237 |
| `src/Controllers/EventController.php` | 100 |
| `src/Controllers/ProviderWebhookController.php` | 60 |
| `src/Controllers/SmtpReportController.php` | 51 |
| `src/Controllers/Auth/LoginController.php` | 54 |
| `src/Controllers/Auth/RegisterController.php` | 89 |
| `src/Models/Campaign.php` | 253 |
| `src/Models/SmtpSetting.php` | 117 |
| `src/Models/Template.php` | 125 |
| `src/Models/Recipient.php` | 142 |
| `src/Models/Message.php` | 40 |
| `src/Models/QueueJob.php` | 96 |
| `src/Models/EmailEvent.php` | 40 |
| `src/Models/Bounce.php` | 40 |
| `src/Models/Subscription.php` | 93 |
| `src/Models/Link.php` | 35 |
| `src/Models/Webhook.php` | 50 |
| `src/Models/User.php` | ~40 |
| `src/Services/CampaignMessageLifecycleService.php` | 380 |
| `src/Services/ObservabilityService.php` | 280 |
| `src/Services/CampaignSafetyService.php` | 131 |
| `src/Services/SmtpReportIngestionService.php` | 234 |
| `src/Services/ProviderWebhookIngestionService.php` | 69 |
| `src/Services/WebhookSecurityService.php` | 95 |
| `src/Services/TemplateValidationService.php` | ~60 |
| `src/Services/ClickTrackingService.php` | ~260 |
| `src/Services/OpenTrackingService.php` | ~380 |
| `src/Services/DeliverabilityAdvisorService.php` | ~60 |
| `src/Console/Commands/SendQueuedCampaignMessagesCommand.php` | 40 |
| `database/migrations/` (all) | ~700 |
| `config/session.php` | 55 |
| `framework/src/Larafony/Auth/UserManager.php` | ~110 |

---

## Priority Action Items

1. **Encrypt SMTP passwords** (C1) -- data breach risk
2. **Add CSRF protection** (C2) -- all forms vulnerable
3. **Fix campaign status ENUM mismatch** (I6) -- sends will fail
4. **Fix Subscription model field name mismatch** (I9) -- unsubscribe broken
5. **Add row-level locking to queue processing** (C4) -- duplicate sends
6. **Escape merge variable output** (C3) -- XSS
7. **Add unsubscribe/bounce check before sending** (I2) -- legal compliance
8. **Validate upload file content** (C6) -- potential RCE
9. **Require webhook secret** (C7) -- data integrity
10. **Wire provider webhook persistence** (I8) -- SendGrid/SES/Mailgun events lost
