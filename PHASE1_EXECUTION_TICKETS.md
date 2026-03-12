# MailCamp Phase-1 Execution Tickets

Scope: Foundation for delivery observability + stable portal UX.  
Constraint: Standard SMTP only (no provider preset UX).

## Legend
- Priority: P0 (must), P1 (high), P2 (nice)
- Effort: S (0.5-1d), M (1-3d), L (3-5d)
- Status:
  - `DONE` = implemented and usable
  - `PARTIAL` = implemented in part / missing polish or breadth
  - `VERIFY` = implemented in code/schema but still needs live end-to-end verification
  - `TODO` = not implemented yet

## Live Snapshot (2026-03-12)
- Canonical deploy path: `/var/project/MailCamp-Larafony`
- Observability tables exist in live DB:
  - `messages`, `email_events`, `links`, `bounces`, `webhooks`
- Current live counts at audit time:
  - campaigns: `5`
  - recipients: `40`
  - messages: `0`
  - email_events: `0`
  - webhooks: `0`
  - links: `0`
- Meaning: schema and app wiring exist, but real delivery/event data has not yet been proven through a full controlled send.

---

## EPIC 1 — Design System & Shared Components

### T1.1 Component hardening pass
- Status: `PARTIAL`
- Priority: P1
- Effort: S
- Depends on: None
- Deliverables:
  - Normalize props/variables for `page-header`, `flash-alert`, `status-badge`, `empty-state`, `table-shell`
  - Add usage notes in comments/docblock
- Acceptance:
  - No duplicated header/empty-state/status badge markup on core pages
- Current notes:
  - Shared components are in active use across dashboard/campaigns/templates/smtp/auth
  - Formal hardening/docs pass still incomplete

### T1.2 Layout consistency QA
- Status: `PARTIAL`
- Priority: P1
- Effort: S
- Depends on: T1.1
- Deliverables:
  - Spacing/typography/button consistency pass on dashboard/campaigns/templates/smtp/auth
- Acceptance:
  - Mobile/tablet/desktop views remain consistent and readable
- Current notes:
  - Major layout modernization exists, but no formal QA sweep is documented

---

## EPIC 2 — Dashboard Architecture

### T2.1 Delivery Health cards MVP
- Status: `VERIFY`
- Priority: P0
- Effort: M
- Depends on: T7.1 (event model exists) partial mock allowed before data wiring
- Deliverables:
  - Cards: Sent, Delivered, Bounced, Opened, Clicked, Unsubscribed
  - Derived metrics: Delivery rate, Open rate, CTR, CTOR
- Acceptance:
  - Metrics formulas applied correctly and show fallback for zero denominators
- Current notes:
  - Cards and derived metrics are wired to real observability queries now
  - Needs validation with live message/event data (current live event tables are empty)

### T2.2 SMTP readiness + campaign health summary
- Status: `DONE`
- Priority: P1
- Effort: S
- Depends on: None
- Deliverables:
  - SMTP configured/test hint
  - Campaign status summary counts
- Acceptance:
  - Dashboard shows health block without breaking existing views

---

## EPIC 3 — Campaigns Module

### T3.1 Campaign detail route stabilization
- Status: `DONE`
- Priority: P0
- Effort: S
- Depends on: None
- Deliverables:
  - Keep stable detail URL behavior and invalid-id fallback to list
- Acceptance:
  - Invalid campaign link never shows hard dead-end for logged-in users

### T3.2 Real-time campaign event log (MVP)
- Status: `PARTIAL`
- Priority: P0
- Effort: M
- Depends on: T7.1, T7.2
- Deliverables:
  - Event stream panel on campaign detail (latest N events)
  - Filter by event type
- Acceptance:
  - New events appear in descending timestamp order
- Current notes:
  - Event stream panel exists on campaign detail
  - Raw/filtered event API links exist
  - In-page filtering/pagination polish still missing
  - Needs live data to confirm operational behavior

### T3.3 Campaign safety rules (autopause)
- Status: `TODO`
- Priority: P1
- Effort: M
- Depends on: T7.1, T7.3
- Deliverables:
  - Rule engine checks bounce/spam rates
  - Auto-pause + user notification when threshold exceeded
- Acceptance:
  - bounce_rate > 8% OR spam_complaints > 0.3% triggers pause

---

## EPIC 4 — Templates Module

### T4.1 Click-tracking rewrite integration
- Status: `VERIFY`
- Priority: P0
- Effort: M
- Depends on: T7.2
- Deliverables:
  - Rewrite links in outgoing email body to `/click/{message_id}?url=...`
- Acceptance:
  - Click records stored before redirect
- Current notes:
  - Link rewriting service and redirect endpoint are implemented
  - Link persistence and click event persistence are implemented
  - Still needs controlled real-send verification

### T4.2 Template variable validation helper
- Status: `TODO`
- Priority: P1
- Effort: S
- Depends on: None
- Deliverables:
  - Validate required vars (`unsubscribe_url` etc.) pre-send
- Acceptance:
  - Missing critical vars block send with actionable message

---

## EPIC 5 — SMTP & Deliverability (Standard SMTP only)

### T5.1 Message lifecycle events (queued/sent)
- Status: `VERIFY`
- Priority: P0
- Effort: M
- Depends on: T7.1
- Deliverables:
  - Emit `queued` and `sent` events per message
  - Persist SMTP response metadata
