// Los layouts publican base-url y csrf-token en <meta> porque no se permite <script> en línea; http.js lee el token vigente de esa meta.
const baseUrl = document.querySelector('meta[name="base-url"]')?.content ?? '';
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// Scroll del menú lateral; en móvil se deja el nativo para no interferir con el táctil.
document.addEventListener('DOMContentLoaded', () => {
    const wrapper = document.querySelector('.sidebar-wrapper');
    const overlay = globalThis.OverlayScrollbarsGlobal?.OverlayScrollbars;
    if (wrapper && overlay && window.innerWidth > 992) {
        overlay(wrapper, { scrollbars: { theme: 'os-theme-light', autoHide: 'leave', clickScroll: true } });
    }
});

/** Helpers globales sin jQuery que cargan los layouts de administración y de autenticación. */

/**
 * Formats a date into a readable string (MM/DD/YYYY)
 *
 * @param {string|Date} date - Date to format
 * @returns {string} Formatted date
 */
function formatDate(date) {
    if (!date) return '';

    const d = new Date(date);
    if (isNaN(d.getTime())) return '';

    return `${String(d.getMonth() + 1).padStart(2, '0')}/${String(d.getDate()).padStart(2, '0')}/${d.getFullYear()}`;
}

/**
 * Auto-initializes password visibility toggle buttons. Any button with
 * [data-password-toggle="#fieldSelector"] shows/hides the target password
 * field and keeps its icon and aria-pressed/aria-label state in sync —
 * no per-module wiring needed.
 */
document.addEventListener('click', (event) => {
    const btn = event.target.closest('[data-password-toggle]');
    if (!btn) return;

    const field = document.querySelector(btn.dataset.passwordToggle);
    if (!field) return;

    const reveal = field.type === 'password';
    field.type = reveal ? 'text' : 'password';

    const icon = btn.querySelector('i');
    if (icon) {
        icon.classList.toggle('bi-eye', reveal);
        icon.classList.toggle('bi-eye-slash', !reveal);
    }
    btn.setAttribute('aria-pressed', String(reveal));
    btn.setAttribute('aria-label', reveal ? 'Hide password' : 'Show password');
});

/**
 * Formats a time string into a readable string (HH:MM)
 *
 * @param {string} time - Time to format
 * @returns {string} Formatted time
 */
function formatTime(time) {
    if (!time) return '';

    if (time.length <= 5) return time;

    if (time.includes('T')) {
        const d = new Date(time);
        if (isNaN(d.getTime())) return '';
        return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
    }

    return time.substring(0, 5);
}

/**
 * Formats a number as currency (USD)
 *
 * @param {number} amount   - Amount to format
 * @param {number} decimals - Number of decimal places (optional)
 * @returns {string} Formatted amount
 */
function formatCurrency(amount, decimals = 2) {
    return '$ ' + parseFloat(amount).toFixed(decimals).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}
