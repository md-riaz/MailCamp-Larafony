<?php 
$title = 'Email Templates';
ob_start(); 
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Email Templates</h1>
        <p class="page-subtitle">Create reusable, variable-driven templates for campaigns.</p>
    </div>
    <div class="toolbar">
        <a href="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/templates/create" class="btn btn-success">+ Create Template</a>
    </div>
</div>

<div class="card">
    <?php if (!empty($templates)): ?>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Subject</th>
                <th>Variables</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($templates as $template): ?>
            <tr>
                <td><?php echo htmlspecialchars($template->name); ?></td>
                <td><?php echo htmlspecialchars($template->subject); ?></td>
                <td><?php echo htmlspecialchars($template->variables ?? '[]'); ?></td>
                <td><span class="badge <?php echo $template->is_active ? 'badge-success' : 'badge-muted'; ?>"><?php echo $template->is_active ? 'Active' : 'Inactive'; ?></span></td>
                <td><?php echo $template->created_at->format('M d, Y'); ?></td>
                <td>
                    <a href="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/templates/<?php echo $template->id; ?>" class="btn">Edit</a>
                    <form method="POST" action="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/templates/<?php echo $template->id; ?>" style="display: inline;">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="empty-state">
        <p>No templates yet.</p>
        <p><a href="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/templates/create">Create your first template</a></p>
    </div>
    <?php endif; ?>
</div>

<?php 
$content = ob_get_clean(); 
include dirname(__DIR__, 3) . '/resources/views/blade/layout.php'; 
?>
