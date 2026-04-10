# CHANGELOG

## 2026-04-10

### Fixed — Production Readiness (LOW priority)
- **Removed duplicate model file** at `src/Models/App/Models/Audience.php` that shadowed the canonical `src/Models/Audience.php`.
- **Differentiated `isManager()` from `isAdmin()`** in `User` model — `isManager()` now includes the `Manager` role.
- **Webhooks reject unsigned payloads in production** — `WebhookSecurityService` now returns an error when `APP_ENV=production` and no `SMTP_REPORT_WEBHOOK_SECRET` is configured.
- **Unsubscribe redirect no longer leaks email in URL** — removed `&email=` query parameter from redirect in `UnsubscribeController`.
- **Services moved to constructor properties** — eliminated ~18 inline `new Service()` calls across 6 controllers (`AudienceController`, `DashboardController`, `EventController`, `OpenTrackingController`, `ClickTrackingController`, `CampaignController`).
- **Updated `.env.example`** with `APP_KEY` and `SMTP_REPORT_WEBHOOK_SECRET` placeholder entries.

### Fixed — Production Readiness (MEDIUM priority)
- **Conditional `display_errors`** — `public/index.php` now only enables error display when `APP_DEBUG=true`; production suppresses browser output and logs instead.
- **Removed `loadFileRoutes()` from bootstrap** — eliminated undefined method call; `routes/api.php` deleted since audience routes already use controller attributes.
- **Added pagination to list endpoints** — `CampaignController`, `TemplateController`, and `SmtpSettingController` now paginate with `PER_PAGE = 25` and offset-based queries.
- **Added CSV/text import limits** — max file size of 5MB and max 10,000 recipients per import; rows beyond the limit are counted as skipped.
- **Removed debug logging and forced exception** from audience controller code paths.

### Added
- **`framework_analysis.md`** — comprehensive Larafony framework usage analysis and production readiness audit with 25 identified issues across CRITICAL/HIGH/MEDIUM/LOW severity levels.

## Unreleased

### Planned
- Deliverability hardening for campaign mail.
- System-enforced tracking and unsubscribe protections.
