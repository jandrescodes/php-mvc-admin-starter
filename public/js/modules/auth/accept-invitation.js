/**
 * Aceptar invitación: validación nativa con FormValidator y envío normal del
 * formulario con aviso de carga.
 */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelector('.login-box')?.classList.add('login-animation');

    FormValidator.init('#accept-invitation-form', {
        loadingText: 'Activating...',
        fields: {
            password: {
                messages: {
                    required: 'Password is required.',
                    minlength: 'Password must be at least 8 characters.',
                },
            },
            confirm_password: {
                equalTo: '#password',
                messages: {
                    required: 'Please confirm your password.',
                    minlength: 'Password must be at least 8 characters.',
                    equalTo: 'Passwords do not match.',
                },
            },
        },
        submit: async (form) => {
            ToastUtils.loadingWithMinTime('Activating account...', () => form.submit(), 800);
            // Sin resolver: el botón sigue en «Activating...» y no se reenvía mientras el navegador navega.
            await new Promise(() => {});
        },
    });
});
