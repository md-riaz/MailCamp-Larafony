# MailCamp Phase-1 Execution Tickets

Scope: **SMTP-first campaign delivery platform** with observability, safety, and operator UX.  
Constraint: **Standard SMTP is the primary product path.** Provider webhooks are optional integrations, not a core requirement for SMTP campaigns.

## Product Truth

MailCamp should be designed around this core lifecycle:
1. campaign drafted
2. recipients imported
3. template validated
4. SMTP send attempted
5. message lifecycle recorded
6. open tracking recorded
7. click tracking recorded
8. DSN/report ingestion processed when available
9. dashboard + campaign timeline reflect real delivery state

### Important architecture rule
- **SMTP campaigns must not depend on provider webhooks to be considered complete.**
- Provider webhook endpoints are optional integration adapters for SendGrid / SES / Mailgun style event sources.
- For SMTP-first production, the real critical pieces are:
  - queue/send reliability
  - message lifecycle rows
  - open tracking
  - click tracking
  - DSN / report ingestion
  - campaign safety rules
  - deliverability warnings

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

### T1.2 Layout consistency QA
- Status: `PARTIAL`
- Priority: P1
- Effort: S
- Depends on: T1.1
- Deliverables:
  - Spacing/typography/button consistency pass on dashboard/campaigns/templates/smtp/auth
- Acceptance:
  - Mobile/tablet/desktop views remain consistent and readable

---

## EPIC 2 — SMTP Campaign Core

### T2.1 Campaign launch readiness
- Status: `PARTIAL`
- Priority: P0
- Effort: M
- Depends on: T4.1, T4.2, T6.1
- Deliverables:
  - launch checks for recipients, template, SMTP, and risk posture
  - launch blocks invalid campaigns before activation
- Acceptance:
  - bad template or missing SMTP cannot silently launch
- Current notes:
  - template validation, safety checks, and deliverability warnings are now in place
  - still needs real send verification

### T2.2 Message lifecycle events (`queued`, `sent`)
- Status: `VERIFY`
- Priority: P0
- Effort: M
- Depends on: T7.1
- Deliverables:
  - emit `queued` and `sent` events per message
  - persist SMTP response metadata
- Acceptance:
  - each message has traceable initial lifecycle events
- Current notes:
  - queue/send code emits lifecycle events in code
  - needs controlled send verification against live DB

### T2.3 DSN / report ingestion for SMTP outcomes
- Status: `PARTIAL`
- Priority: P0
- Effort: L
- Depends on: T7.1, T6.2
- Deliverables:
  - ingest DSN/report payloads and normalize to internal event types
  - map outcomes into `email_events`, `bounces`, and `webhooks`
- Acceptance:
  - delivered / bounced / deferred / complaint outcomes become visible in timelines and dashboard
- Current notes:
  - SMTP report ingestion path exists
  - bounce/webhook/event persistence exists in part
  - still needs live verification and deeper outcome coverage

---

## EPIC 3 — Tracking & Engagement

### T3.1 Open tracking pixel endpoint
- Status: `VERIFY`
- Priority: P0
- Effort: M
- Depends on: T7.2
- Deliverables:
  - `/open/{message_id}.png` endpoint
  - bot/proxy filtering
- Acceptance:
  - valid opens recorded with metadata; endpoint returns transparent PNG

### T3.2 Click-tracking rewrite integration
- Status: `VERIFY`
- Priority: P0
- Effort: M
- Depends on: T7.2
- Deliverables:
  - rewrite links in outgoing email body to `/click/{message_id}?url=...`
- Acceptance:
  - click records stored before redirect

### T3.3 Campaign event timeline UX
- Status: `PARTIAL`
- Priority: P1
- Effort: M
- Depends on: T7.1, T7.2
- Deliverables:
  - campaign timeline page
  - message timeline page
  - filters + readable event details
- Acceptance:
  - operator can inspect lifecycle without using raw JSON
- Current notes:
  - HTML timeline pages exist
  - deeper live-data polish still pending

---

## EPIC 4 — Template & Pre-send Quality

### T4.1 Template variable validation helper
- Status: `PARTIAL`
- Priority: P0
- Effort: S
- Depends on: None
- Deliverables:
  - validate required vars like `unsubscribe_url` pre-send
  - warn on weak template quality signals
- Acceptance:
  - missing critical vars block send with actionable message
- Current notes:
  - required unsubscribe variable is enforced
  - basic template-quality warnings exist

### T4.2 Deliverability advisor (SMTP-first)
- Status: `PARTIAL`
- Priority: P1
- Effort: M
- Depends on: T2.1
- Deliverables:
  - sender-domain checks
  - MX / SPF / DMARC / common-DKIM checks
  - recommendations surfaced before launch
- Acceptance:
  - campaign page shows risk summary + actionable guidance
- Current notes:
  - DNS-based MX/SPF/DMARC/common-DKIM checks are surfaced now
  - domain age and advanced spam/inbox scoring are still not implemented

---

## EPIC 5 — Safety & Protection

