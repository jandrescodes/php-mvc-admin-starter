/**
 * Modal de rol (crear/editar): abre en modo crear o editar, valida con
 * FormValidator (unicidad del nombre en el servidor), envía por AJAX y
 * sincroniza los permisos elegidos en un segundo POST.
 */
document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('modalRole');
    const form = document.getElementById('formRole');
    if (!modalEl || !form) return;

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    const header = document.getElementById('modalRoleHeader');
    const title = document.getElementById('modalRoleLabel');
    const saveBtn = document.getElementById('btnSaveRole');
    const field = (id) => document.getElementById(id);
    const permSelect = field('rolePermissions');
    const permHint = field('rolePermissionsHint');
    const permissions = ComponentUtils.initSelects(permSelect)[0] ?? permSelect.tomselect;

    const MODES = {
        create: { title: 'Create New Role', header: 'text-bg-primary', button: 'btn-primary', label: 'Save', icon: 'bi-floppy' },
        edit: { title: 'Edit Role', header: 'text-bg-warning', button: 'btn-warning', label: 'Update', icon: 'bi-floppy' },
    };

    const setMode = (mode, role = {}) => {
        const cfg = MODES[mode];
        field('roleAction').value = mode;
        field('roleId').value = role.id ?? '';
        field('roleName').value = role.name ?? '';
        field('roleDescription').value = role.description ?? '';
        title.textContent = cfg.title;
        header.className = `modal-header ${cfg.header}`;
        saveBtn.className = `btn ${cfg.button}`;
        saveBtn.innerHTML = `<i class="bi ${cfg.icon}" aria-hidden="true"></i> ${cfg.label}`;
        // El texto original que FormValidator restaura tras el envío.
        delete saveBtn.dataset.originalHtml;

        // El rol de sistema tiene acceso total: sus permisos no se asignan uno a uno.
        const system = Boolean(role.system);
        permissions.clear(true);
        permissions.setValue(system ? [] : (role.permissions ?? []), true);
        system ? permissions.disable() : permissions.enable();
        permHint.textContent = system
            ? 'System role: has full access, no individual permissions needed.'
            : 'You can only grant permissions you hold yourself.';
    };

    const syncPermissions = (roleId, ids) => Http.post(`${baseUrl}roles/sync-permissions`, {
        role_id: roleId,
        permissions: ids,
    });

    FormValidator.init(form, {
        loadingText: 'Processing...',
        fields: {
            name: {
                messages: { required: 'Role name is required.', maxlength: 'Role name cannot exceed 60 characters.' },
                remote: {
                    url: `${baseUrl}roles/check-name`,
                    data: () => ({ name: field('roleName').value, role_id: field('roleId').value }),
                },
            },
            description: { messages: { maxlength: 'Description cannot exceed 255 characters.' } },
        },
        submit: async (formEl, formData) => {
            // Se leen antes de cerrar: al ocultarse el modal se limpia el formulario.
            const isCreate = field('roleAction').value === 'create';
            const editId = field('roleId').value;
            const selected = [...permissions.getValue()]; // copia: Tom Select devuelve su arreglo interno, que clear() vacía
            const syncable = !permissions.isDisabled && (!isCreate || selected.length > 0);
            modal.hide();
            const response = await new Promise((resolve) => {
                ToastUtils.loadingWithMinTime(isCreate ? 'Creating role...' : 'Updating role...', async () => {
                    resolve(await Http.post(`${baseUrl}roles/${isCreate ? 'create' : 'update'}`, formData));
                }, 800);
            });
            if (response.ok && response.data?.success) {
                if (syncable) {
                    const sync = await syncPermissions(isCreate ? response.data.role.id : editId, selected);
                    if (!(sync.ok && sync.data?.success)) {
                        Swal.close();
                        ToastUtils.error('Error', `Role saved, but permissions were not updated: ${sync.data?.message ?? 'communication error.'}`);
                        setTimeout(() => location.reload(), 3000);
                        return;
                    }
                }
                location.reload();
                return;
            }
            Swal.close();
            ToastUtils.error('Error', response.data?.message ?? 'A communication error occurred with the server.');
        },
    });

    document.getElementById('btnNewRole')?.addEventListener('click', () => {
        setMode('create');
        modal.show();
    });

    document.addEventListener('click', (event) => {
        const btn = event.target.closest('.btn-edit');
        if (!btn) return;
        setMode('edit', {
            id: btn.dataset.id,
            name: btn.dataset.name,
            description: btn.dataset.description,
            system: btn.dataset.system === '1',
            permissions: JSON.parse(btn.dataset.permissions || '[]').map(String),
        });
        modal.show();
    });

    modalEl.addEventListener('hidden.bs.modal', () => {
        form.reset();
        FormValidator.reset(form);
        permissions.clear(true);
    });
});
