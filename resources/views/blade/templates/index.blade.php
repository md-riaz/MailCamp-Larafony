<?php
$title = 'Email Templates';
ob_start();
$basePath = rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/');
$componentsPath = dirname(__DIR__, 3) . '/resources/views/blade/components';

ob_start();
?>
<a href="<?= $basePath ?>/templates/create" class="btn btn-success">+ Create Template</a>
<?php
$actionsHtml = ob_get_clean();
$title = 'Email Templates';
$subtitle = 'Create reusable, variable-driven templates for campaigns.';
include $componentsPath . '/page-header.blade.php';
?>

<div class="card portal-hero mb-4">
    <div class="card-body">
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-end gap-4">
            <div>
                <div class="eyebrow mb-3">Content Workspace</div>
                <h2 class="display-6 fw-bold mb-2">Manage reusable email templates like product assets, not loose snippets.</h2>
                <p class="mb-0" style="max-width: 760px; color: rgba(255,255,255,0.84) !important;">Keep subjects, merge fields, and call-to-action structure consistent so campaigns launch faster and stay easier to debug.</p>
            </div>
        </div>
        <div class="portal-grid portal-grid-4 mt-4">
            <div class="portal-metric">
                <div class="metric-label">Templates</div>
                <div class="metric-value"><?php echo count($templates ?? []); ?></div>
                <div class="metric-note">Templates currently available to campaigns.</div>
            </div>
            <div class="portal-metric">
                <div class="metric-label">Active state</div>
                <div class="metric-value" style="font-size:22px;"><?php echo !empty($templates) ? 'Ready' : 'Empty'; ?></div>
                <div class="metric-note">Whether the content library is usable right now.</div>
            </div>
            <div class="portal-metric">
                <div class="metric-label">Variable model</div>
                <div class="metric-value" style="font-size:22px;">Merge</div>
                <div class="metric-note">Template bodies are driven by merge variables.</div>
            </div>
            <div class="portal-metric">
                <div class="metric-label">Launch fit</div>
                <div class="metric-value" style="font-size:22px;">SMTP-first</div>
                <div class="metric-note">Templates are aligned to the SMTP campaign workflow.</div>
            </div>
        </div>
    </div>
</div>

<?php
ob_start();
if (!empty($templates)):
?>
<div class="d-flex justify-content-between align-items-center gap-3 mb-3">
    <div>
        <div class="portal-section-title mb-1">Template inventory</div>
        <h2 class="h5 mb-1">Available templates</h2>
        <p class="text-secondary small mb-0">Review merge fields, status, and creation dates before attaching a template to a campaign.</p>
    </div>
</div>
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th>Name</th><th>Subject</th><th>Variables</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($templates as $template): ?>
            <tr>
                <td><?php echo htmlspecialchars($template->name); ?></td>
                <td><?php echo htmlspecialchars($template->subject); ?></td>
                <td><code><?php echo htmlspecialchars($template->variables ?? '[]'); ?></code></td>
                <td>
                    <?php
                    $label = $template->is_active ? 'Active' : 'Inactive';
                    $class = $template->is_active ? 'badge-success' : 'badge-muted';
                    include $componentsPath . '/status-badge.blade.php';
                    ?>
                </td>
                <td><?php $created = $template->created_at ?? null; echo is_object($created) && method_exists($created, 'format') ? $created->format('M d, Y') : (is_string($created) && $created !== '' ? date('M d, Y', strtotime($created)) : '—'); ?></td>
                <td>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="<?= $basePath ?>/templates/<?php echo $template->id; ?>" class="btn btn-sm btn-primary">Edit</a>
                        <form method="POST" action="<?= $basePath ?>/templates/<?php echo $template->id; ?>" class="d-inline">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
else:
    $message = 'No templates yet.';
    $actionHtml = '<a href="' . $basePath . '/templates/create">Create your first template</a>';
    include $componentsPath . '/empty-state.blade.php';
endif;
$contentHtml = ob_get_clean();
include $componentsPath . '/table-shell.blade.php';

$content = ob_get_clean();
include dirname(__DIR__, 3) . '/resources/views/blade/layout.php';
