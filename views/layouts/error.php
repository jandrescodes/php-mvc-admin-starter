<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($code) ?> - <?= htmlspecialchars($heading) ?></title>

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

    <?php $base = defined('URL') ? URL : '/'; ?>
    <link rel="stylesheet" href="<?= $base ?>css/lib/fontawesome/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>css/core/webfonts.css">
    <link rel="icon" type="image/png" href="<?= $base ?>img/e-commerce_logo.png">
    <link rel="stylesheet" href="<?= $base ?>css/core/dark-mode.css">
    <link rel="stylesheet" href="<?= $base ?>css/modules/errors/errors.css">
    <link rel="stylesheet" href="<?= $base ?>css/modules/errors/errors-dark.css">
</head>

<body class="error-page" data-tone="<?= htmlspecialchars($tone) ?>">
    <main class="error-content">
        <h1 class="error-code"><?= htmlspecialchars($code) ?></h1>
        <h2 class="error-heading"><?= htmlspecialchars($heading) ?></h2>
        <p class="error-message"><?= htmlspecialchars($message) ?></p>
        <a href="<?= $base ?>" class="error-btn btn">
            <i class="fas fa-home" aria-hidden="true"></i> Back to Home
        </a>
    </main>
</body>

</html>