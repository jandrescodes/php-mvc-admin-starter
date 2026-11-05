<?php

/**
 * Asset registry for plugin and module resources.
 *
 * Centralizes plugin CSS/JS definitions so layouts/controllers can
 * reuse one source of truth while migrating to app/Core rendering.
 *
 * @package ProyectoBase
 * @subpackage App\Core
 * @author jandrescodes
 * @version 1.0
 */

namespace App\Core;

class AssetRegistry
{
    /**
     * Returns the plugin-to-CSS map.
     *
     * @return array
     */
    public static function getPluginCssMap(): array
    {
        return [
            'tabulator' => [
                'plugins/tabulator/tabulator_bootstrap5.min.css',
            ],
            'tom-select' => [
                'plugins/tom-select/tom-select.bootstrap5.min.css',
            ],
        ];
    }

    /**
     * Returns the plugin-to-JS map.
     *
     * @return array
     */
    public static function getPluginJsMap(): array
    {
        return [
            'tabulator' => [
                'plugins/tabulator/tabulator.min.js',
            ],
            'tom-select' => [
                'plugins/tom-select/tom-select.complete.min.js',
            ],
            'export' => [
                'plugins/utils/xlsx.full.min.js',
                'plugins/utils/jspdf.umd.min.js',
                'plugins/utils/jspdf.plugin.autotable.min.js',
            ],
            'chart' => [
                'plugins/chart/chart.umd.min.js',
            ],
        ];
    }

    /**
     * Resolves plugin CSS files for the provided plugin names.
     *
     * @param array $plugins
     * @return array
     */
    public static function resolvePluginCss(array $plugins): array
    {
        return self::resolve($plugins, self::getPluginCssMap());
    }

    /**
     * Resolves plugin JS files for the provided plugin names.
     *
     * @param array $plugins
     * @return array
     */
    public static function resolvePluginJs(array $plugins): array
    {
        return self::resolve($plugins, self::getPluginJsMap());
    }

    /**
     * Resolves plugin assets from a map.
     *
     * @param array $plugins
     * @param array $map
     * @return array
     */
    private static function resolve(array $plugins, array $map): array
    {
        $assets = [];
        foreach ($plugins as $plugin) {
            if (!isset($map[$plugin])) {
                continue;
            }

            foreach ($map[$plugin] as $asset) {
                $assets[] = $asset;
            }
        }

        return $assets;
    }
}
