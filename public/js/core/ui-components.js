/**
 * Componentes de UI sin jQuery: selectores con búsqueda (Tom Select) y
 * tooltips de Bootstrap 5.
 */

const ComponentUtils = {
    // Con pocas opciones el buscador estorba; equivale al minimumResultsForSearch de Select2.
    SEARCH_MIN_OPTIONS: 7,

    // `.select2` sigue en las vistas sin migrar; las nuevas usan `.tom-select`.
    selectSelector: 'select.tom-select, select.select2',

    selectBaseConfig: {
        maxOptions: null, // por defecto Tom Select oculta todo lo que pase de 50 opciones
        allowEmptyOption: true, // conserva el «All» / «Select…» con value=""
        render: {
            no_results: () => '<div class="no-results">No se encontraron resultados</div>',
        },
    },

    /**
     * Inicializa Tom Select en los <select> que coincidan. Es idempotente y no
     * necesita dropdownParent: el menú vive dentro del contenedor del control,
     * así que funciona igual dentro de un modal.
     *
     * @param {string|Element|NodeList} target - Selector o elementos (por defecto `selectSelector`)
     * @param {object} customConfig - Opciones extra de Tom Select
     * @returns {TomSelect[]}
     */
    initSelects(target = this.selectSelector, customConfig = {}) {
        if (typeof TomSelect === 'undefined') {
            return [];
        }
        const elements = typeof target === 'string' ? document.querySelectorAll(target)
            : (target instanceof Element ? [target] : target);

        const created = [];
        elements.forEach((select) => {
            if (select.tomselect) return;

            const config = { ...this.selectBaseConfig, ...customConfig };
            if (select.multiple) {
                config.plugins = ['remove_button', ...(customConfig.plugins ?? [])];
            } else if (select.options.length <= this.SEARCH_MIN_OPTIONS && !('controlInput' in customConfig)) {
                config.controlInput = null;
            }
            created.push(new TomSelect(select, config));
        });
        return created;
    },

    destroySelects(target = this.selectSelector) {
        document.querySelectorAll(target).forEach((select) => select.tomselect?.destroy());
    },

    /**
     * Un único Tooltip delegado en <body>: cubre los elementos
     * `[data-bs-toggle="tooltip"]` presentes y los que se añadan o redibujen
     * después (tablas paginadas, modales).
     */
    initTooltips(customConfig = {}) {
        if (typeof bootstrap === 'undefined' || this._tooltip) return this._tooltip ?? null;
        this._tooltip = new bootstrap.Tooltip(document.body, {
            selector: '[data-bs-toggle="tooltip"]',
            trigger: 'hover focus',
            container: 'body',
            delay: { show: 200, hide: 0 },
            ...customConfig,
        });
        return this._tooltip;
    },

    initAll() {
        this.initSelects();
        this.initTooltips();
    },
};

document.addEventListener('DOMContentLoaded', () => ComponentUtils.initAll());
