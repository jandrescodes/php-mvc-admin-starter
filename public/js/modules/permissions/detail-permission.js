/**
 * Detalle de permiso: tabla Tabulator de las personas con el permiso y
 * asignación/revocación desde esta vista. El modal de edición vive en
 * modal-permission.js.
 */
document.addEventListener('DOMContentLoaded', () => {
    const tableEl = document.getElementById('tablePermissionDetail');
    const permissionId = Number(tableEl.dataset.permissionId);

    const table = TableUtils.create('#tablePermissionDetail', {
        searchInput: '#detailSearch',
        columnMenu: '#detailColumnMenu',
        pageSizes: [5, 10, 25, 50, 100],
        pageSize: 10,
        pageSizeSelect: '#detailPageSize',
        info: '#detailInfo',
        pager: '#detailPager',
        toolbar: '#detailTop, #detailBottom',
        placeholder: 'No users have been assigned this permission.',
        initialSort: [{ column: 'name', dir: 'asc' }],
        columns: [
            { title: '#', field: 'id', width: 60, hozAlign: 'center', responsive: 3, counter: true, headerSort: false },
            { title: 'Name', field: 'name', minWidth: 120, responsive: 0, sorter: 'string' },
            { title: 'Email', field: 'email', minWidth: 140, responsive: 4 },
            { title: 'Role', field: 'role', minWidth: 90, responsive: 2 },
            {
                title: 'Actions',
                headerSort: false,
                download: false,
                columnToggle: false,
                responsive: 0,
                width: 110,
                hozAlign: 'center',
                formatter: (cell) => {
                    const user = cell.getRow().getData();
                    return `<div class="btn-group btn-group-sm">
                        <a href="${baseUrl}users/${Number(user.id)}" class="btn btn-info" data-bs-toggle="tooltip" title="View user" aria-label="View user">
                            <i class="bi bi-eye" aria-hidden="true"></i>
                        </a>
                        <button type="button" class="btn btn-danger btn-revoke" data-user-id="${Number(user.id)}"
                            data-name="${escapeHtml(user.shortName)}"
                            data-bs-toggle="tooltip" title="Revoke permission" aria-label="Revoke permission">
                            <i class="bi bi-person-dash" aria-hidden="true"></i>
                        </button>
                    </div>`;
                },
            },
        ],
    });

    document.querySelectorAll('#detailTop [data-export]').forEach((button) => {
        button.addEventListener('click', () => {
            TableUtils.exportTable(table, button.dataset.export, {
                filename: 'permission_users',
                title: 'Users with this permission',
                sheetName: 'Users',
            });
        });
    });

    const post = (path, userId) => Http.post(`${baseUrl}permissions/${path}`, { user_id: userId, permission_id: permissionId });

    const modalEl = document.getElementById('modalAssignUser');
    const assignBtn = document.getElementById('btnConfirmAssign');
    const userSelect = document.getElementById('selectUser');
    const users = userSelect ? (ComponentUtils.initSelects(userSelect)[0] ?? userSelect.tomselect) : null;
    const modal = modalEl ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;

    document.getElementById('btnAssignUser')?.addEventListener('click', () => {
        users.clear(true);
        modal.show();
    });

    assignBtn?.addEventListener('click', () => {
        // Se lee antes de cerrar: al ocultarse el modal se limpia la selección.
        const userId = users.getValue();
        if (!userId) {
            ToastUtils.warning('Select a user', '', 2500);
            return;
        }
        modal.hide();
        ToastUtils.loadingWithMinTime('Assigning user...', async () => {
            const { data } = await post('assign-user', userId);
            if (data?.success) {
                location.reload();
                return;
            }
            Swal.close();
            ToastUtils.error('Error', data?.message ?? 'A communication error occurred with the server.');
        }, 800);
    });

    document.addEventListener('click', (event) => {
        const button = event.target.closest('.btn-revoke');
        if (!button) return;

        AlertUtils.confirm(
            'Revoke permission?',
            null,
            () => {
                button.disabled = true;
                ToastUtils.loadingWithMinTime('Revoking permission...', async () => {
                    const { data } = await post('revoke-user', button.dataset.userId);
                    if (data?.success) {
                        location.reload();
                        return;
                    }
                    Swal.close();
                    ToastUtils.error('Error', data?.message ?? 'A communication error occurred with the server.');
                    button.disabled = false;
                }, 800);
            },
            {
                html: `This permission will be removed from <b>${escapeHtml(button.dataset.name)}</b>.`,
                confirmText: 'Yes, revoke',
                confirmColor: SwalTheme.DANGER,
                cancelColor: '#6c757d',
                cancelText: 'Cancel',
            }
        );
    });
});
