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
<div class="alert <?= $alertClass ?>" role="alert" aria-live="assertive"<?= $alertId ? ' id="' . htmlspecialchars($alertId, ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
    <?= htmlspecialchars($alertMessage, ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>
