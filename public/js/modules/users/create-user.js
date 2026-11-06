/**
 * Alta de usuario: validación nativa con FormValidator (unicidad de correo y
 * documento en el servidor), vista previa del perfil e imagen, y atajos de
 * permisos. El envío es el POST normal del formulario.
 */
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formUser');
    if (!form) return;

    const field = (id) => document.getElementById(id);
    const MAX_IMAGE_BYTES = 5 * 1024 * 1024;
    const IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    const BADGES = {
        active: { text: 'Active', variant: 'success' },
        inactive: { text: 'Inactive', variant: 'danger' },
        pending: { text: 'Pending', variant: 'warning' },
    };
    const setBadge = (key) => {
        const badge = field('profile-preview-badge');
        badge.className = `badge rounded-pill text-bg-${BADGES[key].variant}`;
        badge.textContent = BADGES[key].text;
    };
    const statusBadge = () => (field('status').value === '1' ? 'active' : 'inactive');

    // ---- Invitación: la persona define su contraseña, así que no se piden ni el estado.
    const passwordFields = [field('password'), field('confirm_password')];
    field('invite_toggle').addEventListener('change', (event) => {
        const invite = event.target.checked;
        field('invite').value = invite ? '1' : '0';
        field('password-fields').hidden = invite;
        field('status-field').hidden = invite;
        passwordFields.forEach((input) => {
            input.required = !invite;
            if (invite) {
                input.value = '';
                FormValidator.reset(form);
            }
        });
        setBadge(invite ? 'pending' : statusBadge());
    });

    // ---- Validación
    FormValidator.init(form, {
        loadingText: 'Saving...',
        // El alta es un POST normal con redirección y aviso por sesión: se muestra la carga hasta que el navegador navega.
        submit: async (formEl) => {
            ToastUtils.loadingWithMinTime(field('invite').value === '1' ? 'Sending invitation...' : 'Creating user...', () => formEl.submit(), 800);
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
                    data: () => ({ document_type: field('document_type').value, document_number: field('document_number').value, user_id: '' }),
                },
            },
            email: {
                messages: { required: 'Email is required.', email: 'Enter a valid email address.' },
                remote: {
                    url: `${baseUrl}users/check-email`,
                    data: () => ({ email: field('email').value, user_id: '' }),
                },
            },
            role_id: { messages: { required: 'Select a role.' } },
            password: { messages: { required: 'Password is required.', minlength: 'Password must be at least 8 characters.' } },
            confirm_password: { equalTo: '#password', messages: { required: 'Please confirm the password.', equalTo: 'Passwords do not match.' } },
        },
    });

    // ---- Formato del documento según el tipo; el tipo también cambia la unicidad.
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

    // ---- Vista previa del perfil
    ['name', 'first_surname', 'second_surname'].forEach((id) => {
        field(id).addEventListener('input', () => {
            const fullName = ['name', 'first_surname', 'second_surname'].map((part) => field(part).value.trim()).filter(Boolean).join(' ');
            field('profile-preview-name').textContent = fullName || 'New User';
        });
    });
    field('status').addEventListener('change', () => setBadge(statusBadge()));

    // ---- Permisos
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
