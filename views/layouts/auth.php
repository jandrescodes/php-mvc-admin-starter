<!DOCTYPE html>
<html lang="en" data-lte-color-mode="off">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <meta name="base-url" content="<?= htmlspecialchars(URL) ?>">
    <meta name="csrf-token" content="<?= htmlspecialchars(generateCSRFToken()) ?>">
    <title>Base System | <?= htmlspecialchars($title ?? '') ?></title>

    <!-- Anti-FOUC: aplica el tema antes del primer pintado -->
    <script>
        (function() {
            try {
                const saved = localStorage.getItem('theme');
                const dark = saved === 'dark' || (saved !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                const theme = dark ? 'dark' : 'light';
                document.documentElement.setAttribute('data-bs-theme', theme);
                document.documentElement.style.colorScheme = theme;
            } catch (e) {}
        })();
    </script>

    <link rel="icon" type="image/png" href="<?= URL ?>img/e-commerce_logo.png">
    <link rel="stylesheet" href="<?= URL ?>css/lib/bootstrap-icons/bootstrap-icons.min.css">
    <!-- AdminLTE 4 (incluye Bootstrap 5) -->
    <link rel="stylesheet" href="<?= URL ?>css/lib/adminlte/adminlte.min.css">
    <!-- Dark mode tokens -->
    <link rel="stylesheet" href="<?= URL ?>css/core/dark-mode.css">
    <link rel="stylesheet" href="<?= URL ?>css/modules/login/login.css">
    <link rel="stylesheet" href="<?= URL ?>css/modules/login/login-dark.css">
    <link rel="stylesheet" href="<?= URL ?>css/plugins/sweetalert2/sweetalert2.min.css">
</head>

<body class="login-page bg-body-secondary">
    <!-- Theme toggle -->
    <a href="#" id="theme-toggle" class="auth-theme-toggle" role="button" title="Toggle dark mode"
        aria-label="Toggle dark mode" aria-pressed="false">
        <i class="bi bi-moon-fill theme-icon-to-dark"></i>
        <i class="bi bi-sun-fill theme-icon-to-light"></i>
    </a>
    <div class="login-box">
        <?= $content ?>
    </div>

    <script src="<?= URL ?>js/lib/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="<?= URL ?>js/lib/adminlte/adminlte.min.js"></script>
    <script src="<?= URL ?>js/plugins/sweetalert2/sweetalert2.min.js"></script>
    <script src="<?= URL ?>js/core/sweetalert-utils.js"></script>
    <!-- jQuery y validación: se eliminan en la fase E -->
    <script src="<?= URL ?>js/lib/jquery/jquery.min.js"></script>
    <script src="<?= URL ?>js/plugins/validations/jquery.validate.min.js"></script>
    <script src="<?= URL ?>js/plugins/validations/additional-methods.min.js"></script>
    <script src="<?= URL ?>js/core/common-validate.js"></script>
    <script src="<?= URL ?>js/core/http.js"></script>
    <script src="<?= URL ?>js/core/form-validator.js"></script>
    <script src="<?= URL ?>js/core/common-utils.js"></script>
    <?php foreach ($module_scripts ?? [] as $script): ?>
        <script src="<?= URL ?>js/modules/auth/<?= $script ?>.js"></script>
    <?php endforeach; ?>
    <script src="<?= URL ?>js/modules/profile/theme-toggle.js"></script>

    <?php require_once __DIR__ . '/messages.php'; ?>
</body>

</html>