<?php 
$title = 'Edit Template';
ob_start(); 
$basePath = rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Edit Email Template</h1>
    <a href="<?= $basePath ?>/templates" class="btn btn-outline-secondary">Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= $basePath ?>/templates/<?php echo $template->id; ?>" class="row g-3">
            <div class="col-12">
                <label for="name" class="form-label">Template Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($template->name); ?>" required>
            </div>

            <div class="col-12">
                <label for="subject" class="form-label">Email Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" value="<?php echo htmlspecialchars($template->subject); ?>" placeholder="Use {{variable}} for dynamic content" required>
            </div>

            <div class="col-12">
                <label for="html_content" class="form-label">HTML Content</label>
                <textarea class="form-control font-monospace" id="html_content" name="html_content" style="min-height: 320px;" required><?php echo htmlspecialchars($template->html_content); ?></textarea>
                <div class="form-text">Use {{variable}} for dynamic content. Example: {{name}}, {{email}}, etc.</div>
            </div>

            <div class="col-12 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-success">Update Template</button>
                <a href="<?= $basePath ?>/templates" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
include dirname(__DIR__, 3) . '/resources/views/blade/layout.php'; 
?>