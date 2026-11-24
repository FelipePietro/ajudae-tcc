import { requestJson } from '../core/http.js';

const types = {
    acesso: 'Acesso aos dados',
    correcao: 'Correção de dados',
    exclusao: 'Exclusão de dados',
    informacao: 'Informação sobre o tratamento',
    portabilidade: 'Portabilidade',
};

const statuses = {
    pendente: 'Recebida',
    em_analise: 'Em análise',
    concluida: 'Concluída',
    recusada: 'Recusada',
};

const date = new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeZone: 'America/Sao_Paulo' });

export function initializePrivacyRequests() {
    const root = document.querySelector('[data-identity-privacy]');
    if (!root) return;

    const form = root.querySelector('[data-identity-privacy-form]');
    const submit = form.querySelector('[type=submit]');
    const feedback = root.querySelector('[data-identity-privacy-feedback]');
    const filter = root.querySelector('[data-identity-privacy-filter]');
    const list = root.querySelector('[data-identity-privacy-list]');
    const status = root.querySelector('[data-identity-privacy-status]');
    const template = root.querySelector('[data-identity-privacy-template]');
    const pages = root.querySelector('[data-identity-privacy-pages]');
    const pageLabel = root.querySelector('[data-identity-privacy-page]');
    const previous = root.querySelector('[data-identity-privacy-previous]');
    const next = root.querySelector('[data-identity-privacy-next]');
    let page = 1;
    let pending;

    function show(element, value) {
        element.textContent = value;
        element.hidden = !value;
    }

    function render(request) {
        const card = template.content.firstElementChild.cloneNode(true);
        card.querySelector('h3').textContent = `Protocolo ${request.id} · ${types[request.type] ?? 'Solicitação'}`;
        const state = statuses[request.status] ?? 'Situação indisponível';
        card.querySelector('[data-privacy-case-status]').textContent = request.created_at
            ? `${state} · Registrada em ${date.format(new Date(request.created_at))}`
            : state;
        show(card.querySelector('[data-privacy-case-description]'), request.description ?? '');
        const response = card.querySelector('[data-privacy-case-response]');
        response.hidden = !request.response;
        if (request.responded_at) response.querySelector('h4').textContent = `Resposta em ${date.format(new Date(request.responded_at))}`;
        response.querySelector('p').textContent = request.response ?? '';
        return card;
    }

    async function load(focus = false) {
        pending?.abort();
        const controller = new AbortController();
        pending = controller;
        const query = new URLSearchParams({ page });
        if (filter.value) query.set('status', filter.value);
        list.setAttribute('aria-busy', 'true');
        list.replaceChildren();
        pages.hidden = true;
        show(status, 'Carregando solicitações…');

        try {
            const { data } = await requestJson(`${root.dataset.identityPrivacy}?${query}`, { signal: controller.signal });
            if (controller.signal.aborted) return;
            page = data.page;
            pages.hidden = data.pages <= 1;
            previous.disabled = page <= 1;
            next.disabled = page >= data.pages;
            pageLabel.textContent = `Página ${page} de ${data.pages}`;
            list.replaceChildren(...data.items.map(render));
            show(status, data.items.length === 0 ? 'Nenhuma solicitação encontrada.' : '');
            if (focus) (list.querySelector('h3') ?? status).focus();
        } catch (error) {
            if (!controller.signal.aborted) show(status, error.message);
        } finally {
            if (pending === controller) {
                list.removeAttribute('aria-busy');
                pending = undefined;
            }
        }
    }

    function validation(errors = {}) {
        let first;
        for (const field of form.querySelectorAll('[data-privacy-field-error]')) {
            const name = field.dataset.privacyFieldError;
            const control = form.elements[name];
            const message = errors[name]?.[0] ?? '';
            show(field, message);
            if (message) {
                control.setAttribute('aria-invalid', 'true');
                first ??= control;
            } else {
                control.removeAttribute('aria-invalid');
            }
        }
        first?.focus();
    }

    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (submit.disabled) return;
        submit.disabled = true;
        validation();
        show(feedback, 'Registrando solicitação…');
        try {
            const { data, message } = await requestJson(root.dataset.privacyStore, {
                method: 'POST',
                body: new FormData(form),
                csrfToken: root.dataset.csrfToken,
            });
            form.reset();
            show(feedback, `${message} Protocolo ${data.id}.`);
            filter.value = '';
            page = 1;
            await load();
        } catch (error) {
            show(feedback, error.message);
            validation(error.errors);
        } finally {
            submit.disabled = false;
        }
    });

    filter.addEventListener('change', () => {
        page = 1;
        load();
    });
    previous.addEventListener('click', () => {
        page -= 1;
        load(true);
    });
    next.addEventListener('click', () => {
        page += 1;
        load(true);
    });
    submit.disabled = false;
    load();
}
