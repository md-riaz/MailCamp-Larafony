<?php 
$title = 'Dashboard';
ob_start(); 
?>

<h1>Dashboard</h1>

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
                <td><a href="/campaigns/<?php echo $campaign->id; ?>"><?php echo htmlspecialchars($campaign->name); ?></a></td>
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
    <p>No campaigns yet. <a href="/campaigns/create">Create your first campaign</a></p>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Quick Actions</h2>
    <a href="/campaigns/create" class="btn btn-success">Create New Campaign</a>
    <a href="/templates/create" class="btn">Create Template</a>
    <?php if (isset($user) && $user->isAdmin()): ?>
    <a href="/smtp-settings" class="btn">Configure SMTP</a>
    <?php endif; ?>
</div>

<?php 
$content = ob_get_clean(); 
include __DIR__ . '/../layout.php'; 
?>