- Acceptance:
  - Each message has traceable initial lifecycle events
- Current notes:
  - Queue/send code emits lifecycle events in code
  - Needs controlled send verification against live DB

### T5.2 Delivery/bounce ingestion normalization
- Status: `PARTIAL`
- Priority: P0
- Effort: L
- Depends on: T7.1, T7.3
- Deliverables:
  - Webhook ingestion endpoints + normalization to internal event types
  - Bounce classifier with `bounce_type`, `bounce_reason`, `smtp_code`
- Acceptance:
  - Delivered/soft/hard/deferred visible in UI state mapping
- Current notes:
  - SMTP report ingestion path exists
  - Bounce/webhook/event persistence exists in part
  - Multi-provider normalization and full UI state mapping not finished

### T5.3 Open tracking pixel endpoint
- Status: `VERIFY`
- Priority: P0
- Effort: M
- Depends on: T7.2
- Deliverables:
  - `/open/{message_id}.png` endpoint
  - Bot/proxy filtering
- Acceptance:
  - Valid opens recorded with metadata; endpoint returns transparent PNG
- Current notes:
  - Implemented with transparent pixel response and filtering logic
  - Needs controlled live verification

---

## EPIC 6 — Auth/Security/Platform

### T6.1 Webhook security + idempotency
- Status: `PARTIAL`
- Priority: P0
- Effort: M
- Depends on: T5.2
- Deliverables:
  - Signature verification hooks
  - Idempotency key handling
  - Replay protection window
- Acceptance:
  - Duplicate webhook payloads do not duplicate events
- Current notes:
  - Idempotency handling exists in SMTP report ingestion
  - Signature verification and stronger replay protections are still missing

### T6.2 Subpath compatibility audit
- Status: `PARTIAL`
- Priority: P1
- Effort: S
- Depends on: None
- Deliverables:
  - Verify `/mailcamp` compatibility across new endpoints/routes/redirects
- Acceptance:
  - No hardcoded root-path regressions
- Current notes:
  - Redirect/base-path logic exists at app layer
  - Full endpoint audit still pending, especially for open/click/events paths

---

## EPIC 7 — Data Model & APIs

### T7.1 DB migration pack: observability core
- Status: `DONE` (with migration-risk caveat)
- Priority: P0
- Effort: L
- Depends on: None
- Deliverables:
  - Tables: `messages`, `email_events`, `links`, `bounces`, `webhooks` (plus existing relations)
  - Indexes on `campaign_id`, `subscriber_id`, `event_type`, `timestamp`, `provider_message_id`
- Acceptance:
  - Schema supports campaign/message/event queries at scale
- Current notes:
  - Live schema exists and is registered in `migrations`
  - See `MIGRATION_AUDIT.md` for trust/risk notes

### T7.2 Events APIs
- Status: `DONE`
- Priority: P1
- Effort: M
- Depends on: T7.1
- Deliverables:
  - `GET /campaign/{id}/events`
  - `GET /message/{id}/events`
- Acceptance:
  - Paginated responses with filters and stable ordering

### T7.3 Webhook APIs
- Status: `PARTIAL`
- Priority: P1
- Effort: M
- Depends on: T7.1
- Deliverables:
  - `POST /webhook/provider/sendgrid`
  - `POST /webhook/provider/ses`
  - `POST /webhook/provider/mailgun`
  - provider-agnostic normalization layer
- Acceptance:
  - Provider payloads mapped to unified internal event types
- Current notes:
  - SMTP-specific report ingestion endpoint exists
  - Provider-specific webhook family is not implemented yet

---

## EPIC 8 — Deliverability Advisor

### T8.1 Advisor rules MVP
- Status: `TODO`
- Priority: P1
- Effort: M
- Depends on: T5.1, T5.2, T7.1
- Deliverables:
  - Pre-send warnings for domain age, DKIM/SPF/DMARC status, spam-risk indicators
- Acceptance:
  - Campaign launch screen shows risk summary + guidance

### T8.2 High-risk send gate
- Status: `TODO`
- Priority: P2
- Effort: S
- Depends on: T8.1
- Deliverables:
  - Warn/confirm gate for high-risk campaigns
- Acceptance:
  - User must explicitly confirm before sending high-risk campaign

---

## Current Ordered Execution Plan

1. Sync ticket/spec docs with reality
2. Verify baseline migration trust and stop future schema drift
3. Run one controlled end-to-end campaign lifecycle verification:
   - queue/create message rows
   - send one message
   - verify `queued`/`sent` events
   - verify open/click/webhook/bounce paths
4. Polish campaign/message event UI after real data exists
5. Complete missing P1/P0 spec gaps:
   - T4.2
   - T6.1
   - T6.2
   - T7.3
   - T3.3
6. Move to advisor/protection features only after live delivery flow is proven

---

## Suggested sprint grouping (revised)

### Sprint 1 — Reality lock + verification
- T7.1 validation and migration discipline
- T5.1 controlled lifecycle verification
- T5.3 controlled open verification
- T4.1 controlled click verification
- T3.1/T3.2 campaign detail verification

### Sprint 2 — Outcomes + safety plumbing
- T5.2
- T6.1
- T6.2
- T7.3

### Sprint 3 — Dashboard + protections
- T2.1 real-data validation
- T3.3
- event UI polish

### Sprint 4 — Advisor + UX polish
- T8.1
- T8.2
- T1.1
- T1.2
- T4.2
