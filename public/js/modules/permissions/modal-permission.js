/**
 * Modal de permiso (crear/editar), compartido por el listado y el detalle:
 * abre en modo crear o editar, valida con FormValidator (unicidad del nombre
 * en el servidor) y envía por AJAX.
 */
document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('modalPermission');
    const form = document.getElementById('formPermission');
    if (!modalEl || !form) return;

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    const header = document.getElementById('modalPermissionHeader');
    const title = document.getElementById('modalPermissionLabel');
    const saveBtn = document.getElementById('btnSavePermission');
    const field = (id) => document.getElementById(id);

    const MODES = {
        create: { title: 'Create New Permission', header: 'text-bg-primary', button: 'btn-primary', label: 'Save' },
        edit: { title: 'Edit Permission', header: 'text-bg-warning', button: 'btn-warning', label: 'Update' },
    };

    const setMode = (mode, permission = {}) => {
        const cfg = MODES[mode];
        field('permissionAction').value = mode;
        field('permissionId').value = permission.id ?? '';
        field('permissionName').value = permission.name ?? '';
        field('permissionDescription').value = permission.description ?? '';
        title.textContent = cfg.title;
        header.className = `modal-header ${cfg.header}`;
        saveBtn.className = `btn ${cfg.button}`;
        saveBtn.innerHTML = `<i class="bi bi-floppy" aria-hidden="true"></i> ${cfg.label}`;
        // El texto original que FormValidator restaura tras el envío.
        delete saveBtn.dataset.originalHtml;
    };

    FormValidator.init(form, {
        loadingText: 'Processing...',
        fields: {
            name: {
                messages: { required: 'Permission name is required.', maxlength: 'Permission name cannot exceed 60 characters.' },
                remote: {
                    url: `${baseUrl}permissions/check-name`,
                    data: () => ({ name: field('permissionName').value, permission_id: field('permissionId').value }),
                },
            },
            description: { messages: { maxlength: 'Description cannot exceed 255 characters.' } },
        },
        submit: async (formEl, formData) => {
            // Se lee antes de cerrar: al ocultarse el modal se limpia el formulario.
            const isCreate = field('permissionAction').value === 'create';
            modal.hide();
            const response = await new Promise((resolve) => {
                ToastUtils.loadingWithMinTime(isCreate ? 'Creating permission...' : 'Updating permission...', async () => {
                    resolve(await Http.post(`${baseUrl}permissions/${isCreate ? 'create' : 'update'}`, formData));
                }, 800);
            });
            if (response.ok && response.data?.success) {
                location.reload();
                return;
            }
            Swal.close();
            ToastUtils.error('Error', response.data?.message ?? 'A communication error occurred with the server.');
        },
    });

    document.getElementById('btnNewPermission')?.addEventListener('click', () => {
        setMode('create');
        modal.show();
    });

    document.addEventListener('click', (event) => {
        const btn = event.target.closest('.btn-edit');
        if (!btn) return;
        setMode('edit', { id: btn.dataset.id, name: btn.dataset.name, description: btn.dataset.description });
        modal.show();
    });

    modalEl.addEventListener('hidden.bs.modal', () => {
        form.reset();
        FormValidator.reset(form);
    });
});
