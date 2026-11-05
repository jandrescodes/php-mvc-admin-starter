/**
 * Validación de formularios con la API nativa del navegador.
 *
 * El formulario recibe `novalidate`; cada campo se valida con `validity` y se
 * marca con `.is-invalid` + `.invalid-feedback` de Bootstrap 5. Reglas propias:
 * `equalTo` (selector de otro campo) y `remote` (unicidad contra el servidor,
 * con debounce y AbortController). Los endpoints de unicidad responden `true`
 * o un texto con el error.
 *
 * Se usa `validity` y nunca `checkValidity()`/`reportValidity()`: estos
 * disparan el evento `invalid`, donde `initAccessibility` de AdminLTE crea su
 * propio `<id>-error`. El mensaje reutiliza ese mismo id para no duplicarlo y
 * los campos llevan `disable-adminlte-validations`.
 *
 * FormValidator.init('#formUser', {
 *   fields: {
 *     email: {
 *       messages: { required: '…', email: '…' },
 *       remote: { url: baseUrl + 'users/check-email', data: () => ({ email: …, user_id: '' }) },
 *     },
 *     confirm_password: { equalTo: '#password', messages: { equalTo: '…' } },
 *   },
 *   submit: async (form, formData) => {},  // opcional; por defecto form.submit()
 *   loadingText: 'Saving…',
 * });
 */
