<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Base System | <?= htmlspecialchars($title ?? '') ?></title>

    <!-- Dark mode detection (inline — evita FOUC) -->
    <script>
        (function() {
            try {
                const saved = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (saved === 'dark' || (!saved && prefersDark)) {
                    document.documentElement.classList.add('dark-mode');
                }
            } catch (e) {}
        })();
    </script>

    <link rel="stylesheet" href="<?= URL ?>css/lib/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?= URL ?>css/lib/fontawesome/all.min.css">
    <link rel="stylesheet" href="<?= URL ?>css/lib/adminlte/adminlte.min.css">
    <link rel="stylesheet" href="<?= URL ?>css/lib/bootstrap/icheck-bootstrap.min.css">
    <link rel="stylesheet" href="<?= URL ?>css/core/webfonts.css">
    <link rel="icon" type="image/png" href="<?= URL ?>img/e-commerce_logo.png">
    <link rel="stylesheet" href="<?= URL ?>css/core/dark-mode.css">
    <link rel="stylesheet" href="<?= URL ?>css/modules/login/login.css">
    <link rel="stylesheet" href="<?= URL ?>css/modules/login/login-dark.css">
    <link rel="stylesheet" href="<?= URL ?>css/plugins/sweetalert2/sweetalert2.min.css">
    <script src="<?= URL ?>js/plugins/sweetalert2/sweetalert2.min.js"></script>
    <script src="<?= URL ?>js/core/sweetalert-utils.js"></script>
</head>

<body class="hold-transition login-page">
    <!-- Dark mode toggle -->
    <a href="#" id="theme-toggle" class="auth-theme-toggle" role="button" title="Toggle dark mode"
        aria-label="Toggle dark mode" aria-pressed="false">
        <i class="fas fa-moon"></i>
    </a>
    <div class="login-box">
        <?= $content ?>
    </div>

    <script src="<?= URL ?>js/lib/jquery/jquery.min.js"></script>
    <script src="<?= URL ?>js/lib/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="<?= URL ?>js/lib/adminlte/adminlte.min.js"></script>
    <script src="<?= URL ?>js/plugins/validations/jquery.validate.min.js"></script>
    <script src="<?= URL ?>js/plugins/validations/additional-methods.min.js"></script>
    <script src="<?= URL ?>js/core/common-validate.js"></script>
    <script src="<?= URL ?>js/core/common-utils.js"></script>
    <?php foreach ($module_scripts ?? [] as $script): ?>
        <script src="<?= URL ?>js/modules/auth/<?= $script ?>.js"></script>
    <?php endforeach; ?>
    <script src="<?= URL ?>js/modules/profile/theme-toggle.js"></script>

    <?php require_once __DIR__ . '/messages.php'; ?>
</body>

</html>
