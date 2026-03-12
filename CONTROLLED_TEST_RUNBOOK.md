# Controlled Real-Test Runbook

## Goal
Run one safe, minimal end-to-end verification of MailCamp without creating confusion in production data.

## Pre-checks
1. Confirm canonical app path:
   - `/var/project/MailCamp-Larafony`
2. Confirm active sender / SMTP is intentional
3. Choose one safe internal recipient email
4. Choose or create one safe test campaign and template
5. Ensure template contains:
   - `{{unsubscribe_url}}`
   - at least one clickable link
   - at least one visible content block

## Test sequence

### 1) Launch preparation
- Open campaign detail page
- Review Safety & Deliverability panel
- Confirm no blocking errors remain
- Note current DB counts:
  - messages
  - email_events
  - links
  - webhooks
  - bounces

### 2) Queue/send verification
- Launch campaign
- Run the queue/send path intentionally
- Verify:
  - message row created
  - queued event created
  - sent event created
  - provider_message_id captured if available

### 3) Open tracking verification
- Open delivered email
- Trigger tracking pixel
- Verify:
  - opened event created
  - dashboard/campaign timeline reflects it

### 4) Click tracking verification
- Click tracked link
- Verify:
  - redirect works
  - links row created/updated
  - clicked event created
  - message timeline reflects click

### 5) Webhook/report verification
- Send one controlled SMTP/provider report payload
- Verify:
  - webhook row stored
  - dedupe works on resend
  - bounce/deferred/delivered mapping behaves as expected

### 6) Risk history verification
- Review `logs` entries for:
  - `campaign_safety_snapshot`
  - `campaign_autopaused` if triggered

## Record after test
Capture:
- campaign id
- recipient used
- sender domain used
- counts before/after
- any mismatches between expected and observed behavior

## Success criteria
- message lifecycle rows exist
- open/click events exist
- webhook/report ingestion works
- safety panel and dashboard reflect reality correctly
- no base-path regressions under `/mailcamp`
