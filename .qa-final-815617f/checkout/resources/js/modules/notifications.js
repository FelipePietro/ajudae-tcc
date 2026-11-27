import { requestJson } from '../core/http.js';

export function initializeNotifications() {
    const root = document.querySelector('[data-identity-inbox]');
    if (!root) return;

    const form = root.querySelector('[data-notification-filters]');
    const list = root.querySelector('[data-notification-list]');
    const template = root.querySelector('[data-notification-template]');
    const message = root.querySelector('[data-notification-message]');
    const feedback = root.querySelector('[data-notification-feedback]');
    const summary = root.querySelector('[data-notification-summary]');
    const all = root.querySelector('[data-notification-all]');
    const pages = root.querySelector('[data-notification-pagination]');
    const previous = root.querySelector('[data-notification-previous]');
    const next = root.querySelector('[data-notification-next]');
    const pageLabel = root.querySelector('[data-notification-page]');
    const dateFormat = new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short', timeZone: 'America/Sao_Paulo' });
    let requestedPage = 1;
    let outstanding;
    let debounce;
    let writing = false;

    function status(element, text) {
        element.textContent = text;
        element.hidden = !text;
    }

    function params() {
        const query = new URLSearchParams({ page: requestedPage });
        const search = form.elements.busca.value.trim();
        if (search) query.set('busca', search);
        if (form.elements.leitura.value !== 'todas') query.set('leitura', form.elements.leitura.value);
        if (form.elements.tipo.value !== 'todos') query.set('tipo', form.elements.tipo.value);
        return query;
    }

    function errorMessage(error) {
        switch (error.status) {
            case 401:
            case 419:
                return 'Sua sessão terminou. Atualize a página para entrar novamente.';
            case 403:
                return 'Sua conta não tem acesso a esta operação.';
            case 422:
                return 'Confira os filtros selecionados e tente novamente.';
            default:
                return 'Não foi possível atualizar os avisos. Tente novamente.';
        }
    }

    function renderItem(item) {
        const card = template.content.firstElementChild.cloneNode(true);
        card.dataset.noticeId = item.id;
        card.dataset.unread = String(!item.read);
        const category = card.querySelector('[data-notification-type]');
        category.textContent = item.category;
        category.hidden = item.title === item.category;
        card.querySelector('h2').textContent = item.title;
        card.querySelector('[data-notification-body]').textContent = item.message;
        card.querySelector('[data-notification-status]').textContent = item.read ? 'Lida' : 'Não lida';
        card.querySelector('[data-notification-origin]').hidden = true;
        const time = card.querySelector('time');
        if (item.created_at) {
            time.dateTime = item.created_at;
            time.textContent = dateFormat.format(new Date(item.created_at));
        }
        const read = card.querySelector('[data-notification-read]');
        read.disabled = item.read;
        read.setAttribute('aria-label', `Marcar como lida: ${item.title}`);
        read.addEventListener('click', () => mark(item.read_url, read, item.id));
        const open = card.querySelector('[data-notification-open]');
        open.disabled = !item.details_url;
        open.setAttribute('aria-label', `Ver detalhes: ${item.title}`);
        card.querySelector('[data-notification-unavailable]').hidden = Boolean(item.details_url);
        open.addEventListener('click', () => {
            if (item.read) {
                location.assign(item.details_url);
            } else {
                mark(item.read_url, open, item.id, item.details_url);
            }
        });
        return card;
    }

    async function load({ focusId, focusPage = false } = {}) {
        clearTimeout(debounce);
        outstanding?.abort();
        const controller = new AbortController();
        outstanding = controller;
        list.setAttribute('aria-busy', 'true');
        list.replaceChildren();
        all.disabled = previous.disabled = next.disabled = true;
        status(message, 'Carregando notificações…');

        try {
            const { data } = await requestJson(`${root.dataset.identityInbox}?${params()}`, { signal: controller.signal });
            if (controller.signal.aborted) return;

            requestedPage = data.page;
            const type = form.elements.tipo;
            const selected = type.value;
            type.replaceChildren(new Option('Todos os tipos', 'todos'));
            for (const [value, label] of Object.entries(data.types)) type.add(new Option(label, value));
            type.value = Object.hasOwn(data.types, selected) ? selected : 'todos';
            summary.textContent = `${data.unread} ${data.unread === 1 ? 'não lida' : 'não lidas'} · ${data.total} ${data.total === 1 ? 'aviso' : 'avisos'}`;
            all.disabled = data.unread === 0;
            pages.hidden = data.pages <= 1;
            previous.disabled = data.page <= 1;
            next.disabled = data.page >= data.pages;
            pageLabel.textContent = `Página ${data.page} de ${data.pages}`;
            const empty = data.total === 0 ? 'Você ainda não tem notificações.' : 'Nenhum aviso encontrado com esses filtros.';
            status(message, data.items.length === 0 ? empty : '');
            list.replaceChildren(...data.items.map(renderItem));
            if (focusId !== undefined) {
                const current = Array.from(list.children).find(card => card.dataset.noticeId === String(focusId));
                (current?.querySelector('h2') ?? (message.hidden ? summary : message)).focus();
            } else if (focusPage) {
                (list.querySelector('h2') ?? message).focus();
            }
        } catch (error) {
            if (controller.signal.aborted || error.name === 'AbortError') return;
            pages.hidden = true;
            summary.textContent = 'Consulta indisponível';
            status(message, errorMessage(error));
        } finally {
            if (outstanding === controller) {
                list.removeAttribute('aria-busy');
                outstanding = undefined;
            }
        }
    }

    async function mark(url, control, focusId, destination) {
        if (writing) return;
        writing = true;
        control.disabled = true;
        status(feedback, '');
        try {
            const response = await requestJson(url, { method: 'PATCH', csrfToken: root.dataset.csrfToken });
            if (destination) {
                location.assign(destination);
                return;
            }
            await load({ focusId });
            status(feedback, response.message);
        } catch (error) {
            status(feedback, errorMessage(error));
            control.disabled = false;
            control.focus();
        } finally {
            writing = false;
        }
    }

    summary.tabIndex = -1;
    form.addEventListener('submit', event => {
        event.preventDefault();
        requestedPage = 1;
        load();
    });
    form.addEventListener('input', () => {
        requestedPage = 1;
        clearTimeout(debounce);
        outstanding?.abort();
        debounce = setTimeout(load, 200);
    });
    root.querySelector('[data-notification-reset]').addEventListener('click', () => {
        form.reset();
        requestedPage = 1;
        load();
    });
    previous.addEventListener('click', () => {
        requestedPage -= 1;
        load({ focusPage: true });
    });
    next.addEventListener('click', () => {
        requestedPage += 1;
        load({ focusPage: true });
    });
    all.addEventListener('click', () => mark(root.dataset.readAll, all));
    load();
}
