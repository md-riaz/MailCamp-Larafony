<?php 
$title = 'Create Campaign';
ob_start(); 
$basePath = rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/');
$componentsPath = dirname(__DIR__, 3) . '/resources/views/blade/components';
$templates = $templates ?? [];

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

<?php if (empty($templates)): ?>
    <?php
    $message = 'No active templates are available yet. Create a template first so campaigns can start with reusable content.';
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
                        <?php else: ?>
                        <div class="form-text">Choose a reusable template now. You’ll import recipients and run safety checks from the campaign detail page next.</div>
                        <?php endif; ?>
                    </div>

                    <div class="col-12 d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-success" <?php echo empty($templates) ? 'disabled' : ''; ?>>Create Campaign</button>
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
$content = ob_get_clean(); 
include dirname(__DIR__, 3) . '/resources/views/blade/layout.php'; 
?>