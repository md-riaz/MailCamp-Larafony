<?php
$title = $title ?? 'Event Timeline';
ob_start();
$basePath = rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/');
$componentsPath = dirname(__DIR__, 3) . '/resources/views/blade/components';
$events = $payload['data'] ?? [];
$meta = $payload['meta'] ?? [];
$filters = $filters ?? [];
$contextType = $contextType ?? 'campaign';
$contextId = (int) ($contextId ?? 0);
$backUrl = $backUrl ?? ($basePath . '/campaigns');
$heading = $heading ?? 'Event Timeline';
$subtitle = $subtitle ?? 'Filtered observability timeline.';
$eventType = (string) ($filters['event_type'] ?? '');
$sort = (string) ($filters['sort'] ?? 'desc');
$limit = (int) ($filters['limit'] ?? 50);
$page = (int) ($meta['page'] ?? 1);
$total = (int) ($meta['total'] ?? 0);
$after = (string) ($filters['after'] ?? '');
$before = (string) ($filters['before'] ?? '');

ob_start();
?>
<div class="d-flex gap-2 flex-wrap">
    <a href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary">Back</a>
    <a href="<?= $basePath ?>/<?= $contextType ?>/<?= $contextId ?>/events?event_type=opened" class="btn btn-outline-secondary">Opened</a>
    <a href="<?= $basePath ?>/<?= $contextType ?>/<?= $contextId ?>/events?event_type=clicked" class="btn btn-outline-secondary">Clicked</a>
    <a href="<?= $basePath ?>/<?= $contextType ?>/<?= $contextId ?>/events?event_type=bounced" class="btn btn-outline-secondary">Bounced</a>
</div>
<?php
$actionsHtml = ob_get_clean();
include $componentsPath . '/page-header.blade.php';
?>

<div class="card portal-hero mb-4">
    <div class="card-body">
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-end gap-4">
            <div>
                <div class="eyebrow mb-3">Observability Timeline</div>
                <h2 class="display-6 fw-bold mb-2"><?= htmlspecialchars((string) $heading, ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="mb-0" style="max-width: 760px; color: rgba(255,255,255,0.84) !important;">Use this timeline to inspect lifecycle events, trace message-level outcomes, and debug delivery without dropping into raw tables.</p>
            </div>
        </div>
        <div class="portal-grid portal-grid-4 mt-4">
            <div class="portal-metric">
                <div class="metric-label">Context</div>
                <div class="metric-value" style="font-size:22px;"><?= htmlspecialchars(strtoupper((string) $contextType), ENT_QUOTES, 'UTF-8') ?></div>
                <div class="metric-note">Timeline context type currently being inspected.</div>
            </div>
            <div class="portal-metric">
                <div class="metric-label">Context ID</div>
                <div class="metric-value">#<?= $contextId ?></div>
                <div class="metric-note">Target campaign/message the timeline is scoped to.</div>
            </div>
            <div class="portal-metric">
                <div class="metric-label">Events returned</div>
                <div class="metric-value"><?= count($events) ?></div>
                <div class="metric-note">Number of rows returned for the current filter window.</div>
            </div>
            <div class="portal-metric">
                <div class="metric-label">Sort / limit</div>
                <div class="metric-value" style="font-size:22px;"><?= htmlspecialchars(strtoupper($sort), ENT_QUOTES, 'UTF-8') ?> / <?= $limit ?></div>
                <div class="metric-note">Current query posture for timeline inspection.</div>
            </div>
        </div>
    </div>
</div>

<div class="card portal-surface-soft mb-4">
    <div class="card-body">
        <div class="portal-section-title">Timeline filters</div>
        <form method="GET" action="<?= $basePath ?>/<?= $contextType ?>/<?= $contextId ?>/events" class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label for="event_type" class="form-label">Event type</label>
                <select class="form-select" id="event_type" name="event_type">
                    <option value="">All events</option>
                    <?php foreach (['queued','sent','delivered','opened','clicked','bounced','deferred','unsubscribed','spam_report'] as $option): ?>
                        <option value="<?= $option ?>" <?= $eventType === $option ? 'selected' : '' ?>><?= ucfirst($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label for="sort" class="form-label">Sort</label>
                <select class="form-select" id="sort" name="sort">
                    <option value="desc" <?= $sort === 'desc' ? 'selected' : '' ?>>Newest first</option>
                    <option value="asc" <?= $sort === 'asc' ? 'selected' : '' ?>>Oldest first</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label for="limit" class="form-label">Limit</label>
                <select class="form-select" id="limit" name="limit">
                    <?php foreach ([25,50,100,200] as $option): ?>
                        <option value="<?= $option ?>" <?= $limit === $option ? 'selected' : '' ?>><?= $option ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label for="after" class="form-label">After</label>
                <input type="text" class="form-control" id="after" name="after" value="<?= htmlspecialchars($after, ENT_QUOTES, 'UTF-8') ?>" placeholder="YYYY-MM-DD HH:MM:SS">
            </div>
            <div class="col-12 col-md-2">
                <label for="before" class="form-label">Before</label>
                <input type="text" class="form-control" id="before" name="before" value="<?= htmlspecialchars($before, ENT_QUOTES, 'UTF-8') ?>" placeholder="YYYY-MM-DD HH:MM:SS">
            </div>
            <div class="col-12 col-md-1 d-grid">
                <button type="submit" class="btn btn-primary">Go</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <div>
                <div class="portal-section-title mb-1">Event stream</div>
                <h2 class="h5 mb-1">Timeline</h2>
                <p class="text-secondary small mb-0">Showing page <?= max($page, 1) ?> · total events <?= $total ?></p>
            </div>
        </div>

        <?php if (!empty($events)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Event</th>
                        <th>Message</th>
                        <th>Recipient</th>
                        <th>Provider</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) ($event['timestamp'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars((string) ($event['event_type'] ?? 'unknown'), ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td>
                            <?php if (!empty($event['message_id'])): ?>
                                <a href="<?= $basePath ?>/message/<?= (int) $event['message_id'] ?>/events">#<?= (int) $event['message_id'] ?></a>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td><?= !empty($event['recipient_id']) ? '#' . (int) $event['recipient_id'] : '—' ?></td>
                        <td><?= htmlspecialchars((string) ($event['provider'] ?? 'smtp'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="small text-secondary">
                            <?php
                            $details = [];
                            $metadata = $event['metadata'] ?? null;
                            if (is_array($metadata)) {
                                if (!empty($metadata['recipient_email'])) { $details[] = $metadata['recipient_email']; }
                                if (!empty($metadata['clicked_url'])) { $details[] = 'clicked: ' . $metadata['clicked_url']; }
                                if (!empty($metadata['smtp_code'])) { $details[] = 'SMTP ' . $metadata['smtp_code']; }
                                if (!empty($metadata['bounce_reason'])) { $details[] = $metadata['bounce_reason']; }
                                if (!empty($metadata['tracking_source'])) { $details[] = $metadata['tracking_source']; }
                            }
                            echo htmlspecialchars($details !== [] ? implode(' · ', $details) : '—', ENT_QUOTES, 'UTF-8');
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <?php $message = 'No events found for the current filters.'; $actionHtml = '<a href="' . $basePath . '/' . $contextType . '/' . $contextId . '/events">Clear filters</a>'; include $componentsPath . '/empty-state.blade.php'; ?>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include dirname(__DIR__, 3) . '/resources/views/blade/layout.php';
