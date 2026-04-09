# MailCamp task queue

## In progress
- [ ] Stabilize campaign deliverability at the system level.
  - [x] Keep click tracking auto-injected at send time.
  - [x] Keep open tracking auto-injected at send time.
  - [x] Make unsubscribe URL system-generated so template authors cannot break it.
  - [x] Add plain-text body generation at send time.
  - [x] Add List-Unsubscribe and List-Unsubscribe-Post headers.
  - [x] Skip unsubscribed, bounced, or complained recipients before queue/send.
  - [x] Keep per-email send delay / pacing configurable.

- [ ] Add suppression foundations.
  - [x] Store SMTP-specific unsubscribes separately from global subscription state.
  - [x] Honor suppressions during queue/send.

## Backlog
- [ ] Add reusable recipient lists / audience segments instead of campaign-only recipient imports.
  - [ ] Create master subscription/list model separate from one campaign.
  - [ ] Support reusable segments for multiple campaigns.
  - [ ] Track per-subscriber status across campaigns: subscribed, unsubscribed, bounced, complained, engaged.
  - [x] Enforce global suppression so unsubscribed/bounced emails are excluded from future campaigns reliably.
  - [x] Use unsubscribe actions to update the master subscription/suppression record, not just one campaign row.
  - [ ] Allow campaign membership snapshots so a campaign records who it targeted at send time.
  - [ ] Avoid re-importing the same recipients for every campaign.
- [ ] Verify unsubscribe endpoint/flow end to end.
- [ ] Audit tracking routes end to end with real sent messages.
- [ ] Review deliverability defaults: sender identity, auth alignment, headers, pacing.

## Notes
- Use this file as the live queue for requested work so tasks are not lost between sessions.
- Record completed shipped items in CHANGELOG.md.
