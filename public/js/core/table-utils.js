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

    SYSTEM_NAME: 'Base MVC System',

    /**
     * Exporta la tabla en `copy`, `pdf`, `xlsx`, `csv` o `print`. Toma las filas
     * filtradas de todas las páginas y las columnas visibles; una columna con
     * `download: false` queda fuera. Mismos reportes que DataTables Buttons:
     * PDF con título centrado, subtítulo, fecha de generación y pie
     * (sistema · página · confidencial); Excel con título y mensajes arriba y abajo;
     * impresión con título y fecha; CSV con BOM para que Excel respete los acentos.
     * `title` y `subtitle` encabezan el reporte; `filename` no lleva extensión ni fecha.
     */
    exportTable(table, type, { filename = 'export', title = '', subtitle = '', sheetName = 'Data', systemName = this.SYSTEM_NAME } = {}) {
        if (!table) return;
        const stamp = new Date().toISOString().slice(0, 10);
        const file = `${filename}_${stamp}`;
        const generated = new Date().toLocaleString('en-US');
        const subtitleText = subtitle || `Record of ${title.toLowerCase()}`;

        if (type === 'copy') {
            table.copyToClipboard('active');
        } else if (type === 'csv') {
            table.download('csv', `${file}.csv`, { bom: true });
        } else if (type === 'print') {
            this._printFrame(table, { title, subtitle: subtitleText, generated, systemName });
        } else if (type === 'xlsx') {
            if (typeof XLSX === 'undefined') return ToastUtils.error('Export failed', 'The Excel library is not loaded.');
            table.download('xlsx', `${file}.xlsx`, {
                sheetName,
                documentProcessing: (workbook) => this._decorateSheet(workbook, { title, subtitleText, closing: `Document generated on ${new Date().toLocaleDateString('en-US')}` }),
            });
        } else if (type === 'pdf') {
            if (!globalThis.jspdf?.jsPDF) return ToastUtils.error('Export failed', 'The PDF library is not loaded.');
            table.download('pdf', `${file}.pdf`, {
                orientation: 'portrait',
                jsPDF: { format: 'letter' },
                // Sin `title` de Tabulator (pisaría didDrawPage): encabezado y pie se dibujan en documentProcessing, con el total de páginas ya conocido.
                autoTable: () => ({
                    styles: { fontSize: 9, cellPadding: 4, overflow: 'linebreak' },
                    headStyles: { fontSize: 10, fillColor: [75, 84, 92], textColor: 255 },
                    startY: 90, // el encabezado solo se dibuja en la primera página
                    margin: { top: 40, left: 40, right: 40, bottom: 40 },
                }),
                documentProcessing: (doc) => this._decoratePdf(doc, { title, subtitleText, generated, systemName }),
            });
        }
    },

    /**
     * Imprime en un iframe aislado con una tabla simple de Bootstrap, como la vista de
     * impresión de DataTables Buttons: un documento limpio sin el layout de AdminLTE, que
     * es lo que hacía salir la tabla en muchas hojas.
     */
    _printFrame(table, { title, subtitle, generated, systemName }) {
        const css = document.querySelector('link[href*="adminlte.min.css"]')?.href ?? '';
        const frame = Object.assign(document.createElement('iframe'), { title: 'print' });
        frame.style.cssText = 'position:fixed;width:794px;height:0;border:0;visibility:hidden';
        document.body.appendChild(frame);

        const doc = frame.contentDocument;
        doc.open();
        doc.write(`<!doctype html><html lang="en" data-bs-theme="light"><head><meta charset="utf-8">
<title>${escapeHtml(title)}</title><link rel="stylesheet" href="${css}">
<style>
/* Sin margen de hoja el navegador no tiene dónde dibujar su URL, fecha y título; el margen va como padding. */
@page { size: A4 portrait; margin: 0; }
html, body { background: #fff; color: #000; margin: 0; height: auto; overflow: visible; }
body { padding: 12mm 12mm 20mm; }
header { text-align: center; border-bottom: 1px solid #000; margin-bottom: 10px; padding-bottom: 6px; }
h1 { font-size: 18px; font-weight: 700; margin: 0; text-transform: uppercase; }
.subtitle { font-size: 12px; font-style: italic; margin: 2px 0 6px; }
.generated { font-size: 11px; text-align: right; margin: 0; }
table { width: 100%; font-size: 12px; } thead { display: table-header-group; } tr { break-inside: avoid; }
footer { position: fixed; left: 12mm; right: 12mm; bottom: 8mm; display: flex; justify-content: space-between; font-size: 10px; border-top: 1px solid #999; padding-top: 3px; }
* { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
</style></head><body>
<header><h1>${escapeHtml(title)}</h1><p class="subtitle">${escapeHtml(subtitle)}</p><p class="generated">Generated on: ${escapeHtml(generated)}</p></header>
${table.getHtml('active', false)}
<footer><span>${escapeHtml(systemName)}</span><span>Confidential</span></footer>
</body></html>`);
        doc.close();
        // El control `+`/`−` de la fila responsiva viaja en la primera celda: su SVG no tiene tamaño y estira cada fila.
        doc.querySelectorAll('.tabulator-responsive-collapse-toggle').forEach((toggle) => toggle.remove());
        doc.querySelector('table')?.classList.add('table', 'table-striped', 'table-bordered', 'table-sm');

        const link = doc.querySelector('link');
        const ready = link?.sheet ? Promise.resolve() : new Promise((resolve) => { link.onload = link.onerror = resolve; });
        ready.then(() => {
            const done = () => frame.remove();
            frame.contentWindow.addEventListener('afterprint', done, { once: true });
            frame.contentWindow.focus();
            frame.contentWindow.print();
            setTimeout(done, 60000); // algunos navegadores no disparan afterprint
        });
    },

    /** Título, mensaje superior y mensaje inferior de la hoja, como `title`/`messageTop`/`messageBottom` de DataTables. */
    _decorateSheet(workbook, { title, subtitleText, closing }) {
        const name = workbook.SheetNames[0];
        const rows = XLSX.utils.sheet_to_json(workbook.Sheets[name], { header: 1 });
        const sheet = XLSX.utils.aoa_to_sheet([[title], [subtitleText], [], ...rows, [], [closing]]);
        sheet['!cols'] = rows[0]?.map((_, col) => ({ wch: Math.min(60, Math.max(10, ...rows.map((row) => String(row[col] ?? '').length + 2))) }));
        workbook.Sheets[name] = sheet;
        return workbook;
    },

    /** Título en mayúsculas, subtítulo, fecha de generación y pie «sistema · página · confidencial». */
    _decoratePdf(doc, { title, subtitleText, generated, systemName }) {
        const pages = doc.getNumberOfPages();
        const width = doc.internal.pageSize.getWidth();
        const height = doc.internal.pageSize.getHeight();
        for (let page = 1; page <= pages; page++) {
            doc.setPage(page);
            doc.setFontSize(8);
            doc.text(systemName, 40, height - 20);
            doc.text(`Page ${page} of ${pages}`, width / 2, height - 20, { align: 'center' });
            doc.text('Confidential', width - 40, height - 20, { align: 'right' });
        }
        doc.setPage(1);
        doc.setFont(undefined, 'bold');
        doc.setFontSize(16);
        doc.text(doc.splitTextToSize(title.toUpperCase(), width - 80), width / 2, 34, { align: 'center' });
        doc.setFont(undefined, 'italic');
        doc.setFontSize(11);
        doc.text(subtitleText, width / 2, 52, { align: 'center' });
        doc.setFont(undefined, 'normal');
        doc.setFontSize(9);
        doc.text(`Generated on: ${generated}`, width - 40, 72, { align: 'right' });
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
            responsiveLayoutCollapseFormatter: this._collapseInColumnOrder,
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
            locale: 'es',
            langs: this.langs,
            placeholder: 'No hay datos para mostrar',
        };
    },

    /**
     * Detalle de la fila responsiva con las columnas ocultas en su orden original
     * (DataTables Responsive), no en el orden en que Tabulator las fue ocultando.
     * Tabulator lo enlaza a la tabla: `this` es la instancia de `Tabulator`.
     */
    _collapseInColumnOrder(data) {
        const order = this.getColumns().map((column) => column.getField());
        const list = document.createElement('table');
        [...data].sort((a, b) => order.indexOf(a.field) - order.indexOf(b.field)).forEach(({ title, value }) => {
            const row = list.insertRow();
            const label = document.createElement('strong');
            label.textContent = title;
            row.insertCell().append(label);
            const cell = row.insertCell();
            if (value instanceof Node) cell.append(value);
            else cell.innerHTML = value;
        });
        return data.length ? list : '';
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
                const fields = table.getColumns().map((column) => column.getField()).filter(Boolean);
                this._setFilter(table, 'search', term === '' ? null : (row) => fields.some((field) => this._normalize(row[field]).includes(term)));
            }, this.SEARCH_DEBOUNCE_MS);
        });
    },

    /**
     * Filtra por el valor de un `<select>` contra un campo de la fila (la opción
     * vacía quita el filtro). Convive con la búsqueda y con los demás filtros.
     */
    bindSelectFilter(table, selectElement, field) {
        const element = typeof selectElement === 'string' ? document.querySelector(selectElement) : selectElement;
        if (!table || !element) return;
        element.addEventListener('change', () => {
            const value = element.value;
            this._setFilter(table, `select:${field}`, value === '' ? null : (row) => String(row[field]) === value);
        });
    },

    /** Posición (desde 1) de la fila entre todas las filtradas y ordenadas, de cualquier página. */
    _activePosition(row) {
        return row.getTable().getRows('active').findIndex((candidate) => candidate.getElement() === row.getElement() || candidate === row) + 1;
    },

    /** Tabulator solo guarda un conjunto de filtros: se combinan aquí con AND. */
    _setFilter(table, key, predicate) {
        table._tblFilters ??= new Map();
        predicate ? table._tblFilters.set(key, predicate) : table._tblFilters.delete(key);
        if (table._tblFilters.size === 0) {
            table.clearFilter();
            return;
        }
        const predicates = [...table._tblFilters.values()];
        table.setFilter((row) => predicates.every((test) => test(row)));
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
     * Una columna con `counter: true` muestra el número de fila (1, 2, 3…) en lugar de su valor, renumerado al ordenar y filtrar.
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
        let counterField = null;
        const columns = (options.columns ?? []).map(({ exportAs, columnToggle, counter, ...column }) => {
            if (columnToggle === false && column.title) hiddenFromToggle.add(column.title);
            if (counter) counterField = column.field;
            return {
                ...(counter ? {
                    formatter: (cell) => String(cell.getRow().getPosition(true)),
                    // Copiar y exportar numeran por la posición en la tabla filtrada y ordenada, no por el id.
                    // `getPosition(true)` solo conoce las filas de la página visible: con paginación daría `false`.
                    accessorDownload: (_value, _data, _type, _params, _column, row) => this._activePosition(row),
                    accessorClipboard: (_value, _data, _type, _params, _column, row) => this._activePosition(row),
                } : {}),
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

        // Un contador se renumera al ordenar, filtrar o paginar: el formatter solo corre al crear la fila.
        if (counterField) {
            table.on('renderComplete', () => {
                table.getRows('visible').forEach((row) => {
                    row.getCell(counterField)?.getElement().replaceChildren(String(row.getPosition(true)));
                });
            });
        }

        table.on('clipboardCopied', () => ToastUtils.success('Copied to clipboard', 'The table data is ready to paste.'));

        table.on('tableBuilt', () => {
            // Tabulator pone el cuerpo (`tabindex=0`) sin rol dentro del `role=grid`; el cuerpo es el grupo de filas.
            element.querySelector('.tabulator-tableholder')?.setAttribute('role', 'rowgroup');
            element.querySelector('.tabulator-table')?.setAttribute('role', 'presentation');
            // Los encabezados ordenables son `div`s sin foco: sin esto el orden solo se cambia con ratón.
            const focusSortable = () => element.querySelectorAll('.tabulator-col.tabulator-sortable:not([tabindex])')
                .forEach((col) => col.setAttribute('tabindex', '0'));
            focusSortable();
            table.on('renderComplete', focusSortable);
            element.addEventListener('keydown', (event) => {
                const col = event.target.closest?.('.tabulator-col.tabulator-sortable');
                if (col && event.target === col && (event.key === 'Enter' || event.key === ' ')) {
                    event.preventDefault();
                    col.click();
                }
            });
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
