# Larafony framework analysis

## Project overview

This repository is a Larafony application called **MailCamp**, described in `composer.json` as a multi-tenant mail campaign manager built on the Larafony Framework.

The framework is consumed as a local path dependency:

- `larafony/core: @dev`
- repository path: `./framework`
- PSR-4 autoload maps `Larafony\Framework\` to `framework/src/Larafony/`

That means this project is not only using Larafony conventions, but is also closely coupled to a local development copy of the framework.

## Core framework wiring

### HTTP entrypoint

`public/index.php` is the front controller.

What it does:

1. Loads Composer autoloading.
2. Reads `.env` values manually if present.
3. Normalizes `REQUEST_URI` when the app is mounted under a subpath derived from `APP_URL`.
4. Requires `bootstrap/app.php`.
5. Calls `$app->run()`.

This is a conventional framework bootstrap flow, but with an additional app-specific URI normalization step that helps deployments under a subdirectory.

### Application bootstrap

`bootstrap/app.php` creates the Larafony web application instance:

- `Larafony\Framework\Web\Application::instance(...)`

It then registers a broad set of Larafony service providers:

- Config
- Cache
- Session
- Events
- Database
- HTTP
- Logging
- Routing
- Views
- Console
- Error handling
- Auth
- Debug bar
- Web

This confirms the application is using Larafony in its intended provider-based architecture rather than bypassing core services.

## Routing usage

Routing is configured in `bootstrap/app.php` with:

- `loadAttributeRoutes(__DIR__ . '/../src/Controllers')`
- `loadFileRoutes(__DIR__ . '/../routes/api.php')`

This shows the project is using a **hybrid routing approach**.

### 1. Attribute route support

The bootstrap is configured to scan controllers for route attributes, which matches Larafony's documentation style and modern PHP-first design.

This indicates the intended long-term direction of the app is controller-driven, attribute-based routing.

### 2. File route support

The project also still uses explicit route registration in `routes/api.php`.

Current API routes include:

- `POST /audiences`
- `GET /audiences`

These are mapped to `App\Controllers\AudienceController` methods.

### Practical interpretation

The codebase is in a mixed state where Larafony's attribute routing is enabled globally, but explicit route files are still being used for selected endpoints. That is a reasonable transitional pattern in a framework that supports both styles.

## Controller usage

Application HTTP handlers live under `src/Controllers/`.

A current example is `src/Controllers/AudienceController.php`.

Observations:

- Controllers return `Larafony\Framework\Http\Response` objects.
- Input validation can happen directly from the request object using `$request->validate([...])`.
- Controllers currently instantiate services directly (`new AudienceService()`) rather than resolving them through container injection.

This means the project is using Larafony's HTTP abstraction layer, but not yet fully leaning into dependency injection at the controller level in all places.

## Validation patterns in use

The repository uses **two validation styles**.

### 1. Inline request validation

In `AudienceController`, validation is done directly on the request:

- `name => required|string|max:255`
- `description => string|nullable`

This is a familiar controller-local style.

### 2. Typed DTO / FormRequest validation

The project also has DTOs under `src/DTOs/` such as:

- `CreateCampaignDto`
- `UpdateCampaignDto`
- `LoginDto`
- `RegisterDto`
- `CreateTemplateDto`
- `CreateSmtpSettingDto`

`CreateCampaignDto` demonstrates Larafony's modern typed validation approach:

- extends `Larafony\Framework\Validation\FormRequest`
- uses validation attributes like `#[IsValidated]` and `#[MinLength(...)]`
- uses PHP asymmetric visibility, e.g. `public protected(set) string $name;`

This strongly matches Larafony's documentation around type-safe DTOs and attribute-driven validation.

### Practical interpretation

The project is actively using Larafony's advanced DTO validation system in some features, while some newer or unfinished endpoints still use inline request validation. So the codebase is partially standardized around DTOs, but not uniformly yet.

## Model / ORM usage

The project uses Larafony's ORM model base.

Example:

- `src/Models/Audience.php` extends `Larafony\Framework\Database\ORM\Model`

Current model characteristics:

- explicit table name: `audiences`
- fillable attributes for persistence
- model creation through normal object mutation and `save()`
- querying via `Audience::query()->orderBy('name')->get()->toArray()`

This aligns with an Eloquent-style active-record workflow, which Larafony documents as part of its database layer.

