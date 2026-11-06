/**
 * index-dashboard.js - Dashboard metrics charts
 *
 * Renders Chart.js visualizations reading data from data-* attributes
 * set by PHP on each canvas element. No HTML is generated here.
 * Re-renders automatically when the dark/light theme changes.
 *
 * @package ProyectoBase
 * @subpackage JavaScript\Modules\Dashboard
 * @author jandrescodes
 * @version 1.1
 */

/**
 * index-dashboard.js - Dashboard metrics charts
 *
 * Renders Chart.js 4 visualizations reading data from data-* attributes
 * set by PHP on each canvas element. No HTML is generated here.
 * Colors come from Bootstrap's CSS variables, and charts re-render when the
 * theme switch changes `data-bs-theme` on <html>.
 *
 * @package ProyectoBase
 * @subpackage JavaScript\Modules\Dashboard
 * @author jandrescodes
 * @version 2.0
 */

/** References to active Chart instances for destroy-on-theme-change. */
const _charts = {};

document.addEventListener('DOMContentLoaded', function () {
    initAccessMetricsToggle();

    if (typeof Chart === 'undefined') return;

    renderAllCharts();
    watchThemeChanges();
});

/**
 * Reads a Bootstrap CSS variable (e.g. '--bs-success') as resolved for the active theme.
 * @param {string} name  variable name including the leading '--'
 * @returns {string}
 */
function cssVar(name) {
    return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
}

/**
 * Applies Chart.js 4 global defaults from the theme's own variables, so text
 * and grid lines keep their contrast in light and dark.
 * Must be called before rendering any chart.
 */
function applyChartTheme() {
    Chart.defaults.color = cssVar('--bs-body-color');
    Chart.defaults.borderColor = cssVar('--bs-border-color');
}

/**
 * Normalizes any CSS color (hex, rgb(), percentages…) to '#rrggbb' through a canvas:
 * Bootstrap exposes some variables in forms that are not plain hex.
 * @returns {string}
 */
function toHex(color) {
    const context = document.createElement('canvas').getContext('2d');
    context.fillStyle = color;
    return context.fillStyle;
}

/**
 * Mixes two CSS colors; `amount` is the share (0-1) taken from `b`.
 * @returns {string} '#rrggbb'
 */
function mixColors(a, b, amount) {
    const [ha, hb] = [toHex(a), toHex(b)];
    const channel = (hex, i) => parseInt(hex.replace('#', '').substring(i * 2, i * 2 + 2), 16);
    const mixed = [0, 1, 2].map((i) => Math.round(channel(ha, i) * (1 - amount) + channel(hb, i) * amount));
    return '#' + mixed.map((v) => v.toString(16).padStart(2, '0')).join('');
}

/**
 * Semantic chart colors from Bootstrap's variables for the active theme.
 * El amarillo de Bootstrap sobre fondo claro da ~1.6:1; en tema claro se
 * mezcla con su color de énfasis (marrón) para pasar de 3:1 sin perder el tono.
 * @returns {{success:string, danger:string, warning:string, primary:string}}
 */
function getChartColors() {
    const light = document.documentElement.getAttribute('data-bs-theme') !== 'dark';
    const warning = cssVar('--bs-warning');

    return {
        success: cssVar('--bs-success'),
        danger: cssVar('--bs-danger'),
        warning: light ? mixColors(warning, cssVar('--bs-warning-text-emphasis'), 0.5) : warning,
        primary: cssVar('--bs-primary'),
    };
}

/**
 * Destroys all active chart instances and re-renders them.
 * Called on initial load and on every theme change.
 */
function renderAllCharts() {
    Object.keys(_charts).forEach(function (key) {
        if (_charts[key]) {
            _charts[key].destroy();
            delete _charts[key];
        }
    });

    applyChartTheme();
    initUserStatusChart();
    initTopPermissionsChart();
    initUsersByMonthChart();
}

/**
 * Observes `data-bs-theme` on <html> and re-renders the charts when the
 * theme switch flips it.
 */
function watchThemeChanges() {
    const observer = new MutationObserver(function () {
        renderAllCharts();
    });

    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });
}

/**
 * Donut chart — active vs inactive vs pending users.
 * Reads data-active, data-inactive and data-pending from the canvas element.
 */
function initUserStatusChart() {
    const canvas = document.getElementById('chartUserStatus');
    const fallback = document.getElementById('chartUserStatusFallback');

    if (!canvas || !fallback) return;

    const active = parseInt(canvas.dataset.active, 10) || 0;
    const inactive = parseInt(canvas.dataset.inactive, 10) || 0;
    const pending = parseInt(canvas.dataset.pending, 10) || 0;

    if (active === 0 && inactive === 0 && pending === 0) {
        canvas.style.display = 'none';
        fallback.style.display = 'block';
        return;
    }

    const colors = getChartColors();

    _charts.userStatus = new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Inactive', 'Pending'],
            datasets: [{
                data: [active, inactive, pending],
                backgroundColor: [colors.success, colors.danger, colors.warning],
                borderColor: cssVar('--bs-body-bg'), // separa los sectores con el fondo del tema, no con blanco fijo
                borderWidth: 2,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 700, easing: 'easeInOutQuart' },
            plugins: {
                legend: { position: 'bottom' },
            },
        },
    });
}

