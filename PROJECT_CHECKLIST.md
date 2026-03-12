# MailCamp Project Checklist

## Current Product/UX Work
- [x] Bootstrap-based responsive layout baseline
- [x] Page-by-page UI modernization (auth, dashboard, campaigns, templates, SMTP)
- [x] Reusable Blade components (header, alert, badge, empty-state, table shell)
- [x] Campaign list search/filter/sort with URL query params
- [~] Dashboard architecture polish (iterative)
- [~] Campaign detail architecture polish (iterative)

## Active Execution Now
- [ ] Verify one full campaign lifecycle end-to-end
  - [ ] create/send
  - [ ] message row created
  - [ ] event rows created
  - [ ] open/click/bounce/webhook paths behave
- [~] Audit all existing migrations
  - [x] identify risky migrations and current migration mechanics
  - [x] write migration audit doc
  - [x] verify baseline 2024 migrations against fresh-boot assumptions
  - [x] define forward migration discipline
- [~] Build the event UI
  - [x] campaign events API
  - [x] message events API
  - [x] dashboard delivery metrics wired to real data
  - [x] campaign event drilldown panel
  - [x] dashboard recent delivery events panel
  - [x] dedicated message/campaign timeline polish
  - [x] dashboard funnel and bounce breakdown blocks
- [~] Wire click tracking fully
  - [x] outbound link rewrite service
  - [x] click redirect endpoint
  - [x] click event persistence
  - [x] link table persistence
  - [ ] end-to-end click verification with a real sent message

## Email Delivery Observability Roadmap (Added per request)

### 1) Event Tracking Architecture
- [ ] Create `email_events` table as analytics backbone:
  - `id`, `campaign_id`, `subscriber_id`, `event_type`, `timestamp`, `ip_address`, `user_agent`, `metadata(JSON)`
- [ ] Define core event taxonomy:
  - `queued`, `sent`, `delivered`, `bounced`, `opened`, `clicked`, `unsubscribed`, `spam_report`
- [ ] Persist metadata standards:
  - SMTP response, bounce reason/code, clicked URL, webhook payload references

### 2) Delivery Tracking (SMTP + Webhooks)
- [ ] Add webhook ingestion endpoint: `POST /webhooks/email`
- [ ] Normalize provider/webhook events into `email_events`
- [ ] Support UI delivery states:
  - Sent, Delivered, Soft bounce, Hard bounce, Deferred
- [ ] Store SMTP acceptance + downstream delivery confirmations

### 3) Bounce Classification System
- [ ] Add bounce classifier with categories:
  - Hard bounce, Soft bounce, Blocked, Domain error
- [ ] Store structured bounce fields:
  - `bounce_type`, `bounce_reason`, `smtp_code`
- [ ] Expose human-friendly bounce reasons in UI

### 4) Open Tracking (Pixel)
- [x] Inject tracking pixel per message:
  - `/open/{message_id}.png`
- [x] Implement endpoint to record `opened` event with timestamp/ip/user-agent
- [x] Return transparent PNG response
- [x] Add bot/proxy filtering:
  - prefetch bots, Apple MPP, known scanners

### 5) Click Tracking
- [x] Rewrite outbound links per message:
  - `/click/{message_id}?url=...`
- [x] Record `clicked` event + metadata (`clicked_url`, device, location)
- [x] Redirect safely to destination URL

### 6) Inbox Placement Testing Tool
- [ ] Build “Test Deliverability” workflow with seed inbox list
- [ ] Send campaign test to internal seed addresses
- [ ] Report placement outcomes by provider:
  - Inbox / Promotions / Spam / Other folder

### 7) Spam Score Analyzer (Pre-send)
- [ ] Add preflight checks:
  - SPF, DKIM, DMARC
  - spam keywords, link domain quality, image/text ratio
  - domain/IP blacklist checks
- [ ] Integrate scoring sources (as feasible):
  - SpamAssassin, DNSBL sources, MX diagnostics
- [ ] Show score + risk + actionable issues before send

### 8) Domain Health Dashboard
- [ ] Surface domain-level health metrics:
  - reputation, domain age, blacklist status, bounce rate, complaint rate
- [ ] Add alert rules:
  - bounce_rate > 5% => alert
  - spam_rate > 0.1% => auto-pause campaign (configurable)

### 9) Campaign Delivery Dashboard (Funnel Analytics)
- [ ] Build funnel visualization:
  - queued -> sent -> delivered -> opened -> clicked -> unsubscribed/bounced
- [ ] Add time-series and segment filters (campaign/date/domain)
- [ ] Add export/download of delivery analytics
- [ ] Add derived metrics block with formulas:
  - Delivery rate = delivered / sent
  - Open rate = opened / delivered
  - CTR = clicks / delivered
  - CTOR = clicks / opens
- [ ] Show key totals (example style): sent, delivered, bounced, opened, clicked, unsubscribed

### 10) Real-Time Campaign Log
- [ ] Build per-campaign event stream UI (timestamp + event + recipient + detail)
- [ ] Include key events in chronological order:
  - sent, delivered, opened, clicked, bounced, unsubscribed
- [ ] Add filters (event type, recipient, time window)
- [ ] Add pagination/virtualized list for high-volume logs

