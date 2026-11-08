<?php

// Falla si algún asset de AssetRegistry no existe en public/ (se ejecuta en CI).
require __DIR__ . '/../vendor/autoload.php';

use App\Core\AssetRegistry;

$public = dirname(__DIR__) . '/public';
$checks = [
    'css' => AssetRegistry::getPluginCssMap(),
    'js' => AssetRegistry::getPluginJsMap(),
];

$missing = [];
$total = 0;
foreach ($checks as $dir => $map) {
    foreach ($map as $plugin => $files) {
        foreach ($files as $file) {
            $total++;
            if (!is_file("$public/$dir/$file")) {
                $missing[] = "[$plugin] public/$dir/$file";
            }
        }
    }
}

if ($missing) {
    fwrite(STDERR, "Faltan assets:\n  " . implode("\n  ", $missing) . "\n");
    exit(1);
}

echo "OK: $total assets existen en public/\n";
