/**
 * SweetAlert2 Utilities
 * Funciones helper para notificaciones Toast y Alerts usando SweetAlert2
 */

// ============================================
// ToastUtils - Utilidades para Toast (notificaciones pequeñas)
// ============================================

// SweetAlert2 interpreta title/html como HTML: los textos con datos de usuario deben escaparse.
function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
}

const ToastUtils = {
    // Configuración base del toast
    baseConfig: {
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    },

    /**
     * Toast de éxito
     * @param {string} title - Título del toast
     * @param {string} text - Texto adicional (opcional)
     * @param {number} timer - Tiempo en ms (default: 3000)
     * @returns {Promise}
     */
    success: function (title, text = '', timer = 3000) {
        const Toast = Swal.mixin(this.baseConfig);
        return Toast.fire({
            icon: 'success',
            title: escapeHtml(title),
            text: text,
            timer: timer
        });
    },

    /**
     * Toast de error
     * @param {string} title - Título del toast
     * @param {string} text - Texto adicional (opcional)
     * @param {number} timer - Tiempo en ms (default: 4000)
     * @returns {Promise}
     */
    error: function (title, text = '', timer = 4000) {
        const Toast = Swal.mixin(this.baseConfig);
        return Toast.fire({
            icon: 'error',
            title: escapeHtml(title),
            text: text,
            timer: timer
        });
    },

    /**
     * Toast de advertencia
     * @param {string} title - Título del toast
     * @param {string} text - Texto adicional (opcional)
     * @param {number} timer - Tiempo en ms (default: 4000)
     * @returns {Promise}
     */
    warning: function (title, text = '', timer = 4000) {
        const Toast = Swal.mixin(this.baseConfig);
        return Toast.fire({
            icon: 'warning',
            title: escapeHtml(title),
            text: text,
            timer: timer
        });
    },

    /**
     * Toast de información
     * @param {string} title - Título del toast
     * @param {string} text - Texto adicional (opcional)
     * @param {number} timer - Tiempo en ms (default: 3000)
     * @returns {Promise}
     */
    info: function (title, text = '', timer = 3000) {
        const Toast = Swal.mixin(this.baseConfig);
        return Toast.fire({
            icon: 'info',
            title: escapeHtml(title),
            text: text,
            timer: timer
        });
    },

    /**
     * Toast de carga (loading)
     * @param {string} title - Título del loading
     * @returns {Promise}
     */
    loading: function (title = 'Cargando...') {
        return Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        }).fire({
            title: escapeHtml(title)
        });
    },

    /**
     * Toast de carga con tiempo mínimo garantizado
     * Útil para evitar flashes de loading muy rápidos
     * @param {string} mensaje - Mensaje del loading
     * @param {function} accion - Función a ejecutar después del tiempo mínimo
     * @param {number} tiempoMinimo - Tiempo mínimo en ms (default: 1000ms)
     * @returns {Promise}
     */
    loadingWithMinTime: function (mensaje = 'Cargando...', accion, tiempoMinimo = 1000) {
        const loadingToast = this.loading(mensaje);
        const tiempoInicio = Date.now();

        const ejecutarAccion = () => {
            const tiempoTranscurrido = Date.now() - tiempoInicio;

            if (tiempoTranscurrido < tiempoMinimo) {
                setTimeout(() => {
                    accion(loadingToast);
                }, tiempoMinimo - tiempoTranscurrido);
            } else {
                accion(loadingToast);
            }
        };

        setTimeout(ejecutarAccion, 0);
        return loadingToast;
    }
};

// ============================================
// AlertUtils - Utilidades para Alerts (modales completos)
// ============================================

