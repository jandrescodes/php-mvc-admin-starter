/**
 * Perfil propio: vista previa de la foto, datos de contacto (POST normal con
 * redirección) y cambio de contraseña por AJAX con Http, que mantiene la sesión
 * abierta (el servidor sincroniza la huella de la contraseña).
 */
document.addEventListener('DOMContentLoaded', () => {
    const field = (id) => document.getElementById(id);
    const MAX_IMAGE_BYTES = 5 * 1024 * 1024;
    const IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    // ---- Foto
    field('image').addEventListener('change', (event) => {
        const input = event.target;
        const file = input.files?.[0];
        if (!file) return;

        if (file.size > MAX_IMAGE_BYTES) {
            ToastUtils.error('File too large', 'The file must not exceed 5 MB.');
            input.value = '';
            return;
        }
        if (!IMAGE_TYPES.includes(file.type)) {
            ToastUtils.error('Invalid format', 'Only JPG, PNG, GIF, and WEBP are allowed.');
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = (loaded) => {
            field('preview-image').src = loaded.target.result;
            field('sidebar-avatar').src = loaded.target.result;
        };
        reader.readAsDataURL(file);
    });

    // ---- Datos de contacto: POST normal con redirección y aviso por sesión.
    FormValidator.init('#formProfile', {
        loadingText: 'Saving...',
        fields: {
            phone: { messages: { maxlength: 'Phone cannot exceed 20 characters.' } },
        },
        submit: async (form) => {
            ToastUtils.loadingWithMinTime('Updating profile...', () => form.submit(), 800);
            // Sin resolver: el botón sigue en «Saving...» y no se reenvía mientras el navegador navega.
            await new Promise(() => {});
        },
    });

    // ---- Contraseña: AJAX; la sesión sigue abierta.
    FormValidator.init('#formChangePassword', {
        loadingText: 'Updating password...',
        fields: {
            current_password: { messages: { required: 'Enter your current password.' } },
            new_password: { messages: { required: 'Enter a new password.', minlength: 'Password must be at least 8 characters.' } },
            confirm_password: {
                equalTo: '#new_password',
                messages: { required: 'Confirm the new password.', minlength: 'Password must be at least 8 characters.', equalTo: 'Passwords do not match.' },
            },
        },
        submit: async (form, formData) => {
            const response = await new Promise((resolve) => {
                ToastUtils.loadingWithMinTime('Updating password...', async () => {
                    resolve(await Http.post(`${baseUrl}users/change-password`, formData));
                }, 800);
            });
            Swal.close();

            if (response.ok && response.data?.success) {
                form.reset();
                FormValidator.reset(form);
                ToastUtils.success('Password updated', 'Your session stays open; other devices were signed out.');
                return;
            }
            ToastUtils.error('Error', response.data?.message ?? 'An error occurred while processing the request.');
        },
    });
});
