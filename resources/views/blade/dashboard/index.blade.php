<?php 
$title = 'Dashboard';
ob_start(); 
$basePath = rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/');
?>

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Dashboard</h1>
        <p class="text-secondary mb-0">Overview of your campaigns, recipients, and templates.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= $basePath ?>/campaigns/create" class="btn btn-success">+ New Campaign</a>
        <a href="<?= $basePath ?>/templates/create" class="btn btn-primary">+ New Template</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="text-secondary small">Total Campaigns</div><div class="fs-3 fw-bold"><?php echo $stats['total_campaigns'] ?? 0; ?></div></div></div></div>
    <div class="col-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="text-secondary small">Active Campaigns</div><div class="fs-3 fw-bold"><?php echo $stats['active_campaigns'] ?? 0; ?></div></div></div></div>
    <div class="col-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="text-secondary small">Total Recipients</div><div class="fs-3 fw-bold"><?php echo $stats['total_recipients'] ?? 0; ?></div></div></div></div>
    <div class="col-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="text-secondary small">Templates</div><div class="fs-3 fw-bold"><?php echo $stats['total_templates'] ?? 0; ?></div></div></div></div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h2 class="h5 mb-3">Recent Campaigns</h2>
        <?php if (!empty($recent_campaigns)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr><th>Name</th><th>Status</th><th>Recipients</th><th>Sent</th><th>Failed</th><th>Created</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_campaigns as $campaign): ?>
                    <tr>
                        <td><a href="<?= $basePath ?>/campaigns/<?php echo $campaign->id; ?>"><?php echo htmlspecialchars($campaign->name); ?></a></td>
                        <td><?php echo htmlspecialchars($campaign->status); ?></td>
                        <td><?php echo $campaign->total_recipients; ?></td>
                        <td><?php echo $campaign->sent_count; ?></td>
                        <td><?php echo $campaign->failed_count; ?></td>
                        <td><?php $created = $campaign->created_at ?? null; echo is_object($created) && method_exists($created, 'format') ? $created->format('M d, Y') : (is_string($created) && $created !== '' ? date('M d, Y', strtotime($created)) : '—'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-4 text-secondary">
            <p class="mb-2">No campaigns yet.</p>
            <a href="<?= $basePath ?>/campaigns/create">Create your first campaign</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h2 class="h5 mb-3">Quick Actions</h2>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= $basePath ?>/campaigns/create" class="btn btn-success">Create New Campaign</a>
            <a href="<?= $basePath ?>/templates/create" class="btn btn-primary">Create Template</a>
            <?php if (isset($user) && $user->isAdmin()): ?>
            <a href="<?= $basePath ?>/smtp-settings" class="btn btn-outline-primary">Configure SMTP</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
include dirname(__DIR__, 3) . '/resources/views/blade/layout.php'; 
?>