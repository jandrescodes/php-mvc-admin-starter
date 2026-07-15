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
            'datatables' => [
                'plugins/datatables/datatables.min.css',
                'plugins/datatables/dataTables.bootstrap4.min.css',
                'plugins/datatables/responsive.bootstrap4.min.css',
                'plugins/datatables/buttons.bootstrap4.min.css',
            ],
            'select2' => [
                'plugins/select2/select2.min.css',
                'plugins/select2/select2-bootstrap4.min.css',
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
            'datatables' => [
                'plugins/datatables/jquery.dataTables.min.js',
                'plugins/datatables/dataTables.bootstrap4.min.js',
                'plugins/datatables/dataTables.responsive.min.js',
                'plugins/datatables/responsive.bootstrap4.min.js',
                'plugins/datatables/dataTables.buttons.min.js',
                'plugins/datatables/buttons.bootstrap4.min.js',
                'plugins/datatables/buttons.html5.min.js',
                'plugins/datatables/buttons.print.min.js',
                'plugins/datatables/buttons.colVis.min.js',
                'core/common-datatable.js',
            ],
            'datatables-export' => [
                'plugins/utils/jszip.min.js',
                'plugins/utils/pdfmake.min.js',
                'plugins/utils/vfs_fonts.js',
            ],
            'select2' => [
                'plugins/select2/select2.min.js',
            ],
            'validate' => [
                'plugins/validations/jquery.validate.min.js',
                'plugins/validations/additional-methods.min.js',
                'core/common-validate.js',
            ],
            'chart' => [
                'plugins/chart/Chart.js',
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
