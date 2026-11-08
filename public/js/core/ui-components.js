/**
 * Componentes de UI nativos: selectores con búsqueda (Tom Select) y
 * tooltips de Bootstrap 5.
 */

const ComponentUtils = {
    // Con pocas opciones el buscador estorba; equivale al minimumResultsForSearch de Select2.
    SEARCH_MIN_OPTIONS: 7,

    selectSelector: 'select.tom-select',

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
                // Un `<select multiple data-max-items="2">` limita cuántas opciones se pueden elegir (sin el atributo, todas).
                const maxItems = Number.parseInt(select.dataset.maxItems, 10);
                if (maxItems > 0 && !('maxItems' in customConfig)) config.maxItems = maxItems;
            } else if (select.options.length <= this.SEARCH_MIN_OPTIONS && !('controlInput' in customConfig)) {
                config.controlInput = null;
            }
            const instance = new TomSelect(select, config);
            this._exposeAccessibleName(select, instance);
            created.push(instance);
        });
        return created;
    },

    /**
     * Tom Select deja el <select> original fuera de la pantalla pero dentro del árbol
     * de accesibilidad (sin etiqueta, porque reasigna el `for` del <label> a su control).
     * Se oculta ese duplicado y se da nombre al control visible, también cuando el
     * <label> envuelve al <select> (filtros de las tablas).
     */
    _exposeAccessibleName(select, instance) {
        select.setAttribute('aria-hidden', 'true');
        const control = instance.focus_node;
        if (!control || control.hasAttribute('aria-label') || control.hasAttribute('aria-labelledby')) return;
        const wrapping = select.closest('label');
        const text = select.getAttribute('aria-label')
            ?? (wrapping ? [...wrapping.childNodes].filter((n) => n.nodeType === Node.TEXT_NODE).map((n) => n.textContent).join(' ') : '');
        const name = text.replace(/[\s:]+$/, '').trim();
        if (name) control.setAttribute('aria-label', name);
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
