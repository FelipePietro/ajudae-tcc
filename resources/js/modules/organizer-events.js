import { requestJson } from '../core/http.js';

const eventLabels = {
    ativo: 'Publicado',
    'aguardando a confirmação': 'Aguardando aprovação',
    reprovado: 'Reprovado',
    finalizado: 'Finalizado',
    cancelado: 'Cancelado',
};

async function getActiveOrganizers(url) {
    const active = [];
    let next = url;
    while (next) {
        const { data } = await requestJson(next);
        const links = data.data.filter(link => link.status === 'ativo');
        active.push(...links);
        next = links.length === data.data.length ? data.next_page_url : null;
    }
    return active;
}

function initializeDashboard() {
    const root = document.querySelector('[data-active-organizer-summary]');
    if (!root) return;

    const feedback = root.querySelector('[data-active-organizer-feedback]');
    feedback.textContent = 'Carregando vínculos…';
    getActiveOrganizers(root.dataset.activeOrganizerSummary).then(links => {
        document.querySelector('[data-active-organizer-count]').textContent = links.length;
        const list = root.querySelector('[data-active-organizer-names]');
        links.forEach(link => {
            const item = document.createElement('li');
            item.className = 'ff-item';
            item.textContent = link.nome;
            list.append(item);
        });
        feedback.textContent = links.length ? '' : 'Nenhum vínculo ativo.';
    }).catch(error => { feedback.textContent = error.message; });
}

export function initializeOrganizerEvents() {
    initializeDashboard();
    const root = document.querySelector('[data-organizer-event-list]');
    if (!root) return;

    const rows = root.querySelector('[data-organizer-event-rows]');
    const feedback = root.querySelector('[data-event-list-feedback]');
    const more = root.querySelector('[data-event-list-more]');
    const template = root.querySelector('template');
    const linksUrl = root.dataset.linksUrl;
    let next = root.dataset.url;
    let active = null;
    let busy = false;
    let reloadPending = false;

    function renderEvent(event) {
        const row = template.content.firstElementChild.cloneNode(true);
        row.querySelector('[data-event-name]').textContent = event.nm_evento;
        const status = row.querySelector('[data-event-status]');
        status.textContent = eventLabels[event.status_evento] ?? event.status_evento.replaceAll('_', ' ');
        if (!linksUrl) return row;

        const current = active?.find(link => link.pessoa_id === event.responsavel_pessoa_id);
        const responsibility = row.querySelector('[data-event-responsible]');
        responsibility.textContent = event.responsavel_pessoa_id === null
            ? 'Responsável atual: conta da organização'
            : `Responsável atual: ${current?.nome ?? 'organizador designado'}`;
        const form = row.querySelector('form');
        const result = row.querySelector('[data-event-result]');
        const terminal = ['cancelado', 'finalizado'].includes(event.status_evento);
        const started = event.agendas?.some(agenda => Date.parse(agenda.data_inicio) <= Date.now());
        if (terminal || started || active === null) {
            result.textContent = terminal ? 'A responsabilidade de um Evento encerrado é preservada no histórico.'
                : started ? 'O responsável só pode mudar antes do início do Evento.'
                    : 'A lista de organizadores vinculados está indisponível.';
            return row;
        }

        const select = form.elements.pessoa_id;
        select.add(new Option('Conta da organização', '', false, event.responsavel_pessoa_id === null));
        for (const link of active.filter(link => link.pessoa_id !== null)) {
            select.add(new Option(link.nome, link.pessoa_id, false, link === current));
        }
        if (!current && event.responsavel_pessoa_id !== null) {
            const option = new Option('Organizador designado', event.responsavel_pessoa_id, false, true);
            option.disabled = true;
            select.add(option);
        }
        form.hidden = false;
        const button = form.querySelector('button');
        const updateControl = () => {
            button.disabled = select.value === String(event.responsavel_pessoa_id ?? '');
        };
        updateControl();
        select.addEventListener('change', updateControl);
        form.addEventListener('submit', async eventSubmit => {
            eventSubmit.preventDefault();
            if (busy || button.disabled) return;
            busy = true;
            button.disabled = select.disabled = true;
            result.textContent = 'Atualizando responsável…';
            try {
                const { data, message } = await requestJson(`/conta/ong/eventos/${event.evento_id}/responsavel`, {
                    method: 'PATCH', body: { pessoa_id: select.value === '' ? null : Number(select.value) },
                    csrfToken: root.dataset.csrf,
                });
                const updated = renderEvent({ ...event, ...data });
                row.replaceWith(updated);
                const confirmation = updated.querySelector('[data-event-result]');
                confirmation.textContent = message;
                confirmation.tabIndex = -1;
                confirmation.focus();
            } catch (error) {
                result.textContent = error.errors?.pessoa_id?.[0] ?? error.message;
            } finally {
                select.disabled = false;
                updateControl();
                finish();
            }
        });
        return row;
    }

    function finish() {
        busy = false;
        if (reloadPending) {
            reloadPending = false;
            load(true);
        }
    }

    async function load(reset = false) {
        if (busy) {
            reloadPending ||= reset;
            return;
        }
        busy = true;
        if (reset) {
            next = root.dataset.url;
            rows.replaceChildren();
        }
        more.disabled = true;
        feedback.textContent = 'Carregando Eventos…';
        let linkError = '';
        try {
            if (linksUrl && (reset || active === null)) {
                try {
                    active = await getActiveOrganizers(linksUrl);
                } catch (error) {
                    active = null;
                    linkError = error.message;
                }
            }
            const { data } = await requestJson(next);
            rows.append(...data.data.map(renderEvent));
            next = data.next_page_url;
            more.textContent = 'Carregar mais';
            feedback.textContent = linkError || (rows.children.length ? '' : 'Nenhum Evento disponível.');
            if (linkError) {
                more.textContent = 'Tentar novamente';
                next = root.dataset.url;
            }
        } catch (error) {
            feedback.textContent = error.message;
            more.textContent = 'Tentar novamente';
        } finally {
            more.hidden = !next;
            more.disabled = false;
            finish();
        }
    }

    more.addEventListener('click', () => load(Boolean(linksUrl && active === null)));
    if (linksUrl) document.addEventListener('organizer-links-updated', () => load(true));
    load();
}
