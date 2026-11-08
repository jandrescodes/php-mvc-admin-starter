<?php

use App\Core\Auth;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base = rtrim(parse_url(URL, PHP_URL_PATH), '/');

/**
 * Returns 'active' if $segment matches the current URI path.
 * $segment is the path after APP_URL base (e.g. 'users', 'roles').
 */
$isActive = function (string $segment) use ($uri, $base): string {
    $path = $base . '/' . ltrim($segment, '/');
    return str_starts_with($uri, $path) ? 'active' : '';
};

$isDashboard = ($uri === $base || $uri === $base . '/') ? 'active' : '';

$adminPerms = ['users', 'permissions', 'roles'];
$hasAdminAccess = array_reduce($adminPerms, fn($carry, $p) => $carry || Auth::hasPermission($p), false);
$adminMenuOpen = array_reduce($adminPerms, fn($carry, $p) => $carry || $isActive($p) !== '', false);

?>

<aside class="app-sidebar bg-body shadow">
    <div class="sidebar-brand">
        <a href="<?= URL ?>" class="brand-link">
            <img src="<?= URL ?>/img/e-commerce_logo.png" loading="eager" alt="Logo"
                class="brand-image rounded-circle shadow" width="33" height="33">
            <span class="brand-text fw-light">Base System</span>
        </a>
    </div>

    <div class="sidebar-wrapper">
        <nav class="mt-2" aria-label="Main navigation">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" data-accordion="false">

                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="<?= URL ?>" class="nav-link <?= $isDashboard ?>">
                        <i class="nav-icon bi bi-speedometer2"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Administration -->
                <?php if ($hasAdminAccess) : ?>
                    <li class="nav-item <?= $adminMenuOpen ? 'menu-open' : '' ?>">
                        <a href="#" class="nav-link <?= $adminMenuOpen ? 'active' : '' ?>">
                            <i class="nav-icon bi bi-shield-lock"></i>
                            <p>
                                Administration
                                <i class="nav-arrow bi bi-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php if (Auth::hasPermission('users')) : ?>
                                <li class="nav-item">
                                    <a href="<?= URL ?>users" class="nav-link <?= $isActive('users') ?>">
                                        <i class="nav-icon bi bi-person"></i>
                                        <p>Users</p>
                                    </a>
                                </li>
                            <?php endif ?>
                            <?php if (Auth::hasPermission('permissions')) : ?>
                                <li class="nav-item">
                                    <a href="<?= URL ?>permissions" class="nav-link <?= $isActive('permissions') ?>">
                                        <i class="nav-icon bi bi-key"></i>
                                        <p>Permissions</p>
                                    </a>
                                </li>
                            <?php endif ?>
                            <?php if (Auth::hasPermission('roles')) : ?>
                                <li class="nav-item">
                                    <a href="<?= URL ?>roles" class="nav-link <?= $isActive('roles') ?>">
                                        <i class="nav-icon bi bi-person-badge"></i>
                                        <p>Roles</p>
                                    </a>
                                </li>
                            <?php endif ?>
                        </ul>
                    </li>
                <?php endif ?>

                <!-- Audit Log -->
                <?php if (Auth::hasPermission('audit_log')) : ?>
                    <li class="nav-item">
                        <a href="<?= URL ?>audit-log" class="nav-link <?= $isActive('audit-log') ?>">
                            <i class="nav-icon bi bi-clock-history"></i>
                            <p>Audit Log</p>
                        </a>
                    </li>
                <?php endif ?>

                <!-- Space to add new modules -->

            </ul>
        </nav>
    </div>
</aside>