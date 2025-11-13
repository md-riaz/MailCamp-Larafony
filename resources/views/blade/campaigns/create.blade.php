<?php 
$title = 'Create Campaign';
ob_start(); 
?>

<h1>Create Email Campaign</h1>

<div class="card">
    <form method="POST" action="/campaigns">
        <div class="form-group">
            <label for="name">Campaign Name</label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="template_id">Select Template</label>
            <select id="template_id" name="template_id" required>
                <option value="">-- Select a template --</option>
                <?php if (!empty($templates)): ?>
                    <?php foreach ($templates as $template): ?>
                    <option value="<?php echo $template->id; ?>">
                        <?php echo htmlspecialchars($template->name); ?>
                    </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <?php if (empty($templates)): ?>
            <small>No templates available. <a href="/templates/create">Create a template first</a></small>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-success" <?php echo empty($templates) ? 'disabled' : ''; ?>>Create Campaign</button>
            <a href="/campaigns" class="btn">Cancel</a>
        </div>
    </form>
</div>

<?php 
$content = ob_get_clean(); 
include __DIR__ . '/../layout.php'; 
?>
