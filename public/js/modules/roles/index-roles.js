/**
 * index-roles.js - Listado de roles
 *
 * Tabla Tabulator con búsqueda, columnas visibles y exportación, y el cambio
 * de estado con confirmación. El modal (crear/editar) vive en modal-role.js.
 */
document.addEventListener('DOMContentLoaded', () => {
    const STATUS = {
        1: { label: 'Active', variant: 'success' },
        0: { label: 'Inactive', variant: 'danger' },
    };

    const table = TableUtils.create('#tableRoles', {
        searchInput: '#rolesSearch',
        columnMenu: '#rolesColumnMenu',
        pageSizes: [5, 10, 25, 50, 100],
        pageSize: 10,
        pageSizeSelect: '#rolesPageSize',
        info: '#rolesInfo',
        pager: '#rolesPager',
        toolbar: '#rolesTop, #rolesBottom',
        initialSort: [{ column: 'id', dir: 'asc' }],
        columns: [
            { title: '#', field: 'id', width: 60, hozAlign: 'center', headerHozAlign: 'center', responsive: 3, counter: true },
            {
                title: 'Name',
                field: 'name',
                minWidth: 160,
                responsive: 0,
                sorter: 'string',
                formatter: (cell) => {
                    const role = cell.getRow().getData();
                    const system = role.system ? ' <span class="badge text-bg-danger ms-1">System</span>' : '';
                    return `${escapeHtml(role.name)}${system}`;
                },
                exportAs: TableUtils.exporters.plain,
            },
            {
                title: 'Description',
                field: 'description',
                minWidth: 220,
                widthGrow: 2,
                responsive: 5,
                formatter: (cell) => escapeHtml(cell.getValue() || 'N/A'),
                exportAs: (value) => value || 'N/A',
            },
            {
                title: 'Users',
                field: 'users',
                width: 110,
                responsive: 4,
                hozAlign: 'center',
                headerHozAlign: 'center',
                formatter: (cell) => {
                    const total = Number(cell.getValue()) || 0;
                    const variant = total > 0 ? 'primary' : 'secondary';
                    return `<span class="badge text-bg-${variant}">${total} ${total === 1 ? 'user' : 'users'}</span>`;
                },
                exportAs: (value) => `${Number(value) || 0} ${Number(value) === 1 ? 'user' : 'users'}`,
            },
            {
                title: 'Status',
                field: 'status',
                width: 115,
                responsive: 2,
                hozAlign: 'center',
                headerHozAlign: 'center',
                formatter: (cell) => {
                    const entry = STATUS[cell.getValue()] ?? STATUS[0];
                    return `<span class="badge text-bg-${entry.variant}">${entry.label}</span>`;
                },
                exportAs: TableUtils.exporters.badge(STATUS),
            },
            {
                title: 'Actions',
                field: 'actions',
                headerSort: false,
                download: false,
                columnToggle: false,
                responsive: 0,
                width: 120,
                cssClass: 'tbl-col-actions',
                hozAlign: 'center',
                headerHozAlign: 'center',
                formatter: (cell) => {
                    const role = cell.getRow().getData();
                    const id = Number(role.id);
                    const active = role.status === 1;
                    // Solo un administrador toca un rol de sistema: el servidor lo rechaza, así que no se ofrece.
                    const manage = role.manage !== false;
                    const name = escapeHtml(role.name);
                    const editData = `data-id="${id}" data-name="${name}" data-description="${escapeHtml(role.description ?? '')}"
                            data-system="${role.system ? 1 : 0}" data-permissions="${escapeHtml(JSON.stringify(role.permissions ?? []))}"`;
                    const statusData = `data-id="${id}" data-current-status="${active ? 1 : 0}" data-users="${Number(role.users) || 0}"`;
                    const statusLabel = active ? 'Deactivate' : 'Activate';
                    const buttons = `<div class="btn-group btn-group-sm d-none d-sm-inline-flex" role="group" aria-label="Actions for role ${name}">
                        <a href="${baseUrl}roles/${id}" class="btn btn-info" data-bs-toggle="tooltip" title="View permissions" aria-label="View permissions of ${name}">
                            <i class="bi bi-key" aria-hidden="true"></i>
                        </a>${manage ? `
                        <button type="button" class="btn btn-warning btn-edit" ${editData}
                            data-bs-toggle="tooltip" title="Edit" aria-label="Edit role ${name}">
                            <i class="bi bi-pencil-square" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="btn ${active ? 'btn-danger' : 'btn-success'} btn-toggle-status" ${statusData}
                            data-bs-toggle="tooltip" title="${statusLabel}" aria-label="${statusLabel} role ${name}">
                            <i class="bi ${active ? 'bi-x-lg' : 'bi-check-lg'}" aria-hidden="true"></i>
                        </button>` : ''}
                    </div>`;
                    // En móvil las acciones se pliegan en un menú; `fixed` evita que la celda lo recorte.
                    const item = (cls, attrs, color, icon, text) => `<button type="button" class="dropdown-item ${cls}" ${attrs}>
                        <span class="badge text-bg-${color} me-2 tbl-menu-icon"><i class="bi ${icon}" aria-hidden="true"></i></span>${text}</button>`;
                    const menu = [
                        `<li><a class="dropdown-item" href="${baseUrl}roles/${id}"><span class="badge text-bg-info me-2 tbl-menu-icon"><i class="bi bi-key" aria-hidden="true"></i></span>Permissions</a></li>`,
                        manage ? `<li>${item('btn-edit', editData, 'warning', 'bi-pencil-square', 'Edit')}</li>` : '',
                        manage ? `<li>${item('btn-toggle-status', statusData, active ? 'danger' : 'success', active ? 'bi-x-lg' : 'bi-check-lg', statusLabel)}</li>` : '',
                    ].join('');
                    return `${buttons}
                    <div class="dropdown d-sm-none">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false" aria-label="Actions for role ${name}">
                            <i class="bi bi-three-dots-vertical" aria-hidden="true"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">${menu}</ul>
                    </div>`;
                },
            },
        ],
    });

    TableUtils.bindSelectFilter(table, '#rolesStatusFilter', 'status');

    document.querySelectorAll('#rolesTop [data-export]').forEach((button) => {
        button.addEventListener('click', () => {
            TableUtils.exportTable(table, button.dataset.export, {
                filename: 'roles_system',
                title: 'Roles',
                sheetName: 'Roles',
            });
        });
    });

    document.addEventListener('click', (event) => {
        const button = event.target.closest('.btn-toggle-status');
        if (button) toggleStatus(button);
    });

    function toggleStatus(button) {
        const id = Number(button.dataset.id);
        const currentStatus = Number(button.dataset.currentStatus);
        const users = Number(button.dataset.users);

        if (currentStatus === 1 && users > 0) {
            ToastUtils.error('Error', 'Cannot deactivate this role because it has assigned users.');
            return;
        }

        const action = currentStatus === 1 ? 'deactivate' : 'activate';
        const actionCapitalized = action.charAt(0).toUpperCase() + action.slice(1);

        AlertUtils.confirm(
            `${actionCapitalized} this role?`,
            `The role will be ${action}d.`,
            () => {
                ToastUtils.loadingWithMinTime(`${actionCapitalized}ing role...`, async () => {
                    const { data } = await Http.post(`${baseUrl}roles/toggle-status`, { id, current_status: currentStatus });
                    if (data.success) {
                        location.reload();
                        return;
                    }
                    Swal.close();
                    ToastUtils.error('Error', data.message);
                }, 800);
            },
            {
                confirmText: `Yes, ${action}`,
                confirmColor: currentStatus === 1 ? SwalTheme.DANGER : '#146c43',
                cancelColor: '#6c757d',
                cancelText: 'Cancel',
            }
        );
    }
});
