<?php 
$title = 'Email Templates';
ob_start(); 
$basePath = rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/');
?>

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Email Templates</h1>
        <p class="text-secondary mb-0">Create reusable, variable-driven templates for campaigns.</p>
    </div>
    <div>
        <a href="<?= $basePath ?>/templates/create" class="btn btn-success">+ Create Template</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($templates)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Name</th><th>Subject</th><th>Variables</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($templates as $template): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($template->name); ?></td>
                        <td><?php echo htmlspecialchars($template->subject); ?></td>
                        <td><code><?php echo htmlspecialchars($template->variables ?? '[]'); ?></code></td>
                        <td><span class="badge <?php echo $template->is_active ? 'badge-success' : 'badge-muted'; ?>"><?php echo $template->is_active ? 'Active' : 'Inactive'; ?></span></td>
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
        <?php else: ?>
        <div class="text-center py-5 text-secondary">
            <p class="mb-2">No templates yet.</p>
            <a href="<?= $basePath ?>/templates/create">Create your first template</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
include dirname(__DIR__, 3) . '/resources/views/blade/layout.php'; 
?>