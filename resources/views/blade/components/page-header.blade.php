<?php
$componentTitle = $title ?? '';
$componentSubtitle = $subtitle ?? null;
$componentActions = $actionsHtml ?? '';
$componentClasses = trim('d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4 ' . ($className ?? ''));
?>
<div class="<?= htmlspecialchars($componentClasses, ENT_QUOTES, 'UTF-8') ?>">
    <div>
        <h1 class="h3 mb-1"><?= htmlspecialchars($componentTitle, ENT_QUOTES, 'UTF-8') ?></h1>
        <?php if (!empty($componentSubtitle)): ?>
        <p class="text-secondary mb-0"><?= htmlspecialchars($componentSubtitle, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
    </div>
    <?php if ($componentActions !== ''): ?>
    <div><?= $componentActions ?></div>
    <?php endif; ?>
</div>
