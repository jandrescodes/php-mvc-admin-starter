<!DOCTYPE html>
<html lang="en" data-lte-color-mode="off">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title><?= htmlspecialchars($code) ?> - <?= htmlspecialchars($heading) ?></title>

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

    <?php $base = defined('URL') ? URL : '/'; ?>
    <link rel="icon" type="image/png" href="<?= $base ?>img/e-commerce_logo.png">
    <link rel="stylesheet" href="<?= $base ?>css/lib/bootstrap-icons/bootstrap-icons.min.css">
    <!-- AdminLTE 4 (incluye Bootstrap 5) -->
    <link rel="stylesheet" href="<?= $base ?>css/lib/adminlte/adminlte.min.css">
    <link rel="stylesheet" href="<?= $base ?>css/modules/errors/errors.css">
</head>

<body class="d-flex align-items-center justify-content-center min-vh-100 bg-body-secondary"
    data-tone="<?= htmlspecialchars($tone) ?>">
    <main class="text-center p-4">
        <h1 class="error-code display-1 fw-bold mb-0"><?= htmlspecialchars($code) ?></h1>
        <h2 class="h4 fw-semibold mt-3"><?= htmlspecialchars($heading) ?></h2>
        <p class="text-body-secondary mb-4"><?= htmlspecialchars($message) ?></p>
        <a href="<?= $base ?>" class="btn btn-primary">
            <i class="bi bi-house-door" aria-hidden="true"></i> Back to Home
        </a>
    </main>
</body>

</html>
