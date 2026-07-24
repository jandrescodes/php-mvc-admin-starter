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

/** References to active Chart instances for destroy-on-theme-change. */
const _charts = {};

document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;

    renderAllCharts();
    watchThemeChanges();
    initAccessMetricsToggle();
});

/**
 * Returns true if dark mode is currently active.
 * @returns {boolean}
 */
function isDarkMode() {
    return document.documentElement.classList.contains('dark-mode');
}

/**
 * Applies Chart.js v2 global defaults based on the active theme.
 * Must be called before rendering any chart.
 */
function applyChartTheme() {
    const dark = isDarkMode();
    const textColor = dark ? '#e9ecef' : '#666';
    const gridColor = dark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)';

    // Chart.js v2 global defaults
    Chart.defaults.global.defaultFontColor = textColor;
    Chart.defaults.global.legend.labels.fontColor = textColor;
    Chart.defaults.scale.gridLines = Chart.defaults.scale.gridLines || {};
    Chart.defaults.scale.gridLines.color = gridColor;
    Chart.defaults.scale.ticks = Chart.defaults.scale.ticks || {};
    Chart.defaults.scale.ticks.fontColor = textColor;
}

/**
 * Reads a --dm-* semantic token from html.dark-mode (public/css/core/dark-mode.css).
 * @param {string} name  token name without the leading '--'
 * @returns {string}      resolved color value
 */
function dmToken(name) {
    return getComputedStyle(document.documentElement).getPropertyValue('--' + name).trim();
}

/**
 * Semantic chart colors sourced from the active theme.
 * Dark mode reads the --dm-* tokens from dark-mode.css; light mode uses
 * the equivalent Bootstrap 4 palette already relied on across the admin UI.
 * @returns {{success:string, danger:string, warning:string, primary:string}}
 */
function getChartColors() {
    if (isDarkMode()) {
        return {
            success: dmToken('dm-success'),
            danger:  dmToken('dm-danger'),
            warning: dmToken('dm-warning'),
            primary: dmToken('dm-primary'),
        };
    }

    return {
        success: '#28a745',
        danger:  '#dc3545',
        warning: '#ffc107',
        primary: '#007bff',
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
 * Observes class changes on <html> and re-renders charts
 * when the dark-mode class is added or removed.
 */
function watchThemeChanges() {
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.attributeName === 'class') {
                renderAllCharts();
            }
        });
    });

    observer.observe(document.documentElement, { attributes: true });
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

    applyState(localStorage.getItem(LS_KEY) === '1', false);

    btn.addEventListener('click', function () {
        const nowVisible = !row.classList.contains('metrics-visible');
        localStorage.setItem(LS_KEY, nowVisible ? '1' : '0');
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
