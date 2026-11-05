/**
 * Tablas con Tabulator.
 *
 * Los datos viajan como JSON en un atributo `data-*` del contenedor y las
 * celdas se dibujan en cliente: todo valor de usuario pasa por `escapeHtml`
 * (definido en sweetalert-utils.js), porque un formatter devuelve HTML.
 */
const TableUtils = {
    /** Tamaños de página por defecto; cada módulo los cambia con `pageSizes` / `pageSize` en `create()`. */
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
    baseOptions(pageSizes = this.PAGE_SIZES, pageSize = pageSizes[0]) {
        return {
            layout: 'fitColumns',
            responsiveLayoutCollapseStartOpen: false,
            responsiveLayout: 'collapse', // lo que no cabe pasa a una fila desplegable (`responsive`: mayor número se oculta antes)
            pagination: true,
            paginationSize: pageSize,
            paginationSizeSelector: [...pageSizes], // Tabulator hace unshift sobre el array recibido
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
     * con `columnToggle: false` en la definición que se pasa a `create` no aparece (p. ej. acciones).
     * Use `data-bs-auto-close="outside"` en el botón para que el menú no se cierre
     * en cada clic.
     */
    bindColumnToggle(table, menu, hidden = new Set()) {
        const element = typeof menu === 'string' ? document.querySelector(menu) : menu;
        if (!table || !element) return;

        element.replaceChildren();
        table.getColumns().forEach((column, index) => {
            const definition = column.getDefinition();
            if (!definition.title || hidden.has(definition.title)) return;

            const id = `${element.id || 'col-menu'}-${index}`;
            // El <label> es el ítem completo: se puede pulsar en cualquier punto de la fila.
            const item = document.createElement('label');
            item.className = 'dropdown-item d-flex align-items-center gap-2 mb-0';
            item.htmlFor = id;

            const check = document.createElement('input');
            check.type = 'checkbox';
            check.className = 'form-check-input mt-0';
            check.id = id;
            check.checked = column.isVisible();
            check.addEventListener('change', () => column.toggle());

            const text = document.createElement('span');
            text.textContent = definition.title;

            item.append(check, text);
            element.append(item);
        });
    },

    LABELS: {
        info: (from, to, total, all) => {
            const base = total === 0 ? 'Showing 0 to 0 of 0 entries' : `Showing ${from} to ${to} of ${total} entries`;
            return all > total ? `${base} (filtered from ${all} total entries)` : base;
        },
        previous: 'Previous',
        next: 'Next',
    },

    /** Rellena un `<select>` con los tamaños de página (como "Show N entries" de DataTables). */
    bindPageSize(table, select, pageSizes = this.PAGE_SIZES) {
        const element = typeof select === 'string' ? document.querySelector(select) : select;
        if (!table || !element) return;

        if (element.options.length === 0) {
            pageSizes.forEach((size) => element.add(new Option(String(size), String(size))));
        }
        element.value = String(table.getPageSize());
        element.addEventListener('change', () => table.setPageSize(Number(element.value)));
    },

    /**
     * Números de página con puntos suspensivos (1 … 7 8 9 … 19), igual que el
     * `simple_numbers` de DataTables. `slots` es el máximo de casillas (7 o 5).
     */
    pageNumbers(total, current, slots = 7) {
        if (total <= slots) return Array.from({ length: total }, (_, i) => i + 1);
        const range = (from, to) => Array.from({ length: to - from + 1 }, (_, i) => from + i);
        if (current <= slots - 3) return [...range(1, slots - 2), '…', total];
        if (current >= total - (slots - 4)) return [1, '…', ...range(total - (slots - 3), total)];
        const middle = slots - 4;
        const start = current - Math.floor(middle / 2);
        return [1, '…', ...range(start, start + middle - 1), '…', total];
    },

    /**
     * Pie propio: `info` (texto "Showing X to Y of N entries") y `pager`
     * (`<ul class="pagination">` de Bootstrap con Previous/Next), en lugar del
     * paginador nativo de Tabulator. En pantallas pequeñas muestra menos números.
     */
    bindPager(table, { info, pager }) {
        const infoElement = typeof info === 'string' ? document.querySelector(info) : info;
        const pagerElement = typeof pager === 'string' ? document.querySelector(pager) : pager;
        const compact = globalThis.matchMedia('(max-width: 575.98px)');

        const render = () => {
            const size = table.getPageSize();
            const total = table.getDataCount('active');
            const pages = Math.max(table.getPageMax() || 1, 1);
            const current = table.getPage() || 1;

            if (infoElement) {
                const from = total === 0 ? 0 : (current - 1) * size + 1;
                const to = Math.min(current * size, total);
                infoElement.textContent = this.LABELS.info(from, to, total, table.getDataCount('all'));
            }
            if (!pagerElement) return;

            const item = (label, page, { disabled = false, active = false } = {}) => {
                const li = document.createElement('li');
                li.className = `page-item${disabled ? ' disabled' : ''}${active ? ' active' : ''}`;
                const control = document.createElement(page === null ? 'span' : 'button');
                control.className = 'page-link';
                control.textContent = label;
                if (page !== null) {
                    control.type = 'button';
                    control.dataset.page = String(page);
                    control.disabled = disabled;
                }
                if (active) control.setAttribute('aria-current', 'page');
                li.append(control);
                return li;
            };

            pagerElement.replaceChildren(
                item(this.LABELS.previous, current - 1, { disabled: current <= 1 }),
                ...this.pageNumbers(pages, current, compact.matches ? 5 : 7).map((page) =>
                    page === '…' ? item('…', null, { disabled: true }) : item(String(page), page, { active: page === current })
                ),
                item(this.LABELS.next, current + 1, { disabled: current >= pages })
            );
        };

        pagerElement?.addEventListener('click', (event) => {
            const button = event.target.closest('button[data-page]');
            if (button && !button.disabled) table.setPage(Number(button.dataset.page));
        });
        table.on('pageLoaded', render);
        table.on('renderComplete', render); // dataFiltered/dataProcessed llegan antes de repaginar y dejan total y página viejos
        compact.addEventListener('change', render);
        render();
    },

    /**
     * Pone el control `+`/`−` de la fila responsiva dentro de la primera celda, como el
     * `dtr-inline` de DataTables, en vez de una columna propia. Va en la primera columna
     * con `responsive: 0` (la que nunca se oculta; si no hay, la primera): el control
     * vive en esa celda y Tabulator solo muestra el detalle de las columnas ocultas.
     */
    _embedCollapseToggle(columns) {
        const host = columns.find((column) => column.responsive === 0) ?? columns[0];
        if (!host) return;

        const inner = host.formatter;
        host.formatter = function (cell, params, onRendered) {
            // Copiar/exportar formatea con una fila simulada sin estado responsivo: ahí solo va el contenido.
            if (!cell.getRow()._row?.modules?.responsiveLayout) return inner.call(this, cell, params, onRendered);

            // Es el formatter interno `responsiveCollapse` del módulo, que conserva el estado abierto/cerrado de la fila.
            const toggle = cell.getTable().modules.format.constructor.formatters.responsiveCollapse(cell, params, onRendered);
            cell.getElement().classList.remove('tabulator-row-handle'); // esa clase centra la celda
            const content = document.createElement('span');
            const rendered = inner.call(this, cell, params, onRendered);
            if (typeof rendered === 'string') content.innerHTML = rendered;
            else if (rendered) content.append(rendered);

            const wrapper = document.createElement('div');
            wrapper.className = 'tbl-first-cell';
            wrapper.append(toggle, content);
            return wrapper;
        };
    },

    /**
     * Crea la tabla. `options.data` por defecto sale de `data-rows` del contenedor.
     * `searchInput`, `columnMenu`, `pageSizeSelect`, `info` y `pager` (selectores o elementos)
     * activan la búsqueda global, el selector de columnas, el tamaño de página y el pie propio; `pageSizes` (opciones del selector) y `pageSize` (el inicial) los fija cada módulo; `toolbar` (clase `tbl-toolbar`, oculta por
     * CSS) se muestra cuando la tabla está construida. El tamaño y los bordes se eligen con clases
     * del contenedor impreso por la vista (`tbl-sm`, `tbl-bordered`, … en `table.css`). Las columnas
     * que no definan `formatter` usan el texto escapado y `sorter` natural.
     */
    create(container, options = {}) {
        const element = typeof container === 'string' ? document.querySelector(container) : container;
        if (!element || typeof Tabulator === 'undefined') return null;

        // `columnToggle` no es una opción de Tabulator (avisa "Invalid column definition option"): se quita y se recuerda aparte.
        const hiddenFromToggle = new Set();
        const columns = (options.columns ?? []).map(({ exportAs, columnToggle, ...column }) => {
            if (columnToggle === false && column.title) hiddenFromToggle.add(column.title);
            return {
                formatter: this.formatters.text,
                sorter: 'alphanum', // el sorter por defecto ('string') ordena 19 antes que 2
                ...(exportAs ? { accessorDownload: exportAs, accessorClipboard: exportAs } : {}),
                ...column,
            };
        });

        const { searchInput, columnMenu, toolbar, pageSizeSelect, info, pager, pageSizes: sizes, pageSize: size, ...tabulatorOptions } = options;
        // El tamaño inicial siempre es una opción del selector.
        const pageSize = size ?? sizes?.[0] ?? this.PAGE_SIZES[0];
        const pageSizes = [...new Set([...(sizes ?? this.PAGE_SIZES), pageSize])].sort((a, b) => a - b);

        this._embedCollapseToggle(columns);

        // Con un paginador propio, Tabulator pagina pero no dibuja su pie nativo.
        const customPager = pager || info ? { paginationElement: document.createElement('div') } : {};

        const table = new Tabulator(element, {
            ...this.baseOptions(pageSizes, pageSize),
            data: this.readRows(element),
            ...customPager,
            ...tabulatorOptions,
            columns,
        });

        table.on('clipboardCopied', () => ToastUtils.success('Copied to clipboard', 'The table data is ready to paste.'));

        table.on('tableBuilt', () => {
            if (searchInput) this.bindSearch(table, searchInput);
            if (columnMenu) this.bindColumnToggle(table, columnMenu, hiddenFromToggle);
            if (pageSizeSelect) this.bindPageSize(table, pageSizeSelect, pageSizes);
            if (pager || info) this.bindPager(table, { info, pager });
            const toolbars = typeof toolbar === 'string' ? document.querySelectorAll(toolbar) : [toolbar];
            toolbars.forEach((toolbarElement) => toolbarElement?.classList.add('tbl-toolbar-ready'));
        });
        return table;
    },
};
