# MailCamp Subpath Compatibility Audit

## Scope
Audit `/mailcamp` base-path compatibility across routes, redirects, generated links, and tracking endpoints.

## Current status
Subpath support is **mostly present at app level**, but still needs one final real verification pass after controlled testing.

## What is already correct

### Redirect handling
`src/Controllers/Controller.php` prefixes relative redirects with the base path extracted from `APP_URL`.

### View link generation
Most Blade views derive:

```php
$basePath = rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/');
```

and prepend it to links/forms.

### Tracking URL generation
- `OpenTrackingService` builds tracking URLs from full `APP_URL`
- `ClickTrackingService` builds click URLs from full `APP_URL`

That means `/mailcamp/open/...` and `/mailcamp/click/...` can work correctly **if** `APP_URL` includes the subpath.

## Current findings

### Good
- dashboard/campaign/template/auth routes are mostly base-path aware
- controller redirects are base-path aware
- open/click tracking services derive full URLs from `APP_URL`
- event drilldown links currently use the derived Blade base path

### Risks / still needs live verification
1. Tracking correctness depends entirely on `APP_URL` including `/mailcamp`
2. Any external webhook/callback registration must use the subpath-aware public URL
3. Frontend JS fetches need a quick sweep whenever new routes are added
4. Message-level events page is currently JSON/API-oriented and needs later UX polish/testing under subpath

## Rules going forward
1. Never hardcode root-relative production URLs without base-path handling
2. Generate user-facing links from derived base path or full `APP_URL`
3. Generate email tracking URLs from full `APP_URL`
4. When adding JS fetch endpoints, always prefix with the Blade-derived base path
5. Re-test open/click/event endpoints after any route changes

## Remaining verification task
After controlled testing later, verify these paths under deployed `/mailcamp`:
- `/mailcamp/campaigns`
- `/mailcamp/campaigns/{id}`
- `/mailcamp/campaign/{id}/events`
- `/mailcamp/message/{id}/events`
- `/mailcamp/open/{messageId}.png`
- `/mailcamp/click/{messageId}?url=...`
- `/mailcamp/webhook/smtp/report`
