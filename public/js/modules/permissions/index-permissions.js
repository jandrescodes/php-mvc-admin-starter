/**
 * index-permissions.js - Listado de permisos
 *
 * Tabla Tabulator con búsqueda, columnas visibles, exportación
 * y cambio de estado con confirmación. El modal (crear/editar) vive en
 * modal-permission.js.
 */
document.addEventListener('DOMContentLoaded', () => {
    const STATUS = {
        1: { label: 'Active', variant: 'success' },
        0: { label: 'Inactive', variant: 'danger' },
    };

    const table = TableUtils.create('#tablePermissions', {
        searchInput: '#permissionsSearch',
        columnMenu: '#permissionsColumnMenu',
        pageSizes: [5, 10, 25, 50, 100],
        pageSize: 10,
        pageSizeSelect: '#permissionsPageSize',
        info: '#permissionsInfo',
        pager: '#permissionsPager',
        toolbar: '#permissionsTop, #permissionsBottom',
        initialSort: [{ column: 'name', dir: 'asc' }],
        columns: [
            { title: '#', field: 'id', width: 60, hozAlign: 'center', responsive: 3, counter: true, headerSort: false },
            { title: 'Name', field: 'name', minWidth: 120, responsive: 0, sorter: 'string' },
            {
                title: 'Description',
                field: 'description',
                minWidth: 140,
                responsive: 5,
                formatter: (cell) => escapeHtml(cell.getValue() || 'N/A'),
                exportAs: (value) => value || 'N/A',
            },
            {
                title: 'Users',
                field: 'users',
                width: 90,
                responsive: 4,
                hozAlign: 'center',
                formatter: (cell) => {
                    const total = Number(cell.getValue()) || 0;
                    const variant = total > 0 ? 'primary' : 'secondary';
                    return `<span class="badge rounded-pill text-bg-${variant}">${total} ${total === 1 ? 'user' : 'users'}</span>`;
                },
                exportAs: (value) => `${Number(value) || 0} ${Number(value) === 1 ? 'user' : 'users'}`,
            },
            {
                title: 'Status',
                field: 'status',
                width: 90,
                responsive: 2,
                hozAlign: 'center',
                formatter: (cell) => {
                    const entry = STATUS[cell.getValue()] ?? STATUS[0];
                    return `<span class="badge rounded-pill text-bg-${entry.variant}">${entry.label}</span>`;
                },
                exportAs: TableUtils.exporters.badge(STATUS),
            },
            {
                title: 'Actions',
                headerSort: false,
                download: false,
                columnToggle: false,
                responsive: 0,
                width: 150,
                hozAlign: 'center',
                formatter: (cell) => {
                    const permission = cell.getRow().getData();
                    const id = Number(permission.id);
                    const active = permission.status === 1;
                    return `<div class="btn-group btn-group-sm">
                        <a href="${baseUrl}permissions/${id}" class="btn btn-info" data-bs-toggle="tooltip" title="View details" aria-label="View details">
                            <i class="bi bi-eye" aria-hidden="true"></i>
                        </a>
                        <button type="button" class="btn btn-warning btn-edit" data-id="${id}"
                            data-name="${escapeHtml(permission.name)}" data-description="${escapeHtml(permission.description ?? '')}"
                            data-bs-toggle="tooltip" title="Edit" aria-label="Edit">
                            <i class="bi bi-pencil-square" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="btn ${active ? 'btn-danger' : 'btn-success'} btn-toggle-status" data-id="${id}"
                            data-current-status="${active ? 1 : 0}" data-users="${Number(permission.users) || 0}"
                            data-bs-toggle="tooltip" title="${active ? 'Deactivate' : 'Activate'}" aria-label="${active ? 'Deactivate' : 'Activate'}">
                            <i class="bi ${active ? 'bi-x-lg' : 'bi-check-lg'}" aria-hidden="true"></i>
                        </button>
                    </div>`;
                },
            },
        ],
    });

    document.querySelectorAll('#permissionsTop [data-export]').forEach((button) => {
        button.addEventListener('click', () => {
            TableUtils.exportTable(table, button.dataset.export, {
                filename: 'permissions_system',
                title: 'Permissions',
                sheetName: 'Permissions',
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
            ToastUtils.error('Error', 'Cannot deactivate the permission because there are users assigned to it.');
            return;
        }

        const action = currentStatus === 1 ? 'deactivate' : 'activate';
        const actionCapitalized = action.charAt(0).toUpperCase() + action.slice(1);

        AlertUtils.confirm(
            `${actionCapitalized} this permission?`,
            `The permission will be ${action}d.`,
            () => {
                ToastUtils.loadingWithMinTime(`${actionCapitalized}ing permission...`, async () => {
                    const { data } = await Http.post(`${baseUrl}permissions/toggle-status`, { id, current_status: currentStatus });
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
