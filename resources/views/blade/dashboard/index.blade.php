<?php 
$title = 'Dashboard';
ob_start(); 
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Overview of your campaigns, recipients, and templates.</p>
    </div>
    <div class="toolbar">
        <a href="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/campaigns/create" class="btn btn-success">+ New Campaign</a>
        <a href="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/templates/create" class="btn">+ New Template</a>
    </div>
</div>

<div class="stats">
    <div class="stat-card">
        <h3>Total Campaigns</h3>
        <div class="stat-value"><?php echo $stats['total_campaigns'] ?? 0; ?></div>
    </div>
    <div class="stat-card">
        <h3>Active Campaigns</h3>
        <div class="stat-value"><?php echo $stats['active_campaigns'] ?? 0; ?></div>
    </div>
    <div class="stat-card">
        <h3>Total Recipients</h3>
        <div class="stat-value"><?php echo $stats['total_recipients'] ?? 0; ?></div>
    </div>
    <div class="stat-card">
        <h3>Templates</h3>
        <div class="stat-value"><?php echo $stats['total_templates'] ?? 0; ?></div>
    </div>
</div>

<div class="card">
    <h2>Recent Campaigns</h2>
    <?php if (!empty($recent_campaigns)): ?>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Status</th>
                <th>Recipients</th>
                <th>Sent</th>
                <th>Failed</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_campaigns as $campaign): ?>
            <tr>
                <td><a href="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/campaigns/<?php echo $campaign->id; ?>"><?php echo htmlspecialchars($campaign->name); ?></a></td>
                <td><?php echo htmlspecialchars($campaign->status); ?></td>
                <td><?php echo $campaign->total_recipients; ?></td>
                <td><?php echo $campaign->sent_count; ?></td>
                <td><?php echo $campaign->failed_count; ?></td>
                <td><?php echo $campaign->created_at->format('M d, Y'); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="empty-state">
        <p>No campaigns yet.</p>
        <p><a href="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/campaigns/create">Create your first campaign</a></p>
    </div>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Quick Actions</h2>
    <a href="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/campaigns/create" class="btn btn-success">Create New Campaign</a>
    <a href="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/templates/create" class="btn">Create Template</a>
    <?php if (isset($user) && $user->isAdmin()): ?>
    <a href="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/smtp-settings" class="btn">Configure SMTP</a>
    <?php endif; ?>
</div>

<?php 
$content = ob_get_clean(); 
include dirname(__DIR__, 3) . '/resources/views/blade/layout.php'; 
?>
