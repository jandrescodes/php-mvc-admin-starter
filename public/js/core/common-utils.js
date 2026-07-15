/**
 * common-utils.js - Basic JavaScript utilities for the system
 *
 * Contains helper functions for initializing common components
 * in the project.
 *
 * @package ProyectoBase
 * @subpackage JavaScript\Core
 * @author jandrescodes
 * @version 1.0
 */

/**
 * Wrapper for ComponentUtils.initSelect2Single — initializes Select2 on a
 * specific selector with custom options. Use this when extra options are
 * needed (e.g. dropdownParent for modals); for general page initialization
 * ComponentUtils.initAll() in ui-components.js handles it automatically.
 *
 * @param {string} selector - Selector for elements to initialize (optional)
 * @param {object} options  - Additional Select2 options (optional)
 */
function initializeSelect2(selector = '.select2', options = {}) {
    ComponentUtils.initSelect2(options, selector);
}

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

/**
 * Shows a toast notification using SweetAlert2
 *
 * @param {string} message - Message to display
 * @param {string} icon    - Icon type (success, error, warning, info)
 * @param {number} timer   - Duration in milliseconds (optional)
 */
function showToast(message, icon = 'success', timer = 3000) {
    if (typeof Swal !== 'undefined') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: timer,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        Toast.fire({
            icon: icon,
            title: message
        });
    } else {
        alert(message);
    }
}

/**
 * Performs a simplified AJAX request
 *
 * @param {string}   url             - Request URL
 * @param {string}   method          - HTTP method (GET, POST)
 * @param {object}   data            - Data to send
 * @param {function} successCallback - Success callback
 * @param {function} errorCallback   - Error callback (optional)
 */
function ajaxRequest(url, method = 'GET', data = {}, successCallback, errorCallback = null) {
    $.ajax({
        url: url,
        type: method,
        data: data,
        dataType: 'json',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function (response) {
            if (successCallback && typeof successCallback === 'function') {
                successCallback(response);
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX error:', error);

            if (errorCallback && typeof errorCallback === 'function') {
                errorCallback(xhr, status, error);
            } else if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'A communication error occurred with the server.'
                });
            }
        }
    });
}
