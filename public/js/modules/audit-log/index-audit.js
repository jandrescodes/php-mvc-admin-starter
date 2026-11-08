/**
 * Registro de auditoría (solo lectura): tabla Tabulator con búsqueda, columnas
 * visibles y reportes, filtro de fechas nativo y modal de detalle. Todas las
 * filas llegan en `data-rows`; los valores del log (actor, acción, detalles)
 * incluyen texto de visitantes sin sesión, así que van siempre por `escapeHtml`
 * o `textContent`.
 */
document.addEventListener('DOMContentLoaded', () => {
    const MODULE_VARIANTS = {
        auth: 'info',
        users: 'primary',
        roles: 'warning',
        permissions: 'secondary',
        audit_log: 'success',
    };
    const capitalize = (text) => text.charAt(0).toUpperCase() + text.slice(1);

    const table = TableUtils.create('#tableAuditLog', {
        searchInput: '#auditSearch',
        columnMenu: '#auditColumnMenu',
        pageSizes: [10, 25, 50, 100],
        pageSize: 25,
        pageSizeSelect: '#auditPageSize',
        info: '#auditInfo',
        pager: '#auditPager',
        toolbar: '#auditTop, #auditBottom',
        initialSort: [{ column: 'id', dir: 'desc' }],
        columns: [
            { title: '#', field: 'id', width: 60, hozAlign: 'center', responsive: 5, counter: true },
            {
                title: 'Date / Time',
                field: 'created_at',
                minWidth: 135,
                responsive: 0,
                sorter: 'string',
                formatter: (cell) => escapeHtml(String(cell.getValue()).slice(0, 16)),
                exportAs: (value) => String(value).slice(0, 16),
            },
            {
                title: 'User',
                field: 'actor_label',
                minWidth: 130,
                responsive: 2,
                formatter: (cell) => (cell.getValue() ? escapeHtml(cell.getValue()) : '<span class="text-body-secondary">—</span>'),
                exportAs: (value) => value || '—',
            },
            {
                title: 'Module',
                field: 'module',
                width: 120,
                responsive: 3,
                hozAlign: 'center',
                formatter: (cell) => {
                    const module = String(cell.getValue());
                    const variant = MODULE_VARIANTS[module] ?? 'dark';
                    return `<span class="badge rounded-pill text-bg-${variant}">${escapeHtml(capitalize(module))}</span>`;
                },
                exportAs: (value) => capitalize(String(value)),
            },
            {
                title: 'Action',
                field: 'action',
                minWidth: 110,
                responsive: 1,
                hozAlign: 'center',
                formatter: (cell) => `<code class="small">${escapeHtml(cell.getValue())}</code>`,
            },
            {
                title: 'IP',
                field: 'ip',
                width: 130,
                responsive: 4,
                hozAlign: 'center',
                formatter: (cell) => `<span class="font-monospace small">${escapeHtml(cell.getValue() || '—')}</span>`,
                exportAs: (value) => value || '—',
            },
            {
                title: 'Detail',
                headerSort: false,
                download: false,
                columnToggle: false,
                responsive: 0,
                width: 80,
                hozAlign: 'center',
                formatter: (cell) => `<button type="button" class="btn btn-info btn-sm btn-detail" data-id="${Number(cell.getRow().getData().id)}"
                    data-bs-toggle="tooltip" title="View detail" aria-label="View detail">
                    <i class="bi bi-eye" aria-hidden="true"></i>
                </button>`,
            },
        ],
    });

    document.querySelectorAll('#auditTop [data-export]').forEach((button) => {
        button.addEventListener('click', () => {
            TableUtils.exportTable(table, button.dataset.export, {
                filename: 'audit_log',
                title: 'Audit Log',
                sheetName: 'Audit Log',
            });
        });
    });

    // ---- Filtro de fechas: campos nativos operables con teclado. El botón abre el calendario nativo,
    // que en móvil es la forma cómoda de elegir la fecha.
    document.querySelectorAll('.btn-date-picker').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.pickerTarget);
            if (input && typeof input.showPicker === 'function') input.showPicker();
        });
    });

    // «Desde» no puede pasar de «Hasta» y viceversa: el navegador lo impone con min/max y lo anuncia al teclado.
    const dateFrom = document.getElementById('filterDateFrom');
    const dateTo = document.getElementById('filterDateTo');
    const today = dateFrom.max;
    const linkDates = () => {
        dateTo.min = dateFrom.value || '';
        dateFrom.max = dateTo.value && dateTo.value < today ? dateTo.value : today;
    };
    dateFrom.addEventListener('change', linkDates);
    dateTo.addEventListener('change', linkDates);
    linkDates();

    // ---- Detalle del evento: los datos ya están en la fila (`data-rows`), sin pedir nada al servidor.
    const detailModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalLogDetail'));
    const field = (id) => document.getElementById(id);

    const moduleBadge = (module) => {
        const variant = MODULE_VARIANTS[module] ?? 'dark';
        return `<span class="badge rounded-pill text-bg-${variant}">${escapeHtml(capitalize(String(module)))}</span>`;
    };

    /** snake_case o camelCase → «Identifier type». */
    const humanizeKey = (key) => key
        .replace(/_/g, ' ')
        .replace(/([a-z])([A-Z])/g, '$1 $2')
        .replace(/^./, (c) => c.toUpperCase());

    const MUTED_DASH = '<span class="text-body-secondary">—</span>';

    const formatValue = (value) => {
        if (value === null || value === undefined) return MUTED_DASH;
        if (typeof value === 'object') {
            return Object.entries(value).map(([k, v]) => `${escapeHtml(humanizeKey(k))}: ${escapeHtml(v)}`).join(', ');
        }
        if (typeof value === 'boolean') {
            return value
                ? '<span class="text-success"><i class="bi bi-check-lg me-1" aria-hidden="true"></i>Yes</span>'
                : '<span class="text-body-secondary"><i class="bi bi-x-lg me-1" aria-hidden="true"></i>No</span>';
        }
        return escapeHtml(String(value));
    };

    const renderDetails = (details) => {
        field('detailTableBody').innerHTML = Object.entries(details).map(([key, value]) => `<tr>
            <td class="text-body-secondary small text-uppercase text-nowrap pe-3 align-middle" style="width:35%">${escapeHtml(humanizeKey(key))}</td>
            <td class="align-middle">${formatValue(value)}</td>
        </tr>`).join('');
    };

    const openDetail = (log) => {
        field('detailSubtitle').innerHTML = `${moduleBadge(log.module)}&nbsp;<code class="small">${escapeHtml(log.action || '')}</code>`;
        field('detailId').textContent = log.id || '—';
        field('detailCreatedAt').textContent = log.created_at || '—';
        field('detailModule').innerHTML = moduleBadge(log.module);
        field('detailAction').textContent = log.action || '—';
        field('detailActor').innerHTML = log.actor_id
            ? `${escapeHtml(log.actor_label || '—')} <span class="text-body-secondary">(ID ${escapeHtml(log.actor_id)})</span>`
            : (log.actor_label ? escapeHtml(log.actor_label) : '<span class="text-body-secondary">Anonymous</span>');
        field('detailIp').textContent = log.ip || '—';
        field('detailUserAgent').textContent = log.user_agent || '—';

        field('detailDescription').textContent = log.description || '';
        field('descriptionSection').classList.toggle('d-none', !log.description);

        const hasDetails = log.details && typeof log.details === 'object' && Object.keys(log.details).length > 0;
        if (hasDetails) renderDetails(log.details);
        field('detailsSection').classList.toggle('d-none', !hasDetails);

        detailModal.show();
    };

    document.addEventListener('click', (event) => {
        const button = event.target.closest('.btn-detail');
        if (!button) return;
        const row = table.getRow(Number(button.dataset.id)); // `id` es el índice de Tabulator: sin recorrer las filas
        if (row) openDetail(row.getData());
    });
});
