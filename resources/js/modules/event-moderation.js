import { requestJson } from '../core/http.js';

const dateTime = new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short' });
const modes = { presencial: 'Presencial', online: 'Online', hibrido: 'Híbrido' };

export function initializeEventModeration() {
    const root = document.querySelector('[data-event-moderation]');
    if (!root) return;

    const list = root.querySelector('[data-moderation-list]');
    const feedback = root.querySelector('[data-event-feedback]');
    const search = root.querySelector('[data-event-filter]');
    const more = root.querySelector('[data-moderation-more]');
    const dialog = root.querySelector('dialog');
    const form = dialog.querySelector('[data-event-decision]');
    const motive = form.elements.motivo;
    const submit = form.querySelector('button[type="submit"]');
    const back = form.querySelector('[data-event-decision-back]');
    const errorOutput = form.querySelector('[data-event-decision-error]');
    let currentLoad;
    let debounce;
    let next;
    let replacesList = true;
    let selected;

    function firstPage() {
        const url = new URL(root.dataset.url);
        if (search.value.trim()) url.searchParams.set('q', search.value.trim());
        return url;
    }

    function beginDecision(event, status) {
        selected = { url: event.decisao_url, status };
        form.reset();
        const approve = status === 'ativo';
        dialog.querySelector('h2').textContent = approve ? 'Aprovar publicação?' : 'Reprovar Evento?';
        dialog.querySelector('[data-event-subject]').textContent = event.titulo;
        form.querySelector('[data-motive-label]').hidden = approve;
        motive.disabled = approve;
        motive.required = !approve;
        errorOutput.textContent = '';
        dialog.showModal();
    }

    function render(event) {
        const card = root.querySelector('template').content.cloneNode(true);
        card.querySelector('h2').textContent = event.titulo;
        card.querySelector('[data-event-description]').textContent = event.descricao;
        card.querySelector('[data-event-mode]').textContent = modes[event.modalidade] ?? event.modalidade;
        const location = [event.cidade, event.uf].filter(Boolean).join(', ');
        const locationField = card.querySelector('[data-event-location]');
        locationField.hidden = !location;
        locationField.querySelector('dd').textContent = location;
        const agendaList = card.querySelector('[data-moderation-agendas]');
        for (const agenda of event.agendas) {
            const item = document.createElement('li');
            item.textContent = `${dateTime.format(new Date(agenda.inicio))} até ${dateTime.format(new Date(agenda.fim))} · ${agenda.vagas} vaga(s) · ${agenda.status}`;
            agendaList.append(item);
        }
        card.querySelector('[data-agendas-empty]').hidden = Boolean(event.agendas.length);
        card.querySelector('[data-event-approve]').addEventListener('click', () => beginDecision(event, 'ativo'));
        card.querySelector('[data-event-reject]').addEventListener('click', () => beginDecision(event, 'reprovado'));
        return card;
    }

    async function load(message = '') {
        currentLoad?.abort();
        const request = new AbortController();
        currentLoad = request;
        more.disabled = true;
        feedback.textContent = 'Carregando Eventos…';
        list.setAttribute('aria-busy', 'true');
        try {
            const { data } = await requestJson(next, { signal: request.signal });
            if (currentLoad !== request) return;
            if (replacesList) list.replaceChildren();
            data.data.forEach(event => list.append(render(event)));
            replacesList = false;
            next = data.next_page_url;
            feedback.textContent = message || (list.children.length ? '' : 'Nenhum Evento aguardando análise.');
            more.textContent = 'Carregar mais';
        } catch (error) {
            if (currentLoad !== request || error.name === 'AbortError') return;
            feedback.textContent = message ? `${message} ${error.message}` : error.message;
            more.textContent = 'Tentar novamente';
        } finally {
            if (currentLoad === request) {
                list.removeAttribute('aria-busy');
                more.disabled = false;
                more.hidden = !next;
            }
        }
    }

    function refresh(message) {
        replacesList = true;
        next = firstPage();
        return load(message);
    }

    search.addEventListener('input', () => {
        clearTimeout(debounce);
        debounce = setTimeout(() => refresh(), 250);
    });
    more.addEventListener('click', () => load());
    back.addEventListener('click', () => dialog.close());
    dialog.addEventListener('cancel', event => {
        if (submit.disabled) event.preventDefault();
    });
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (submit.disabled || !selected) return;
        submit.disabled = true;
        back.disabled = true;
        errorOutput.textContent = 'Registrando decisão…';
        try {
            const { message } = await requestJson(selected.url, {
                method: 'PATCH',
                body: { status_evento: selected.status, motivo: motive.disabled ? null : motive.value.trim() },
                csrfToken: root.dataset.csrf,
            });
            dialog.close();
            await refresh(message);
            feedback.focus();
        } catch (error) {
            errorOutput.textContent = error.errors?.motivo?.[0] ?? error.message;
        } finally {
            submit.disabled = false;
            back.disabled = false;
        }
    });
    refresh();
}