The current `Audience` model is still minimal and does not yet demonstrate attribute-based relationships, but the framework usage pattern is clearly ORM-based rather than raw SQL-driven.

## Service layer usage

Business logic is being split into dedicated service classes under `src/Services/`.

Example:

- `src/Services/AudienceService.php`

Current behavior includes:

- creating models
- saving ORM entities
- listing entities through query builder chains
- temporary logging around persistence attempts

This is a healthy sign that the app is separating HTTP concerns from business logic, which fits Larafony's explicit architecture style well.

## View layer usage

The registered service providers include `ViewServiceProvider`, which confirms the app uses Larafony's view system.

Based on the project layout and earlier analysis, views live under `resources/views/blade/`. The naming suggests Blade-style organization, though the project may be using plain PHP templates with framework helpers rather than a full Laravel Blade compiler.

So from a framework-usage perspective, the application is wired for server-rendered view responses in addition to JSON/API endpoints.

## Configuration and environment usage

Framework configuration is centralized under `config/`, including `config/database.php`.

The application combines two layers of environment handling:

1. `public/index.php` manually loads `.env` values into `$_ENV` / `getenv()`.
2. Larafony service providers then consume configuration from the app bootstrap and config files.

This suggests the project relies on explicit environment bootstrapping before the framework begins handling requests.

## Debugging and development features in use

The app registers these development-oriented providers:

- `DebugBarServiceProvider`
- `ErrorHandlerServiceProvider`
- `ConsoleServiceProvider`

That indicates this project is using Larafony's built-in diagnostics, HTML error handling, and CLI support.

Composer setup also confirms a development workflow around framework experimentation:

- local path repository for Larafony
- dev stability allowed via `minimum-stability: dev`
- PHPUnit included for tests

## Current framework adoption summary

The project is actively using these Larafony capabilities:

- provider-based application bootstrap
- web application entrypoint and runtime
- routing infrastructure
- attribute-route scanning
- file-based route registration
- HTTP request/response abstractions
- inline request validation
- FormRequest/DTO-based attribute validation
- ORM models and query builder access
- config-driven database integration
- view system provider registration
- auth/debug/error/console provider wiring

## Notable gaps or inconsistencies

The codebase is using Larafony seriously, but framework adoption is not perfectly uniform yet.

Examples:

1. **Mixed routing styles**
   - both attribute scanning and route files are active
2. **Mixed validation styles**
   - DTO/FormRequest validation is used in some areas, inline request validation in others
3. **Mixed dependency management styles**
   - service providers and container architecture exist, but some controllers still instantiate services manually
4. **Debug leftovers in feature code**
   - temporary logging and a forced exception are still present in audience flow code

These are not signs that the framework is unused; they are signs that the application is still being actively developed and standardized.

## Overall assessment

MailCamp follows Larafony's intended architecture closely.

The strongest evidence is:

- explicit provider-based bootstrap
- attribute route loading
- FormRequest DTOs with validation attributes
- ORM model inheritance from framework base classes
- service-oriented app code under `src/`
- local framework path dependency for active framework-level development

In short, this is a genuine Larafony application that uses the framework's modern PHP patterns in real code, while still containing a few transitional areas where older or simpler patterns remain in place.

---

# Production readiness review

## Severity legend

- 🔴 **CRITICAL** — Must fix before any production deployment. Active security vulnerability or data-loss risk.
- 🟠 **HIGH** — Should fix before production. Will cause operational issues or security exposure under real traffic.
- 🟡 **MEDIUM** — Should fix soon. Technical debt or minor risk that compounds over time.
- 🟢 **LOW** — Improvement opportunity. Not blocking but worth addressing.

---

## 🔴 CRITICAL issues

### 1. SMTP passwords stored with base64 encoding, not encryption

**File:** `src/Models/SmtpSetting.php:99-106`

```php
public static function encryptPassword(string $password): string
{
    return base64_encode($password);
}

public function decryptPassword(): string
{
    return base64_decode($this->password);
}
```

Base64 is not encryption. Anyone with database read access (backups, SQL injection, compromised admin panel) can decode every SMTP password instantly.

