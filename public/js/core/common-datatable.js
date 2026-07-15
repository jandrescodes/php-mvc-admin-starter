/**
 * common-datatable.js - Common DataTables utilities
 *
 * Provides helper functions for DataTables configuration
 * without interfering with existing configurations.
 *
 * @package ProyectoBase
 * @subpackage JavaScript\Core
 * @author jandrescodes
 * @version 1.0
 */

/**
 * Object with predefined DataTables configurations
 */
const DataTableUtils = {
    /**
     * Base language configuration for DataTables (English default)
     */
    languageConfig: {},

    /**
     * Configuración común para botones de exportación
     * @param {string} title - Título para los reportes
     * @param {string} filename - Nombre del archivo (sin fecha)
     * @param {Array|null} exportColumns - Columnas a exportar
     */
    getExportButtonsConfig: function (title, filename, exportColumns = null) {
        const date       = new Date().toISOString().slice(0, 10);
        const systemName = 'Base MVC System';

        return [
            {
                extend: 'collection',
                text: 'Reports',
                buttons: [
                    {
                        extend: 'copy',
                        text: 'Copy',
                        exportOptions: {
                            columns: exportColumns
                        }
                    },
                    {
                        extend: 'excel',
                        text: 'Excel',
                        title: title,
                        messageTop: `Record of ${title.toLowerCase()}`,
                        messageBottom: 'Document generated on ' + new Date().toLocaleDateString('en-US'),
                        filename: `${filename}_${date}`,
                        exportOptions: {
                            columns: exportColumns
                        }
                    },
                    {
                        extend: 'pdf',
                        text: 'PDF',
                        title: title,
                        filename: `${filename}_${date}`,
                        pageSize: 'LETTER',
                        exportOptions: {
                            columns: exportColumns
                        },
                        customize: function (doc) {
                            doc.defaultStyle.fontSize = 10;
                            doc.styles.tableHeader.fontSize = 11;
                            doc.styles.tableHeader.fillColor = '#4b545c';
                            doc.styles.tableHeader.color = '#ffffff';

                            // Main title
                            doc.content.splice(0, 1, {
                                text: title.toUpperCase(),
                                style: {
                                    fontSize: 16,
                                    alignment: 'center',
                                    bold: true,
                                    margin: [0, 10, 0, 10]
                                }
                            });

                            // Subtitle
                            doc.content.splice(1, 0, {
                                text: `Record of ${title.toLowerCase()}`,
                                style: {
                                    fontSize: 11,
                                    alignment: 'center',
                                    italic: true,
                                    margin: [0, 0, 0, 10]
                                }
                            });

                            // Generation date
                            doc.content.splice(2, 0, {
                                text: 'Generated on: ' + new Date().toLocaleString('en-US'),
                                style: {
                                    fontSize: 9,
                                    alignment: 'right',
                                    margin: [0, 0, 0, 10]
                                }
                            });

                            // Footer
                            doc.footer = function (currentPage, pageCount) {
                                return {
                                    columns: [
                                        { text: systemName, alignment: 'left',   fontSize: 8 },
                                        { text: 'Page ' + currentPage + ' of ' + pageCount, alignment: 'center', fontSize: 8 },
                                        { text: 'Confidential', alignment: 'right', fontSize: 8 }
                                    ],
                                    margin: [40, 0]
                                };
                            };
                        }
                    },
                    {
                        extend: 'csv',
                        text: 'CSV',
                        exportOptions: {
                            columns: exportColumns
                        }
                    },
                    {
                        extend: 'print',
                        text: 'Print',
                        title: title,
                        messageTop: 'Report generated on ' + new Date().toLocaleDateString('en-US'),
                        exportOptions: {
                            columns: exportColumns
                        },
                        customize: function (win) {
                            $(win.document.body).find('table')
                                .addClass('table-striped')
                                .css('font-size', '12px');
                        }
                    }
                ]
            },
            {
                extend: 'colvis',
                text: 'Column visibility'
            }
        ];
    },

    /**
     * Base DataTables configuration
     * @param {Object} customOptions - Custom options to merge
     * @returns {Object} Configuration object
     */
    getBaseConfig: function (customOptions = {}) {
        const baseConfig = {
            responsive: true,
            autoWidth: false,
            pageLength: 5,
            lengthMenu: [
                [3, 5, 10, 25, 50],
                [3, 5, 10, 25, 50]
            ],
            language: this.languageConfig,
            initComplete: function () {
                $(this.api().table().node()).css('visibility', 'visible');
            }
        };

        return $.extend(true, {}, baseConfig, customOptions);
    },

    /**
     * Customizes the info message for a specific entity
     * @param {string} entityName - Entity name (e.g. 'Users', 'Permissions')
     * @returns {Object} Customized language configuration
     */
    customizeLanguageFor: function (entityName) {
        const customLanguage = $.extend(true, {}, this.languageConfig);

        customLanguage.sInfo          = `Showing _START_ to _END_ of _TOTAL_ ${entityName}`;
        customLanguage.sInfoEmpty     = `Showing 0 to 0 of 0 ${entityName}`;
        customLanguage.sInfoFiltered  = `(filtered from _MAX_ total ${entityName})`;

        return customLanguage;
    }
};

/**
 * Applies a base DataTables configuration to an already-initialized table
 * @param {Object} table - DataTable instance
 * @param {Object} options - Options to apply
 */
function enhanceDataTable(table, options = {}) {
    // Aplicar opciones específicas a la tabla ya inicializada
    if (options.language) {
        table.language = options.language;
    }

    if (options.responsive !== undefined) {
        table.responsive = options.responsive;
    }

    // Refrescar la tabla para aplicar cambios
    table.draw();
}

/**
 * Creates a customized DataTables configuration for an entity
 * @param {string} entityName - Entity name (e.g. 'Users', 'Permissions')
 * @param {Array|null} exportColumns - Columns to export
 * @param {Object} customOptions - Additional options
 * @returns {Object} Complete DataTables configuration
 */
function createTableConfig(entityName, exportColumns = null, customOptions = {}) {
    const formattedEntityName = entityName.charAt(0).toUpperCase() + entityName.slice(1);
    const filename = entityName.toLowerCase();

    const config = DataTableUtils.getBaseConfig({
        language: DataTableUtils.customizeLanguageFor(formattedEntityName),
        buttons: DataTableUtils.getExportButtonsConfig(
            `${formattedEntityName}`,
            `${filename}_system`,
            exportColumns
        )
    });

    // Combinar con opciones personalizadas
    return $.extend(true, {}, config, customOptions);
}