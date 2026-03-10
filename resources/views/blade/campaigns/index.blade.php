<?php 
$title = 'Campaigns';
ob_start(); 
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Email Campaigns</h1>
        <p class="page-subtitle">Manage campaign drafts, launches, and delivery performance.</p>
    </div>
    <div class="toolbar">
        <a href="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/campaigns/create" class="btn btn-success">+ Create Campaign</a>
    </div>
</div>

<div class="card">
    <?php if (!empty($campaigns)): ?>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Status</th>
                <th>Recipients</th>
                <th>Sent</th>
                <th>Failed</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($campaigns as $campaign): ?>
            <tr>
                <td><a href="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/campaigns/<?php echo $campaign->id; ?>"><?php echo htmlspecialchars($campaign->name); ?></a></td>
                <td>
                    <?php $statusClass = $campaign->status === 'sent' ? 'badge-success' : ($campaign->status === 'sending' ? 'badge-warning' : ($campaign->status === 'draft' ? 'badge-info' : 'badge-muted')); ?>
                    <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($campaign->status); ?></span>
                </td>
                <td><?php echo $campaign->total_recipients; ?></td>
                <td><?php echo $campaign->sent_count; ?></td>
                <td><?php echo $campaign->failed_count; ?></td>
                <td><?php echo $campaign->created_at->format('M d, Y'); ?></td>
                <td>
                    <a href="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/campaigns/<?php echo $campaign->id; ?>" class="btn">View</a>
                    <?php if ($campaign->status === 'draft'): ?>
                    <form method="POST" action="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/campaigns/<?php echo $campaign->id; ?>/launch" style="display: inline;">
                        <button type="submit" class="btn btn-success" onclick="return confirm('Launch this campaign?')">Launch</button>
                    </form>
                    <?php endif; ?>
                </td>
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

<?php 
$content = ob_get_clean(); 
include __DIR__ . '/../layout.php'; 
?>
