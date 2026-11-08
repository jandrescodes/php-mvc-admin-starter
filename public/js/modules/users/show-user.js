/**
 * Detalle de usuario: recuerda la última pestaña, amplía la imagen de perfil
 * y ofrece reenviar la invitación y desbloquear el acceso con confirmación.
 */
document.addEventListener('DOMContentLoaded', () => {
    const TAB_KEY = 'lastUserDetailTab';
    const FAIL = 'A communication error occurred with the server.';

    // localStorage puede no estar disponible (ventana privada, datos bloqueados): la pestaña recordada es solo una comodidad.
    const readTab = () => { try { return localStorage.getItem(TAB_KEY); } catch { return null; } };
    const saveTab = (target) => { try { localStorage.setItem(TAB_KEY, target); } catch { /* sin almacenamiento */ } };

    const triggers = [...document.querySelectorAll('#user-tabs [data-bs-toggle="tab"]')];
    const last = readTab();
    const lastTrigger = triggers.find((trigger) => trigger.dataset.bsTarget === last);
    if (lastTrigger) bootstrap.Tab.getOrCreateInstance(lastTrigger).show();

    triggers.forEach((trigger) => {
        trigger.addEventListener('shown.bs.tab', (event) => saveTab(event.target.dataset.bsTarget));
    });

    document.getElementById('btn-profile-image')?.addEventListener('click', (event) => {
        AlertUtils.image(event.currentTarget.dataset.src, 'Profile image');
    });

    /** POST con carga mínima de 800 ms; recarga si el servidor acepta, avisa si no. */
    const run = (loadingText, path) => {
        ToastUtils.loadingWithMinTime(loadingText, async () => {
            const { data } = await Http.post(`${baseUrl}${path}`, {});
            if (data?.success) {
                location.reload();
                return;
            }
            Swal.close();
            ToastUtils.error('Error', data?.message ?? FAIL);
        }, 800);
    };

    document.getElementById('btn-resend-invitation')?.addEventListener('click', (event) => {
        const { userId, name } = event.currentTarget.dataset;
        AlertUtils.confirm(
            `Resend invitation to ${name}?`,
            'The previous invitation link will be invalidated.',
            () => run('Sending invitation...', `users/${Number(userId)}/resend-invitation`),
            { confirmText: 'Yes, resend', confirmColor: '#5c636a', cancelText: 'Cancel', cancelColor: '#6c757d' }
        );
    });

    document.getElementById('btn-unlock-login')?.addEventListener('click', (event) => {
        const { userId } = event.currentTarget.dataset;
        AlertUtils.confirm(
            'Unlock login?',
            'The user will be able to log in immediately.',
            () => run('Unlocking user...', `users/${Number(userId)}/unlock-login`),
            { confirmText: 'Yes, unlock', confirmColor: SwalTheme.DANGER, cancelText: 'Cancel', cancelColor: '#6c757d' }
        );
    });
});
