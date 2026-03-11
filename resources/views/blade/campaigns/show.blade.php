<?php
$title = 'Campaign Details';
ob_start();
$basePath = rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/');
$componentsPath = dirname(__DIR__) . '/components';
$created = $campaign->created_at ?? null;
$createdText = is_object($created) && method_exists($created, 'format') ? $created->format('F d, Y H:i:s') : (is_string($created) && $created !== '' ? date('F d, Y H:i:s', strtotime($created)) : '—');
$startedText = $campaign->started_at ? date('F d, Y H:i:s', strtotime($campaign->started_at)) : 'Not started';
$completedText = $campaign->completed_at ? date('F d, Y H:i:s', strtotime($campaign->completed_at)) : 'Not completed';
$deliveryTotal = max((int) ($campaign->total_recipients ?? 0), 0);
$sentCount = max((int) ($campaign->sent_count ?? 0), 0);
$failedCount = max((int) ($campaign->failed_count ?? 0), 0);
$pendingCount = max($deliveryTotal - $sentCount - $failedCount, 0);
$statusLabel = ucfirst((string) $campaign->status);
$statusClass = match ((string) $campaign->status) {
    'sent' => 'badge-success',
    'sending' => 'badge-warning',
    'draft' => 'badge-info',
    'failed' => 'badge-danger',
    default => 'badge-muted',
};

ob_start();
?>
<div class="d-flex gap-2 flex-wrap">
    <a href="<?= $basePath ?>/campaigns" class="btn btn-outline-secondary">Back to campaigns</a>
    <?php if ($campaign->status === 'draft'): ?>
    <a href="<?= $basePath ?>/campaigns/<?php echo $campaign->id; ?>/recipients" class="btn btn-primary">Import recipients</a>
    <?php endif; ?>
    <?php if ($campaign->status === 'draft' && $campaign->total_recipients > 0): ?>
    <form method="POST" action="<?= $basePath ?>/campaigns/<?php echo $campaign->id; ?>/launch" class="d-inline">
        <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to launch this campaign?')">Launch campaign</button>
    </form>
    <?php endif; ?>
</div>
<?php
$actionsHtml = ob_get_clean();
$title = (string) $campaign->name;
$subtitle = 'Campaign details, delivery metrics, and next actions.';
include $componentsPath . '/page-header.blade.php';
?>

<div class="row g-3 mb-4">
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <h2 class="h5 mb-1">Overview</h2>
                        <p class="text-secondary small mb-0">Current status and delivery readiness.</p>
                    </div>
                    <?php
                    $label = $statusLabel;
                    $class = $statusClass;
                    include $componentsPath . '/status-badge.blade.php';
                    ?>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-secondary small">Recipients</div>
                            <div class="fs-4 fw-bold"><?php echo $deliveryTotal; ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-secondary small">Pending</div>
                            <div class="fs-4 fw-bold"><?php echo $pendingCount; ?></div>
                        </div>
                    </div>
                    <div class="col-12">
                        <p class="text-secondary small mb-0">
                            <?php if ($campaign->status === 'draft' && $deliveryTotal === 0): ?>
                            This draft still needs recipients before it can be launched.
                            <?php elseif ($campaign->status === 'draft'): ?>
                            This draft has recipients and is ready for launch.
                            <?php elseif ($campaign->status === 'failed'): ?>
                            Delivery issues were recorded for this campaign. Review failed counts before the next send.
                            <?php else: ?>
                            Delivery progress is shown below using the currently available campaign data.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h5 mb-3">Delivery Metrics</h2>
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-4">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-secondary small">Sent</div>
                            <div class="fs-4 fw-bold text-success"><?php echo $sentCount; ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-secondary small">Failed</div>
                            <div class="fs-4 fw-bold text-danger"><?php echo $failedCount; ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-secondary small">Pending</div>
                            <div class="fs-4 fw-bold"><?php echo $pendingCount; ?></div>
                        </div>
                    </div>
                </div>
                <?php if ($campaign->status === 'sent' || $campaign->status === 'sending'): ?>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-secondary small">Open Rate</div>
                            <div class="fs-5 fw-bold"><?php echo $stats['open_rate'] ?? '0'; ?>%</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-secondary small">Click Rate</div>
                            <div class="fs-5 fw-bold"><?php echo $stats['click_rate'] ?? '0'; ?>%</div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <p class="text-secondary small mb-0">Open and click rates will appear here once delivery starts and tracking data exists.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-xl-7">
        <?php
        ob_start();
        ?>
        <h2 class="h5 mb-3">Campaign Info</h2>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <tr><th style="width:220px;">Campaign ID</th><td><?php echo $campaign->id; ?></td></tr>
                <tr><th>Status</th><td><?php echo htmlspecialchars((string) $campaign->status, ENT_QUOTES, 'UTF-8'); ?></td></tr>
                <tr><th>Created</th><td><?php echo $createdText; ?></td></tr>
                <tr><th>Started</th><td><?php echo $startedText; ?></td></tr>
                <tr><th>Completed</th><td><?php echo $completedText; ?></td></tr>
                <tr><th>Total Recipients</th><td><?php echo $deliveryTotal; ?></td></tr>
            </table>
        </div>
        <?php
        $contentHtml = ob_get_clean();
        $className = 'h-100 mb-0';
        include $componentsPath . '/table-shell.blade.php';
        ?>
    </div>
    <div class="col-12 col-xl-5">
        <?php
        ob_start();
        ?>
        <h2 class="h5 mb-3">Actions</h2>
        <div class="d-grid gap-2">
            <?php if ($campaign->status === 'draft' && $campaign->total_recipients > 0): ?>
            <form method="POST" action="<?= $basePath ?>/campaigns/<?php echo $campaign->id; ?>/launch">
                <button type="submit" class="btn btn-success w-100" onclick="return confirm('Are you sure you want to launch this campaign?')">Launch campaign</button>
            </form>
            <?php endif; ?>

            <?php if ($campaign->status === 'draft'): ?>
            <form method="POST" action="<?= $basePath ?>/campaigns/<?php echo $campaign->id; ?>/recipients" enctype="multipart/form-data" class="row g-3 border rounded-3 p-3 mx-0">
                <div class="col-12 px-0">
                    <label for="recipients_file" class="form-label">Import recipients CSV</label>
                    <input type="file" class="form-control" id="recipients_file" name="recipients_file" accept=".csv" required>
                    <div class="form-text">Format: email,name,custom_field1,custom_field2,...</div>
                </div>
                <div class="col-12 px-0">
                    <button type="submit" class="btn btn-primary">Import recipients</button>
                </div>
            </form>
            <?php endif; ?>

            <a href="<?= $basePath ?>/campaigns" class="btn btn-outline-secondary text-start">Back to campaigns</a>
        </div>
        <?php if ($campaign->status === 'draft' && $campaign->total_recipients === 0): ?>
        <p class="text-secondary small mt-3 mb-0">Import recipients first. Launch stays hidden until the campaign has recipients.</p>
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
