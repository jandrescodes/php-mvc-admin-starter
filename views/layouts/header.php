<!DOCTYPE html>

<html lang="en" data-lte-color-mode="off">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <meta name="base-url" content="<?= htmlspecialchars(URL); ?>">
    <meta name="csrf-token" content="<?= htmlspecialchars(generateCSRFToken()); ?>">
    <title>Base System MVC</title>

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

    <link rel="icon" type="image/png" href="<?= URL; ?>/img/e-commerce_logo.png">
    <link rel="stylesheet" href="<?= URL; ?>/css/lib/overlayscrollbars/overlayscrollbars.min.css">
    <link rel="stylesheet" href="<?= URL; ?>/css/lib/bootstrap-icons/bootstrap-icons.min.css">
    <!-- AdminLTE 4 (incluye Bootstrap 5) -->
    <link rel="stylesheet" href="<?= URL; ?>/css/lib/adminlte/adminlte.min.css">
    <!-- UI Components custom styles -->
    <link rel="stylesheet" href="<?= URL; ?>/css/core/ui-components.css">
    <!-- Dark mode overrides -->
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
    <!-- jQuery (se elimina en la fase E) -->
    <script src="<?= URL; ?>/js/lib/jquery/jquery.min.js"></script>

    <!-- Module-specific styles -->
    <?php if (isset($module_styles) && is_array($module_styles)): ?>
        <?php foreach ($module_styles as $style): ?>
            <link rel="stylesheet" href="<?= URL; ?>/css/modules/<?= $style; ?>.css">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">

        <!-- Navbar -->
        <nav class="app-header navbar navbar-expand bg-body">
            <div class="container-fluid">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Toggle sidebar">
                            <i class="bi bi-list"></i>
                        </a>
                    </li>

                    <!-- Logo visible only on mobile -->
                    <li class="nav-item d-sm-none">
                        <a href="<?= URL; ?>" class="nav-link d-flex align-items-center">
                            <img src="<?= URL; ?>/img/e-commerce_logo.png" alt="Logo" width="25" height="25"
                                class="rounded-circle me-2">
                            <span class="brand-text">Base System</span>
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="theme-toggle" role="button" title="Toggle dark mode"
                            aria-label="Toggle dark mode" aria-pressed="false">
                            <i class="bi bi-moon-fill theme-icon-to-dark"></i>
                            <i class="bi bi-sun-fill theme-icon-to-light"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="fullscreen" href="#" role="button" aria-label="Toggle fullscreen">
                            <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                            <i data-lte-icon="minimize" class="bi bi-fullscreen-exit d-none"></i>
                        </a>
                    </li>
                    <li class="nav-item dropdown user-menu">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-label="User menu">
                            <img src="<?= URL; ?>/uploads/users/<?= htmlspecialchars($currentUser['image'] ?? 'user_default.jpg'); ?>"
                                class="user-image rounded-circle shadow " alt="">
                            <span class="d-none d-md-inline"><?= htmlspecialchars($currentUser['name'] ?? ''); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                            <li class="user-header">
                                <img src="<?= URL; ?>/uploads/users/<?= htmlspecialchars($currentUser['image'] ?? 'user_default.jpg'); ?>"
                                    loading="eager" class="rounded-circle shadow" alt="User Image">
                                <p>
                                    <?= htmlspecialchars($currentUser['name'] ?? ''); ?>
                                    <small><?= htmlspecialchars($currentUser['role'] ?? ''); ?></small>
                                </p>
                            </li>
                            <li class="user-footer">
                                <?php if (\App\Core\Auth::hasPermission('profile')) : ?>
                                    <a href="<?= URL; ?>profile" class="btn btn-outline-secondary">Profile</a>
                                <?php endif; ?>
                                <form method="POST" action="<?= URL; ?>logout" class="d-inline float-end">
                                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
                                    <button type="submit" class="btn btn-outline-secondary">Log Out</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <?php require __DIR__ . '/sidebar.php'; ?>

        <!-- Content (footer.php lo cierra) -->
        <main class="app-main">