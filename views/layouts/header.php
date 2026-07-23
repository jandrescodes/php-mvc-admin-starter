<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Base System MVC</title>

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

    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="<?= URL; ?>/css/lib/bootstrap/bootstrap.min.css">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="<?= URL; ?>/css/lib/fontawesome/all.min.css">
    <link rel="stylesheet" href="<?= URL; ?>/css/core/webfonts.css">
    <link rel="icon" type="image/png" href="<?= URL; ?>/img/e-commerce_logo.png">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?= URL; ?>/css/lib/adminlte/adminlte.min.css">
    <!-- UI Components custom styles -->
    <link rel="stylesheet" href="<?= URL; ?>/css/core/ui-components.css">
    <!-- Dark mode overrides (DataTables, Select2, SweetAlert2) -->
    <link rel="stylesheet" href="<?= URL; ?>/css/core/dark-mode.css">
    <!-- Conditional plugin CSS -->
    <?php
    $activePlugins = (isset($plugins) && is_array($plugins)) ? $plugins : [];
    $pluginCssFiles = \App\Core\AssetRegistry::resolvePluginCss($activePlugins);
    foreach ($pluginCssFiles as $css): ?>
        <link rel="stylesheet" href="<?= URL; ?>/css/<?= $css; ?>">
    <?php endforeach; ?>
    <!-- Sweetalert2 -->
    <link rel="stylesheet" href="<?= URL; ?>/css/plugins/sweetalert2/sweetalert2.min.css">
    <script src="<?= URL; ?>/js/plugins/sweetalert2/sweetalert2.min.js"></script>
    <!-- jQuery -->
    <script src="<?= URL; ?>/js/lib/jquery/jquery.min.js"></script>

    <!-- Module-specific styles -->
    <?php if (isset($module_styles) && is_array($module_styles)): ?>
        <?php foreach ($module_styles as $style): ?>
            <link rel="stylesheet" href="<?= URL; ?>/css/modules/<?= $style; ?>.css">
        <?php endforeach; ?>
    <?php endif; ?>
    <script>
        const baseUrl = "<?= URL; ?>";
        const csrfToken = "<?= generateCSRFToken(); ?>";
    </script>
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>

                <!-- Logo visible only on mobile -->
                <li class="nav-item d-sm-none">
                    <a href="<?= URL; ?>" class="nav-link d-flex align-items-center">
                        <img src="<?= URL; ?>/img/e-commerce_logo.png" alt="Logo" class="img-circle"
                            style="width: 25px; height: 25px; margin-right: 8px;">
                        <span class="brand-text">Base System</span>
                    </a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#" id="theme-toggle" role="button" title="Toggle dark mode"
                        aria-label="Toggle dark mode" aria-pressed="false">
                        <i class="fas fa-moon" id="theme-icon"></i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                        <i class="fas fa-user"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <!-- User image -->
                        <li class="user-header">
                            <img src="<?= URL; ?>/uploads/users/<?= $currentUser['image']; ?>" loading="eager" class="img-circle elevation-2" alt="User Image">
                            <p>
                                <?= $currentUser['name']; ?>
                                <small><?= htmlspecialchars($currentUser['role'] ?? ''); ?></small>
                            </p>
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <?php if (\App\Core\Auth::hasPermission('profile')) : ?>
                                <a href="<?= URL; ?>profile" class="btn btn-default btn-flat">Profile</a>
                            <?php endif; ?>
                            <form method="POST" action="<?= URL; ?>logout" class="d-inline float-right">
                                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
                                <button type="submit" class="btn btn-default btn-flat">Log Out</button>
                            </form>
                        </li>
                    </ul>

                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <?php require __DIR__ . '/sidebar.php'; ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">