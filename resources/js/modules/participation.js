import { requestJson } from '../core/http.js';
import { initializeParticipationManagement } from './participation-management.js';
import { controls, element, lazyDetails, localDate } from './web-operations.js';

const formatDate = new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short' });

const eventStatus = {
    ativo: 'Ativo',
    finalizado: 'Concluído',
    cancelado: 'Cancelado',
    reanalise: 'Em reanálise',
    reprovado: 'Reprovado',
    'aguardando a confirmação': 'Aguardando aprovação',
};
const monitoringStatus = {
    pendente: 'Pendente',
    validada: 'Validada',
    rejeitada: 'Rejeitada',
    nao_aplicavel: 'Não se aplica',
};
const justificationStatus = { pendente: 'Em análise', deferida: 'Deferida', indeferida: 'Indeferida' };

function justification(agenda, ui) {
    return lazyDetails('Justificativa da ocorrência', content => {
        function render(data) {
            content.append(
                element('p', `Protocolo nº ${data.justificativa_id} · ${justificationStatus[data.status] ?? data.status}`),
                element('p', `Enviada em ${localDate(data.enviada_em)}`),
                element('p', `Motivo enviado · ${data.motivo}`, 'break-words'),
            );
            if (data.decidido_em) content.append(element('p', `Decidida em ${localDate(data.decidido_em)}`));
            if (data.motivo_decisao) content.append(element('p', `Motivo da decisão · ${data.motivo_decisao}`, 'break-words'));
        }

        function missing() {
            if (!agenda.enviar_justificativa_url) {
                content.append(element('p', 'Nenhuma justificativa encontrada. Atualize o histórico para conferir a situação.'));
                return;
            }
            content.append(ui.form({
                label: 'Enviar justificativa',
                url: agenda.enviar_justificativa_url,
                method: 'POST',
                fields: [{ name: 'motivo', label: 'Motivo', type: 'textarea', attributes: { required: '', maxlength: 2000 } }],
                complete(message, data) {
                    const feedback = element('p', message);
                    feedback.setAttribute('role', 'status');
                    feedback.tabIndex = -1;
                    content.replaceChildren(feedback);
                    render(data);
                    feedback.focus();
                },
            }));
        }

        ui.read(content, agenda.justificativa_url, render, missing);
    });
}

function monitoring(agenda, ui) {
    return lazyDetails('Monitoria estudantil', content => {
        ui.read(content, agenda.monitoria_url, data => {
            content.append(element('p', monitoringStatus[data.status] ?? data.status));
            if (data.validado_em) content.append(element('p', `Decidida em ${localDate(data.validado_em)}`));
        });
    });
}

function renderParticipation(root, event, ui) {
    const card = root.querySelector('[data-participation-template]').content.cloneNode(true);
    card.querySelector('[data-event-title]').textContent = event.titulo;
    card.querySelector('[data-event-status]').textContent = eventStatus[event.status_evento] ?? event.status_evento;
    const agendas = card.querySelector('[data-event-agendas]');
    for (const agenda of event.agendas) {
        const item = element('li', null, 'border-t border-black/10 pt-2');
        item.append(element('p', `${localDate(agenda.inicio)} · ${agenda.situacao}`));
        if (agenda.justificativa_url) item.append(justification(agenda, ui));
        if (agenda.monitoria_url) item.append(monitoring(agenda, ui));
        agendas.append(item);
    }

    const form = card.querySelector('[data-event-form]');
    const result = card.querySelector('[data-event-rating]');
    if (event.nota !== null) result.textContent = `Sua avaliação da ONG: ${event.nota} de 5.`;
    if (!event.avaliacao_url) return card;
    form.hidden = false;
    if (event.avaliacao_ate) {
        form.querySelector('[data-event-deadline]').textContent =
            `Você pode enviar até ${formatDate.format(new Date(event.avaliacao_ate))}.`;
    }
    form.addEventListener('submit', async submit => {
        submit.preventDefault();
        const button = form.querySelector('button[type="submit"]');
        if (button.disabled || !form.reportValidity()) return;
        button.disabled = true;
        const feedback = form.querySelector('[data-event-error]');
        feedback.textContent = '';
        try {
            const { data } = await requestJson(event.avaliacao_url, {
                method: 'POST',
                body: { nota: Number(form.elements.nota.value) },
                csrfToken: root.dataset.csrf,
            });
            result.textContent = `Avaliação enviada: ${data.nota} de 5.`;
            result.tabIndex = -1;
            result.focus();
            form.hidden = true;
        } catch (error) {
            feedback.textContent = error.errors?.nota?.[0] ?? error.message;
        } finally {
            button.disabled = false;
        }
    });
    return card;
}

export function initializeParticipation() {
    initializeParticipationManagement();
    const root = document.querySelector('[data-web-participation]');
    if (!root) return;

    const status = root.querySelector('[data-participation-status]');
    const list = root.querySelector('[data-participation-list]');
    const groups = new Map(Array.from(list.querySelectorAll('[data-participation-group]'), group => [group.dataset.participationGroup, group]));
    const ui = controls(root);
    let hasEvents = false;
    const button = root.querySelector('[data-participation-next]');
    let next = root.dataset.url;
    async function load() {
        if (!next || button.disabled) return;
        button.disabled = true;
        status.textContent = 'Carregando participações…';
        list.setAttribute('aria-busy', 'true');
        try {
            const { data: page } = await requestJson(next);
            for (const event of page.data) {
                const group = groups.get(event.status_evento) ?? groups.get('outros');
                group.querySelector('[data-group-events]').append(renderParticipation(root, event, ui));
                group.hidden = false;
                hasEvents = true;
            }
            next = page.next_page_url;
            status.textContent = hasEvents ? '' : 'Você ainda não tem participações para acompanhar.';
            button.textContent = 'Carregar mais';
            if (!next && document.activeElement === button) {
                status.tabIndex = -1;
                status.focus();
            }
        } catch (error) {
            status.textContent = error.message;
            button.textContent = 'Tentar novamente';
        } finally {
            list.removeAttribute('aria-busy');
            button.hidden = !next;
            button.disabled = false;
        }
    }
    button.addEventListener('click', load);
    load();
}
