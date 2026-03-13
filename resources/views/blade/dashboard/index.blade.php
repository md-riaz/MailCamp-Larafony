<?php
$title = 'Dashboard';
ob_start();
$basePath = rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/');
$componentsPath = dirname(__DIR__, 3) . '/resources/views/blade/components';
$campaignHealth = $campaignHealth ?? [];
$smtpConfigured = isset($smtpSetting) && $smtpSetting && $smtpSetting->validate();
$smtpStatusLabel = $smtpConfigured ? 'Configured' : 'Needs setup';
$smtpStatusClass = $smtpConfigured ? 'badge-success' : 'badge-warning';
$recentEvents = $recentEvents ?? [];
$deliveryFunnel = $deliveryFunnel ?? ['queued' => 0, 'sent' => 0, 'delivered' => 0, 'opened' => 0, 'clicked' => 0, 'bounced' => 0, 'unsubscribed' => 0, 'complained' => 0];
$organizationBounceBreakdown = $organizationBounceBreakdown ?? ['hard' => 0, 'soft' => 0, 'blocked' => 0, 'domain_error' => 0, 'unknown' => 0];

ob_start();
?>
<div class="d-flex gap-2 flex-wrap">
    <a href="<?= $basePath ?>/campaigns/create" class="btn btn-success">+ New Campaign</a>
    <a href="<?= $basePath ?>/templates/create" class="btn btn-primary">+ New Template</a>
    <?php if (isset($user) && $user->isAdmin()): ?>
    <a href="<?= $basePath ?>/smtp-settings" class="btn btn-outline-primary">SMTP Settings</a>
    <?php endif; ?>
</div>
<?php
$actionsHtml = ob_get_clean();
$title = 'Dashboard';
$subtitle = 'Overview of your campaigns, recipients, templates, and delivery readiness.';
include $componentsPath . '/page-header.blade.php';
?>

<div class="card portal-hero mb-4">
    <div class="card-body">
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-end gap-4">
            <div>
                <div class="eyebrow mb-3">Modern Mail Operations</div>
                <h2 class="display-6 fw-bold mb-2">SMTP campaigns, safety checks, and delivery observability in one portal.</h2>
                <p class="mb-0" style="max-width: 760px; color: rgba(255,255,255,0.84) !important;">Use this dashboard as your command center: validate sender posture, watch campaign health, and inspect real delivery events without jumping between tools.</p>
            </div>
        </div>
        <div class="portal-grid portal-grid-4 mt-4">
            <div class="portal-metric">
                <div class="metric-label">Total campaigns</div>
                <div class="metric-value"><?php echo $stats['total_campaigns'] ?? 0; ?></div>
                <div class="metric-note">All campaigns created in this workspace.</div>
            </div>
            <div class="portal-metric">
                <div class="metric-label">Active campaigns</div>
                <div class="metric-value"><?php echo $stats['active_campaigns'] ?? 0; ?></div>
                <div class="metric-note">Currently moving through send/monitor flow.</div>
            </div>
            <div class="portal-metric">
                <div class="metric-label">Recipients</div>
                <div class="metric-value"><?php echo $stats['total_recipients'] ?? 0; ?></div>
                <div class="metric-note">Imported audience size across campaigns.</div>
            </div>
            <div class="portal-metric">
                <div class="metric-label">Templates</div>
                <div class="metric-value"><?php echo $stats['total_templates'] ?? 0; ?></div>
                <div class="metric-note">Available content assets ready for launch.</div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center gap-3 mb-3">
    <div>
        <div class="portal-section-title mb-1">Operations snapshot</div>
        <h2 class="h4 mb-1">Delivery Health</h2>
        <p class="text-secondary mb-0">A fast read on queue movement, engagement, and bounce pressure.</p>
    </div>
    <span class="badge bg-secondary text-uppercase" style="font-size: 0.65em;">Preview</span>