const FormValidator = (() => {
    const REMOTE_DEBOUNCE_MS = 400;
    const NATIVE_RULES = [
        ['valueMissing', 'required'],
        ['typeMismatch', 'email'],
        ['tooShort', 'minlength'],
        ['tooLong', 'maxlength'],
        ['patternMismatch', 'pattern'],
        ['rangeUnderflow', 'min'],
        ['rangeOverflow', 'max'],
    ];

    const state = new WeakMap();

    const feedbackFor = (field) => {
        const id = `${field.id || field.name}-error`;
        let box = document.getElementById(id);
        if (!box) {
            box = document.createElement('div');
            box.id = id;
            box.className = 'invalid-feedback d-block';
            const anchor = field.closest('.input-group') ?? field;
            anchor.insertAdjacentElement('afterend', box);
        }
        return box;
    };

    const show = (field, message) => {
        const box = feedbackFor(field);
        box.textContent = message;
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
        field.setAttribute('aria-invalid', 'true');
        field.setAttribute('aria-describedby', box.id);
    };

    const clear = (field) => {
        document.getElementById(`${field.id || field.name}-error`)?.remove();
        field.classList.remove('is-invalid');
        field.classList.toggle('is-valid', field.value !== '');
        field.removeAttribute('aria-invalid');
        field.removeAttribute('aria-describedby');
    };

    const nativeMessage = (field, config) => {
        const { validity } = field;
        for (const [flag, rule] of NATIVE_RULES) {
            if (validity[flag]) return config.messages?.[rule] ?? field.validationMessage;
        }
        // tooShort/tooLong solo se activan con escritura del usuario, no al asignar `value`.
        if (field.minLength > 0 && field.value !== '' && field.value.length < field.minLength) {
            return config.messages?.minlength ?? field.validationMessage;
        }
        if (field.maxLength >= 0 && field.value.length > field.maxLength) {
            return config.messages?.maxlength ?? field.validationMessage;
        }
        return validity.customError ? field.validationMessage : '';
    };

    const runRemote = async (field, config, ctx) => {
        ctx.controllers.get(field)?.abort();
        const controller = new AbortController();
        ctx.controllers.set(field, controller);
        try {
            const { data } = await Http.post(config.remote.url, config.remote.data?.() ?? {}, {
                signal: controller.signal,
            });
            return data === true ? '' : (typeof data === 'string' ? data : (data?.message ?? config.messages?.remote ?? 'Valor no válido.'));
        } catch (error) {
            if (error.name === 'AbortError') return null;
            return '';
        }
    };

    /** Devuelve true si el campo es válido; null si la comprobación remota fue cancelada. */
    const validateField = async (field, ctx) => {
        const config = ctx.fields[field.name] ?? {};
        field.setCustomValidity('');
        if (!field.willValidate) return true;

        let message = nativeMessage(field, config);

        if (!message && config.equalTo && field.value !== document.querySelector(config.equalTo)?.value) {
            message = config.messages?.equalTo ?? 'Los valores no coinciden.';
        }

        if (!message && config.remote && field.value !== '') {
            const remote = await runRemote(field, config, ctx);
            if (remote === null) return null;
            message = remote;
        }

        if (message) {
            field.setCustomValidity(message);
            show(field, message);
            return false;
        }
        clear(field);
        return true;
    };

    const setLoading = (button, loading, text) => {
        if (!button) return;
        if (loading) {
            button.dataset.originalHtml = button.innerHTML;
            button.disabled = true;
            button.innerHTML = `<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>${text}`;
        } else {
            button.disabled = false;
            button.innerHTML = button.dataset.originalHtml ?? button.innerHTML;
        }
    };

    const init = (target, options = {}) => {
        const form = typeof target === 'string' ? document.querySelector(target) : target;
        if (!form) return null;

        const ctx = { fields: options.fields ?? {}, controllers: new WeakMap() };
        state.set(form, ctx);
        form.noValidate = true;

        const controls = () => [...form.elements].filter((el) => el.name && el.willValidate !== undefined && el.type !== 'submit' && el.type !== 'button');
        controls().forEach((el) => el.classList.add('disable-adminlte-validations'));

        const timers = new WeakMap();
        const revalidateDependents = (field) => {
            controls()
                .filter((el) => ctx.fields[el.name]?.equalTo && document.querySelector(ctx.fields[el.name].equalTo) === field && el.value !== '')
                .forEach((el) => validateField(el, ctx));
        };

        form.addEventListener('focusout', (event) => {
            const field = event.target;
            if (!field.name || !controls().includes(field)) return;
            clearTimeout(timers.get(field));
            validateField(field, ctx);
        });

        form.addEventListener('input', (event) => {
            const field = event.target;
            if (!field.name || !controls().includes(field)) return;
            clearTimeout(timers.get(field));
            if (ctx.fields[field.name]?.remote) {
                timers.set(field, setTimeout(() => validateField(field, ctx), REMOTE_DEBOUNCE_MS));
            } else if (field.classList.contains('is-invalid')) {
                validateField(field, ctx);
            }
            revalidateDependents(field);
        });

        form.addEventListener('change', (event) => {
            const field = event.target;
            if (field.name && controls().includes(field) && field.tagName === 'SELECT') validateField(field, ctx);
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (ctx.submitting) return;
            ctx.submitting = true;

            const results = await Promise.all(controls().map((el) => validateField(el, ctx)));
            if (results.some((ok) => ok === false || ok === null)) {
                form.querySelector('.is-invalid')?.focus();
                ctx.submitting = false;
                return;
            }

            const button = event.submitter ?? form.querySelector('[type="submit"]');
            setLoading(button, true, options.loadingText ?? 'Guardando…');
            try {
                if (options.submit) {
                    await options.submit(form, new FormData(form));
                } else {
                    form.submit();
                    return;
                }
            } finally {
                if (options.submit) setLoading(button, false);
                ctx.submitting = false;
            }
        });

        return { validate: () => Promise.all(controls().map((el) => validateField(el, ctx))).then((r) => r.every(Boolean)) };
    };

    const reset = (target) => {
        const form = typeof target === 'string' ? document.querySelector(target) : target;
        if (!form) return;
        [...form.elements].forEach((el) => {
            if (!el.name) return;
            el.setCustomValidity?.('');
            document.getElementById(`${el.id || el.name}-error`)?.remove();
            el.classList.remove('is-invalid', 'is-valid');
            el.removeAttribute('aria-invalid');
            el.removeAttribute('aria-describedby');
        });
    };

    return { init, reset };
})();
