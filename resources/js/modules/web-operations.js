import { requestJson } from '../core/http.js';

const dateTime = new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short' });
let nextFieldId = 0;

export function localDate(value) {
    const date = new Date(value);
    return value && Number.isFinite(date.getTime()) ? dateTime.format(date) : '—';
}

export function element(tag, value, className) {
    const node = document.createElement(tag);
    if (value != null) node.textContent = value;
    if (className) node.className = className;
    return node;
}

export function card(title, lines = []) {
    const article = element('article', null, 'admin-card account-card grid gap-2 min-w-0 break-words');
    article.append(element('h3', title, 'font-semibold'));
    for (const [label, value] of lines) {
        if (value != null && value !== '') article.append(element('p', `${label} · ${value}`));
    }
    return article;
}

export function privateDownload(article, url, label) {
    const destination = new URL(url, window.location.href);
    if (destination.origin !== window.location.origin || destination.username || destination.password) return;
    const link = element('a', label, 'underline');
    link.href = destination.href;
    article.append(link);
}

export function lazyDetails(label, load) {
    const details = element('details', null, 'my-2');
    const summary = element('summary', label, 'cursor-pointer font-medium');
    const content = element('div', null, 'mt-3 grid gap-3');
    details.append(summary, content);
    details.addEventListener('toggle', () => {
        if (details.open && !content.hasChildNodes()) load(content);
    });
    return details;
}

export function controls(root) {
    const call = (url, options) => requestJson(url, { ...options, csrfToken: root.dataset.csrf });
    const button = label => {
        const node = root.querySelector('[data-operations-button]').content.firstElementChild.cloneNode(true);
        node.textContent = label;
        return node;
    };

    let submitting = false;

    function form({ label, url, method, fields = [], body = {}, complete }) {
        const node = root.querySelector('[data-operations-form]').content.firstElementChild.cloneNode(true);
        const submit = node.querySelector('button');
        const feedback = node.querySelector('[data-operation-feedback]');
        const inputs = new Map();
        submit.textContent = label;
        for (const { name, label: title, options, value, type = 'text', attributes = {} } of fields) {
            const field = element('label', null, 'grid gap-1');
            const input = element(options ? 'select' : type === 'textarea' ? 'textarea' : 'input');
            input.className = 'cfg-input w-full border rounded px-2 py-1';
            input.name = name;
            if (options) {
                for (const [key, description] of Object.entries(options)) input.add(new Option(description, key));
            } else if (type !== 'textarea') {
                input.type = type;
            }
            for (const [key, setting] of Object.entries(attributes)) input.setAttribute(key, setting);
            if (value != null) input.value = value;
            const error = element('small', null, 'text-red-800');
            const errorId = `operation-${++nextFieldId}`;
            error.id = errorId;
            input.setAttribute('aria-describedby', errorId);
            field.append(element('span', title), input, error);
            node.querySelector('[data-operation-fields]').append(field);
            inputs.set(name, { input, error, type });
        }
        node.addEventListener('submit', async event => {
            event.preventDefault();
            if (submit.disabled) return;
            if (submitting) {
                feedback.textContent = 'Aguarde a operação em andamento.';
                return;
            }
            submitting = true;
            const payload = { ...body };
            for (const [name, { input, error, type }] of inputs) {
                error.textContent = '';
                input.removeAttribute('aria-invalid');
                const value = input.value.trim();
                if (type === 'number') payload[name] = value === '' ? null : Number(value);
                else if (type === 'boolean') payload[name] = value === 'true';
                else payload[name] = value || null;
            }
            submit.disabled = true;
            feedback.textContent = 'Enviando…';
            try {
                const result = await call(typeof url === 'function' ? url(payload) : url, {
                    method, body: Object.keys(payload).length ? payload : undefined,
                });
                feedback.textContent = result?.message ?? 'Operação concluída.';
                if (complete) await complete(feedback.textContent, result?.data);
            } catch (error) {
                feedback.textContent = error.message;
                let firstInvalid;
                for (const [name, field] of inputs) {
                    if (!error.errors?.[name]?.length) continue;
                    field.error.textContent = error.errors[name][0];
                    field.input.setAttribute('aria-invalid', 'true');
                    firstInvalid ??= field.input;
                }
                (firstInvalid ?? feedback).focus();
            } finally {
                submit.disabled = false;
                submitting = false;
            }
        });
        return node;
    }

    function paginator(container, { url, render, query = () => ({}), select = data => data, arrayPageSize, empty = 'Nenhum registro encontrado.' }) {
        const status = element('p');
        status.setAttribute('role', 'status');
        status.tabIndex = -1;
        const list = element('div', null, 'grid gap-3');
        const navigation = element('div', null, 'flex flex-wrap gap-2');
        const previous = button('Anterior');
        const next = button('Próxima');
        const retry = button('Tentar novamente');
        navigation.append(previous, next, retry);
        container.append(status, list, navigation);
        let page = 1;
        let request;

        async function load(target = page, message = '') {
            request?.abort();
            const current = new AbortController();
            request = current;
            status.textContent = message || 'Carregando…';
            list.setAttribute('aria-busy', 'true');
            previous.hidden = next.hidden = retry.hidden = true;
            list.replaceChildren();
            try {
                const address = new URL(url, window.location.href);
                address.searchParams.set('page', target);
                if (arrayPageSize) address.searchParams.set('per_page', arrayPageSize);
                for (const [key, value] of Object.entries(query())) if (value) address.searchParams.set(key, value);
                const { data } = await call(address, { signal: current.signal });
                if (request !== current) return;
                const projection = select(data);
                const result = Array.isArray(projection)
                    ? { data: projection, next_page_url: projection.length === arrayPageSize }
                    : projection;
                if (result.data.length === 0 && target > 1 && message) return load(target - 1, message);
                page = target;
                for (const row of result.data) list.append(render(row, refresh, data));
                const summary = result.data.length ? `Página ${target}.` : empty;
                status.textContent = [message, summary].filter(Boolean).join(' ');
                previous.hidden = target === 1;
                next.hidden = !(result.next_page_url || target < result.last_page);
            } catch (error) {
                if (request !== current || error.name === 'AbortError') return;
                status.textContent = [message, error.message].filter(Boolean).join(' ');
                retry.hidden = false;
                retry.onclick = () => navigate(target);
            } finally {
                if (request === current) list.removeAttribute('aria-busy');
            }
        }

        async function navigate(target, message) {
            await load(target, message);
            status.focus();
        }

        function refresh(message) {
            return navigate(page, message);
        }

        previous.addEventListener('click', () => navigate(page - 1));
        next.addEventListener('click', () => navigate(page + 1));
        load();
        return message => navigate(1, message);
    }

    async function read(container, url, render, missing) {
        container.replaceChildren(element('p', 'Carregando…'));
        try {
            const { data } = await call(url);
            container.replaceChildren();
            render(data);
        } catch (error) {
            container.replaceChildren();
            if (missing && error.status === 404) {
                missing();
                return;
            }
            const retry = button('Tentar novamente');
            retry.addEventListener('click', () => read(container, url, render, missing));
            container.append(element('p', error.message), retry);
        }
    }

    return { form, paginator, read };
}
