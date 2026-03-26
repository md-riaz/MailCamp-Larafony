<?php
$title = 'SMTP Settings';
ob_start();
$basePath = rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/');
$componentsPath = dirname(__DIR__, 3) . '/resources/views/blade/components';
$smtpSettings = $smtpSettings ?? [];
$activeSmtp = $activeSmtp ?? null;
$noticeKey = (string) ($_GET['notice'] ?? '');
$activeCount = 0;
$inactiveCount = 0;
foreach ($smtpSettings as $setting) {
    if (!empty($setting->is_active)) {
        $activeCount++;
    } else {
        $inactiveCount++;
    }
}

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
                <p class="mb-0" style="max-width: 760px; color: rgba(255,255,255,0.84) !important;">Use multiple SMTP connections to isolate traffic, swap senders safely, and keep campaigns routed through the right identity.</p>
            </div>
            <div class="portal-grid portal-grid-4 w-100 w-xl-auto">
                <div class="portal-metric">
                    <div class="metric-label">Active SMTP</div>
                    <div class="metric-value"><?php echo $activeCount; ?></div>
                    <div class="metric-note">Enabled accounts available to campaigns.</div>
                </div>
                <div class="portal-metric">
                    <div class="metric-label">Inactive SMTP</div>
                    <div class="metric-value"><?php echo $inactiveCount; ?></div>
                    <div class="metric-note">Saved but currently disabled.</div>
                </div>
                <div class="portal-metric">
                    <div class="metric-label">Default sender</div>
                    <div class="metric-value" style="font-size:22px;"><?php echo $activeSmtp ? htmlspecialchars($activeSmtp->from_email, ENT_QUOTES, 'UTF-8') : 'Not set'; ?></div>
                    <div class="metric-note">Campaigns pick from active accounts.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($noticeKey)): ?>
    <?php
    $noticeMessage = match ($noticeKey) {
        'smtp_saved' => 'SMTP account added. You can activate or deactivate it below.',
        'smtp_missing' => 'SMTP record not found for this organization.',
        'smtp_activated' => 'SMTP account activated.',
        'smtp_deactivated' => 'SMTP account deactivated.',
        default => ''
    };
    if ($noticeMessage !== '') {
        $message = $noticeMessage;
        $type = str_starts_with($noticeKey, 'smtp_a') ? 'success' : ($noticeKey === 'smtp_saved' ? 'success' : 'danger');
        include $componentsPath . '/flash-alert.blade.php';
    }
    ?>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-12 col-xl-8">
        <div class="card portal-surface-soft h-100">
            <div class="card-body">
                <div class="portal-section-title">Add SMTP account</div>
                <form method="POST" action="<?= $basePath ?>/smtp-settings" class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="host" class="form-label">SMTP Host</label>
                        <input type="text" class="form-control" id="host" name="host" value="" placeholder="smtp.example.com" required>
                    </div>

                    <div class="col-6 col-md-3">
                        <label for="port" class="form-label">SMTP Port</label>
                        <input type="number" class="form-control" id="port" name="port" value="587" required>
                    </div>

                    <div class="col-6 col-md-3">
                        <label for="encryption" class="form-label">Encryption</label>
                        <select class="form-select" id="encryption" name="encryption" required>
                            <option value="tls" selected>TLS</option>
                            <option value="ssl">SSL</option>
                            <option value="none">None</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="username" class="form-label">SMTP Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="" placeholder="user@example.com" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="password" class="form-label">SMTP Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="App password or SMTP credential" required>
                        <div class="form-text">Use an app-specific password or provider-issued credential when available.</div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="from_email" class="form-label">From Email</label>
                        <input type="email" class="form-control" id="from_email" name="from_email" value="" placeholder="sender@yourdomain.com" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="from_name" class="form-label">From Name</label>
                        <input type="text" class="form-control" id="from_name" name="from_name" value="" placeholder="Marketing Team" required>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" checked>
                            <label class="form-check-label" for="is_active">Mark this account as active/usable</label>
                        </div>
                        <div class="form-text">Active accounts show up when creating or editing campaigns.</div>
                    </div>

                    <div class="col-12 d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-success">Add SMTP account</button>
                        <a href="<?= $basePath ?>/campaigns/create" class="btn btn-outline-secondary">Create a campaign</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card h-100">
            <div class="card-body portal-stack">
                <div class="portal-section-title">Setup guidance</div>
                <h2 class="h5 mb-2">How to configure SMTP</h2>
                <ul class="text-secondary small mb-3 ps-3">
                    <li>Use TLS on port 587 when your provider supports it. Use SSL/465 only if required.</li>
                    <li>Set <strong>From Email</strong> to a domain you control and that has SPF/DKIM configured.</li>
                    <li>For Gmail/Workspace, create an app password and use <code>smtp.gmail.com</code> with TLS/587.</li>
                    <li>For SendGrid, use username <code>apikey</code> and a generated API key as the password.</li>
                    <li>After saving, select this account on the campaign form to route sends through it.</li>
                </ul>
                <p class="text-secondary small mb-0">Tip: Keep multiple accounts active to A/B sender reputation or separate transactional vs marketing traffic.</p>
            </div>
        </div>
    </div>
</div>

<div class="card portal-surface-soft">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <div>
                <div class="portal-section-title">Saved SMTP accounts</div>
                <h2 class="h5 mb-1">Manage and toggle availability</h2>
                <p class="text-secondary small mb-0">Active accounts are selectable when creating or editing campaigns.</p>
            </div>
        </div>

        <?php if (!empty($smtpSettings)): ?>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr><th>ID</th><th>From</th><th>Host</th><th>Status</th><th style="width:160px;">Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($smtpSettings as $setting): ?>
                    <?php $isActive = !empty($setting->is_active); ?>
                    <tr>
                        <td>#<?php echo (int) $setting->id; ?></td>
                        <td><?php echo htmlspecialchars((string) ($setting->from_name ?? ''), ENT_QUOTES, 'UTF-8'); ?><br><small class="text-secondary"><?php echo htmlspecialchars((string) ($setting->from_email ?? '—'), ENT_QUOTES, 'UTF-8'); ?></small></td>
                        <td><?php echo htmlspecialchars((string) ($setting->host ?? '—'), ENT_QUOTES, 'UTF-8'); ?>:<?php echo (int) ($setting->port ?? 0); ?><br><small class="text-secondary"><?php echo htmlspecialchars((string) ($setting->encryption ?? ''), ENT_QUOTES, 'UTF-8'); ?></small></td>
                        <td>
                            <span class="badge <?php echo $isActive ? 'badge-success' : 'badge-muted'; ?>"><?php echo $isActive ? 'Active' : 'Inactive'; ?></span>
                        </td>
                        <td>
                            <?php if ($isActive): ?>
                                <form method="POST" action="<?= $basePath ?>/smtp-settings/<?php echo $setting->id; ?>/deactivate" class="d-inline">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Deactivate</button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="<?= $basePath ?>/smtp-settings/<?php echo $setting->id; ?>/activate" class="d-inline">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Activate</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="text-secondary small mb-0">No SMTP accounts exist yet. Add at least one active connection above to unlock campaign sending.</p>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include dirname(__DIR__, 3) . '/resources/views/blade/layout.php';
