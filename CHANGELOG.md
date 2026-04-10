# CHANGELOG

## 2026-04-10

### Fixed — Production Readiness (LOW priority)
- **Removed duplicate model file** at `src/Models/App/Models/Audience.php` that shadowed the canonical `src/Models/Audience.php`.
- **Differentiated `isManager()` from `isAdmin()`** in `User` model — `isManager()` now includes the `Manager` role.
- **Webhooks reject unsigned payloads in production** — `WebhookSecurityService` now returns an error when `APP_ENV=production` and no `SMTP_REPORT_WEBHOOK_SECRET` is configured.
- **Unsubscribe redirect no longer leaks email in URL** — removed `&email=` query parameter from redirect in `UnsubscribeController`.
- **Services moved to constructor properties** — eliminated ~18 inline `new Service()` calls across 6 controllers (`AudienceController`, `DashboardController`, `EventController`, `OpenTrackingController`, `ClickTrackingController`, `CampaignController`).
- **Updated `.env.example`** with `APP_KEY` and `SMTP_REPORT_WEBHOOK_SECRET` placeholder entries.

### Fixed — Production Readiness (CRITICAL priority)
- **SMTP password encryption** — Replaced base64 with framework-native `EncryptionService` (XChaCha20-Poly1305 AEAD) for SMTP credentials.
- **Removed forced debug exception** from `AudienceController` (fixed in previous turn).

### Fixed — Production Readiness (HIGH priority)
- **Unified Authentication Handling** — Replaced manual `if(!Auth::check())` blocks in 25 methods across 6 controllers with class-level `#[Middleware(beforeGlobal: [AuthMiddleware::class])]` attributes.
- **Closed click tracking open redirect** — `ClickTrackingService` now verifies target URLs exist in the `links` table before redirecting.
- **Removed raw superglobal usage** — `SmtpSettingController` now uses PSR-7 request data via `CreateSmtpSettingDto` instead of `$_POST`.
- **Removed raw PDO usage** — Replaced manual connection logic in `SmtpSettingController` with the framework's `Schema` API.
- **Conditional Query Logging** — Database logging now defaults to `false` and respects the `DB_LOG_QUERIES` environment variable.
- **Debug-only DebugBar** — The DebugBar middleware is now only registered when `APP_DEBUG` is `true`.
- **Secure Sessions** — Verified `config/session.php` auto-detects HTTPS to set the `secure` flag on cookies.

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
