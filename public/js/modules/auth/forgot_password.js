/**
 * Olvido de contraseña: validación nativa con FormValidator y envío normal
 * del formulario con aviso de carga.
 */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelector('.login-box')?.classList.add('login-animation');

    FormValidator.init('#forgot-password-form', {
        loadingText: 'Sending...',
        fields: {
            email: {
                messages: {
                    required: 'Please enter your email address',
                    email: 'Please enter a valid email address',
                },
            },
        },
        submit: async (form) => {
            ToastUtils.loadingWithMinTime('Sending reset link...', () => form.submit(), 800);
            // Sin resolver: el botón sigue en «Sending...» y no se reenvía mientras el navegador navega.
            await new Promise(() => {});
        },
    });
});