**Fix:** Use real symmetric encryption (e.g., Larafony's documented `XChaCha20-Poly1305` AEAD encryption) with `APP_KEY` as the key. Rotate all existing SMTP passwords after deploying the fix.

### 2. Debug mode enabled in production `.env`

**File:** `.env:3`

```
APP_ENV=production
APP_DEBUG=true
```

`APP_DEBUG=true` while `APP_ENV=production` means:
- Full stack traces with file paths, line numbers, and variable state are shown to end users.
- The Larafony `DetailedErrorHandler` renders an interactive HTML debug page on every unhandled exception.
- Internal framework internals, database queries, and environment values can leak to attackers.

**Fix:** Set `APP_DEBUG=false` in production. Ensure the `.env` file deployed to production never has `APP_DEBUG=true`.

### 3. Database credentials committed to version control

**File:** `.env:9-13`

```
DB_HOST=127.0.0.1
DB_DATABASE=mailcamp
DB_USERNAME=mailcamp_user
DB_PASSWORD=yGMcCWTlnOq1g6NRqGig
```

The `.env` file with real credentials is tracked in the repository. Anyone with repository access has the database password.

**Fix:** Add `.env` to `.gitignore`. Rotate the database password. Use environment-specific secret management (vault, CI/CD secrets, cloud provider secrets manager).

### 4. APP_KEY committed to version control

**File:** `.env:26`

```
APP_KEY=JGRplSfgHCUFUpd5vg10PlP5A8Q1kv4mPTSgERk3CB0=
```

The application encryption key is in the repo. This key protects session cookies and any encrypted data. If an attacker knows this key, they can forge sessions and decrypt any data encrypted with it.

**Fix:** Remove from VCS. Generate a new key for production and inject via environment variable or secrets manager.

### 5. Forced debug exception in production code path

**File:** `src/Controllers/AudienceController.php:27`

```php
throw new \Exception('Debug HTTP: '.print_r($request->all(),true)); // Temporary Debugging.
```

This line unconditionally throws an exception on every audience creation request, which:
- Breaks the feature entirely.
- Dumps the full request payload (including any user data) into error output.
- In debug mode, this data is rendered to the browser.

**Fix:** Remove the line entirely.

---

## 🟠 HIGH issues

### 6. No CSRF protection on state-changing POST routes

No CSRF token middleware is registered in `config/middleware.php`. All POST forms (login, register, campaign creation, SMTP settings, recipient import) are vulnerable to cross-site request forgery.

An attacker could craft a page that submits a form to `/campaigns` or `/smtp-settings` on behalf of a logged-in user.

**Fix:** Add CSRF middleware to the global middleware stack. Larafony likely provides one, or implement a standard PSR-15 CSRF middleware that validates a token on every non-GET request.

### 7. Click tracking endpoint is an open redirect

**File:** `src/Controllers/ClickTrackingController.php:29`

```php
return $this->redirect((string) $result['redirect_url'], 302);
```

The `/click/{messageId}?url=...` endpoint redirects to whatever URL is in the `url` query parameter. While `shouldTrackHref()` does validate with `FILTER_VALIDATE_URL`, it still allows redirects to any valid URL (e.g., phishing sites).

An attacker can craft links like `/click/123?url=https://evil.com` and distribute them — the redirect comes from your domain, lending it credibility.

**Fix:** Validate the redirect URL against a whitelist of tracked domains, or at minimum require the URL exists in the `links` table for that message before redirecting.

### 8. Raw `$_POST` superglobal used in controller

**File:** `src/Controllers/SmtpSettingController.php:69`

```php
$isActive = isset($_POST['is_active']) && (string) $_POST['is_active'] !== '' ? 1 : 0;
```

This bypasses the PSR-7 request object and the DTO validation layer. It means:
- Middleware that transforms or validates the request body has no effect on this value.
- This value is not subject to any input validation.

**Fix:** Read `is_active` from the `CreateSmtpSettingDto` or from `$request->getParsedBody()`.

### 9. Raw PDO connection created outside the framework

**File:** `src/Controllers/SmtpSettingController.php:184-218`

The `campaignsTableHasSmtpSettingId()` method manually creates a PDO connection using raw `$_ENV` values, bypassing the framework's database layer entirely. This:
- Creates a connection not managed by the connection pool.
- Reads credentials differently than the config system.
- Silently fails and returns `false` on any error, masking real problems.

**Fix:** Use the framework's query builder or schema introspection to check for column existence. If that's not available, at minimum resolve the PDO connection from the container.

### 10. Query logging enabled in production database config

**File:** `config/database.php:44-47`

```php
'logging' => [
    'path' => 'storage/logs/query.log',
    'enabled' => true,
],
```

Every SQL query is being logged to disk. In production with real traffic, this:
- Causes significant I/O overhead.
- Can fill disk space rapidly.
- May log sensitive data (emails, names) that appears in WHERE clauses.

**Fix:** Make query logging conditional on `APP_DEBUG` or `DB_LOG_QUERIES`, and default to `false` in production.

### 11. Session cookie not configured as secure

**File:** `.env:23`

```
SESSION_SECURE_COOKIE=false
```

Session cookies can be transmitted over plain HTTP, making them vulnerable to interception on any non-HTTPS connection.

**Fix:** Set `SESSION_SECURE_COOKIE=true` in production (requires HTTPS, which production should have).

### 12. DebugBar middleware active in global middleware stack

**File:** `config/middleware.php:15`

```php
'after_global' => [
    InjectDebugBar::class,
],
```

The debug bar is unconditionally injected into responses. Even if the debug bar config checks `APP_DEBUG`, the middleware itself runs on every request, adding overhead. If the debug bar is misconfigured, it could leak query data, request data, and internal routing info.

**Fix:** Only register `InjectDebugBar` middleware when `APP_DEBUG` is true, or remove it from the middleware config entirely for production.

---

## 🟡 MEDIUM issues

### 13. No rate limiting on authentication endpoints

`/login` and `/register` have no rate limiting. An attacker can brute-force credentials or create unlimited organizations.

**Fix:** Add rate-limiting middleware to authentication routes (e.g., 5 attempts per minute per IP on `/login`).

### 14. No pagination on list queries

**File:** `src/Controllers/DashboardController.php:90-94`, `src/Controllers/CampaignController.php:72-74`

Campaign lists use `->get()` without `->limit()` or pagination. With thousands of campaigns, this will:
- Load all records into memory.
- Render extremely large HTML pages.
- Cause timeouts or OOM errors.

**Fix:** Add pagination to all list endpoints.

### 15. Debug logging left in production code

**File:** `src/Controllers/AudienceController.php:21`

```php
\Larafony\Framework\Log\Log::error('Request Error', ['request' => $request->toArray()]);
```

This logs the full request body at ERROR level on every audience creation, including in production. It pollutes logs and may log sensitive user data.

**Fix:** Remove debug logging statements from production code paths.

### 16. AuthMiddleware exists but is not applied

`src/Middleware/AuthMiddleware.php` and `src/Middleware/RoleMiddleware.php` exist, but authentication is handled by manual `Auth::check()` calls at the top of every controller method. This means:
- Every controller must remember to check auth.
- If a developer forgets, the route is publicly accessible.
- The middleware pattern exists but is unused.

**Fix:** Apply `AuthMiddleware` to protected route groups instead of checking `Auth::check()` in every method.

### 17. `loadFileRoutes()` may not exist on the router

The earlier analysis in this document identified that `Advanced\Router` does not implement `loadFileRoutes()`. If this is still the case, the bootstrap will crash on every request.

**Fix:** Verify the framework router supports this method. If not, migrate file-based routes to attribute routes or implement the method in the framework.

### 18. No input size limits on CSV/text recipient import

**File:** `src/Controllers/CampaignController.php:546-635`

The CSV import reads the entire file into memory with `$stream->getContents()`, then processes all rows one at a time with individual `save()` calls. With a large CSV:
- Memory usage scales linearly with file size.
- Each row is an individual INSERT query (no batching).
- No maximum row count is enforced.

**Fix:** Add a file size limit, process in chunks, and use batch inserts.

### 19. Duplicate model directory

**File:** `src/Models/App/Models/Audience.php`

There is an `Audience.php` at both `src/Models/Audience.php` and `src/Models/App/Models/Audience.php`. This creates confusion about which class is autoloaded and may cause subtle bugs.

**Fix:** Remove the duplicate at `src/Models/App/Models/Audience.php`.

### 20. `error_reporting(E_ALL)` and `display_errors` hardcoded in entrypoint

**File:** `public/index.php:33-34`

```php
error_reporting(E_ALL);
ini_set('display_errors', '1');
```

These are hardcoded regardless of environment, meaning PHP-level errors and notices are always displayed to the browser in production.

**Fix:** Make these conditional on `APP_DEBUG`, or remove them entirely and let the framework error handler manage error display.

---

## 🟢 LOW issues

### 21. Services instantiated with `new` instead of container resolution

Multiple controllers create services with `new ServiceClass()`:
- `new AudienceService()`
- `new ObservabilityService()`
- `new CampaignSafetyService()`
- `new CampaignRiskHistoryService()`
- `new TemplateValidationService()`

This makes testing difficult and bypasses the container's ability to manage dependencies, caching, and lifecycle.

**Fix:** Resolve services through constructor injection or the container.

### 22. `isManager()` method is identical to `isAdmin()`

**File:** `src/Models/User.php:82-93`

```php
public function isAdmin(): bool
{
    return in_array($this->role, ['Admin', 'Superadmin'], true);
}

public function isManager(): bool
{
    return in_array($this->role, ['Admin', 'Superadmin'], true);
}
```

Both methods check the same roles. Either `isManager()` should include a `Manager` role, or it should be removed to avoid confusion.

### 23. Webhook endpoints accept unsigned payloads when no secret is configured

**File:** `src/Services/WebhookSecurityService.php:60-68`

When `SMTP_REPORT_WEBHOOK_SECRET` is empty, all webhook payloads are accepted without signature verification (`reason: 'unsigned_allowed'`). This is fine for development, but in production any attacker who discovers the webhook URL can inject fake bounce/complaint events.

**Fix:** Require `SMTP_REPORT_WEBHOOK_SECRET` to be set in production. Reject unsigned payloads when running in production environment.

### 24. Unsubscribe redirect leaks email in URL

**File:** `src/Controllers/UnsubscribeController.php:56`

```php
return $this->redirect('/?notice=unsubscribed&email=' . rawurlencode($decoded['email']), 302);
```

The subscriber's email address is placed in the redirect URL query string, which:
- Appears in browser history.
- May be logged by proxies and CDNs.
- Could appear in analytics tools.

**Fix:** Use a session flash message instead of URL parameters for the email.

### 25. No `.gitignore` for `.env`

The `.env` file is committed to the repository (confirmed by the file being present and containing real credentials). Standard practice is to exclude it.

**Fix:** Add `.env` to `.gitignore`, commit a `.env.example` with placeholder values.

---

## Production readiness summary

| Category | Status | Notes |
|----------|--------|-------|
| **Security: Secrets management** | 🔴 Fail | Credentials and APP_KEY in VCS; SMTP passwords base64-encoded |
| **Security: Debug exposure** | 🔴 Fail | APP_DEBUG=true in production; display_errors forced on |
| **Security: CSRF** | 🟠 Fail | No CSRF middleware on any POST route |
| **Security: Open redirect** | 🟠 Fail | Click tracking redirects to any URL |
| **Security: Session security** | 🟠 Fail | Secure cookie flag off |
| **Code quality: Debug artifacts** | 🔴 Fail | Forced exception and debug logging in production paths |
| **Performance: Query logging** | 🟠 Fail | All queries logged to disk unconditionally |
| **Performance: Pagination** | 🟡 Warning | No pagination on list endpoints |
| **Performance: CSV import** | 🟡 Warning | Unbounded memory and no batch inserts |
| **Architecture: Auth middleware** | 🟡 Warning | Manual auth checks instead of middleware |
| **Architecture: DI usage** | 🟢 Info | Services manually instantiated |
| **Observability: Logging** | 🟢 OK | Structured daily JSON logs configured |
| **Multi-tenancy: Org scoping** | 🟢 OK | Queries consistently scope by organization_id |
| **Webhook security** | 🟢 Conditional | Works when secret is configured |

### Minimum actions before production deployment

1. Remove `.env` from VCS, rotate all secrets (DB password, APP_KEY, SMTP passwords).
2. Set `APP_DEBUG=false` and remove hardcoded `display_errors`.
3. Implement real SMTP password encryption.
4. Remove the forced debug exception in `AudienceController`.
5. Remove debug logging statements.
6. Add CSRF middleware.
7. Restrict click tracking redirects.
8. Set `SESSION_SECURE_COOKIE=true`.
9. Disable query logging in production.
10. Remove or conditionally load DebugBar middleware.
