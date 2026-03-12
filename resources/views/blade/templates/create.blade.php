<?php 
$title = 'Create Template';
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
$title = 'Create Email Template';
$subtitle = 'Build a reusable template that can survive real SMTP campaigns and merge-field validation.';
include $componentsPath . '/page-header.blade.php';
?>

<div class="card portal-hero mb-4">
    <div class="card-body">
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-end gap-4">
            <div>
                <div class="eyebrow mb-3">Template Builder</div>
                <h2 class="display-6 fw-bold mb-2">Create campaign-ready content with merge fields and unsubscribe-safe structure.</h2>
                <p class="mb-0" style="max-width: 760px; color: rgba(255,255,255,0.84) !important;">Templates are product assets. Build them once, keep the structure disciplined, and launch campaigns with less guesswork.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <?= $actionsHtml ?>
            </div>
        </div>
    </div>
</div>

<div class="card portal-surface-soft mb-4">
    <div class="card-body">
        <div class="portal-section-title">Compose template</div>
        <form method="POST" action="<?= $basePath ?>/templates" class="row g-3">
            <div class="col-12">
                <label for="name" class="form-label">Template Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>

            <div class="col-12">
                <label for="subject" class="form-label">Email Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" placeholder="Use {{variable}} for dynamic content" required>
            </div>

            <div class="col-12">
                <label for="html_content" class="form-label">HTML Content</label>
                <textarea class="form-control font-monospace" id="html_content" name="html_content" style="min-height: 320px;" required></textarea>
                <div class="form-text">Use {{variable}} for dynamic content. Example: {{name}}, {{email}}, etc.</div>
            </div>

            <div class="col-12 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-success">Create Template</button>
                <a href="<?= $basePath ?>/templates" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="portal-section-title">Reference</div>
        <h2 class="h5 mb-3">Example Template</h2>
<pre class="bg-light rounded p-3 border small mb-0">&lt;html&gt;
&lt;body&gt;
    &lt;h1&gt;Hello {{name}}!&lt;/h1&gt;
    &lt;p&gt;Thank you for subscribing to our newsletter.&lt;/p&gt;
    &lt;p&gt;Your email address is: {{email}}&lt;/p&gt;
    &lt;p&gt;Best regards,&lt;br&gt;The Team&lt;/p&gt;
    &lt;p&gt;&lt;small&gt;&lt;a href="{{unsubscribe_url}}"&gt;Unsubscribe&lt;/a&gt;&lt;/small&gt;&lt;/p&gt;
&lt;/body&gt;
&lt;/html&gt;</pre>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
include dirname(__DIR__, 3) . '/resources/views/blade/layout.php'; 
?>