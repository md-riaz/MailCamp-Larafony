<?php
$title = 'Campaign Details';
ob_start();
$basePath = rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/');
$componentsPath = dirname(__DIR__, 3) . '/resources/views/blade/components';
$created = $campaign->created_at ?? null;
$createdText = is_object($created) && method_exists($created, 'format') ? $created->format('F d, Y H:i:s') : (is_string($created) && $created !== '' ? date('F d, Y H:i:s', strtotime($created)) : '—');
$startedText = $campaign->started_at ? date('F d, Y H:i:s', strtotime($campaign->started_at)) : 'Not started';
$completedText = $campaign->completed_at ? date('F d, Y H:i:s', strtotime($campaign->completed_at)) : 'Not completed';
$deliveryTotal = max((int) ($campaign->total_recipients ?? 0), 0);
$sentCount = max((int) ($campaign->sent_count ?? 0), 0);
$failedCount = max((int) ($campaign->failed_count ?? 0), 0);
$pendingCount = max($deliveryTotal - $sentCount - $failedCount, 0);
$campaignMetrics = $campaignMetrics ?? [];
$bounceBreakdown = $bounceBreakdown ?? ['hard' => 0, 'soft' => 0, 'blocked' => 0, 'domain_error' => 0, 'unknown' => 0];
$recentEvents = $recentEvents ?? [];
$safety = $safety ?? ['ok' => true, 'should_pause' => false, 'risk_level' => 'low', 'errors' => [], 'warnings' => [], 'metrics' => [], 'deliverability' => ['domain' => null, 'checks' => [], 'warnings' => [], 'recommendations' => []]];
$deliverability = $safety['deliverability'] ?? ['domain' => null, 'checks' => [], 'warnings' => [], 'recommendations' => []];
$riskHistory = $riskHistory ?? [];
$smtpSetting = $smtpSetting ?? null;
$smtpSettings = $smtpSettings ?? [];
$template = $template ?? null;
$templates = $templates ?? [];
$templatesJson = json_encode(array_map(static fn($t) => [
    'id' => $t->id,
    'name' => $t->name,
    'subject' => $t->subject,
    'html_content' => $t->html_content,
], $templates), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
$smtpLabel = $smtpSetting ? ($smtpSetting->from_email . ' @ ' . $smtpSetting->host) : 'Not selected';
$templateLabel = $template?->name ?? '—';
$statusLabel = ucfirst((string) $campaign->status);
$statusClass = match ((string) $campaign->status) {
    'sent' => 'badge-success',
    'sending' => 'badge-warning',
    'draft' => 'badge-info',
    'failed' => 'badge-danger',
    default => 'badge-muted',
};
$canEdit = $campaign->canEdit();
$workspaceMode = $canEdit || (string) ($_GET['workspace'] ?? '') === 'edit';

ob_start();
?>
<div class="d-flex gap-2 flex-wrap">
    <a href="<?= $basePath ?>/campaigns" class="btn btn-outline-secondary">Back to campaigns</a>
    <?php if (($user->role === 'Admin' || $user->role === 'Superadmin') && $campaign->canStart() && $campaign->total_recipients > 0): ?>
    <form method="POST" action="<?= $basePath ?>/campaigns/<?php echo $campaign->id; ?>/launch" class="d-inline">
        <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to launch this campaign?')">Launch campaign</button>
    </form>
    <?php endif; ?>
</div>
<?php
$actionsHtml = ob_get_clean();
$title = (string) $campaign->name;
$subtitle = $canEdit
    ? 'Draft and refine the campaign here, then import recipients, schedule, and launch from the same workspace.'
    : 'Campaign delivery details, metrics, and recent activity.';
include $componentsPath . '/page-header.blade.php';
?>

<div class="d-flex flex-wrap align-items-center gap-3 mb-4">
    <?php
    $label = $statusLabel;
    $class = $statusClass;
    include $componentsPath . '/status-badge.blade.php';
    ?>
    <span class="text-secondary small">Recipients: <?php echo $deliveryTotal; ?></span>
    <span class="text-secondary small">Sent: <?php echo $sentCount; ?></span>
    <span class="text-secondary small">Failed: <?php echo $failedCount; ?></span>
    <span class="text-secondary small">SMTP: <?php echo htmlspecialchars($smtpLabel, ENT_QUOTES, 'UTF-8'); ?></span>
</div>

<?php if (!empty($notice['message'] ?? '')): ?>
    <?php
    $message = $notice['message'];
    $type = $notice['type'] ?? 'info';
    include $componentsPath . '/flash-alert.blade.php';
    ?>
<?php endif; ?>

<?php if (!empty($safety['errors'])): ?>
    <?php
    $message = implode(' ', $safety['errors']);
    $type = 'danger';
    include $componentsPath . '/flash-alert.blade.php';
    ?>
<?php endif; ?>

<?php if (!empty($safety['warnings'])): ?>
    <?php
    $message = implode(' ', $safety['warnings']);
    $type = 'warning';
    include $componentsPath . '/flash-alert.blade.php';
    ?>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-12 col-xl-8">
        <?php if ($canEdit): ?>
        <div class="card h-100">
            <div class="card-body">
                <form method="POST" action="<?= $basePath ?>/campaigns/<?php echo $campaign->id; ?>" class="row g-3">
                    <input type="hidden" name="_method" value="PUT">

                    <div class="col-12 col-md-6">
                        <label for="name" class="form-label">Campaign Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars((string) $campaign->name, ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="smtp_setting_id" class="form-label">SMTP Account</label>
                        <select class="form-select" id="smtp_setting_id" name="smtp_setting_id" required <?php echo empty($smtpSettings) ? 'disabled' : ''; ?>>
                            <option value="">-- Select an SMTP account --</option>
                            <?php foreach ($smtpSettings as $smtp): ?>
                            <option value="<?php echo $smtp->id; ?>" <?php echo (int) $campaign->smtp_setting_id === (int) $smtp->id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($smtp->from_email . ' @ ' . $smtp->host, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-8">
                        <label for="subject" class="form-label">Email Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject" value="<?= htmlspecialchars((string) ($template?->subject ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="template_loader" class="form-label">Prefill from template</label>
                        <select class="form-select" id="template_loader">
                            <option value="">-- Optional prefill --</option>
                            <?php foreach ($templates as $availableTemplate): ?>
                            <option value="<?php echo $availableTemplate->id; ?>"><?php echo htmlspecialchars($availableTemplate->name, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-2">
                            <label for="html_content" class="form-label mb-0">HTML Content</label>
                            <div class="d-flex gap-2 flex-wrap align-items-center">
                                <button type="button" class="merge-variable-chip" data-insert-variable="&#123;&#123;name&#125;&#125;">&#123;&#123;name&#125;&#125;</button>
                                <button type="button" class="merge-variable-chip" data-insert-variable="&#123;&#123;email&#125;&#125;">&#123;&#123;email&#125;&#125;</button>
                                <button type="button" class="merge-variable-chip" data-insert-variable="&#123;&#123;unsubscribe_url&#125;&#125;">&#123;&#123;unsubscribe_url&#125;&#125;</button>
                                <span class="text-secondary small">Click a badge to insert it at the cursor.</span>
                            </div>
                        </div>
                        <textarea class="form-control font-monospace" id="html_content" name="html_content" style="min-height: 560px;" required><?= htmlspecialchars((string) ($template?->html_content ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="scheduled_at" class="form-label">Scheduled At</label>
                        <input type="datetime-local" class="form-control" id="scheduled_at" name="scheduled_at" value="<?= !empty($campaign->scheduled_at) ? htmlspecialchars(date('Y-m-d\TH:i', strtotime((string) $campaign->scheduled_at)), ENT_QUOTES, 'UTF-8') : '' ?>">
                    </div>

                    <div class="col-12 d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-primary" <?php echo empty($smtpSettings) ? 'disabled' : ''; ?>>Save campaign</button>
                        <?php if ($campaign->canStart() && $campaign->total_recipients > 0): ?>
                        <button type="submit" formaction="<?= $basePath ?>/campaigns/<?php echo $campaign->id; ?>/launch" formmethod="POST" class="btn btn-success" onclick="return confirm('Are you sure you want to launch this campaign?')">Launch now</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
        <?php
        ob_start();
        ?>
        <h2 class="h5 mb-3">Campaign Content</h2>
        <div class="mb-3">
            <div class="text-secondary small">Subject</div>
            <div class="fw-semibold"><?= htmlspecialchars((string) ($template?->subject ?? '—'), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div class="border rounded-3 p-3 bg-light-subtle" style="min-height: 220px;">
            <?= (string) ($template?->html_content ?? '<span class="text-secondary">No HTML content available.</span>') ?>
        </div>
        <?php
        $contentHtml = ob_get_clean();
        $className = 'h-100 mb-0';
        include $componentsPath . '/table-shell.blade.php';
        ?>
        <?php endif; ?>
    </div>

    <div class="col-12 col-xl-4">
        <?php
        ob_start();
        ?>
        <h2 class="h5 mb-3">Workspace</h2>
        <div class="small text-secondary mb-3">Keep the campaign moving from one place: content, sender, schedule, recipients, and launch readiness.</div>
        <div class="row g-3 mb-3">
            <div class="col-6">
                <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                    <div class="text-secondary small">Template</div>
                    <div class="fw-semibold"><?= htmlspecialchars($templateLabel, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </div>
            <div class="col-6">
                <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                    <div class="text-secondary small">Pending</div>
                    <div class="fw-semibold"><?php echo $pendingCount; ?></div>
                </div>
            </div>
            <div class="col-6">
                <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                    <div class="text-secondary small">Risk</div>
                    <div class="fw-semibold"><?php echo htmlspecialchars(strtoupper((string) ($safety['risk_level'] ?? 'low')), ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            </div>
            <div class="col-6">
                <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                    <div class="text-secondary small">Started</div>
                    <div class="fw-semibold small"><?php echo htmlspecialchars($startedText, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            </div>
        </div>

        <?php if ($canEdit): ?>
        <form id="recipient-import" method="POST" action="<?= $basePath ?>/campaigns/<?php echo $campaign->id; ?>/recipients" class="border rounded-3 p-3 mb-3">
            <label for="manual_recipients" class="form-label">Recipients</label>
            <textarea class="form-control mb-2" id="manual_recipients" name="manual_recipients" rows="5" placeholder="mdriaz@alpha.net.bd, second@example.com&#10;third@example.com"></textarea>
            <div class="form-text mb-3">Paste comma-separated or newline-separated email addresses.</div>
            <button type="submit" class="btn btn-outline-primary w-100" <?php echo !$canEdit ? 'disabled' : ''; ?>>Import typed recipients</button>
        </form>

        <form id="recipient-import-csv" method="POST" action="<?= $basePath ?>/campaigns/<?php echo $campaign->id; ?>/recipients" enctype="multipart/form-data" class="border rounded-3 p-3 mb-3">
            <label for="recipients_file" class="form-label">Import recipients CSV</label>
            <input type="file" class="form-control mb-2" id="recipients_file" name="recipients_file" accept=".csv">
            <div class="form-text mb-3">Format: email,name,custom_field1,custom_field2,...</div>
            <button type="submit" class="btn btn-outline-secondary w-100" <?php echo !$canEdit ? 'disabled' : ''; ?>>Import CSV recipients</button>
        </form>
        <?php endif; ?>

        <div class="small text-secondary">
            <div><strong>Created:</strong> <?php echo htmlspecialchars($createdText, ENT_QUOTES, 'UTF-8'); ?></div>
            <div><strong>Completed:</strong> <?php echo htmlspecialchars($completedText, ENT_QUOTES, 'UTF-8'); ?></div>
            <div><strong>Sender:</strong> <?php echo htmlspecialchars($smtpLabel, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
        <?php
        $contentHtml = ob_get_clean();
        $className = 'h-100 mb-0';
        include $componentsPath . '/table-shell.blade.php';
        ?>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-xl-6">
        <?php
        ob_start();
        ?>
        <h2 class="h5 mb-3">Delivery Metrics</h2>
        <div class="row g-3 mb-3">
            <div class="col-4"><div class="border rounded-3 p-3 h-100 bg-light-subtle"><div class="text-secondary small">Sent</div><div class="fs-4 fw-bold text-success"><?php echo $sentCount; ?></div></div></div>
            <div class="col-4"><div class="border rounded-3 p-3 h-100 bg-light-subtle"><div class="text-secondary small">Failed</div><div class="fs-4 fw-bold text-danger"><?php echo $failedCount; ?></div></div></div>
            <div class="col-4"><div class="border rounded-3 p-3 h-100 bg-light-subtle"><div class="text-secondary small">Pending</div><div class="fs-4 fw-bold"><?php echo $pendingCount; ?></div></div></div>
        </div>
        <div class="row g-3">
            <div class="col-6 col-md-3"><div class="border rounded-3 p-3 h-100"><div class="text-secondary small">Delivered</div><div class="fs-5 fw-bold text-success"><?php echo (int) ($campaignMetrics['delivered'] ?? 0); ?></div></div></div>
            <div class="col-6 col-md-3"><div class="border rounded-3 p-3 h-100"><div class="text-secondary small">Opened</div><div class="fs-5 fw-bold text-info"><?php echo (int) ($campaignMetrics['opened'] ?? 0); ?></div></div></div>
            <div class="col-6 col-md-3"><div class="border rounded-3 p-3 h-100"><div class="text-secondary small">Clicked</div><div class="fs-5 fw-bold text-primary"><?php echo (int) ($campaignMetrics['clicked'] ?? 0); ?></div></div></div>
            <div class="col-6 col-md-3"><div class="border rounded-3 p-3 h-100"><div class="text-secondary small">Bounced</div><div class="fs-5 fw-bold text-danger"><?php echo (int) ($campaignMetrics['bounced'] ?? 0); ?></div></div></div>
        </div>
        <?php
        $contentHtml = ob_get_clean();
        $className = 'h-100 mb-0';
        include $componentsPath . '/table-shell.blade.php';
        ?>
    </div>
    <div class="col-12 col-xl-6">
        <?php
        ob_start();
        ?>
        <h2 class="h5 mb-3">Safety & Deliverability</h2>
        <div class="row g-3 mb-3">
            <div class="col-6"><div class="border rounded-3 p-3 h-100 bg-light-subtle"><div class="text-secondary small">Risk level</div><div class="fw-semibold"><?php echo htmlspecialchars(strtoupper((string) ($safety['risk_level'] ?? 'low')), ENT_QUOTES, 'UTF-8'); ?></div></div></div>
            <div class="col-6"><div class="border rounded-3 p-3 h-100 bg-light-subtle"><div class="text-secondary small">Autopause</div><div class="fw-semibold"><?php echo !empty($safety['should_pause']) ? 'ARMED' : 'CLEAR'; ?></div></div></div>
        </div>
        <?php if (!empty($deliverability['recommendations'])): ?>
        <div class="small text-secondary">
            <strong>Recommendations</strong>
            <ul class="mb-0 mt-2">
                <?php foreach ($deliverability['recommendations'] as $recommendation): ?>
                <li><?php echo htmlspecialchars((string) $recommendation, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php else: ?>
        <?php
        $message = 'No additional deliverability recommendations are currently available.';
        $actionHtml = '';
        include $componentsPath . '/empty-state.blade.php';
        ?>
        <?php endif; ?>
        <?php
        $contentHtml = ob_get_clean();
        $className = 'h-100 mb-0';
        include $componentsPath . '/table-shell.blade.php';
        ?>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-xl-6">
        <?php ob_start(); ?>
        <h2 class="h5 mb-3">Risk History</h2>
        <?php if (!empty($riskHistory)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>ID</th><th>Type</th><th>Risk</th><th>Autopause</th><th>Notes</th></tr></thead>
                <tbody>
                <?php foreach ($riskHistory as $entry): $data = $entry['data'] ?? []; ?>
                    <tr>
                        <td>#<?php echo (int) ($entry['id'] ?? 0); ?></td>
                        <td><?php echo htmlspecialchars((string) ($entry['type'] ?? 'unknown'), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars(strtoupper((string) ($data['risk_level'] ?? 'unknown')), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo !empty($data['should_pause']) ? 'YES' : 'NO'; ?></td>
                        <td class="small text-secondary"><?php echo htmlspecialchars(implode(' · ', array_slice(array_merge($data['errors'] ?? [], $data['warnings'] ?? []), 0, 3)) ?: '—', ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <?php
        $message = 'No risk snapshots recorded yet. Launch evaluation will populate this history.';
        $actionHtml = '';
        include $componentsPath . '/empty-state.blade.php';
        ?>
        <?php endif; ?>
        <?php $contentHtml = ob_get_clean(); $className = 'mb-0'; include $componentsPath . '/table-shell.blade.php'; ?>
    </div>
    <div class="col-12 col-xl-6">
        <?php ob_start(); ?>
        <h2 class="h5 mb-3">Recent Campaign Events</h2>
        <?php if (!empty($recentEvents)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr><th>When</th><th>Event</th><th>Recipient</th><th>Details</th></tr>
                </thead>
                <tbody>
                <?php foreach ($recentEvents as $event): ?>
                    <tr>
                        <td><?php echo htmlspecialchars((string) ($event['timestamp'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars((string) ($event['event_type'] ?? 'unknown'), ENT_QUOTES, 'UTF-8'); ?></span></td>
                        <td><?php echo !empty($event['recipient_id']) ? '#' . (int) $event['recipient_id'] : '—'; ?></td>
                        <td class="small text-secondary">
                            <?php
                            $details = [];
                            $metadata = $event['metadata'] ?? null;
                            if (is_array($metadata)) {
                                if (!empty($metadata['recipient_email'])) { $details[] = $metadata['recipient_email']; }
                                if (!empty($metadata['clicked_url'])) { $details[] = 'URL click'; }
                                if (!empty($metadata['smtp_code'])) { $details[] = 'SMTP ' . $metadata['smtp_code']; }
                                if (!empty($metadata['tracking_source'])) { $details[] = $metadata['tracking_source']; }
                                if (!empty($metadata['error'])) { $details[] = $metadata['error']; }
                            }
                            echo htmlspecialchars($details !== [] ? implode(' · ', $details) : '—', ENT_QUOTES, 'UTF-8');
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <?php
        $message = 'No campaign events recorded yet. Launch and send to start building the timeline.';
        $actionHtml = '';
        include $componentsPath . '/empty-state.blade.php';
        ?>
        <?php endif; ?>
        <?php $contentHtml = ob_get_clean(); $className = 'h-100 mb-0'; include $componentsPath . '/table-shell.blade.php'; ?>
    </div>
</div>

<?php
$editorAssets = dirname(__DIR__, 3) . '/resources/views/blade/templates/_editor_assets.blade.php';
ob_start();
if ($canEdit) {
    include $editorAssets;
}
?>
<script>
(function() {
    if (!<?php echo $canEdit ? 'true' : 'false'; ?>) {
        return;
    }

    const templates = <?php echo $templatesJson ?: '[]'; ?>;
    const selector = document.getElementById('template_loader');
    const subjectInput = document.getElementById('subject');
    const textarea = document.getElementById('html_content');

    if (!selector) {
        return;
    }

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
