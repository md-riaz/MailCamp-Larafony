<?php
$title = 'Create Campaign';
ob_start();
$basePath = rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/');
$componentsPath = dirname(__DIR__, 3) . '/resources/views/blade/components';
$templates = $templates ?? [];
$smtpSettings = $smtpSettings ?? [];
$noticeKey = (string) ($_GET['notice'] ?? '');
$templatesJson = json_encode(array_map(static fn($t) => [
    'id' => $t->id,
    'name' => $t->name,
    'subject' => $t->subject,
    'html_content' => $t->html_content,
], $templates), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

ob_start();
?>
<div class="d-flex gap-2 flex-wrap">
    <a href="<?= $basePath ?>/campaigns" class="btn btn-outline-secondary">Back to campaigns</a>
</div>
<?php
$actionsHtml = ob_get_clean();
$title = 'Create Campaign';
$subtitle = 'Start fast with subject, HTML, sender, and optional template prefill.';
include $componentsPath . '/page-header.blade.php';
?>

<?php if (!empty($noticeKey)): ?>
    <?php
    $noticeMessage = match ($noticeKey) {
        'smtp_missing' => 'Select an active SMTP account before creating a campaign.',
        'content_missing' => 'Subject and HTML content are required to create a campaign.',
        default => ''
    };
    if ($noticeMessage !== '') {
        $message = $noticeMessage;
        $type = 'danger';
        include $componentsPath . '/flash-alert.blade.php';
    }
    ?>
<?php endif; ?>

<?php if (empty($smtpSettings)): ?>
    <?php
    $message = 'Add at least one active SMTP account in SMTP Settings before creating a campaign.';
    $type = 'warning';
    include $componentsPath . '/flash-alert.blade.php';
    ?>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-12 col-xl-8">
        <div class="card h-100">
            <div class="card-body">
                <form method="POST" action="<?= $basePath ?>/campaigns" class="row g-3">
                    <div class="col-12 col-md-7">
                        <label for="name" class="form-label">Campaign Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="e.g. April launch update" required>
                    </div>
                    <div class="col-12 col-md-5">
                        <label for="smtp_setting_id" class="form-label">SMTP Account</label>
                        <select class="form-select" id="smtp_setting_id" name="smtp_setting_id" required <?php echo empty($smtpSettings) ? 'disabled' : ''; ?>>
                            <option value="">-- Select an SMTP account --</option>
                            <?php foreach ($smtpSettings as $smtp): ?>
                            <option value="<?php echo $smtp->id; ?>"><?php echo htmlspecialchars($smtp->from_email . ' @ ' . $smtp->host, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-8">
                        <label for="subject" class="form-label">Email Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject" placeholder="Use {{name}} for personalization" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="template_loader" class="form-label">Start from template</label>
                        <select class="form-select" id="template_loader">
                            <option value="">-- Optional prefill --</option>
                            <?php foreach ($templates as $template): ?>
                            <option value="<?php echo $template->id; ?>"><?php echo htmlspecialchars($template->name, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-2">
                            <label for="html_content" class="form-label mb-0">HTML Content</label>
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-insert-variable="{{name}}">{{name}}</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-insert-variable="{{email}}">{{email}}</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-insert-variable="{{unsubscribe_url}}">{{unsubscribe_url}}</button>
                            </div>
                        </div>
                        <textarea class="form-control font-monospace" id="html_content" name="html_content" style="min-height: 320px;" required></textarea>
                        <div class="form-text">Edit freely here. The campaign will keep its own backing template and can still be reused later if you choose.</div>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="save_as_template" name="save_as_template" checked>
                            <label class="form-check-label" for="save_as_template">Save this as a reusable template too</label>
                        </div>
                    </div>

                    <div class="col-12 d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-success" <?php echo empty($smtpSettings) ? 'disabled' : ''; ?>>Create campaign</button>
                        <a href="<?= $basePath ?>/campaigns" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <?php
        ob_start();
        ?>
        <h2 class="h5 mb-3">Quick start</h2>
        <div class="small text-secondary mb-3">Keep the first step focused: choose sender, draft the message, then continue in the campaign workspace for recipients, scheduling, and launch.</div>
        <div class="d-grid gap-2">
            <a href="<?= $basePath ?>/smtp-settings" class="btn btn-outline-secondary text-start">Manage SMTP accounts</a>
            <a href="<?= $basePath ?>/templates" class="btn btn-outline-secondary text-start">Browse templates</a>
        </div>
        <?php
        $contentHtml = ob_get_clean();
        $className = 'h-100 mb-0';
        include $componentsPath . '/table-shell.blade.php';
        ?>
    </div>
</div>

<?php
$editorAssets = dirname(__DIR__, 1) . '/templates/_editor_assets.blade.php';
ob_start();
include $editorAssets;
?>
<script>
(function() {
    const templates = <?php echo $templatesJson ?: '[]'; ?>;
    const selector = document.getElementById('template_loader');
    const subjectInput = document.getElementById('subject');
    const textarea = document.getElementById('html_content');

    if (!selector) return;

    selector.addEventListener('change', function() {
        const chosen = templates.find(t => String(t.id) === String(this.value));
        if (!chosen) {
            return;
        }

        if (subjectInput) {
            subjectInput.value = chosen.subject || '';
        }

        if (window.mailcampEditor) {
            window.mailcampEditor.setData(chosen.html_content || '');
        } else if (textarea) {
            textarea.value = chosen.html_content || '';
        }
    });
})();
</script>
<?php
$scripts = ob_get_clean();
$content = ob_get_clean();
include dirname(__DIR__, 3) . '/resources/views/blade/layout.php';
?>
