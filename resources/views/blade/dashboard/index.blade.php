<?php
$title = 'Dashboard';
ob_start();
$basePath = rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/');
$componentsPath = dirname(__DIR__) . '/components';
$campaignHealth = $campaignHealth ?? [];
$smtpConfigured = isset($smtpSetting) && $smtpSetting && $smtpSetting->validate();
$smtpStatusLabel = $smtpConfigured ? 'Configured' : 'Needs setup';
$smtpStatusClass = $smtpConfigured ? 'badge-success' : 'badge-warning';

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

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="text-secondary small">Total Campaigns</div><div class="fs-3 fw-bold"><?php echo $stats['total_campaigns'] ?? 0; ?></div></div></div></div>
    <div class="col-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="text-secondary small">Active Campaigns</div><div class="fs-3 fw-bold"><?php echo $stats['active_campaigns'] ?? 0; ?></div></div></div></div>
    <div class="col-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="text-secondary small">Total Recipients</div><div class="fs-3 fw-bold"><?php echo $stats['total_recipients'] ?? 0; ?></div></div></div></div>
    <div class="col-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="text-secondary small">Templates</div><div class="fs-3 fw-bold"><?php echo $stats['total_templates'] ?? 0; ?></div></div></div></div>
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
    <div class="col-12 col-xl-8">
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
    <div class="col-12 col-xl-4">
        <?php
        ob_start();
        ?>
        <h2 class="h5 mb-3">Quick Actions</h2>
        <div class="d-grid gap-2">
            <a href="<?= $basePath ?>/campaigns/create" class="btn btn-success text-start">Create new campaign</a>
            <a href="<?= $basePath ?>/templates/create" class="btn btn-primary text-start">Create template</a>
            <a href="<?= $basePath ?>/campaigns" class="btn btn-outline-primary text-start">Review campaigns</a>
            <?php if (isset($user) && $user->isAdmin()): ?>
            <a href="<?= $basePath ?>/smtp-settings" class="btn btn-outline-secondary text-start">Configure SMTP</a>
            <?php endif; ?>
        </div>
        <p class="text-secondary small mt-3 mb-0">Same actions, cleaner order. Create, review, then validate delivery settings before launch.</p>
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