### T5.1 Campaign safety rules (autopause)
- Status: `PARTIAL`
- Priority: P0
- Effort: M
- Depends on: T2.2, T2.3
- Deliverables:
  - rule engine checks bounce/spam rates
  - auto-pause + user notification when threshold exceeded
- Acceptance:
  - bounce_rate > 8% OR spam_complaints > 0.3% triggers pause
- Current notes:
  - risk evaluator and launch-time guards exist
  - high-risk launches can be blocked and severe risk can auto-pause before activation
  - risk history snapshots and autopause log entries are now recorded
  - real-send verification still pending

### T5.2 Risk history + operator visibility
- Status: `DONE`
- Priority: P1
- Effort: S
- Depends on: T5.1
- Deliverables:
  - persist campaign safety snapshots / autopause decisions in logs
  - expose recent risk history in campaign UI
- Acceptance:
  - operator can see recent safety decisions without checking DB directly

---

## EPIC 6 — Dashboard & Operator UX

### T6.1 Delivery Health cards MVP
- Status: `VERIFY`
- Priority: P0
- Effort: M
- Depends on: T7.1
- Deliverables:
  - cards: queued, sent, delivered, bounced, opened, clicked, unsubscribed
  - derived metrics: delivery rate, open rate, CTR, CTOR
- Acceptance:
  - metrics formulas applied correctly and show fallback for zero denominators

### T6.2 Dashboard funnel + bounce breakdown
- Status: `DONE`
- Priority: P1
- Effort: M
- Depends on: T7.1
- Deliverables:
  - queued → sent → delivered → opened → clicked → bounced / unsubscribed funnel
  - org-wide bounce breakdown
- Acceptance:
  - dashboard is usable as an operator control surface

### T6.3 SMTP readiness + campaign health summary
- Status: `DONE`
- Priority: P1
- Effort: S
- Depends on: None
- Deliverables:
  - SMTP configured/test hint
  - campaign status summary counts
- Acceptance:
  - dashboard shows health block without breaking existing views

---

## EPIC 7 — Data Model & Internal APIs

### T7.1 DB migration pack: observability core
- Status: `DONE` (with migration-risk caveat)
- Priority: P0
- Effort: L
- Depends on: None
- Deliverables:
  - tables: `messages`, `email_events`, `links`, `bounces`, `webhooks`
  - indexes for campaign/message/event queries
- Acceptance:
  - schema supports campaign/message/event queries at scale
- Current notes:
  - live schema exists and is registered in `migrations`
  - see `MIGRATION_AUDIT.md` for trust/risk notes

### T7.2 Events APIs
- Status: `DONE`
- Priority: P1
- Effort: M
- Depends on: T7.1
- Deliverables:
  - `GET /campaign/{id}/events`
  - `GET /message/{id}/events`
- Acceptance:
  - paginated responses with filters and stable ordering

### T7.3 Migration discipline / deploy safety
- Status: `PARTIAL`
- Priority: P0
- Effort: M
- Depends on: None
- Deliverables:
  - migration risk audit
  - forward migration rules
  - safe deploy guidance
- Acceptance:
  - future schema changes do not depend on guesswork

---

## EPIC 8 — Optional Provider Integrations

### T8.1 Provider webhook routes + normalization scaffolds
- Status: `PARTIAL`
- Priority: P2
- Effort: M
- Depends on: T7.1
- Deliverables:
  - `POST /webhook/provider/sendgrid`
  - `POST /webhook/provider/ses`
  - `POST /webhook/provider/mailgun`
  - provider-agnostic normalization layer
- Acceptance:
  - provider payloads mapped to unified internal event names
- Current notes:
  - routes and normalization scaffolds exist
  - full persistence into core tables is still pending

### T8.2 Provider persistence adapters
- Status: `TODO`
- Priority: P2
- Effort: L
- Depends on: T8.1, T7.1
- Deliverables:
  - persist provider webhook events into `webhooks`, `email_events`, and `bounces` where applicable
- Acceptance:
  - provider integrations behave like optional extensions, not SMTP dependencies

---

## Current Ordered Execution Plan

1. Run one controlled end-to-end SMTP verification:
   - queue/create message rows
   - send one message
   - verify `queued` / `sent`
   - verify open/click
   - verify DSN/report ingestion
2. Validate dashboard and campaign timelines against real data
3. Tighten SMTP report ingestion / outcome mapping where real testing exposes gaps
4. Finish migration/deploy discipline lock
5. Only then expand optional provider adapters if still needed

---

## Suggested sprint grouping (SMTP-first)

### Sprint 1 — Core SMTP truth
- controlled lifecycle verification
- open tracking verification
- click tracking verification
- DSN/report verification

### Sprint 2 — Operator confidence
- dashboard validation
- campaign timeline validation
- autopause / risk-history validation
- final subpath verification under `/mailcamp`

### Sprint 3 — Deploy confidence
- migration/deploy lock
- checklist/spec cleanup
- production readiness review

### Sprint 4 — Optional integrations
- provider webhook persistence adapters
- advanced deliverability scoring
- optional future provider features
