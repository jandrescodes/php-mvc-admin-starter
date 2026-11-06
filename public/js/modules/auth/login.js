/**
 * Inicio de sesión: validación nativa con FormValidator y envío normal del
 * formulario (el servidor valida y redirige) con aviso de carga.
 */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelector('.login-box')?.classList.add('login-animation');

    FormValidator.init('#login-form', {
        loadingText: 'Signing in...',
        fields: {
            identifier: {
                messages: {
                    required: 'Please enter your email or document number',
                    minlength: 'Identifier must be at least 3 characters',
                },
            },
            password: {
                messages: {
                    required: 'Please enter your password',
                    minlength: 'Password must be at least 6 characters',
                },
            },
        },
        submit: async (form) => {
            ToastUtils.loadingWithMinTime('Signing in...', () => form.submit(), 1000);
            // Sin resolver: el botón sigue en «Signing in...» y no se reenvía mientras el navegador navega.
            await new Promise(() => {});
        },
    });
});
