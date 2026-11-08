<?php
$welcome = isset($_SESSION['welcome_user']) ? (string) $_SESSION['welcome_user'] : null;
$hasFlash = isset($_SESSION['message'], $_SESSION['icon']);
$message = $hasFlash ? (string) $_SESSION['message'] : '';
$icon = $hasFlash ? (string) $_SESSION['icon'] : '';
$bsClass = ['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'][$icon] ?? 'info';
unset($_SESSION['welcome_user'], $_SESSION['message'], $_SESSION['icon']);
?>
<?php if ($welcome !== null || $hasFlash): ?>
    <div id="flash-data" hidden
        <?= $welcome !== null ? 'data-welcome="' . htmlspecialchars($welcome) . '"' : '' ?>
        <?= $hasFlash ? 'data-message="' . htmlspecialchars($message) . '" data-icon="' . htmlspecialchars($icon) . '"' : '' ?>></div>
<?php endif; ?>
<?php if ($hasFlash): ?>
    <noscript>
        <div class="alert alert-<?= htmlspecialchars($bsClass) ?> text-center m-3" role="alert">
            <?= htmlspecialchars($message) ?>
        </div>
    </noscript>
<?php endif; ?>