### 11) Deliverability Protection System
- [ ] Add safety rules engine for campaign auto-protection
- [ ] Pause campaign when thresholds are exceeded (configurable defaults):
  - bounce_rate > 8%
  - spam_complaints > 0.3%
- [ ] Auto actions:
  - pause sending
  - notify user
  - show recommended remediation (e.g., warmup guidance)

### 12) Database Core Tables (Minimal)
- [x] Ensure core schema coverage exists for:
  - campaigns
  - subscribers
  - messages
  - email_events
  - links
  - bounces
  - webhooks
- [x] `messages` table baseline fields:
  - id, campaign_id, subscriber_id, status, provider_message_id, sent_at

### 13) API Spec for Integrations
- [x] Events APIs:
  - `GET /campaign/{id}/events`
  - `GET /message/{id}/events`
- [ ] Provider webhook APIs (ingestion endpoints):
  - `POST /webhook/provider/sendgrid`
  - `POST /webhook/provider/ses`
  - `POST /webhook/provider/mailgun`
- [ ] Document auth, retry behavior, idempotency keys, and error responses

### 14) Deliverability Advisor (Power Feature)
- [~] Pre-send advisor that surfaces risk warnings before campaign launch
- [~] Warning examples:
  - domain age too new
  - DKIM missing/misconfigured
  - spam score high
  - low inbox probability signal
- [~] Provide actionable recommendations and block/confirm gate when risk is high
- [x] DNS-based MX/SPF/DMARC/common-DKIM checks shown on campaign page

### 15) Future Advanced Features (Backlog)
- [ ] AI spam detection
- [ ] Inbox probability scoring
- [ ] Domain warmup automation
- [ ] Reply tracking
- [ ] Engagement scoring

## Guardrails
- Only standard SMTP configuration in core UI (no provider preset UX for now)
- Preserve `/mailcamp` base-path compatibility throughout tracking endpoints and redirects
- Favor provider-agnostic event normalization model

## Existing Epics (Updated with Delivery/Observability Tasks)

### EPIC 1 — Design System & Shared Components
- [x] Bootstrap-based responsive layout baseline
- [x] Reusable Blade components (header, alert, badge, empty-state, table shell)
- [ ] Continue consolidating repeated UI snippets into shared components

### EPIC 2 — Dashboard Architecture
- [ ] Dashboard architecture polish (iterative)
- [ ] Delivery Health block (SMTP readiness + campaign health counters)
- [ ] Funnel metrics widgets (sent, delivered, bounced, opened, clicked, unsubscribed)
- [ ] Derived metric cards:
  - Delivery rate = delivered / sent
  - Open rate = opened / delivered
  - CTR = clicks / delivered
  - CTOR = clicks / opens

### EPIC 3 — Campaigns Module
- [x] Campaign list search/filter/sort with URL query params
- [ ] Campaign detail architecture polish (iterative)
- [ ] Campaign delivery dashboard (funnel + trends)
- [ ] Real-time campaign event log stream
- [ ] Campaign safety auto-protection (pause on bounce/spam thresholds)

### EPIC 4 — Templates Module
- [x] Page modernization baseline complete
- [ ] Template analytics hooks (template-level open/click performance)
- [ ] Link rewriting integration for click tracking

### EPIC 5 — SMTP & Deliverability (Standard SMTP only)
- [ ] Standard SMTP send pipeline observability (`queued` -> `sent` -> outcomes)
- [ ] SMTP/webhook ingestion and normalization into `email_events`
- [ ] Bounce classifier (hard/soft/blocked/domain-error)
- [ ] Deliverability test workflow (seed inbox testing)
- [ ] Spam score analyzer pre-send checks (SPF/DKIM/DMARC/content/domain)

### EPIC 6 — Auth, Security, and Platform Stability
- [~] Keep `/mailcamp` base-path compatibility across all new endpoints
- [~] Idempotent webhook handling + signature verification + retry safety
- [~] Protection rules/alerts for high-risk sending behavior
- [x] Campaign risk history snapshots and autopause log entries
- [x] Provider webhook route scaffolds for SendGrid / SES / Mailgun

### EPIC 7 — Data Model & APIs
- [ ] Core tables finalized: campaigns, subscribers, messages, email_events, links, bounces, webhooks
- [ ] `messages` schema baseline: id, campaign_id, subscriber_id, status, provider_message_id, sent_at
- [ ] Events APIs:
  - `GET /campaign/{id}/events`
  - `GET /message/{id}/events`
- [ ] Webhook APIs:
  - `POST /webhook/provider/sendgrid`
  - `POST /webhook/provider/ses`
  - `POST /webhook/provider/mailgun`

### EPIC 8 — Deliverability Advisor & Advanced Roadmap
- [~] Pre-send Deliverability Advisor (warnings + actionable recommendations)
- [~] High-risk send gate (warn/confirm/block)
- [ ] Future backlog:
  - AI spam detection
  - Inbox probability scoring
  - Domain warmup automation
  - Reply tracking
  - Engagement scoring

## Suggested Implementation Order
1. Event table + message table + webhook ingestion foundation
2. Send pipeline hooks (`queued/sent`) + delivery/bounce normalization
3. Open/click tracking + link rewrite + event log stream
4. Funnel dashboard + derived metrics + campaign safeguards
5. Deliverability test + spam/domain health + advisor
