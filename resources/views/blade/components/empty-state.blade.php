<?php
$emptyMessage = $message ?? '';
$emptyAction = $actionHtml ?? '';
?>
<div class="text-center py-5 text-secondary">
    <?php if ($emptyMessage !== ''): ?>
    <p class="mb-2"><?= htmlspecialchars($emptyMessage, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if ($emptyAction !== ''): ?>
    <?= $emptyAction ?>
    <?php endif; ?>
</div>
