/**
 * theme-toggle.js
 * Gestiona el toggle de dark/light mode.
 * Persiste la preferencia en localStorage (clave: 'theme').
 * Fallback: prefers-color-scheme del sistema (aplicado en <head> para evitar FOUC).
 */
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('theme-toggle');
    if (!toggle) return;

    const icon = toggle.querySelector('i');

    /** Aplica el tema visualmente sin tocar localStorage. */
    function applyTheme(isDark) {
        document.documentElement.classList.toggle('dark-mode', isDark);
        toggle.setAttribute('aria-pressed', String(isDark));
        if (icon) {
            icon.classList.toggle('fa-moon', !isDark);
            icon.classList.toggle('fa-sun',  isDark);
        }
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    /** Anima el icono con spin 360° al cambiar de tema (respeta prefers-reduced-motion). */
    function animateIcon(callback) {
        if (!icon || prefersReducedMotion.matches) { callback(); return; }

        icon.style.transition = 'transform 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.2s ease';
        icon.style.transform  = 'rotate(360deg) scale(0.5)';
        icon.style.opacity    = '0';
        setTimeout(function () {
            icon.style.transition = 'none';
            icon.style.transform  = 'rotate(0deg) scale(1)';
            callback();
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    icon.style.transition = 'transform 0.3s cubic-bezier(0.25, 1, 0.5, 1), opacity 0.2s ease';
                    icon.style.opacity    = '1';
                });
            });
        }, 250);
    }

    // Sincronizar icono con el estado actual (ya aplicado por el script de <head>)
    applyTheme(document.documentElement.classList.contains('dark-mode'));

    // Toggle al hacer clic
    toggle.addEventListener('click', function (e) {
        e.preventDefault();
        const isDark   = document.documentElement.classList.contains('dark-mode');
        const newTheme = isDark ? 'light' : 'dark';
        animateIcon(function () {
            try { localStorage.setItem('theme', newTheme); } catch (err) {}
            applyTheme(!isDark);
        });
    });

    // Reaccionar a cambios del sistema en tiempo real (solo si no hay preferencia manual)
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
        try {
            if (!localStorage.getItem('theme')) applyTheme(e.matches);
        } catch (err) {}
    });
});