const AlertUtils = {
    // Configuración base para alerts (solo para modales)
    baseConfig: {
        showConfirmButton: true,
        confirmButtonText: 'Aceptar',
        confirmButtonColor: '#3085d6'
    },

    /**
     * Alert de éxito (Delegado a ToastUtils)
     * @param {string} title
     * @param {string} text
     * @param {number} timer
     */
    success: function (title, text = '', timer = 3000) {
        return ToastUtils.success(title, text, timer);
    },

    /**
     * Alert de error (Delegado a ToastUtils)
     * @param {string} title
     * @param {string} text
     */
    error: function (title, text = '') {
        return ToastUtils.error(title, text);
    },

    /**
     * Alert de advertencia (Delegado a ToastUtils)
     * @param {string} title
     * @param {string} text
     */
    warning: function (title, text = '') {
        return ToastUtils.warning(title, text);
    },

    /**
     * Alert de información (Delegado a ToastUtils)
     * @param {string} title
     * @param {string} text
     */
    info: function (title, text = '') {
        return ToastUtils.info(title, text);
    },

    /**
     * Alert de confirmación
     * @param {string} title - Título del alert
     * @param {string} text - Texto del alert
     * @param {function} onConfirm - Callback si confirma
     * @param {object} options - Opciones adicionales
     * @returns {Promise}
     */
    confirm: function (title, text, onConfirm, options = {}) {
        return Swal.fire({
            title: escapeHtml(title),
            ...(options.html ? { html: options.html } : { text: text }),
            icon: options.icon || 'warning',
            showCancelButton: true,
            confirmButtonColor: options.confirmColor || '#d33',
            cancelButtonColor: options.cancelColor || '#3085d6',
            confirmButtonText: options.confirmText || 'Sí, continuar',
            cancelButtonText: options.cancelText || 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed && typeof onConfirm === 'function') {
                onConfirm();
            }
            return result;
        });
    },

    /**
     * Alert de confirmación para eliminación
     * @param {string} url - URL a la que redirigir si confirma
     * @param {string} title - Título del alert
     * @param {string} text - Texto del alert
     * @returns {boolean}
     */
    confirmDelete: function (url, title = '¿Está seguro de eliminar este registro?', text = 'Esta acción no se puede deshacer') {
        Swal.fire({
            title: escapeHtml(title),
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                ToastUtils.loadingWithMinTime('Eliminando...', () => {
                    window.location.href = url;
                }, 800);
            }
        });
        return false;
    },

    /**
     * Alert de bienvenida personalizado
     * @param {string} userName - Nombre del usuario
     */
    welcome: function (userName) {
        const greeting = this._getGreeting();
        const safeName = escapeHtml(userName);

        Swal.fire({
            title: `${greeting}, ${safeName}!`,
            html: `<p>Es un placer verte de nuevo</p>
                   <p class="text-muted mb-0"><small>${new Date().toLocaleDateString('es-ES', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            })}</small></p>`,
            icon: 'success',
            timer: 5000,
            timerProgressBar: true,
            showConfirmButton: false,
            position: 'center',
            backdrop: true,
            customClass: {
                popup: 'animated fadeInDown'
            }
        });
    },

    /**
     * Modal de imagen ampliada
     * @param {string} src - URL de la imagen
     * @param {string} alt - Texto alternativo
     * @param {string} confirmButtonText - Texto del botón (default: 'Close')
     * @returns {Promise}
     */
    image: function (src, alt = '', confirmButtonText = 'Close') {
        return Swal.fire({
            imageUrl: src,
            imageAlt: alt,
            confirmButtonText: confirmButtonText,
            customClass: { image: 'img-fluid' }
        });
    },

    /**
     * Obtiene el saludo según la hora del día
     * @private
     * @returns {string}
     */
    _getGreeting: function () {
        const hour = new Date().getHours();

        if (hour >= 5 && hour < 12) {
            return 'Buenos días';
        } else if (hour >= 12 && hour < 19) {
            return 'Buenas tardes';
        } else {
            return 'Buenas noches';
        }
    }
};

// ============================================
// Funciones legacy (para compatibilidad)
// ============================================

/**
 * @deprecated Usar ToastUtils.success() en su lugar
 */
function showToast(icon, title) {
    const Toast = Swal.mixin(ToastUtils.baseConfig);
    Toast.fire({ icon: icon, title: escapeHtml(title) });
}

/**
 * @deprecated Usar AlertUtils.confirmDelete() en su lugar
 */
function confirmDelete(url, title, text) {
    return AlertUtils.confirmDelete(url, title, text);
}

/**
 * @deprecated Usar AlertUtils.success() en su lugar
 */
function showSuccess(message) {
    AlertUtils.success('¡Éxito!', message, 3000);
}

/**
 * @deprecated Usar AlertUtils.error() en su lugar
 */
function showError(message) {
    AlertUtils.error('Error', message);
}

/**
 * @deprecated Usar AlertUtils.warning() en su lugar
 */
function showWarning(message) {
    AlertUtils.warning('Advertencia', message);
}

/**
 * @deprecated Usar AlertUtils.info() en su lugar
 */
function showInfo(message) {
    AlertUtils.info('Información', message);
}

/**
 * @deprecated Usar AlertUtils.welcome() en su lugar
 */
function showWelcomeMessage(userName) {
    AlertUtils.welcome(userName);
}
