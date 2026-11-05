/**
 * Tablas con Tabulator.
 *
 * Los datos viajan como JSON en un atributo `data-*` del contenedor y las
 * celdas se dibujan en cliente: todo valor de usuario pasa por `escapeHtml`
 * (definido en sweetalert-utils.js), porque un formatter devuelve HTML.
 */
const TableUtils = {
    PAGE_SIZES: [10, 25, 50, 100],

    langs: {
        es: {
            groups: { item: 'elemento', items: 'elementos' },
            data: { loading: 'Cargando', error: 'Error' },
            pagination: {
                page_size: 'Filas por página',
                page_title: 'Mostrar página',
                first: 'Primera',
                first_title: 'Primera página',
                last: 'Última',
                last_title: 'Última página',
                prev: 'Anterior',
                prev_title: 'Página anterior',
                next: 'Siguiente',
                next_title: 'Página siguiente',
                all: 'Todas',
                counter: { showing: 'Mostrando', of: 'de', rows: 'filas', pages: 'páginas' },
            },
            headerFilters: { default: 'filtrar columna…' },
        },
    },

    /** Formatters seguros reutilizables en las definiciones de columna. */
    formatters: {
        text: (cell) => escapeHtml(cell.getValue() ?? ''),

        /**
         * Insignia de Bootstrap según el valor. `map`: { valor: { label, variant } };
         * un valor desconocido se muestra escapado con `fallbackVariant`.
         */
        badge: (map, fallbackVariant = 'secondary') => (cell) => {
            const value = cell.getValue();
            const entry = map[value] ?? { label: value ?? '', variant: fallbackVariant };
            return `<span class="badge text-bg-${escapeHtml(entry.variant)}">${escapeHtml(entry.label)}</span>`;
        },
    },

    /**
     * Valores para exportar (`exportAs` en la columna). Copiar y CSV usan el dato
     * crudo, no el formatter: una columna que se dibuja con marcado (insignia,
     * enlace) debe declarar qué texto exporta, para no sacar `1`, `<span…>` o `<b>…</b>`.
     */
    exporters: {
        /** Quita el marcado de un valor con HTML y deja solo su texto. */
        plain: (value) => new DOMParser().parseFromString(String(value ?? ''), 'text/html').body.textContent,
        /** Texto de la insignia que dibuja `formatters.badge(map)`. */
        badge: (map) => (value) => String(map[value]?.label ?? value ?? ''),
    },

    /**
     * Exporta la tabla en `copy`, `csv` o `print` (nativos de Tabulator). Toma las
     * filas filtradas de todas las páginas y las columnas visibles; una columna con
     * `download: false` queda fuera. El CSV lleva BOM para que Excel respete los acentos.
     */
    exportTable(table, type, { filename = 'export', title = '', sheetName = 'Datos', footerLabel = '' } = {}) {
        if (!table) return;
        if (type === 'copy') {
            table.copyToClipboard('active');
        } else if (type === 'csv') {
            table.download('csv', `${filename}.csv`, { bom: true });
        } else if (type === 'print') {
            table.print('active', true);
        } else if (type === 'xlsx') {
            if (typeof XLSX === 'undefined') return ToastUtils.error('No se pudo exportar', 'La librería de Excel no está cargada.');
            table.download('xlsx', `${filename}.xlsx`, { sheetName });
        } else if (type === 'pdf') {
            if (!globalThis.jspdf?.jsPDF) return ToastUtils.error('No se pudo exportar', 'La librería de PDF no está cargada.');
            table.download('pdf', `${filename}.pdf`, {
                orientation: 'landscape',
                title,
                autoTable: () => ({
                    styles: { fontSize: 9 },
                    headStyles: { fillColor: [75, 84, 92] },
                    margin: { top: title ? 50 : 40, left: 40, right: 40, bottom: 40 },
                }),
                // `title` hace que Tabulator pise didDrawPage, así que el pie se dibuja aquí, con el total de páginas ya conocido.
                documentProcessing: (doc) => this._drawFooter(doc, footerLabel),
            });
        }
    },

    _drawFooter(doc, label) {
        const pages = doc.getNumberOfPages();
        const { width, height } = doc.internal.pageSize;
        const date = new Date().toLocaleDateString('es');
        doc.setFontSize(8);
        for (let page = 1; page <= pages; page++) {
            doc.setPage(page);
            doc.text(label, 40, height - 20);
            doc.text(`Página ${page} de ${pages}`, width / 2, height - 20, { align: 'center' });
            doc.text(date, width - 40, height - 20, { align: 'right' });
        }
    },

    /** Lee las filas del atributo `data-rows` (JSON) del contenedor. */
    readRows(container) {
        const element = typeof container === 'string' ? document.querySelector(container) : container;
        try {
            const rows = JSON.parse(element?.dataset.rows ?? '[]');
            return Array.isArray(rows) ? rows : [];
        } catch {
            return [];
        }
    },

    /** Opciones comunes: paginación, orden, layout y textos en español. */
    baseOptions() {
        return {
            layout: 'fitColumns',
            pagination: true,
            paginationSize: this.PAGE_SIZES[0],
            paginationSizeSelector: this.PAGE_SIZES,
            paginationCounter: 'rows',
            movableColumns: false,
            clipboard: true,
            clipboardCopyStyled: false, // texto plano tabulado, no HTML con estilos
            clipboardCopyRowRange: 'active', // todas las filas filtradas, no solo la página
            downloadRowRange: 'active',
            printRowRange: 'active',
            printAsHtml: true,
            printStyled: true,
            locale: 'es',
            langs: this.langs,
            placeholder: 'No hay datos para mostrar',
        };
    },

    SEARCH_DEBOUNCE_MS: 200,

    _normalize(value) {
        return String(value ?? '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    },

    /**
     * Búsqueda global: filtra por el texto de todas las columnas con `field`,
     * sin distinguir mayúsculas ni acentos. Debe llamarse con la tabla construida.
     */
    bindSearch(table, input) {
        const element = typeof input === 'string' ? document.querySelector(input) : input;
        if (!table || !element) return;

        let timer;
        element.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                const term = this._normalize(element.value.trim());
                if (term === '') {
                    table.clearFilter();
                    return;
                }
                const fields = table.getColumns().map((column) => column.getField()).filter(Boolean);
                table.setFilter((row) => fields.some((field) => this._normalize(row[field]).includes(term)));
            }, this.SEARCH_DEBOUNCE_MS);
        });
    },

    /**
     * Rellena un `.dropdown-menu` de Bootstrap con una casilla por columna para
     * mostrarla u ocultarla (Tabulator no trae selector de columnas). Una columna
     * con `columnToggle: false` en su definición no aparece (p. ej. acciones).
     * Use `data-bs-auto-close="outside"` en el botón para que el menú no se cierre
     * en cada clic.
     */
    bindColumnToggle(table, menu) {
        const element = typeof menu === 'string' ? document.querySelector(menu) : menu;
        if (!table || !element) return;

        element.replaceChildren();
        table.getColumns().forEach((column, index) => {
            const definition = column.getDefinition();
            if (!definition.title || definition.columnToggle === false) return;

            const id = `${element.id || 'col-menu'}-${index}`;
            const item = document.createElement('div');
            item.className = 'form-check mx-3 my-1';

            const check = document.createElement('input');
            check.type = 'checkbox';
            check.className = 'form-check-input';
            check.id = id;
            check.checked = column.isVisible();
            check.addEventListener('change', () => column.toggle());

            const label = document.createElement('label');
            label.className = 'form-check-label';
            label.htmlFor = id;
            label.textContent = definition.title;

            item.append(check, label);
            element.append(item);
        });
    },

    /**
     * Crea la tabla. `options.data` por defecto sale de `data-rows` del contenedor.
     * `searchInput` y `columnMenu` (selectores o elementos) activan la búsqueda
     * global y el selector de columnas. Las columnas que no definan `formatter` usan el texto escapado y `sorter` natural.
     */
    create(container, options = {}) {
        const element = typeof container === 'string' ? document.querySelector(container) : container;
        if (!element || typeof Tabulator === 'undefined') return null;

        const columns = (options.columns ?? []).map(({ exportAs, ...column }) => ({
            formatter: this.formatters.text,
            sorter: 'alphanum', // el sorter por defecto ('string') ordena 19 antes que 2
            ...(exportAs ? { accessorDownload: exportAs, accessorClipboard: exportAs } : {}),
            ...column,
        }));

        const { searchInput, columnMenu, ...tabulatorOptions } = options;
        const table = new Tabulator(element, {
            ...this.baseOptions(),
            data: this.readRows(element),
            ...tabulatorOptions,
            columns,
        });

        table.on('tableBuilt', () => {
            if (searchInput) this.bindSearch(table, searchInput);
            if (columnMenu) this.bindColumnToggle(table, columnMenu);
        });
        return table;
    },
};
