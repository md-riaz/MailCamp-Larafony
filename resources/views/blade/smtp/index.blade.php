<?php
$title = 'SMTP Settings';
ob_start();
$basePath = rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/');
$componentsPath = dirname(__DIR__, 3) . '/resources/views/blade/components';
$smtpSettings = $smtpSettings ?? [];
$activeSmtp = $activeSmtp ?? null;
$canDeleteById = $canDeleteById ?? [];
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
$subtitle = 'Manage the SMTP accounts used for campaign delivery. Multiple accounts can stay active at the same time.';
ob_start();
?>
<div class="d-flex gap-2 flex-wrap">
    <?php if ($user->role === 'Superadmin'): ?>
<a href="#add-smtp-account" class="btn btn-success">Add SMTP account</a>
<?php endif; ?>
    <?php if ($user->role === 'Superadmin' || $user->role === 'Admin'): ?>
<a href="#saved-smtp-accounts" class="btn btn-outline-secondary">View saved accounts</a>
<?php endif; ?>
</div>
<?php
$actionsHtml = ob_get_clean();
include $componentsPath . '/page-header.blade.php';
?>

<div class="card portal-hero mb-4">
    <div class="card-body">
        <div class="d-flex flex-column gap-4">
            <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-end gap-4">
                <div>
                    <div class="eyebrow mb-3">Delivery Infrastructure</div>
                    <h2 class="display-6 fw-bold mb-2">Keep sender setup clean and predictable.</h2>
                    <p class="mb-0" style="max-width: 760px; color: rgba(255,255,255,0.84) !important;">Multiple SMTP accounts can stay active at the same time. Campaigns can then choose from those active accounts during setup.</p>
                </div>
                <div class="text-xl-end">
                    <div class="small text-uppercase fw-bold" style="letter-spacing:0.08em;color:rgba(255,255,255,0.72);">Active senders</div>
                    <div class="h4 mb-1"><?php echo $activeCount; ?> configured</div>
                    <div class="small" style="color: rgba(255,255,255,0.72);">
                        <?php if ($activeCount > 0): ?>
                            <?php echo $activeCount; ?> active account<?php echo $activeCount === 1 ? '' : 's'; ?> available for campaigns.
                        <?php else: ?>
                            No active sender is available yet.
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="portal-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
                <div class="portal-metric">
                    <div class="metric-label">Total accounts</div>
                    <div class="metric-value"><?php echo count($smtpSettings); ?></div>
                    <div class="metric-note">Saved SMTP connections in this workspace.</div>
                </div>
                <div class="portal-metric">
                    <div class="metric-label">Active</div>
                    <div class="metric-value"><?php echo $activeCount; ?></div>
                    <div class="metric-note">All active accounts appear in campaign setup.</div>
                </div>
                <div class="portal-metric">
                    <div class="metric-label">Inactive</div>
                    <div class="metric-value"><?php echo $inactiveCount; ?></div>
                    <div class="metric-note">Saved, but hidden from campaign selection.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="add-smtp-account"></div>

<?php if (!empty($noticeKey)): ?>
    <?php
    $noticeMessage = match ($noticeKey) {
        'smtp_saved' => 'SMTP account added. You can activate or deactivate it below.',
        'smtp_missing' => 'SMTP record not found for this organization.',
        'smtp_activated' => 'SMTP account activated. Multiple SMTP accounts can stay active at the same time.',
        'smtp_deactivated' => 'SMTP account deactivated.',
        'smtp_deleted' => 'SMTP account deleted.',
        'smtp_in_use' => 'This SMTP account cannot be deleted because campaigns already exist for this workspace or it has already been used by a campaign.',
        default => ''
    };
    if ($noticeMessage !== '') {
        $message = $noticeMessage;
        $type = match ($noticeKey) {
            'smtp_saved', 'smtp_activated', 'smtp_deactivated', 'smtp_deleted' => 'success',
            'smtp_in_use', 'smtp_missing' => 'danger',
            default => 'info',
        };
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
                    <li>After saving, keep one or more accounts active so campaigns can choose between them.</li>
                </ul>
                <p class="text-secondary small mb-0">Tip: You can keep multiple SMTP accounts active at once for different senders or traffic types.</p>
            </div>
        </div>
    </div>
</div>

<div class="card portal-surface-soft" id="saved-smtp-accounts">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <div>
                <div class="portal-section-title">Saved SMTP accounts</div>
                <h2 class="h5 mb-1">Manage and toggle availability</h2>
                <p class="text-secondary small mb-0">Every active account is selectable when creating or editing campaigns.</p>
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
                    <?php
                        $isActive = !empty($setting->is_active);
                        $canDelete = (bool) ($canDeleteById[(int) $setting->id] ?? false);
                    ?>
                    <tr>
                        <td>#<?php echo (int) $setting->id; ?></td>
                        <td><?php echo htmlspecialchars((string) ($setting->from_name ?? ''), ENT_QUOTES, 'UTF-8'); ?><br><small class="text-secondary"><?php echo htmlspecialchars((string) ($setting->from_email ?? '—'), ENT_QUOTES, 'UTF-8'); ?></small></td>
                        <td><?php echo htmlspecialchars((string) ($setting->host ?? '—'), ENT_QUOTES, 'UTF-8'); ?>:<?php echo (int) ($setting->port ?? 0); ?><br><small class="text-secondary"><?php echo htmlspecialchars((string) ($setting->encryption ?? ''), ENT_QUOTES, 'UTF-8'); ?></small></td>
                        <td>
                            <span class="badge <?php echo $isActive ? 'badge-success' : 'badge-muted'; ?>"><?php echo $isActive ? 'Active' : 'Inactive'; ?></span>
                            <?php if ($isActive): ?>
                                <div><small class="text-secondary">Usable in campaign selection</small></div>
                            <?php endif; ?>
                            <?php if ($canDelete): ?>
                                <div><small class="text-secondary">Can be deleted</small></div>
                            <?php else: ?>
                                <div><small class="text-secondary">Delete locked after campaign usage</small></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-2 flex-wrap">
                                <?php if ($isActive): ?>
                                    <form method="POST" action="<?= $basePath ?>/smtp-settings/<?php echo $setting->id; ?>/deactivate" class="d-inline">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">Deactivate</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="<?= $basePath ?>/smtp-settings/<?php echo $setting->id; ?>/activate" class="d-inline">
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Activate</button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($canDelete): ?>
                                    <form method="POST" action="<?= $basePath ?>/smtp-settings/<?php echo $setting->id; ?>/delete" class="d-inline" onsubmit="return confirm('Delete this SMTP account? This cannot be undone.');">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger" disabled title="This SMTP account is locked because campaigns already exist or it has been used before.">Delete</button>
                                <?php endif; ?>
                            </div>
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
