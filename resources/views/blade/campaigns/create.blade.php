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
    <a href="<?= $basePath ?>/templates/create" class="btn btn-outline-primary">Create template</a>
</div>
<?php
$actionsHtml = ob_get_clean();
$title = 'Create Campaign';
$subtitle = 'Start a new SMTP campaign with the right template, naming, and launch posture from the beginning.';
include $componentsPath . '/page-header.blade.php';
?>

<div class="card portal-hero mb-4">
    <div class="card-body">
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-end gap-4">
            <div>
                <div class="eyebrow mb-3">Campaign Setup</div>
                <h2 class="display-6 fw-bold mb-2">Create a campaign shell before you import recipients, validate risk, and launch.</h2>
                <p class="mb-0" style="max-width: 760px; color: rgba(255,255,255,0.84) !important;">The campaign creation step should stay clean: define the campaign name, attach a reusable template, then continue in the operator console for recipients, safety, and launch.</p>
            </div>
        </div>
        <div class="portal-grid portal-grid-4 mt-4">
            <div class="portal-metric">
                <div class="metric-label">Templates ready</div>
                <div class="metric-value"><?php echo count($templates); ?></div>
                <div class="metric-note">Reusable templates available to attach right now.</div>
            </div>
            <div class="portal-metric">
                <div class="metric-label">Flow</div>
                <div class="metric-value" style="font-size:22px;">Draft → Import</div>
                <div class="metric-note">Campaigns start as drafts before recipients and launch.</div>
            </div>
            <div class="portal-metric">
                <div class="metric-label">Launch path</div>
                <div class="metric-value" style="font-size:22px;">SMTP-first</div>
                <div class="metric-note">This setup is aligned to the SMTP delivery workflow.</div>
            </div>
            <div class="portal-metric">
                <div class="metric-label">Guardrails</div>
                <div class="metric-value" style="font-size:22px;">Safety later</div>
                <div class="metric-note">Risk and deliverability checks happen on the campaign detail page before launch.</div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($noticeKey)): ?>
    <?php
    $noticeMessage = match ($noticeKey) {
        'smtp_missing' => 'Select an active SMTP account before creating a campaign.',
        'template_missing' => 'Choose a valid template from your organization.',
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

<?php if (empty($templates)): ?>
    <?php
    $message = 'No active templates are available yet. Create a template first so campaigns can start with reusable content.';
    $type = 'warning';
    include $componentsPath . '/flash-alert.blade.php';
    ?>
<?php endif; ?>
<?php if (empty($smtpSettings)): ?>
    <?php
    $message = 'Add at least one active SMTP account so campaigns can choose the correct sender.';
    $type = 'warning';
    include $componentsPath . '/flash-alert.blade.php';
    ?>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-12 col-xl-8">
        <div class="card portal-surface-soft h-100">
            <div class="card-body">
                <div class="portal-section-title">Campaign definition</div>
                <form method="POST" action="<?= $basePath ?>/campaigns" class="row g-3">
                    <div class="col-12">
                        <label for="name" class="form-label">Campaign Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="e.g. March Product Update" required>
                        <div class="form-text">Use a name operators can recognize later in timelines, reports, and send verification.</div>
                    </div>

                    <div class="col-12 col-md-8">
                        <label for="subject" class="form-label">Email Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject" placeholder="Use {{name}} for personalization" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="template_loader" class="form-label">Start from template (optional)</label>
                        <select class="form-select" id="template_loader">
                            <option value="">-- Choose to prefill --</option>
                            <?php foreach ($templates as $template): ?>
                            <option value="<?php echo $template->id; ?>"><?php echo htmlspecialchars($template->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Selecting a template will prefill the editor; you can edit freely.</div>
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
                        <div class="form-text">Compose the campaign body. Template selection only pre-fills this editor.</div>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="save_as_template" name="save_as_template" checked>
                            <label class="form-check-label" for="save_as_template">Save this HTML as a reusable template (uses subject as name)</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <label for="smtp_setting_id" class="form-label">SMTP Account</label>
                        <select class="form-select" id="smtp_setting_id" name="smtp_setting_id" required <?php echo empty($smtpSettings) ? 'disabled' : ''; ?>>
                            <option value="">-- Select an SMTP account --</option>
                            <?php if (!empty($smtpSettings)): foreach ($smtpSettings as $smtp): ?>
                            <option value="<?php echo $smtp->id; ?>"><?php echo htmlspecialchars($smtp->from_email . ' @ ' . $smtp->host, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                        <?php if (empty($smtpSettings)): ?>
                        <div class="form-text text-danger">Add an active SMTP account first in <a href="<?= $basePath ?>/smtp-settings">SMTP Settings</a>.</div>
                        <?php else: ?>
                        <div class="form-text">Pick which SMTP connection this campaign should use. Manage accounts in <a href="<?= $basePath ?>/smtp-settings">SMTP Settings</a>.</div>
                        <?php endif; ?>
                    </div>

                    <div class="col-12 d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-success" <?php echo empty($smtpSettings) ? 'disabled' : ''; ?>>Create Campaign</button>
                        <a href="<?= $basePath ?>/campaigns" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card h-100">
            <div class="card-body portal-stack">
                <div>
                    <div class="portal-section-title">What happens next</div>
                    <h2 class="h5 mb-2">Campaign workflow</h2>
                    <p class="text-secondary small mb-0">Creation only establishes the campaign shell. Delivery readiness is handled in the operator console.</p>
                </div>
                <div class="border rounded-3 p-3 bg-light-subtle">
                    <div class="fw-semibold mb-1">1. Create draft</div>
                    <div class="small text-secondary">Name the campaign and attach a template.</div>
                </div>
                <div class="border rounded-3 p-3 bg-light-subtle">
                    <div class="fw-semibold mb-1">2. Import recipients</div>
                    <div class="small text-secondary">Upload the recipient CSV from the campaign detail page.</div>
                </div>
                <div class="border rounded-3 p-3 bg-light-subtle">
                    <div class="fw-semibold mb-1">3. Review safety</div>
                    <div class="small text-secondary">Check risk level, sender posture, and DNS deliverability guidance.</div>
                </div>
                <div class="border rounded-3 p-3 bg-light-subtle">
                    <div class="fw-semibold mb-1">4. Launch</div>
                    <div class="small text-secondary">Move to the send path only after the campaign is clean and ready.</div>
                </div>
            </div>
        </div>
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
        const templateId = this.value;
        const chosen = templates.find(t => String(t.id) === String(templateId));
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