</div>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2"><div class="card h-100"><div class="card-body"><div class="text-secondary small">Queued</div><div class="fs-4 fw-bold"><?php echo number_format($deliveryHealthMetrics['queued'] ?? 0); ?></div></div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="card h-100"><div class="card-body"><div class="text-secondary small">Sent</div><div class="fs-4 fw-bold"><?php echo number_format($deliveryHealthMetrics['sent'] ?? 0); ?></div></div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="card h-100"><div class="card-body"><div class="text-secondary small">Delivered</div><div class="fs-4 fw-bold text-success"><?php echo number_format($deliveryHealthMetrics['delivered'] ?? 0); ?></div><div class="text-muted small"><?php echo $deliveryHealthMetrics['delivery_rate'] ?? 0; ?>% rate</div></div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="card h-100"><div class="card-body"><div class="text-secondary small">Opened</div><div class="fs-4 fw-bold text-info"><?php echo number_format($deliveryHealthMetrics['opened'] ?? 0); ?></div><div class="text-muted small"><?php echo $deliveryHealthMetrics['open_rate'] ?? 0; ?>% rate</div></div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="card h-100"><div class="card-body"><div class="text-secondary small">Clicked</div><div class="fs-4 fw-bold text-primary"><?php echo number_format($deliveryHealthMetrics['clicked'] ?? 0); ?></div><div class="text-muted small"><?php echo $deliveryHealthMetrics['ctr'] ?? 0; ?>% CTR | <?php echo $deliveryHealthMetrics['ctor'] ?? 0; ?>% CTOR</div></div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="card h-100"><div class="card-body"><div class="text-secondary small">Bounced</div><div class="fs-4 fw-bold text-danger"><?php echo number_format($deliveryHealthMetrics['bounced'] ?? 0); ?></div><div class="text-muted small"><?php echo number_format(($deliveryHealthMetrics['hard_bounces'] ?? 0) + ($deliveryHealthMetrics['soft_bounces'] ?? 0)); ?> classified</div></div></div></div>
</div>
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <div>
                <h2 class="h5 mb-1">Delivery Funnel</h2>
                <p class="text-secondary small mb-0">Queue to engagement progression from normalized event data.</p>
            </div>
        </div>
        <div class="row g-3 align-items-stretch">
            <?php foreach ([
                'queued' => 'Queued',
                'sent' => 'Sent',
                'delivered' => 'Delivered',
                'opened' => 'Opened',
                'clicked' => 'Clicked',
                'bounced' => 'Bounced',
                'unsubscribed' => 'Unsubscribed',
                'complained' => 'Complaints',
            ] as $key => $label): ?>
            <div class="col-6 col-md-3 col-xl">
                <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                    <div class="text-secondary small"><?php echo $label; ?></div>
                    <div class="fs-4 fw-bold"><?php echo number_format((int) ($deliveryFunnel[$key] ?? 0)); ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <hr>
        <div class="row g-3">
            <div class="col-6 col-md-2"><div class="small text-secondary">Hard bounces</div><div class="fw-semibold text-danger"><?php echo (int) ($organizationBounceBreakdown['hard'] ?? 0); ?></div></div>
            <div class="col-6 col-md-2"><div class="small text-secondary">Soft bounces</div><div class="fw-semibold text-warning"><?php echo (int) ($organizationBounceBreakdown['soft'] ?? 0); ?></div></div>
            <div class="col-6 col-md-2"><div class="small text-secondary">Blocked</div><div class="fw-semibold text-danger"><?php echo (int) ($organizationBounceBreakdown['blocked'] ?? 0); ?></div></div>
            <div class="col-6 col-md-3"><div class="small text-secondary">Domain errors</div><div class="fw-semibold text-secondary"><?php echo (int) ($organizationBounceBreakdown['domain_error'] ?? 0); ?></div></div>
            <div class="col-6 col-md-3"><div class="small text-secondary">Unknown</div><div class="fw-semibold"><?php echo (int) ($organizationBounceBreakdown['unknown'] ?? 0); ?></div></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-lg-5">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <h2 class="h5 mb-1">Delivery Health</h2>
                        <p class="text-secondary small mb-0">Standard SMTP only. No extra providers enabled.</p>
                    </div>
                    <?php
                    $label = $smtpStatusLabel;
                    $class = $smtpStatusClass;
                    include $componentsPath . '/status-badge.blade.php';
                    ?>
                </div>

                <?php if ($smtpConfigured): ?>
                <p class="mb-2"><strong><?php echo htmlspecialchars((string) $smtpSetting->host, ENT_QUOTES, 'UTF-8'); ?></strong>:<?php echo (int) ($smtpSetting->port ?? 0); ?> using <?php echo htmlspecialchars(strtoupper((string) ($smtpSetting->encryption ?? 'none')), ENT_QUOTES, 'UTF-8'); ?>.</p>
                <p class="text-secondary small mb-0">Need to validate it? Open SMTP Settings and run the built-in connection test before launching the next send.</p>
                <?php else: ?>
                <p class="mb-2">SMTP is not fully configured for this organization yet.</p>
                <p class="text-secondary small mb-0">Set the host, port, encryption, sender, and credentials in SMTP Settings, then use the built-in test before launching campaigns.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-7">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h5 mb-3">Campaign Health Summary</h2>
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-secondary small">Healthy</div>
                            <div class="fs-4 fw-bold"><?php echo (int) ($campaignHealth['healthy_campaigns'] ?? 0); ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-secondary small">Draft</div>
                            <div class="fs-4 fw-bold"><?php echo (int) ($campaignHealth['draft_campaigns'] ?? 0); ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-secondary small">Sent</div>
                            <div class="fs-4 fw-bold text-success"><?php echo (int) ($campaignHealth['sent_campaigns'] ?? 0); ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-secondary small">Failed</div>
                            <div class="fs-4 fw-bold text-danger"><?php echo (int) ($campaignHealth['failed_campaigns'] ?? 0); ?></div>
                        </div>
                    </div>
                </div>
                <p class="text-secondary small mb-0">
                    <?php if ((int) ($campaignHealth['failed_campaigns'] ?? 0) > 0): ?>
                    Some campaigns need attention because failures have been recorded.
                    <?php elseif ((int) ($campaignHealth['draft_campaigns'] ?? 0) > 0): ?>
                    Draft campaigns are waiting for recipients or launch.
                    <?php else: ?>
                    No campaign health issues are visible from the currently available dashboard data.
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12">
        <?php
        ob_start();
        ?>
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <div>
                <h2 class="h5 mb-1">Recent Delivery Events</h2>
                <p class="text-secondary small mb-0">Newest organization-wide delivery, open, click, and bounce activity.</p>
            </div>
        </div>
        <?php if (!empty($recentEvents)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr><th>When</th><th>Event</th><th>Campaign</th><th>Message</th><th>Recipient</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($recentEvents as $event): ?>
                    <tr>
                        <td><?php echo htmlspecialchars((string) ($event['timestamp'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars((string) ($event['event_type'] ?? 'unknown'), ENT_QUOTES, 'UTF-8'); ?></span></td>
                        <td><?php if (!empty($event['campaign_id'])): ?><a href="<?= $basePath ?>/campaigns/<?php echo (int) $event['campaign_id']; ?>">#<?php echo (int) $event['campaign_id']; ?></a><?php else: ?>—<?php endif; ?></td>
                        <td><?php if (!empty($event['message_id'])): ?><a href="<?= $basePath ?>/message/<?php echo (int) $event['message_id']; ?>/events">#<?php echo (int) $event['message_id']; ?></a><?php else: ?>—<?php endif; ?></td>
                        <td><?php echo !empty($event['recipient_id']) ? '#' . (int) $event['recipient_id'] : '—'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="text-secondary small mb-0">No delivery events have been recorded yet.</p>
        <?php endif; ?>
        <?php
        $contentHtml = ob_get_clean();
        $className = 'mb-0';
        include $componentsPath . '/table-shell.blade.php';
        ?>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12">
        <?php
        ob_start();
        ?>
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <div>
                <h2 class="h5 mb-1">Recent Activity</h2>
                <p class="text-secondary small mb-0">Latest campaigns created in this workspace.</p>
            </div>
            <a href="<?= $basePath ?>/campaigns" class="btn btn-sm btn-outline-primary">View all campaigns</a>
        </div>
        <?php if (!empty($recent_campaigns)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr><th>Name</th><th>Status</th><th>Recipients</th><th>Sent</th><th>Failed</th><th>Created</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_campaigns as $campaign): ?>
                    <tr>
                        <td>
                            <a href="<?= $basePath ?>/campaigns/<?php echo $campaign->id; ?>"><?php echo htmlspecialchars($campaign->name); ?></a>
                            <div class="text-secondary small">Campaign #<?php echo $campaign->id; ?></div>
                        </td>
                        <td>
                            <?php
                            $label = ucfirst((string) ($campaign->status ?? 'draft'));
                            $class = match ((string) ($campaign->status ?? 'draft')) {
                                'sent' => 'badge-success',
                                'sending' => 'badge-warning',
                                'draft' => 'badge-info',
                                'failed' => 'badge-danger',
                                default => 'badge-muted',
                            };
                            include $componentsPath . '/status-badge.blade.php';
                            ?>
                        </td>
                        <td><?php echo (int) $campaign->total_recipients; ?></td>
                        <td><?php echo (int) $campaign->sent_count; ?></td>
                        <td><?php echo (int) $campaign->failed_count; ?></td>
                        <td><?php $created = $campaign->created_at ?? null; echo is_object($created) && method_exists($created, 'format') ? $created->format('M d, Y') : (is_string($created) && $created !== '' ? date('M d, Y', strtotime($created)) : '—'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <?php
            $message = 'No recent campaign activity yet.';
            $actionHtml = '<a href="' . $basePath . '/campaigns/create">Create your first campaign</a>';
            include $componentsPath . '/empty-state.blade.php';
            ?>
        <?php endif; ?>
        <?php
        $contentHtml = ob_get_clean();
        $className = 'h-100 mb-0';
        include $componentsPath . '/table-shell.blade.php';
        ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include dirname(__DIR__, 3) . '/resources/views/blade/layout.php';
