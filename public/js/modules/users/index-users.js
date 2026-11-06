/**
 * index-users.js - User list page management
 *
 * Tabla Tabulator de usuarios (búsqueda, filtros por rol y estado,
 * columnas y reportes), cambio de estado con confirmación y reenvío de invitación.
 *
 * @package ProyectoBase
 * @subpackage JavaScript\Modules\Users
 * @author jandrescodes
 * @version 1.0
 */

document.addEventListener('DOMContentLoaded', () => {
    const STATUS = {
        1: { label: 'Active', variant: 'success' },
        0: { label: 'Inactive', variant: 'danger' },
        2: { label: 'Pending', variant: 'warning' },
    };

    const table = TableUtils.create('#tableUsers', {
        searchInput: '#usersSearch',
        columnMenu: '#usersColumnMenu',
        pageSizes: [5, 10, 25, 50, 100],
        pageSize: 5,
        pageSizeSelect: '#usersPageSize',
        info: '#usersInfo',
        pager: '#usersPager',
        toolbar: '#usersTop, #usersBottom',
        initialSort: [{ column: 'id', dir: 'desc' }],
        columns: [
            { title: '#', field: 'id', width: 60, hozAlign: 'center', responsive: 3, counter: true },
            { title: 'Name', field: 'name', minWidth: 120, responsive: 0, sorter: 'string' },
            { title: 'Document', field: 'document', minWidth: 110, responsive: 5 },
            { title: 'Email', field: 'email', minWidth: 160, responsive: 6, sorter: 'string' },
            { title: 'Role', field: 'role', minWidth: 90, responsive: 4, sorter: 'string' },
            {
                title: 'Status',
                field: 'status',
                width: 95,
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
                    const user = cell.getRow().getData();
                    const id = Number(user.id);
                    const name = escapeHtml(user.name);
                    const pending = user.status === 2;
                    const active = user.status === 1;
                    // Editar, cambiar el estado y reenviar solo se ofrecen si el servidor las aceptaría (`manage`).
                    let edit = '';
                    let status = '';
                    if (user.manage) {
                        edit = `<a href="${baseUrl}users/${id}/edit" class="btn btn-warning" data-bs-toggle="tooltip" title="Edit user" aria-label="Edit user ${name}">
                            <i class="bi bi-pencil-square" aria-hidden="true"></i>
                        </a>`;
                        if (pending) {
                            status = `<button type="button" class="btn btn-secondary btn-resend-invitation" data-id="${id}" data-name="${name}"
                                data-bs-toggle="tooltip" title="Resend invitation" aria-label="Resend invitation to ${name}">
                                <i class="bi bi-send" aria-hidden="true"></i>
                            </button>`;
                        } else if (!user.self) {
                            status = `<button type="button" class="btn ${active ? 'btn-danger' : 'btn-success'} btn-toggle-status" data-id="${id}"
                                data-status="${user.status}" data-name="${name}"
                                data-bs-toggle="tooltip" title="${active ? 'Deactivate user' : 'Activate user'}" aria-label="${active ? 'Deactivate' : 'Activate'} user ${name}">
                                <i class="bi ${active ? 'bi-person-slash' : 'bi-person-check'}" aria-hidden="true"></i>
                            </button>`;
                        }
                    }
                    return `<div class="btn-group btn-group-sm">
                        <a href="${baseUrl}users/${id}" class="btn btn-info" data-bs-toggle="tooltip" title="View user" aria-label="View user ${name}">
                            <i class="bi bi-eye" aria-hidden="true"></i>
                        </a>
                        ${edit}
                        ${status}
                    </div>`;
                },
            },
        ],
    });

    TableUtils.bindSelectFilter(table, '#usersRoleFilter', 'role');
    TableUtils.bindSelectFilter(table, '#usersStatusFilter', 'status');

    document.querySelectorAll('#usersTop [data-export]').forEach((button) => {
        button.addEventListener('click', () => {
            TableUtils.exportTable(table, button.dataset.export, {
                filename: 'users_system',
                title: 'Users',
                sheetName: 'Users',
            });
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const FAIL = 'A communication error occurred with the server.';

    /** POST con carga mínima de 800 ms; recarga si el servidor acepta, avisa si no. */
    const run = (loadingText, path, payload) => {
        ToastUtils.loadingWithMinTime(loadingText, async () => {
            const { data } = await Http.post(`${baseUrl}${path}`, payload);
            if (data?.success) {
                location.reload();
                return;
            }
            Swal.close();
            ToastUtils.error('Error', data?.message ?? FAIL);
        }, 800);
    };

    const toggleStatus = (button) => {
        const { id, name } = button.dataset;
        const currentStatus = Number(button.dataset.status);
        const deactivating = currentStatus === 1;

        AlertUtils.confirm(
            deactivating ? `Deactivate ${name}?` : `Activate ${name}?`,
            deactivating ? 'The user will not be able to access the system.' : 'The user will be able to access the system again.',
            () => run(deactivating ? 'Deactivating user...' : 'Activating user...', 'users/toggle-status', { id, current_status: currentStatus }),
            {
                confirmText: deactivating ? 'Yes, deactivate' : 'Yes, activate',
                confirmColor: deactivating ? SwalTheme.DANGER : '#146c43',
                cancelColor: '#6c757d',
                cancelText: 'Cancel',
            }
        );
    };

    const resendInvitation = (button) => {
        const { id, name } = button.dataset;

        AlertUtils.confirm(
            `Resend invitation to ${name}?`,
            'The previous invitation link will be invalidated.',
            () => run('Sending invitation...', `users/${Number(id)}/resend-invitation`, {}),
            { confirmText: 'Yes, resend', confirmColor: '#5c636a', cancelText: 'Cancel', cancelColor: '#6c757d' }
        );
    };

    document.addEventListener('click', (event) => {
        const toggle = event.target.closest('.btn-toggle-status');
        if (toggle) return toggleStatus(toggle);
        const resend = event.target.closest('.btn-resend-invitation');
        if (resend) resendInvitation(resend);
    });
});
