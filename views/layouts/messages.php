<?php if (isset($_SESSION['welcome_user'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof AlertUtils !== 'undefined') {
                AlertUtils.welcome(<?= json_encode((string) $_SESSION['welcome_user']); ?>);
            }
        });
    </script>
<?php
    unset($_SESSION['welcome_user']);
endif;

if (isset($_SESSION['message'], $_SESSION['icon'])):
    $message = $_SESSION['message'];
    $icon    = $_SESSION['icon'];
    $bsClass = ['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'][$icon] ?? 'info';
?>
    <noscript>
        <div class="alert alert-<?= htmlspecialchars($bsClass) ?> text-center m-3" role="alert">
            <?= htmlspecialchars($message) ?>
        </div>
    </noscript>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var message = <?= json_encode((string) $message); ?>;
            var icon = <?= json_encode((string) $icon); ?>;

            if (typeof ToastUtils !== 'undefined' && typeof ToastUtils[icon] === 'function') {
                ToastUtils[icon](message);
                return;
            }

            // Fallback: SweetAlert2/ToastUtils failed to load — show a plain Bootstrap alert.
            var bsClass = {
                success: 'success',
                error: 'danger',
                warning: 'warning',
                info: 'info'
            } [icon] || 'info';
            var alertEl = document.createElement('div');
            alertEl.className = 'alert alert-' + bsClass + ' text-center m-3';
            alertEl.setAttribute('role', 'alert');
            alertEl.textContent = message;
            document.body.insertBefore(alertEl, document.body.firstChild);
        });
    </script>
<?php
    unset($_SESSION['message'], $_SESSION['icon']);
endif; ?>