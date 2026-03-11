<?php
$title = 'Campaign Details';
ob_start();
$basePath = rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/');
$componentsPath = dirname(__DIR__) . '/components';
$created = $campaign->created_at ?? null;
$createdText = is_object($created) && method_exists($created, 'format') ? $created->format('F d, Y H:i:s') : (is_string($created) && $created !== '' ? date('F d, Y H:i:s', strtotime($created)) : '—');

ob_start();
?>
<div class="d-flex gap-2 flex-wrap">
    <a href="<?= $basePath ?>/campaigns" class="btn btn-outline-secondary">← Back to Campaigns</a>
    <?php if ($campaign->status === 'draft'): ?>
    <a href="<?= $basePath ?>/campaigns/<?php echo $campaign->id; ?>/recipients" class="btn btn-success">Import Recipients</a>
    <?php endif; ?>
</div>
<?php
$actionsHtml = ob_get_clean();
$title = (string) $campaign->name;
$subtitle = 'Campaign details and delivery metrics';
include $componentsPath . '/page-header.blade.php';
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="text-secondary small">Status</div><div class="fs-4 fw-bold"><?php $label = ucfirst((string) $campaign->status); $class = match ((string) $campaign->status) { 'sent' => 'badge-success', 'sending' => 'badge-warning', 'draft' => 'badge-info', 'failed' => 'badge-danger', default => 'badge-muted', }; include $componentsPath . '/status-badge.blade.php'; ?></div></div></div></div>
    <div class="col-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="text-secondary small">Total Recipients</div><div class="fs-4 fw-bold"><?php echo $campaign->total_recipients; ?></div></div></div></div>
    <div class="col-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="text-secondary small">Sent</div><div class="fs-4 fw-bold text-success"><?php echo $campaign->sent_count; ?></div></div></div></div>
    <div class="col-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="text-secondary small">Failed</div><div class="fs-4 fw-bold text-danger"><?php echo $campaign->failed_count; ?></div></div></div></div>
</div>

<?php if ($campaign->status === 'sent' || $campaign->status === 'sending'): ?>
<div class="row g-3 mb-4">
    <div class="col-md-6"><div class="card"><div class="card-body"><div class="text-secondary small">Open Rate</div><div class="fs-3 fw-bold"><?php echo $stats['open_rate'] ?? '0'; ?>%</div></div></div></div>
    <div class="col-md-6"><div class="card"><div class="card-body"><div class="text-secondary small">Click Rate</div><div class="fs-3 fw-bold"><?php echo $stats['click_rate'] ?? '0'; ?>%</div></div></div></div>
</div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-body">
        <h2 class="h5 mb-3">Campaign Information</h2>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <tr><th style="width:220px;">Campaign ID</th><td><?php echo $campaign->id; ?></td></tr>
                <tr><th>Status</th><td><?php echo htmlspecialchars($campaign->status); ?></td></tr>
                <tr><th>Created</th><td><?php echo $createdText; ?></td></tr>
                <?php if ($campaign->started_at): ?><tr><th>Started</th><td><?php echo date('F d, Y H:i:s', strtotime($campaign->started_at)); ?></td></tr><?php endif; ?>
                <?php if ($campaign->completed_at): ?><tr><th>Completed</th><td><?php echo date('F d, Y H:i:s', strtotime($campaign->completed_at)); ?></td></tr><?php endif; ?>
            </table>
        </div>
    </div>
</div>

<?php if ($campaign->status === 'draft' && $campaign->total_recipients > 0): ?>
<div class="card mb-4">
    <div class="card-body">
        <h2 class="h5 mb-2">Ready to Launch</h2>
        <p class="text-secondary">Your campaign has <?php echo $campaign->total_recipients; ?> recipients and is ready to be launched.</p>
        <form method="POST" action="<?= $basePath ?>/campaigns/<?php echo $campaign->id; ?>/launch">
            <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to launch this campaign?')">Launch Campaign</button>
        </form>
    </div>
</div>
<?php elseif ($campaign->status === 'draft'): ?>
<div class="card">
    <div class="card-body">
        <h2 class="h5 mb-2">Import Recipients</h2>
        <p class="text-secondary">Upload a CSV file with recipient information to add them to this campaign.</p>
        <form method="POST" action="<?= $basePath ?>/campaigns/<?php echo $campaign->id; ?>/recipients" enctype="multipart/form-data" class="row g-3">
            <div class="col-12">
                <label for="recipients_file" class="form-label">CSV File (email, name, custom_field1, ...)</label>
                <input type="file" class="form-control" id="recipients_file" name="recipients_file" accept=".csv" required>
                <div class="form-text">Format: email,name,custom_field1,custom_field2,...</div>
            </div>
            <div class="col-12"><button type="submit" class="btn btn-success">Import Recipients</button></div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include dirname(__DIR__, 3) . '/resources/views/blade/layout.php';
