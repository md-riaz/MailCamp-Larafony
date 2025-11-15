<?php 
$title = 'Email Templates';
ob_start(); 
?>

<h1>Email Templates</h1>

<div style="margin-bottom: 20px;">
    <a href="/templates/create" class="btn btn-success">Create New Template</a>
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
                <td><?php echo $template->is_active ? 'Active' : 'Inactive'; ?></td>
                <td><?php echo $template->created_at->format('M d, Y'); ?></td>
                <td>
                    <a href="/templates/<?php echo $template->id; ?>/edit" class="btn">Edit</a>
                    <form method="POST" action="/templates/<?php echo $template->id; ?>" style="display: inline;">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>No templates yet. <a href="/templates/create">Create your first template</a></p>
    <?php endif; ?>
</div>

<?php 
$content = ob_get_clean(); 
include __DIR__ . '/../layout.php'; 
?>
