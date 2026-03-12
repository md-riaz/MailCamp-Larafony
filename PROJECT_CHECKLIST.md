# MailCamp Project Checklist

## Product Direction
- [x] Make SMTP the first-class product path
- [x] Treat provider webhooks as optional integrations, not SMTP requirements
- [x] Keep `/var/project/MailCamp-Larafony` as the canonical deploy path
- [x] Keep `vendor/` and `framework/` read-only

## Active Execution Now
- [ ] Verify one full SMTP campaign lifecycle end-to-end
  - [ ] create/send
  - [ ] message row created
  - [ ] queued event written
  - [ ] sent event written
  - [ ] open tracking behaves correctly
  - [ ] click tracking behaves correctly
  - [ ] DSN/report ingestion behaves correctly
- [~] Audit all existing migrations
  - [x] identify risky migrations and current migration mechanics
  - [x] write migration audit doc
  - [x] verify baseline 2024 migrations against fresh-boot assumptions
  - [x] define forward migration discipline
- [~] Build operator observability UI
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

## SMTP Core Roadmap

### 1) SMTP Send Pipeline
- [~] Queue/send flow records message lifecycle
- [ ] Controlled real-send verification complete
- [ ] SMTP response capture verified against real sends

### 2) DSN / Report Ingestion
- [~] SMTP report ingestion endpoint exists
- [~] DSN/bounce normalization exists
- [~] Bounce classifier exists
- [ ] Controlled real-ingestion verification complete
- [ ] Outcome mapping fully validated for delivered / deferred / bounced / complaint

### 3) Open Tracking
- [x] Tracking pixel injected per message
- [x] Open endpoint records events
- [x] Transparent PNG response exists
- [x] Basic bot/proxy filtering exists
- [ ] Controlled real-open verification complete

### 4) Click Tracking
- [x] Outbound links rewritten per message
- [x] Click redirect endpoint exists
- [x] Click event persistence exists
- [x] Link table persistence exists
- [ ] Controlled real-click verification complete

### 5) Template & Pre-send Quality
- [~] Required variable validation exists (`{{unsubscribe_url}}`)
- [~] Pre-send quality warnings exist
- [~] DNS-based MX/SPF/DMARC/common-DKIM checks exist
- [ ] Domain age check
- [ ] Advanced spam/content scoring
- [ ] Inbox probability / reputation signals

### 6) Campaign Safety
- [~] Risk evaluator exists
- [~] Launch-time high-risk blocking exists
- [~] Auto-pause threshold path exists
- [x] Risk history snapshots logged
- [x] Campaign risk history shown in UI
- [ ] Real-world validation of thresholds

## Operator UX

### Dashboard
- [x] SMTP readiness block
- [x] campaign health summary
- [x] delivery funnel block
- [x] org-wide bounce breakdown
- [ ] validate all numbers against live message/event data

### Campaign & Message Observability
- [x] campaign timeline page
- [x] message timeline page
- [x] event filters and readable details
- [ ] validate live event rendering after real sends

### Subpath Compatibility
- [~] `/mailcamp` base-path handling implemented in app layer
- [~] tracking endpoints built with `APP_URL`
- [ ] live verification under deployed `/mailcamp`

## Migration & Deploy Safety
- [x] migration audit doc exists
- [x] forward migration discipline documented
- [ ] final deploy checklist written
- [ ] deploy-time DB verification checklist written

## Optional Provider Integrations

### Provider Webhooks (Optional)
- [x] SendGrid route scaffold
- [x] SES route scaffold
- [x] Mailgun route scaffold
- [x] provider normalization scaffold
- [ ] persist provider events into core observability tables
- [ ] provider-specific auth/retry/error docs

## Deferred / Advanced Features
- [ ] deliverability seed inbox testing
- [ ] domain age lookup
- [ ] AI spam detection
- [ ] inbox probability scoring
- [ ] domain warmup automation
- [ ] reply tracking
- [ ] engagement scoring

## Guardrails
- SMTP is the main product path
- Provider webhooks are optional integrations
- Open/click tracking and DSN/report ingestion matter more than provider adapters for SMTP mode
- Preserve `/mailcamp` base-path compatibility throughout
- Favor provider-agnostic internal event normalization

## Suggested Implementation Order
1. Real SMTP lifecycle verification
2. Real open/click verification
3. Real DSN/report verification
4. Dashboard/timeline truth validation
5. Deploy checklist + migration/deploy safety lock
6. Optional provider webhook persistence later, only if still needed
