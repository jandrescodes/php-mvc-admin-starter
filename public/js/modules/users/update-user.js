/**
 * Edición de usuario: validación nativa con FormValidator (unicidad de correo
 * y documento excluyendo a la propia persona), cambio de contraseña opcional,
 * vista previa del perfil e imagen, y atajos de permisos. El envío es el POST
 * normal del formulario.
 */
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formUser');
    if (!form) return;

    const field = (id) => document.getElementById(id);
    const userId = form.elements.user_id.value;
    const MAX_IMAGE_BYTES = 5 * 1024 * 1024;
    const IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    FormValidator.init(form, {
        loadingText: 'Saving...',
        // La actualización es un POST normal con redirección y aviso por sesión: se muestra la carga hasta que el navegador navega.
        submit: async (formEl) => {
            ToastUtils.loadingWithMinTime('Updating user...', () => formEl.submit(), 800);
            // Sin resolver: así el botón sigue en «Saving...» y no se puede reenviar mientras se navega.
            await new Promise(() => {});
        },
        fields: {
            name: { messages: { required: 'Name is required.' } },
            first_surname: { messages: { required: 'First surname is required.' } },
            document_type: { messages: { required: 'Select a document type.' } },
            document_number: {
                messages: {
                    required: 'Document number is required.',
                    pattern: 'The number does not match the document type.',
                },
                remote: {
                    url: `${baseUrl}users/check-document`,
                    data: () => ({ document_type: field('document_type').value, document_number: field('document_number').value, user_id: userId }),
                },
            },
            email: {
                messages: { required: 'Email is required.', email: 'Enter a valid email address.' },
                remote: {
                    url: `${baseUrl}users/check-email`,
                    data: () => ({ email: field('email').value, user_id: userId }),
                },
            },
            role_id: { messages: { required: 'Select a role.' } },
            password: { messages: { minlength: 'Password must be at least 8 characters.' } },
            confirm_password: { equalTo: '#password', messages: { equalTo: 'Passwords do not match.' } },
        },
    });

    // ---- Formato del documento según el tipo; el tipo también cambia la unicidad.
    // Sin tocar el tipo se conserva el maxlength de la vista, para no rechazar documentos ya guardados.
    const DOCUMENT_RULES = {
        DNI: { maxlength: 8, pattern: '[0-9]{8}', placeholder: 'Enter 8 digits' },
        RUC: { maxlength: 11, pattern: '[0-9]{11}', placeholder: 'Enter 11 digits' },
        Passport: { maxlength: 12, pattern: null, placeholder: 'Enter passport number' },
    };
    field('document_type').addEventListener('change', (event) => {
        const rule = DOCUMENT_RULES[event.target.value] ?? { maxlength: null, pattern: null, placeholder: 'Enter document number' };
        const number = field('document_number');
        rule.maxlength ? number.setAttribute('maxlength', rule.maxlength) : number.removeAttribute('maxlength');
        rule.pattern ? number.setAttribute('pattern', rule.pattern) : number.removeAttribute('pattern');
        number.placeholder = rule.placeholder;
        if (number.value !== '') number.dispatchEvent(new Event('focusout', { bubbles: true }));
    });

    // ---- Imagen
    field('image').addEventListener('change', (event) => {
        const input = event.target;
        const file = input.files?.[0];
        if (!file) return;
        if (!IMAGE_TYPES.includes(file.type)) {
            ToastUtils.error('Invalid image', 'Only images are allowed (jpg, png, gif, webp).');
            input.value = '';
            return;
        }
        if (file.size > MAX_IMAGE_BYTES) {
            ToastUtils.error('Image too large', 'The maximum size is 5 MB.');
            input.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = (loaded) => {
            field('preview-image').src = loaded.target.result;
            field('preview-container').classList.remove('d-none');
            field('profile-preview-img').src = loaded.target.result;
        };
        reader.readAsDataURL(file);
    });

    // ---- Vista previa del nombre
    ['name', 'first_surname', 'second_surname'].forEach((id) => {
        field(id).addEventListener('input', () => {
            const fullName = ['name', 'first_surname', 'second_surname'].map((part) => field(part).value.trim()).filter(Boolean).join(' ');
            field('profile-preview-name').textContent = fullName || 'User';
        });
    });

    // ---- Permisos. El campo oculto `permissions_submitted` viaja siempre: sin él, desmarcar todo
    // enviaría una lista vacía indistinguible de un cliente que omitió el campo.
    const setAllPermissions = (checked) => form.querySelectorAll('input[name="permissions[]"]').forEach((box) => { box.checked = checked; });
    field('select-all').addEventListener('click', () => {
        setAllPermissions(true);
        ToastUtils.success('All permissions selected');
    });
    field('deselect-all').addEventListener('click', () => {
        setAllPermissions(false);
        ToastUtils.info('All permissions deselected');
    });
});
