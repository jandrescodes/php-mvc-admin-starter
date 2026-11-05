/**
 * Cliente HTTP sin jQuery para las acciones AJAX.
 *
 * Envía siempre X-Requested-With: csrfCheck() y ErrorHandler lo usan para
 * responder JSON en vez de HTML. El token CSRF vigente vive en la meta
 * `csrf-token`; se actualiza cuando una respuesta trae `csrf_token`, porque
 * el servidor lo rota tras cada envío exitoso y en rutas sensibles.
 */
const Http = (() => {
    const tokenMeta = () => document.querySelector('meta[name="csrf-token"]');

    const getToken = () => tokenMeta()?.content ?? '';

    const setToken = (token) => {
        if (typeof token !== 'string' || token === '') return;
        const meta = tokenMeta();
        if (meta) meta.content = token;
        document.querySelectorAll('input[name="csrf_token"]').forEach((input) => {
            input.value = token;
        });
    };

    const buildBody = (data) => {
        const body = new URLSearchParams();
        const source = data instanceof FormData ? data.entries() : Object.entries(data ?? {});
        for (const [key, value] of source) {
            if (value === undefined || value === null) continue;
            if (Array.isArray(value)) {
                value.forEach((item) => body.append(`${key}[]`, item));
            } else {
                body.append(key, value);
            }
        }
        if (!body.has('csrf_token')) body.set('csrf_token', getToken());
        return body;
    };

    /**
     * Resuelve siempre con { ok, status, data }; `data` es el JSON (o un
     * objeto { success:false, message } si la respuesta no es JSON o la red
     * falla). Nunca lanza, salvo AbortError para que el llamador lo distinga.
     */
    const request = async (url, { method = 'GET', data, signal } = {}) => {
        const verb = method.toUpperCase();
        const init = {
            method: verb,
            credentials: 'same-origin',
            signal,
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
        };
        let target = url;

        if (verb === 'GET') {
            const query = new URLSearchParams(data instanceof FormData ? data : (data ?? {})).toString();
            if (query) target += (url.includes('?') ? '&' : '?') + query;
        } else {
            init.body = buildBody(data);
        }

        let response;
        try {
            response = await fetch(target, init);
        } catch (error) {
            if (error.name === 'AbortError') throw error;
            return { ok: false, status: 0, data: { success: false, message: 'Error de conexión. Inténtalo de nuevo.' } };
        }

        let payload;
        try {
            payload = await response.json();
        } catch {
            payload = { success: false, message: `Respuesta inesperada del servidor (${response.status}).` };
        }

        if (payload && typeof payload === 'object') setToken(payload.csrf_token);

        return { ok: response.ok, status: response.status, data: payload };
    };

    return {
        request,
        get: (url, data, options = {}) => request(url, { ...options, method: 'GET', data }),
        post: (url, data, options = {}) => request(url, { ...options, method: 'POST', data }),
        getToken,
        setToken,
    };
})();
