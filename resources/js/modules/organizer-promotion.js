import { requestJson } from '../core/http.js';

const labels = {
    pendente: 'Aguardando análise',
    em_analise: 'Em análise',
    aprovada: 'Solicitação aprovada',
    reprovada: 'Solicitação recusada',
};

export function initializeOrganizerPromotion() {
    const root = document.querySelector('[data-web-promotion]');
    if (!root) return;

    const form = root.querySelector('form');
    const status = root.querySelector('[data-web-promotion-status]');
    const reason = root.querySelector('[data-web-promotion-reason]');
    const feedback = root.querySelector('[data-web-promotion-feedback]');
    const field = form.elements.justificativa;
    const errorSlot = form.querySelector('[role="alert"]');
    const submit = form.querySelector('button');
    const refresh = root.querySelector('[data-web-promotion-refresh]');
    const url = '/conta/pessoa/solicitacao-organizador';
    let busy = false;
    let loaded = false;

    async function run(send = false) {
        if (busy) return;
        busy = true;
        submit.disabled = refresh.disabled = true;
        feedback.textContent = send ? 'Enviando solicitação…' : 'Consultando solicitação…';
        errorSlot.textContent = '';
        field.removeAttribute('aria-invalid');
        let message = '';
        try {
            if (send) {
                const response = await requestJson(url, {
                    method: 'POST', body: { justificativa: field.value.trim() }, csrfToken: root.dataset.csrf,
                });
                message = response.message;
                form.reset();
                form.hidden = true;
            }
            const { data } = await requestJson(url);
            const { data: person } = await requestJson('/conta/pessoa/validacao');
            status.textContent = labels[data?.status] ?? 'Nenhuma solicitação registrada';
            status.hidden = false;
            reason.textContent = data?.motivo_decisao ?? '';
            reason.hidden = !reason.textContent;
            const eligibleAt = data?.status === 'reprovada' && data.decidida_em
                ? new Date(Date.parse(data.decidida_em) + 30 * 24 * 60 * 60 * 1000) : null;
            const coolingDown = eligibleAt && eligibleAt.getTime() > Date.now();
            form.hidden = person.status_validacao !== 'aprovado' || person.role_pessoa !== 'voluntario'
                || (person.bloqueado_ate && Date.parse(person.bloqueado_ate) > Date.now())
                || ['pendente', 'em_analise', 'aprovada'].includes(data?.status) || coolingDown;
            const waitMessage = coolingDown ? `Nova solicitação disponível a partir de ${eligibleAt.toLocaleDateString('pt-BR')}.` : '';
            feedback.textContent = [message, waitMessage].filter(Boolean).join(' ');
            loaded = true;
            if (send) feedback.focus();
        } catch (error) {
            if (error.errors?.justificativa?.[0]) {
                feedback.textContent = '';
                errorSlot.textContent = error.errors.justificativa[0];
                field.setAttribute('aria-invalid', 'true');
                field.focus();
            } else {
                feedback.textContent = [message, error.message].filter(Boolean).join(' ');
            }
        } finally {
            submit.disabled = refresh.disabled = false;
            busy = false;
        }
    }

    root.addEventListener('toggle', () => { if (root.open && !loaded) run(); });
    refresh.addEventListener('click', () => run());
    form.addEventListener('submit', event => {
        event.preventDefault();
        run(true);
    });
    if (root.open) run();
}
