/**
 * profile-user.js — Logic for the authenticated user's profile page
 */

// ─── Image preview ───────────────────────────────────────────────────────────

document.getElementById('image').addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;

    if (file.size > 2 * 1024 * 1024) {
        ToastUtils.error('File too large', 'The file must not exceed 2 MB.');
        e.target.value = '';
        return;
    }

    const allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowed.includes(file.type)) {
        ToastUtils.error('Invalid format', 'Only JPG, PNG, GIF, and WEBP are allowed.');
        e.target.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = ev => {
        document.getElementById('preview-image').src = ev.target.result;
        document.getElementById('sidebar-avatar').src = ev.target.result;
    };
    reader.readAsDataURL(file);

    const label = e.target.nextElementSibling;
    if (label) label.textContent = file.name;
});

// ─── Change password (AJAX) ──────────────────────────────────────────────────

document.getElementById('formChangePassword').addEventListener('submit', function (e) {
    e.preventDefault();

    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (newPassword !== confirmPassword) {
        ToastUtils.error('Error', 'Passwords do not match.');
        return;
    }

    const btn = document.getElementById('btnChangePassword');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating password...';

    const form = this;
    ToastUtils.loadingWithMinTime('Updating password...', () => {
        fetch(`${baseUrl}users/change-password`, {
            method: 'POST',
            body: new FormData(form)
        })
            .then(r => r.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-key mr-1"></i> Change password';
                Swal.close();

                if (data.success) {
                    ToastUtils.success('Password updated', 'The session will be closed.').then(() => {
                        // El logout es POST con CSRF; el token se regeneró al cambiar la contraseña.
                        const logoutForm = document.createElement('form');
                        logoutForm.method = 'POST';
                        logoutForm.action = `${baseUrl}logout`;
                        const tokenInput = document.createElement('input');
                        tokenInput.type = 'hidden';
                        tokenInput.name = 'csrf_token';
                        tokenInput.value = data.csrf_token;
                        logoutForm.appendChild(tokenInput);
                        document.body.appendChild(logoutForm);
                        logoutForm.submit();
                    });
                } else {
                    ToastUtils.error('Error', data.message);
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-key mr-1"></i> Change password';
                Swal.close();
                ToastUtils.error('Error', 'An error occurred while processing the request.');
            });
    }, 800);
});

// ─── Update profile info (form submit) ──────────────────────────────────────

document.querySelector('form[action$="profile"]').addEventListener('submit', function (e) {
    e.preventDefault();
    const btn = this.querySelector('[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    const form = this;
    ToastUtils.loadingWithMinTime('Updating profile...', () => {
        form.submit();
    }, 800);
});