/**
 * Bar chart — top 5 permissions by assigned users.
 * Reads data-chart (JSON array) from the canvas element.
 */
function initTopPermissionsChart() {
    const canvas = document.getElementById('chartTopPermissions');
    const fallback = document.getElementById('chartTopPermissionsFallback');

    if (!canvas || !fallback) return;

    const dataset = JSON.parse(canvas.dataset.chart || '[]');

    if (!dataset.length) {
        canvas.style.display = 'none';
        fallback.style.display = 'block';
        return;
    }

    const primary = getChartColors().primary;

    _charts.topPermissions = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: dataset.map(p => p.name),
            datasets: [{
                label: 'Assigned users',
                data: dataset.map(p => p.total_users),
                backgroundColor: hexToRgba(primary, 0.7),
                borderColor: hexToRgba(primary, 1),
                borderWidth: 1,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 700, easing: 'easeInOutQuart' },
            plugins: {
                legend: { display: false },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, precision: 0 },
                },
            },
        },
    });
}

/**
 * Line chart — user registrations per month (last 6 months).
 * Reads data-chart (JSON array) from the canvas element.
 */
function initUsersByMonthChart() {
    const canvas = document.getElementById('chartUsersByMonth');
    const fallback = document.getElementById('chartUsersByMonthFallback');

    if (!canvas || !fallback) return;

    const dataset = JSON.parse(canvas.dataset.chart || '[]');
    const hasData = dataset.some(e => e.total > 0);

    if (!hasData) {
        canvas.style.display = 'none';
        fallback.style.display = 'block';
        return;
    }

    const success = getChartColors().success;

    _charts.usersByMonth = new Chart(canvas, {
        type: 'line',
        data: {
            labels: dataset.map(e => formatYearMonth(e.ym)),
            datasets: [{
                label: 'Registered users',
                data: dataset.map(e => e.total),
                borderColor: hexToRgba(success, 1),
                backgroundColor: hexToRgba(success, 0.1),
                tension: 0.3,
                fill: true,
                pointRadius: 4,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 700, easing: 'easeInOutQuart' },
            plugins: {
                legend: { display: false },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, precision: 0 },
                },
            },
        },
    });
}

/**
 * Toggles the access-metrics row with a slide+fade CSS transition.
 * Persists preference in localStorage under 'dashboard_access_metrics_visible'.
 */
function initAccessMetricsToggle() {
    const row = document.getElementById('rowAccessMetrics');
    const btn = document.getElementById('btnToggleAccessMetrics');
    const label = document.getElementById('labelToggleAccessMetrics');
    const arrow = document.getElementById('arrowToggleAccessMetrics');

    if (!row || !btn) return;

    const LS_KEY = 'dashboard_access_metrics_visible';

    function applyState(visible, animate) {
        label.textContent = visible ? 'Hide access metrics' : 'Show access metrics';
        arrow.classList.toggle('rotated', visible);
        btn.setAttribute('aria-expanded', visible ? 'true' : 'false');

        if (visible) {
            row.classList.add('metrics-visible');
        } else {
            if (!animate) {
                // Suppress transition on initial restore
                row.style.transition = 'none';
                row.classList.remove('metrics-visible');
                // Re-enable transition after next paint
                requestAnimationFrame(() => requestAnimationFrame(() => {
                    row.style.transition = '';
                }));
            } else {
                row.classList.remove('metrics-visible');
            }
        }
    }

    // localStorage puede no estar disponible (ventana privada, datos bloqueados): la preferencia es solo una comodidad.
    const readVisible = () => { try { return localStorage.getItem(LS_KEY) === '1'; } catch { return false; } };
    const saveVisible = (visible) => { try { localStorage.setItem(LS_KEY, visible ? '1' : '0'); } catch { /* sin almacenamiento */ } };

    applyState(readVisible(), false);

    btn.addEventListener('click', function () {
        const nowVisible = !row.classList.contains('metrics-visible');
        saveVisible(nowVisible);
        applyState(nowVisible, true);
    });
}

/**
 * Converts a '#rrggbb' color to an 'rgba(r, g, b, alpha)' string.
 * @param {string} hex    e.g. '#28a745'
 * @param {number} alpha  0-1
 * @returns {string}
 */
function hexToRgba(hex, alpha) {
    const value = hex.replace('#', '');
    const r = parseInt(value.substring(0, 2), 16);
    const g = parseInt(value.substring(2, 4), 16);
    const b = parseInt(value.substring(4, 6), 16);
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

/**
 * Formats 'YYYY-MM' as 'Mon YYYY' using the browser locale.
 * @param {string} ym  e.g. '2025-11'
 * @returns {string}   e.g. 'Nov 2025'
 */
function formatYearMonth(ym) {
    const [year, month] = ym.split('-');
    const date = new Date(parseInt(year, 10), parseInt(month, 10) - 1, 1);
    return date.toLocaleDateString(undefined, { month: 'short', year: 'numeric' });
}
