<?php 
$title = 'Edit Campaign';
ob_start(); 
$basePath = rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/');
$componentsPath = dirname(__DIR__, 3) . '/resources/views/blade/components';
$templates = $templates ?? [];
$smtpSettings = $smtpSettings ?? [];
$noticeKey = (string) ($_GET['notice'] ?? '');

ob_start();
?>
<div class="d-flex gap-2 flex-wrap">
    <a href="<?= $basePath ?>/campaigns/<?php echo $campaign->id; ?>" class="btn btn-outline-secondary">Back to campaign</a>
</div>
<?php
$actionsHtml = ob_get_clean();
$title = 'Edit Campaign';
$subtitle = 'Update campaign structure while it is still editable.';
include $componentsPath . '/page-header.blade.php';
?>

<div class="card portal-hero mb-4">
    <div class="card-body">
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-end gap-4">
            <div>
                <div class="eyebrow mb-3">Scheduled Campaign Editing</div>
                <h2 class="display-6 fw-bold mb-2">Adjust campaign settings before execution locks the structure.</h2>
                <p class="mb-0" style="max-width: 760px; color: rgba(255,255,255,0.84) !important;">Draft, scheduled, paused, and failed campaigns can still be updated. Once sending begins, structure must stay locked.</p>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($noticeKey)): ?>
    <?php
    $noticeMessage = match ($noticeKey) {
        'smtp_missing' => 'Select an active SMTP account before saving this campaign.',
        'template_missing' => 'Choose a valid template from your organization.',
        default => ''
    };
    if ($noticeMessage !== '') {
        $message = $noticeMessage;
        $type = 'danger';
        include $componentsPath . '/flash-alert.blade.php';
    }
    ?>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-12 col-xl-8">
        <div class="card portal-surface-soft h-100">
            <div class="card-body">
                <div class="portal-section-title">Editable campaign fields</div>
                <form method="POST" action="<?= $basePath ?>/campaigns/<?php echo $campaign->id; ?>" class="row g-3">
                    <input type="hidden" name="_method" value="PUT">

                    <div class="col-12">
                        <label for="name" class="form-label">Campaign Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars((string) $campaign->name, ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="col-12">
                        <label for="template_id" class="form-label">Template</label>
                        <select class="form-select" id="template_id" name="template_id" required>
                            <option value="">-- Select a template --</option>
                            <?php foreach ($templates as $template): ?>
                            <option value="<?php echo $template->id; ?>" <?php echo (int) $campaign->template_id === (int) $template->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($template->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label for="smtp_setting_id" class="form-label">SMTP Account</label>
                        <select class="form-select" id="smtp_setting_id" name="smtp_setting_id" required <?php echo empty($smtpSettings) ? 'disabled' : ''; ?>>
                            <option value="">-- Select an SMTP account --</option>
                            <?php foreach ($smtpSettings as $smtp): ?>
                            <option value="<?php echo $smtp->id; ?>" <?php echo (int) $campaign->smtp_setting_id === (int) $smtp->id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($smtp->from_email . ' @ ' . $smtp->host, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($smtpSettings)): ?>
                        <div class="form-text text-danger">Add an active SMTP account first in <a href="<?= $basePath ?>/smtp-settings">SMTP Settings</a>.</div>
                        <?php else: ?>
                        <div class="form-text">Pick which SMTP connection this campaign should use. Manage accounts in <a href="<?= $basePath ?>/smtp-settings">SMTP Settings</a>.</div>
                        <?php endif; ?>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="scheduled_at" class="form-label">Scheduled At</label>
                        <input type="datetime-local" class="form-control" id="scheduled_at" name="scheduled_at" value="<?= !empty($campaign->scheduled_at) ? htmlspecialchars(date('Y-m-d\TH:i', strtotime((string) $campaign->scheduled_at)), ENT_QUOTES, 'UTF-8') : '' ?>">
                        <div class="form-text">Leave blank to keep the campaign as a draft instead of scheduled.</div>
                    </div>

                    <div class="col-12 d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-primary" <?php echo empty($smtpSettings) ? 'disabled' : ''; ?>>Save changes</button>
                        <a href="<?= $basePath ?>/campaigns/<?php echo $campaign->id; ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card h-100">
            <div class="card-body portal-stack">
                <div>
                    <div class="portal-section-title">Editing rules</div>
                    <h2 class="h5 mb-2">Lock policy</h2>
                    <p class="text-secondary small mb-0">Campaign structure can be edited in draft, scheduled, paused, and failed states.</p>
                </div>
                <div class="border rounded-3 p-3 bg-light-subtle">
                    <div class="fw-semibold mb-1">Editable</div>
                    <div class="small text-secondary">Draft · Scheduled · Paused · Failed</div>
                </div>
                <div class="border rounded-3 p-3 bg-light-subtle">
                    <div class="fw-semibold mb-1">Locked</div>
                    <div class="small text-secondary">Sending · Sent</div>
                </div>
                <div class="border rounded-3 p-3 bg-light-subtle">
                    <div class="fw-semibold mb-1">Why this matters</div>
                    <div class="small text-secondary">The operator should be able to fix timing, naming, and template selection until execution begins.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
include dirname(__DIR__, 3) . '/resources/views/blade/layout.php'; 
?>
