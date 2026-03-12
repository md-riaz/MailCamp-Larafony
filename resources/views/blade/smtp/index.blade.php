<?php
$title = 'SMTP Settings';
ob_start();
$basePath = rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/');
$componentsPath = dirname(__DIR__, 3) . '/resources/views/blade/components';

$title = 'SMTP Settings';
$subtitle = 'Configure the SMTP connection used for campaign delivery.';
$actionsHtml = '';
include $componentsPath . '/page-header.blade.php';
?>

<div class="card portal-hero mb-4">
    <div class="card-body">
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-end gap-4">
            <div>
                <div class="eyebrow mb-3">Delivery Infrastructure</div>
                <h2 class="display-6 fw-bold mb-2">Configure the SMTP foundation that every campaign depends on.</h2>
                <p class="mb-0" style="max-width: 760px; color: rgba(255,255,255,0.84) !important;">Treat this as production infrastructure: sender identity, authentication, encryption, and connection testing all determine whether campaigns behave like a platform or a gamble.</p>
            </div>
        </div>
    </div>
</div>

<div class="card portal-surface-soft">
    <div class="card-body">
        <div class="portal-section-title">SMTP configuration</div>
        <form method="POST" action="<?= $basePath ?>/smtp-settings" class="row g-3">
            <div class="col-12 col-md-6">
                <label for="host" class="form-label">SMTP Host</label>
                <input type="text" class="form-control" id="host" name="host" value="<?php echo htmlspecialchars($smtpSetting->host ?? ''); ?>" required>
            </div>

            <div class="col-6 col-md-3">
                <label for="port" class="form-label">SMTP Port</label>
                <input type="number" class="form-control" id="port" name="port" value="<?php echo htmlspecialchars($smtpSetting->port ?? '587'); ?>" required>
            </div>

            <div class="col-6 col-md-3">
                <label for="encryption" class="form-label">Encryption</label>
                <select class="form-select" id="encryption" name="encryption" required>
                    <option value="tls" <?php echo ($smtpSetting->encryption ?? 'tls') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                    <option value="ssl" <?php echo ($smtpSetting->encryption ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                    <option value="none" <?php echo ($smtpSetting->encryption ?? '') === 'none' ? 'selected' : ''; ?>>None</option>
                </select>
            </div>

            <div class="col-12 col-md-6">
                <label for="username" class="form-label">SMTP Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($smtpSetting->username ?? ''); ?>" required>
            </div>

            <div class="col-12 col-md-6">
                <label for="password" class="form-label">SMTP Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="<?php echo isset($smtpSetting) ? '••••••••' : ''; ?>" <?php echo !isset($smtpSetting) ? 'required' : ''; ?>>
                <?php if (isset($smtpSetting)): ?><div class="form-text">Leave blank to keep existing password</div><?php endif; ?>
            </div>

            <div class="col-12 col-md-6">
                <label for="from_email" class="form-label">From Email</label>
                <input type="email" class="form-control" id="from_email" name="from_email" value="<?php echo htmlspecialchars($smtpSetting->from_email ?? ''); ?>" required>
            </div>

            <div class="col-12 col-md-6">
                <label for="from_name" class="form-label">From Name</label>
                <input type="text" class="form-control" id="from_name" name="from_name" value="<?php echo htmlspecialchars($smtpSetting->from_name ?? ''); ?>" required>
            </div>

            <div class="col-12 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-success">Save Settings</button>
                <?php if (isset($smtpSetting)): ?>
                <button type="button" class="btn btn-outline-primary" onclick="testConnection()">Test Connection</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<script>
function testConnection() {
    fetch('<?= $basePath ?>/smtp-settings/test', { method: 'POST' })
        .then(response => response.json())
        .then(data => alert(data.message))
        .catch(error => alert('Test failed: ' + error));
}
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__, 3) . '/resources/views/blade/layout.php';
