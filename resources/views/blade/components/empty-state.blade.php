<?php
$emptyMessage = $message ?? '';
$emptyAction = $actionHtml ?? '';
?>
<div class="text-center py-5 text-secondary border rounded-4 bg-light-subtle">
    <div class="small text-uppercase fw-bold mb-2" style="letter-spacing: 0.08em;">Empty state</div>
    <?php if ($emptyMessage !== ''): ?>
    <p class="mb-2"><?= htmlspecialchars($emptyMessage, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if ($emptyAction !== ''): ?>
    <div class="mt-2"><?= $emptyAction ?></div>
    <?php endif; ?>
</div>
