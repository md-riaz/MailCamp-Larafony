<?php 
$title = 'Campaigns';
ob_start(); 
$basePath = rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/');
?>

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Email Campaigns</h1>
        <p class="text-secondary mb-0">Manage campaign drafts, launches, and delivery performance.</p>
    </div>
    <div>
        <a href="<?= $basePath ?>/campaigns/create" class="btn btn-success">+ Create Campaign</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($campaigns)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th><th>Status</th><th>Recipients</th><th>Sent</th><th>Failed</th><th>Created</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($campaigns as $campaign): ?>
                    <tr>
                        <td><a href="<?= $basePath ?>/campaigns/<?php echo $campaign->id; ?>"><?php echo htmlspecialchars($campaign->name); ?></a></td>
                        <td><span class="badge <?php echo $campaign->statusBadgeClass; ?>"><?php echo htmlspecialchars($campaign->statusLabel); ?></span></td>
                        <td><?php echo $campaign->totalRecipients; ?></td>
                        <td><?php echo $campaign->sentCount; ?></td>
                        <td><?php echo $campaign->failedCount; ?></td>
                        <td><?php echo $campaign->createdAtLabel; ?></td>
                        <td>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="<?= $basePath ?>/campaigns/<?php echo $campaign->id; ?>" class="btn btn-sm btn-primary">View</a>
                                <?php if ($campaign->canLaunch): ?>
                                <form method="POST" action="<?= $basePath ?>/campaigns/<?php echo $campaign->id; ?>/launch" class="d-inline">
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Launch this campaign?')">Launch</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-5 text-secondary">
            <p class="mb-2">No campaigns yet.</p>
            <a href="<?= $basePath ?>/campaigns/create">Create your first campaign</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
include dirname(__DIR__, 3) . '/resources/views/blade/layout.php'; 
?>