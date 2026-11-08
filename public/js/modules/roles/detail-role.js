/**
 * Detalle de rol: asignación de permisos (seleccionar/deseleccionar todos y
 * guardado por AJAX con confirmación).
 */
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formSyncPermissions');
    if (!form) return; // rol de sistema: no se dibuja el formulario

    const boxes = () => form.querySelectorAll('.perm-checkbox');
    const saveBtn = document.getElementById('btnSavePermissions');

    document.getElementById('select-all').addEventListener('click', () => boxes().forEach((box) => { box.checked = true; }));
    document.getElementById('deselect-all').addEventListener('click', () => boxes().forEach((box) => { box.checked = false; }));

    form.addEventListener('submit', (event) => {
        event.preventDefault();

        AlertUtils.confirm(
            'Save permissions',
            'Are you sure you want to update the permissions for this role? All users with this role will be affected.',
            () => {
                const data = {
                    role_id: form.elements.role_id.value,
                    permissions: [...boxes()].filter((box) => box.checked).map((box) => box.value),
                };
                saveBtn.disabled = true;

                ToastUtils.loadingWithMinTime('Saving permissions...', async () => {
                    const { data: res } = await Http.post(`${baseUrl}roles/sync-permissions`, data);
                    if (res.success) {
                        location.reload();
                        return;
                    }
                    Swal.close();
                    ToastUtils.error('Error', res.message || 'Error saving permissions.');
                    saveBtn.disabled = false;
                }, 800);
            },
            {
                confirmText: 'Save',
                cancelText: 'Cancel',
                confirmColor: SwalTheme.PRIMARY,
                cancelColor: '#6c757d',
            }
        );
    });
});
