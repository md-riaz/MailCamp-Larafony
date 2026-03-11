<?php
$tableShellContent = $contentHtml ?? '';
$tableShellClasses = trim('card ' . ($className ?? ''));
?>
<div class="<?= htmlspecialchars($tableShellClasses, ENT_QUOTES, 'UTF-8') ?>">
    <div class="card-body">
        <?= $tableShellContent ?>
    </div>
</div>
