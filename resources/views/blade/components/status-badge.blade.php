<?php
$badgeLabel = $label ?? '';
$badgeClass = $class ?? 'badge-muted';
?>
<span class="badge <?= htmlspecialchars($badgeClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($badgeLabel, ENT_QUOTES, 'UTF-8') ?></span>
