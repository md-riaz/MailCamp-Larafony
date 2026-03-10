<?php 
$title = 'Edit Template';
ob_start(); 
?>

<h1>Edit Email Template</h1>

<div class="card">
    <form method="POST" action="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/templates/<?php echo $template->id; ?>">
        <div class="form-group">
            <label for="name">Template Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($template->name); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="subject">Email Subject</label>
            <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($template->subject); ?>" placeholder="Use {{variable}} for dynamic content" required>
        </div>
        
        <div class="form-group">
            <label for="html_content">HTML Content</label>
            <textarea id="html_content" name="html_content" style="min-height: 300px; font-family: monospace;" required><?php echo htmlspecialchars($template->html_content); ?></textarea>
            <small>Use {{variable}} for dynamic content. Example: {{name}}, {{email}}, etc.</small>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-success">Update Template</button>
            <a href="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/templates" class="btn">Cancel</a>
        </div>
    </form>
</div>

<?php 
$content = ob_get_clean(); 
include __DIR__ . '/../layout.php'; 
?>
