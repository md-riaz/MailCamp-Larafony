<?php
$title = 'Campaigns';
ob_start();
$basePath = rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/');
$componentsPath = dirname(__DIR__, 3) . '/resources/views/blade/components';
$filters = $filters ?? ['q' => '', 'status' => '', 'sort' => 'created_desc'];

ob_start();
?>
<a href="<?= $basePath ?>/campaigns/create" class="btn btn-success">+ Create Campaign</a>
<?php
$actionsHtml = ob_get_clean();
$title = 'Email Campaigns';
$subtitle = 'Manage campaign drafts, launches, and delivery performance.';
include $componentsPath . '/page-header.blade.php';
?>

<?php if (!empty($notice['message'] ?? '')): ?>
    <?php
    $message = $notice['message'];
    $type = $notice['type'] ?? 'info';
    include $componentsPath . '/flash-alert.blade.php';
    ?>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= $basePath ?>/campaigns" class="row g-3 align-items-end">
            <div class="col-12 col-lg-5">
                <label for="q" class="form-label">Search campaigns</label>
                <input type="text" class="form-control" id="q" name="q" placeholder="Search by campaign name" value="<?= htmlspecialchars($filters['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-12 col-md-4 col-lg-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All statuses</option>
                    <?php foreach (($statusOptions ?? []) as $statusValue => $statusLabel): ?>
                    <option value="<?= htmlspecialchars($statusValue, ENT_QUOTES, 'UTF-8') ?>" <?= ($filters['status'] ?? '') === $statusValue ? 'selected' : '' ?>><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-4 col-lg-3">
                <label for="sort" class="form-label">Sort</label>
                <select class="form-select" id="sort" name="sort">
                    <option value="created_desc" <?= ($filters['sort'] ?? 'created_desc') === 'created_desc' ? 'selected' : '' ?>>Newest first</option>
                    <option value="created_asc" <?= ($filters['sort'] ?? '') === 'created_asc' ? 'selected' : '' ?>>Oldest first</option>
                </select>
            </div>
            <div class="col-12 col-md-4 col-lg-1 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary w-100">Apply</button>
            </div>
            <div class="col-12">
                <a href="<?= $basePath ?>/campaigns" class="btn btn-link px-0">Reset filters</a>
            </div>
        </form>
    </div>
</div>

<?php
ob_start();
if (!empty($campaigns)):
?>
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
                <td>
                    <?php
                    $label = $campaign->statusLabel;
                    $class = $campaign->statusBadgeClass;
                    include $componentsPath . '/status-badge.blade.php';
                    ?>
                </td>
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
<?php
else:
    $message = 'No campaigns found.';
    $actionHtml = '<a href="' . $basePath . '/campaigns/create">Create your first campaign</a>';
    include $componentsPath . '/empty-state.blade.php';
endif;
$contentHtml = ob_get_clean();
include $componentsPath . '/table-shell.blade.php';

$content = ob_get_clean();
include dirname(__DIR__, 3) . '/resources/views/blade/layout.php';
