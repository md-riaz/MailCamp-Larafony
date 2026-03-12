# MailCamp Migration Audit

## Status Summary

MailCamp migrations are **usable but not blindly trustworthy**. The runtime migration system is simple and deterministic, but several migration files were written against assumptions that do not cleanly match the live MySQL schema.

## How migrations actually work here

- Migration files live in `database/migrations/`
- Valid filenames match `YYYY_MM_DD_HHMMSS_name.php`
- `php bin/larafony migrate`:
  - scans migration files
  - compares filenames to rows in `migrations`
  - resolves each file
  - calls `up()`
  - logs the filename after `up()` returns
- `Schema::create()` and `Schema::table()` return SQL strings; `Schema::execute()` actually runs the SQL

## Trustworthiness rating by migration set

### Generally trustworthy with verification
These are straightforward create-table migrations and likely reflect the app's original baseline, but should still be verified against live schema before relying on them in a fresh environment:

- `2024_01_01_000001_create_users_table.php`
- `2024_01_01_000002_create_organizations_table.php`
- `2024_01_01_000003_create_user_profiles_table.php`
- `2024_01_01_000004_create_roles_table.php`
- `2024_01_01_000005_create_permissions_table.php`
- `2024_01_01_000006_create_user_roles_table.php`
- `2024_01_01_000007_create_role_permissions_table.php`
- `2024_01_01_000008_create_smtp_settings_table.php`
- `2024_01_01_000009_create_templates_table.php`
- `2024_01_01_000010_create_campaigns_table.php`
- `2024_01_01_000011_create_recipients_table.php`
- `2024_01_01_000012_create_queue_jobs_table.php`
- `2024_01_01_000013_create_logs_table.php`
- `2024_01_01_000014_create_subscriptions_table.php`

### Risky / corrected during live work
These needed intervention or should be treated as hand-verified migrations, not blindly replayed assumptions:

- `2026_03_11_084900_create_observability_core_tables.php`
  - originally mismatched live schema expectations
  - now corrected to align with current live MySQL table/id reality
  - should be considered **verified only in its current patched form**
- `2026_03_12_073100_extend_observability_core_for_real_data.php`
  - built to extend observability schema for real data queries
  - uses best-effort DDL helpers and therefore must be validated after apply
  - should be considered **safe with manual verification**, not fire-and-forget

## Known risk patterns

### 1. Type mismatch risk
Many historical migrations use `bigInteger(...)->unsigned(true)` while the live schema appears to use `INT(11)` in key places. That can break foreign keys and DDL expectations.

### 2. Enum/runtime compatibility risk
Some enum/default combinations described in docs are not fully reliable in this app/runtime combination.

### 3. Silent failure risk
Any migration that catches `\Throwable` around DDL can hide real SQL failures. This is useful for idempotent extension helpers, but dangerous for first-time core table creation.

### 4. Docs vs runtime drift
Public Larafony docs are directionally helpful, but this project's live runtime behavior and schema shape are the real source of truth.

## Rules going forward

1. **Live DB truth wins over documentation assumptions**
2. **Inspect generated SQL before production execution**
3. **Verify after apply** with:
   - `SHOW TABLES LIKE ...`
   - `SHOW COLUMNS FROM ...`
   - `SHOW CREATE TABLE ...`
4. **Match actual foreign key types** before adding constraints
5. **Avoid swallowing exceptions** in first-time table creation migrations
6. For risky schema changes, prefer:
   - explicit SQL
   - small idempotent migrations
   - direct post-apply verification

## Recommended next migration discipline

- Keep new migrations narrow and explicit
- Prefer additive migrations over rewriting old baseline files
- Add a verification note to each risky migration PR/commit
- Treat observability/event schema as a separately verified subsystem
