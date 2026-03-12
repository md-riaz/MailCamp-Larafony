<?php
$alertMessage = $message ?? '';
$alertType = $type ?? 'info';
$alertId = $id ?? null;
$alertClass = match ($alertType) {
    'success' => 'alert-success',
    'danger', 'error' => 'alert-danger',
    default => 'alert-info',
};
?>
<?php if ($alertMessage !== ''): ?>
<div class="alert <?= $alertClass ?> d-flex align-items-start gap-3" role="alert" aria-live="assertive"<?= $alertId ? ' id="' . htmlspecialchars($alertId, ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
    <span class="fw-bold"><?php echo match ($alertType) {
        'success' => '✓',
        'danger', 'error' => '!',
        'warning' => '⚠',
        default => 'i',
    }; ?></span>
    <div class="flex-grow-1"><?= htmlspecialchars($alertMessage, ENT_QUOTES, 'UTF-8') ?></div>
</div>
<?php endif; ?>
