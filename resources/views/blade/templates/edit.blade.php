<?php 
$title = 'Edit Template';
ob_start(); 
$basePath = rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/');
$componentsPath = dirname(__DIR__, 3) . '/resources/views/blade/components';

ob_start();
?>
<div class="d-flex gap-2 flex-wrap">
    <a href="<?= $basePath ?>/templates" class="btn btn-outline-secondary">Back to templates</a>
</div>
<?php
$actionsHtml = ob_get_clean();
$title = 'Edit Email Template';
$subtitle = 'Refine reusable campaign content with image upload and merge-variable aware authoring.';
include $componentsPath . '/page-header.blade.php';
?>

<div class="card portal-hero mb-4">
    <div class="card-body">
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-end gap-4">
            <div>
                <div class="eyebrow mb-3">Template Editor</div>
                <h2 class="display-6 fw-bold mb-2">Update campaign content with a real HTML authoring surface.</h2>
                <p class="mb-0" style="max-width: 760px; color: rgba(255,255,255,0.84) !important;">This editor is now upload-ready, merge-variable aware, and better suited to serious email composition than a raw textarea.</p>
            </div>
        </div>
    </div>
</div>

<div class="card portal-surface-soft">
    <div class="card-body">
        <div class="portal-section-title">Edit template</div>
        <form method="POST" action="<?= $basePath ?>/templates/<?php echo $template->id; ?>" class="row g-3">
            <div class="col-12">
                <label for="name" class="form-label">Template Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($template->name); ?>" required>
            </div>

            <div class="col-12">
                <label for="subject" class="form-label">Email Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" value="<?php echo htmlspecialchars($template->subject); ?>" placeholder="Use &#123;&#123;variable&#125;&#125; for dynamic content" required>
            </div>

            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-2">
                    <label for="html_content" class="form-label mb-0">HTML Content</label>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-insert-variable="&#123;&#123;name&#125;&#125;">&#123;&#123;name&#125;&#125;</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-insert-variable="&#123;&#123;email&#125;&#125;">&#123;&#123;email&#125;&#125;</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-insert-variable="&#123;&#123;unsubscribe_url&#125;&#125;">&#123;&#123;unsubscribe_url&#125;&#125;</button>
                    </div>
                </div>
                <textarea class="form-control font-monospace" id="html_content" name="html_content" style="min-height: 520px;" required><?php echo htmlspecialchars($template->html_content); ?></textarea>
                <div class="form-text">Use merge variables, upload images directly, and keep unsubscribe behavior present for campaign safety.</div>
            </div>

            <div class="col-12 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-success">Update Template</button>
                <a href="<?= $basePath ?>/templates" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php 
$editorAssets = dirname(__DIR__, 3) . '/resources/views/blade/templates/_editor_assets.blade.php';
ob_start();
include $editorAssets;
$scripts = ob_get_clean();
$content = ob_get_clean(); 
include dirname(__DIR__, 3) . '/resources/views/blade/layout.php'; 
?>