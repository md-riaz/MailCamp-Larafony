<?php 
$title = 'Create Campaign';
ob_start(); 
$basePath = rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Create Email Campaign</h1>
    <a href="<?= $basePath ?>/campaigns" class="btn btn-outline-secondary">Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= $basePath ?>/campaigns" class="row g-3">
            <div class="col-12">
                <label for="name" class="form-label">Campaign Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>

            <div class="col-12">
                <label for="template_id" class="form-label">Select Template</label>
                <select class="form-select" id="template_id" name="template_id" required>
                    <option value="">-- Select a template --</option>
                    <?php if (!empty($templates)): foreach ($templates as $template): ?>
                    <option value="<?php echo $template->id; ?>"><?php echo htmlspecialchars($template->name); ?></option>
                    <?php endforeach; endif; ?>
                </select>
                <?php if (empty($templates)): ?>
                <div class="form-text">No templates available. <a href="<?= $basePath ?>/templates/create">Create a template first</a>.</div>
                <?php endif; ?>
            </div>

            <div class="col-12 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-success" <?php echo empty($templates) ? 'disabled' : ''; ?>>Create Campaign</button>
                <a href="<?= $basePath ?>/campaigns" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
include dirname(__DIR__, 3) . '/resources/views/blade/layout.php'; 
?>