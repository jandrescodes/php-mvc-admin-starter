</main>
<!-- /.app-main -->

<footer class="app-footer">
    <div class="float-end d-none d-sm-inline text-body-secondary">
        <small><i class="bi bi-tag"></i> Version <?= APP_VERSION; ?></small>
    </div>
    <strong>Copyright &copy; <?= date('Y'); ?>
        <a href="#" class="text-decoration-none">BaseProject</a>
    </strong>
    - Base System
</footer>
</div>
<!-- ./app-wrapper -->

<script src="<?= URL; ?>/js/lib/bootstrap/bootstrap.bundle.min.js"></script>

<!-- AdminLTE App -->
<script src="<?= URL; ?>/js/lib/adminlte/adminlte.min.js"></script>

<!-- OverlayScrollbars (scroll del menú lateral) -->
<script src="<?= URL; ?>/js/lib/overlayscrollbars/overlayscrollbars.browser.es6.min.js"></script>

<script src="<?= URL; ?>/js/plugins/sweetalert2/sweetalert2.min.js"></script>

<!-- Main application scripts -->
<script src="<?= URL; ?>/js/core/http.js"></script>
<script src="<?= URL; ?>/js/core/common-utils.js"></script>
<script src="<?= URL; ?>/js/core/form-validator.js"></script>

<!-- Conditional JS plugins -->
<?php
$activePlugins = (isset($plugins) && is_array($plugins)) ? $plugins : [];
$pluginJsFiles = \App\Core\AssetRegistry::resolvePluginJs($activePlugins);
foreach ($pluginJsFiles as $js): ?>
    <script src="<?= URL; ?>/js/<?= $js; ?>"></script>
<?php endforeach; ?>

<!-- ui-components.js va después de los plugins: inicializa Tom Select y los tooltips sobre ellos -->
<script src="<?= URL; ?>/js/core/ui-components.js"></script>

<script src="<?= URL; ?>/js/core/sweetalert-utils.js"></script>

<!-- Tablas (Tabulator); solo actúa si la vista carga el plugin -->
<script src="<?= URL; ?>/js/core/table-utils.js"></script>

<!-- Dark mode toggle (global) -->
<script src="<?= URL; ?>/js/modules/profile/theme-toggle.js"></script>

<!-- Module-specific scripts -->
<?php if (isset($module_scripts) && is_array($module_scripts)): ?>
    <?php foreach ($module_scripts as $script): ?>
        <script src="<?= URL; ?>/js/modules/<?= $script; ?>.js"></script>
    <?php endforeach; ?>
<?php endif; ?>

<?php include_once __DIR__ . '/messages.php'; ?>
</body>

</html>