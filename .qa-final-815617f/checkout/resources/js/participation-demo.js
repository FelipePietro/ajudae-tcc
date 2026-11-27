import { publishNotice } from './notifications-demo.js';
import { reliabilitySummary } from './participation-reliability-core.js';
import { experienceCredits, experienceLedger, experienceSummary, reconcileExperience, renderAgendaExperience, renderExperienceProfile, validExperience } from './participation-xp.js';

const actions = {
    presente: ['Confirmar a presença nesta Agenda? O registro fica no histórico.', 'Presença confirmada'],
    ausente: ['Registrar a ausência nesta Agenda?', 'Ausência registrada'],
    validada: ['Confirmar a monitoria estudantil nesta Agenda?', 'Monitoria validada'],
    rejeitada: ['Rejeitar a monitoria estudantil nesta Agenda?', 'Monitoria rejeitada'],
    desistir: ['Confirmar a desistência desta Agenda?', 'Desistência registrada'],
    justificar: ['Encaminhar a justificativa ao responsável pelo Evento?', 'Justificativa enviada'],
    deferida: ['Aceitar a justificativa e isentar a ocorrência da penalidade?', 'Justificativa aceita'],
    indeferida: ['Recusar a justificativa e manter a ocorrência?', 'Justificativa recusada'],
    finalizarAgenda: ['A Agenda será encerrada definitivamente. Depois disso, presença e monitoria não poderão ser alteradas.', 'Agenda finalizada'],
    finalizarEvento: ['O Evento será encerrado definitivamente, com suas Agendas e participações preservadas no histórico.', 'Evento finalizado'],
    avaliarVoluntario: ['Confirme a nota do voluntário. Após o envio, a avaliação fica registrada no histórico.', 'Avaliação do voluntário registrada'],
    avaliarOng: ['Confirme a nota da ONG. O envio é opcional e pode ser feito uma vez por Evento.', 'Avaliação da ONG registrada'],
};

const withdrawalLabels = {
    normal: 'Desistência normal',
    tardia: 'Desistência tardia',
    isenta: 'Desistência sem penalidade',
};

const reasonLabels = {
    imprevisto: 'Imprevisto pessoal',
    transporte: 'Imprevisto no deslocamento',
    horario: 'Conflito de horário',
};

const timestamp = new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short', timeZone: 'America/Sao_Paulo' });
const storageKey = id => `ajudae.participacao.v1.${id}`;

export function applyHistory(record, history) {
    return history.reduce((current, entry) => entry.recordId === record.id ? { ...current, ...entry.change } : current, record);
}

function agendaState(agenda, history) {
    if (agenda.state !== 'ativa') return agenda;
    const closed = history.some(entry => entry.action === 'finalizarAgenda' && entry.agendaId === agenda.id);
    return closed ? { ...agenda, state: 'finalizada' } : agenda;
}

function eventStatus(fixture, history) {
    return fixture.eventStatus === 'ativo' && history.some(entry => entry.action === 'finalizarEvento')
        ? 'finalizado' : fixture.eventStatus;
}

const terminal = agenda => ['finalizada', 'cancelada'].includes(agenda.state);
const manager = fixture => ['ong', 'organizador'].includes(fixture.actor.type);
const approved = record => record.selection === 'aprovada' && !record.withdrawal;
const resolved = record => ['presente', 'ausente'].includes(record.presence);
const validNote = nota => Number.isInteger(nota) && nota >= 1 && nota <= 5;
const evaluationAction = action => action === 'avaliarVoluntario' || action === 'avaliarOng';

function presentPeople(agendas) {
    return [...new Set(agendas.flatMap(agenda => agenda.records
        .filter(record => approved(record) && record.presence === 'presente').map(record => record.personId)))];
}

export function closureProgress(fixture, history, now) {
    const status = eventStatus(fixture, history);
    const canClose = fixture.canClose && fixture.writable && manager(fixture) && status === 'ativo';
    const agendas = fixture.agendas.map(base => {
        const agenda = agendaState(base, history);
        const records = fixture.records.filter(record => record.agendaId === agenda.id)
            .map(record => applyHistory(record, history));
        const active = records.filter(approved);
        const pending = active.filter(record => !resolved(record)).length;
        let reason = '';
        if (agenda.state === 'finalizada') reason = 'Esta Agenda já foi finalizada.';
        else if (agenda.state === 'cancelada') reason = 'Esta Agenda foi cancelada.';
        else if (!canClose) reason = 'O Evento precisa estar em operação para finalizar a Agenda.';
        else if (!Number.isFinite(now) || !(now >= Date.parse(agenda.end))) reason = 'Aguarde o horário de término da Agenda.';
        else if (pending) reason = 'Registre presença ou ausência para todas as pessoas com comparecimento pendente.';
        return {
            ...agenda, records, approved: active.length, pending, resolved: active.length - pending, reason,
            monitoringPending: active.some(record => record.presence === 'presente' && record.student && record.monitoring === 'pendente'),
        };
    });
    const agendasCanceladas = agendas.filter(agenda => agenda.state === 'cancelada');
    const agendasAbertas = agendas.filter(agenda => !terminal(agenda));
    const agendasTerminadas = agendas.filter(terminal).map(agenda => agenda.id);
    const pessoasPresentesDistintas = presentPeople(agendas.filter(agenda => agenda.state !== 'cancelada'));
    const pessoasAvaliaveis = presentPeople(agendas.filter(agenda => agenda.state === 'finalizada'));
    const avaliacoesConcluidas = new Map();
    for (const review of [...(fixture.avaliacoesConcluidas ?? []), ...history.filter(entry => entry.action === 'avaliarVoluntario')]) {
        const nota = review.change?.nota ?? review.nota;
        if (review.eventId === fixture.id && validNote(nota) && pessoasAvaliaveis.includes(review.personId)
            && !avaliacoesConcluidas.has(review.personId)) {
            avaliacoesConcluidas.set(review.personId, { ...review, nota });
        }
    }
    const avaliacoesObrigatoriasPendentes = pessoasPresentesDistintas.filter(id => !avaliacoesConcluidas.has(id));
    const motivosBloqueioEvento = [];
    if (status === 'finalizado') motivosBloqueioEvento.push('Este Evento já foi finalizado.');
    else if (status === 'cancelado') motivosBloqueioEvento.push('Este Evento foi cancelado.');
    else if (!canClose) motivosBloqueioEvento.push('Somente a gestão autorizada pode finalizar um Evento em operação.');
    else {
        if (!agendas.length) motivosBloqueioEvento.push('O Evento não possui Agendas para conferência.');
        if (agendasAbertas.length) motivosBloqueioEvento.push('Finalize as Agendas abertas após resolver o comparecimento.');
        if (avaliacoesObrigatoriasPendentes.length) motivosBloqueioEvento.push('O responsável precisa concluir as avaliações após o fechamento das Agendas.');
    }
    return {
        agendas, status, agendasCanceladas, agendasAbertas, agendasTerminadas, pessoasPresentesDistintas, pessoasAvaliaveis,
        avaliacoesObrigatoriasPendentes, avaliacoesConcluidas, motivosBloqueioEvento,
    };
}

function reviewWindow(agendas, status) {
    return status === 'ativo' && agendas.length > 0 && agendas.every(terminal);
}

function volunteerReviewAllowed(fixture, progress, personId) {
    return fixture.canReviewVolunteers && fixture.writable && manager(fixture)
        && (fixture.actor.type !== 'organizador' || fixture.actor.id !== personId)
        && reviewWindow(progress.agendas, progress.status)
        && progress.avaliacoesObrigatoriasPendentes.includes(personId);
}

function ongReviewState(fixture, history, progress) {
    if (!fixture.reviewOng || fixture.actor.type !== 'pessoa') return { eligible: false };
    const personId = fixture.actor.id;
    const eligible = progress.pessoasAvaliaveis.includes(personId);
    const receipt = history.find(entry => entry.action === 'avaliarOng' && entry.eventId === fixture.id
        && entry.personId === personId && entry.ongId === fixture.reviewOng && validNote(entry.change.nota));
    const agendas = fixture.eventAgendas.map(agenda => agendaState(agenda, history));
    return { eligible, receipt, allowed: fixture.writable && eligible && !receipt && reviewWindow(agendas, progress.status) };
}

export function evaluationDecision(fixture, history, action, personId, nota, now) {
    if (!evaluationAction(action) || !validNote(nota) || !Number.isFinite(now)) return null;
    const progress = closureProgress(fixture, history, now);
    const entry = { action, eventId: fixture.id, personId, actor: fixture.actor, time: now, previous: { nota: null }, change: { nota } };
    if (action === 'avaliarVoluntario') return volunteerReviewAllowed(fixture, progress, personId) ? entry : null;
    if (personId !== fixture.actor.id || !ongReviewState(fixture, history, progress).allowed) return null;
    return { ...entry, ongId: fixture.reviewOng };
}

export function closureDecision(fixture, history, action, targetId, now) {
    if (!Number.isFinite(now) || !fixture.canClose || !fixture.writable || !manager(fixture)) return null;
    const progress = closureProgress(fixture, history, now);
    const common = { action, eventId: fixture.id, actor: fixture.actor, time: now };
    if (action === 'finalizarAgenda') {
        const agenda = progress.agendas.find(item => item.id === targetId);
        if (!agenda || agenda.reason) return null;
        return { ...common, agendaId: agenda.id, minutes: agenda.minutes, previous: { state: 'ativa' }, change: { state: 'finalizada' } };
    }
    if (action !== 'finalizarEvento' || targetId !== fixture.id || progress.motivosBloqueioEvento.length) return null;
    return { ...common, previous: { state: 'ativo' }, change: { state: 'finalizado' } };
}

export function allowedActions(fixture, agenda, record, now) {
    if (!fixture.writable || agenda.state === 'cancelada' || record.selection !== 'aprovada') return [];

    const started = now >= Date.parse(agenda.start);
    const justificationEligible = record.presence === 'ausente' || ['normal', 'tardia'].includes(record.withdrawal);
    if (fixture.actor.type === 'pessoa') {
        if (fixture.actor.id !== record.personId) return [];
        if (justificationEligible && !record.justification) return ['justificar'];
        if (agenda.state === 'ativa' && !started && !record.withdrawal && record.presence === 'pendente') return ['desistir'];
        return [];
    }

    if (!manager(fixture) || (fixture.actor.type === 'organizador' && fixture.actor.id === record.personId)) return [];
    if (record.justification?.status === 'em_analise') return ['deferida', 'indeferida'];
    if (!started || agenda.state !== 'ativa' || record.withdrawal) return [];
    if (record.presence === 'pendente') return ['presente', 'ausente'];
    if (record.presence === 'presente' && record.student && record.monitoring === 'pendente') return ['validada', 'rejeitada'];
    return [];
}

function withdrawalFor(agenda, now) {
    if (agenda.exemption) return 'isenta';
    return Date.parse(agenda.start) - now < 24 * 60 * 60 * 1000 ? 'tardia' : 'normal';
}

export function participationDecision(fixture, history, recordId, action, now, reason, decisionReason = null) {
    const base = fixture.records.find(record => record.id === recordId);
    if (!base) return null;
    const baseAgenda = fixture.agendas.find(item => item.id === base.agendaId);
    const agenda = baseAgenda && agendaState(baseAgenda, history);
    const current = applyHistory(base, history);
    if (!agenda || !Number.isFinite(now) || !allowedActions(fixture, agenda, current, now).includes(action)) return null;

    let change;
    switch (action) {
        case 'presente':
        case 'ausente':
            change = { presence: action };
            break;
        case 'validada':
        case 'rejeitada':
            change = { monitoring: action };
            break;
        case 'desistir':
            change = { withdrawal: withdrawalFor(agenda, now) };
            break;
        case 'justificar':
            if (!Object.hasOwn(reasonLabels, reason)) return null;
            change = { justification: { status: 'em_analise', reason } };
            break;
        default:
            if (decisionReason !== null && !['analise', 'esclarecimento'].includes(decisionReason)) return null;
            change = { justification: { ...current.justification, status: action, ...(decisionReason ? { decisionReason } : {}) } };
    }

    return {
        recordId,
        action,
        change,
        previous: Object.fromEntries(Object.keys(change).map(key => [key, current[key]])),
        actor: fixture.actor,
        time: now,
    };
}

export function readHistory(storage, eventId) {
    const history = JSON.parse(storage.getItem(storageKey(eventId)) ?? '[]');
    const numericEventId = Number(String(eventId).split('.')[0]);
    if (!Array.isArray(history) || history.some(entry => !entry
        || (!Object.hasOwn(actions, entry.action) && entry.action !== 'creditarXp')
        || !Number.isFinite(entry.time) || !entry.change || !entry.actor
        || (entry.xp !== undefined && !validExperience(entry))
        || (evaluationAction(entry.action) ? entry.eventId !== numericEventId
            || !Number.isInteger(entry.personId) || !validNote(entry.change.nota)
            || (entry.action === 'avaliarOng' && (!Number.isInteger(entry.ongId) || entry.actor.type !== 'pessoa' || entry.actor.id !== entry.personId))
            : entry.action === 'finalizarEvento' ? entry.eventId !== numericEventId
            : entry.action === 'finalizarAgenda' || entry.action === 'creditarXp'
                ? !Number.isInteger(entry.agendaId) || entry.eventId !== numericEventId || (entry.action === 'creditarXp' && !validExperience(entry))
                : !Number.isInteger(entry.recordId)))) {
        throw new Error('Histórico inválido.');
    }
    return history;
}

export function historyWithExperience(fixture, now) {
    const key = fixture.storageId ?? fixture.id;
    const history = readHistory(sessionStorage, key);
    const reconciled = reconcileExperience(fixture, history, now, applyHistory);
    if (reconciled !== history) sessionStorage.setItem(storageKey(key), JSON.stringify(reconciled));
    return reconciled;
}

function initExperienceProfile(root) {
    const fixture = JSON.parse(root.querySelector('[data-xp-fixture]').textContent);
    const error = root.querySelector('[data-xp-error]');
    const content = root.querySelector('[data-xp-content]');
    const render = () => {
        try {
            const history = historyWithExperience(fixture, Date.now());
            renderExperienceProfile(root, experienceSummary(fixture.actor.id, experienceLedger(fixture, history)));
            error.hidden = true;
            content.hidden = false;
        } catch {
            error.hidden = false;
            content.hidden = true;
        }
    };
    root.querySelector('[data-xp-filter]').addEventListener('change', render);
    window.addEventListener('pageshow', render);
    render();
}

function optionalText(card, selector, value) {
    const node = card.querySelector(selector);
    node.textContent = value;
    node.hidden = !value;
}

function countLabel(count, singular, plural) {
    return `${count} ${count === 1 ? singular : plural}`;
}

function recordPresence(agenda, record) {
    if (agenda.state === 'cancelada') return 'Participação cancelada pela Agenda';
    if (record.withdrawal) return withdrawalLabels[record.withdrawal];
    return {
        pendente: 'Presença pendente',
        presente: 'Presença confirmada',
        ausente: 'Ausência registrada',
    }[record.presence];
}

function monitoringLabel(agenda, record) {
    if (!record.student || agenda.state === 'cancelada' || record.withdrawal || record.presence === 'ausente') return '';
    const labels = {
        pendente: 'Monitoria estudantil pendente. A validação é necessária para o certificado estudantil.',
        validada: 'Monitoria estudantil validada.',
        rejeitada: 'Monitoria estudantil rejeitada. O certificado estudantil não está disponível.',
    };
    return labels[record.monitoring];
}

export function justificationPresentation(record) {
    const justification = record.justification;
    const visible = Boolean(justification) || record.presence === 'ausente' || ['normal', 'tardia'].includes(record.withdrawal);
    const labels = {
        em_analise: 'Justificativa em análise',
        deferida: 'Justificativa aceita. Ocorrência isenta de penalidade.',
        indeferida: 'Justificativa recusada',
    };
    const grounds = { analise: 'Análise da justificativa apresentada', esclarecimento: 'Esclarecimento confirmado com o titular' };
    return {
        visible,
        status: justification ? (labels[justification.status] ?? 'Situação da justificativa indisponível.') : 'Nenhuma justificativa enviada para esta Agenda.',
        reason: justification && Object.hasOwn(reasonLabels, justification.reason) ? `Motivo enviado: ${reasonLabels[justification.reason]}.` : '',
        decision: justification && Object.hasOwn(grounds, justification.decisionReason) ? `Fundamento da decisão: ${grounds[justification.decisionReason]}.` : '',
    };
}

function renderRecord(card, record, agenda, fixture, history) {
    const indicator = card.querySelector('[data-record-reliability]');
    if (indicator) {
        const metric = reliabilitySummary(fixture, history, record.personId);
        indicator.hidden = false;
        indicator.textContent = `Confiabilidade global indisponível neste recorte. ${metric.confirmed} presenças confirmadas neste Evento.`;
    }
    card.querySelector('[data-record-presence]').textContent = recordPresence(agenda, record);
    optionalText(card, '[data-record-monitoring]', monitoringLabel(agenda, record));
    const justification = justificationPresentation(record);
    card.querySelector('[data-record-justification-panel]').hidden = !justification.visible;
    optionalText(card, '[data-record-justification]', justification.status);
    optionalText(card, '[data-record-justification-reason]', justification.reason);
    optionalText(card, '[data-record-justification-decision]', justification.decision);

    const timeline = card.querySelector('[data-record-history]');
    const entries = history.filter(entry => entry.recordId === record.id);
    const items = entries.map(entry => {
        const item = document.createElement('li');
        const actor = { ong: 'Organização', organizador: 'Organizador responsável', pessoa: 'Voluntário' }[entry.actor.type];
        const detail = justificationPresentation({ justification: entry.change.justification });
        const reason = entry.action === 'justificar' ? detail.reason : detail.decision;
        item.textContent = `${actions[entry.action][1]} · ${actor} · ${timestamp.format(entry.time)}${reason ? ' · ' + reason : ''}`;
        return item;
    });
    timeline.querySelector('ol').replaceChildren(...items);
    timeline.hidden = items.length === 0;
}

function renderSelections() {
    const histories = new Map();
    for (const card of document.querySelectorAll('[data-participation-selection]')) {
        const { participationEvent, participationSelection } = card.dataset;
        try {
            if (!histories.has(participationEvent)) histories.set(participationEvent, readHistory(sessionStorage, participationEvent));
            const entries = histories.get(participationEvent);
            const record = applyHistory({ id: Number(participationSelection) }, entries);
            if (record.withdrawal) card.querySelector('[data-selection-label]').textContent = withdrawalLabels[record.withdrawal];
            else if (record.presence === 'presente') card.querySelector('[data-selection-label]').textContent = 'Presença confirmada';
            else if (record.presence === 'ausente') card.querySelector('[data-selection-label]').textContent = 'Ausência registrada';
        } catch {
            // The selection remains visible. The attendance view reports storage failures.
        }
    }
}

function renderClosureBadges() {
    for (const badge of document.querySelectorAll('[data-closure-event-badge], [data-closure-agenda-badge]')) {
        const { closureEventBadge, closureAgendaBadge, agenda } = badge.dataset;
        try {
            const history = readHistory(sessionStorage, closureEventBadge ?? closureAgendaBadge);
            const action = closureEventBadge ? 'finalizarEvento' : 'finalizarAgenda';
            if (history.some(entry => entry.action === action && (closureEventBadge || entry.agendaId === Number(agenda)))) {
                badge.textContent = closureEventBadge ? 'Finalizado' : 'Agenda finalizada';
            }
        } catch {
            // Keep the server-rendered label when the current tab cannot read its history.
        }
    }
}

function closureHistory(history, action, agendaId) {
    const entry = history.find(item => item.action === action && (agendaId === undefined || item.agendaId === agendaId));
    if (!entry) return '';
    const actor = entry.actor.type === 'ong' ? 'Organização' : 'Organizador responsável';
    return `${actions[action][1]} · ${actor} · ${timestamp.format(entry.time)}`;
}

function renderEvaluations(root, fixture, history, progress) {
    if (!manager(fixture)) {
        const section = root.querySelector('[data-ong-evaluation]');
        if (!section) return;
        const { eligible, receipt, allowed } = ongReviewState(fixture, history, progress);
        section.hidden = !eligible && !receipt;
        section.querySelector('[data-evaluation-action]').hidden = !allowed;
        const message = receipt ? `Avaliação enviada · ${receipt.change.nota}/5 · ${timestamp.format(receipt.time)}.`
            : progress.status !== 'ativo' ? 'O prazo para avaliar terminou com o encerramento do Evento.'
                : allowed ? 'Envie uma nota de 1 a 5 para a ONG responsável.'
                    : 'Você poderá avaliar quando todas as Agendas estiverem encerradas.';
        optionalText(section, '[data-evaluation-state]', message);
        return;
    }

    const { avaliacoesConcluidas, pessoasPresentesDistintas, pessoasAvaliaveis, agendasAbertas, status } = progress;
    root.querySelector('[data-closure-total="avaliadas"]').textContent = avaliacoesConcluidas.size;
    const guidance = !pessoasPresentesDistintas.length ? 'Nenhuma presença confirmada para avaliar.'
        : !fixture.canReviewVolunteers ? 'O responsável operacional registra as avaliações. Acompanhe a conclusão por aqui.'
            : status === 'ativo' && agendasAbertas.length ? 'Encerre as Agendas abertas para registrar as avaliações.' : '';
    optionalText(root, '[data-closure-review-help]', guidance);
    for (const card of root.querySelectorAll('[data-volunteer-evaluation]')) {
        const personId = Number(card.dataset.volunteerEvaluation);
        const review = avaliacoesConcluidas.get(personId);
        card.hidden = !pessoasAvaliaveis.includes(personId);
        card.querySelector('[data-evaluation-action]').hidden = !volunteerReviewAllowed(fixture, progress, personId);
        const ownReview = review && (!review.actor || (review.actor.type === fixture.actor.type && review.actor.id === fixture.actor.id));
        const details = review?.time ? ` · ${timestamp.format(review.time)}` : '';
        const label = !review ? 'Pendente' : fixture.canReviewVolunteers && ownReview ? `Avaliada · ${review.nota}/5${details}` : 'Avaliada';
        optionalText(card, '[data-evaluation-state]', label);
    }
}

function renderClosure(root, fixture, history, progress) {
    const status = progress.status;
    const finished = status === 'finalizado';
    const summary = root.querySelector('[data-closure-event-status]');
    if (!manager(fixture)) {
        const agendas = fixture.eventAgendas.map(agenda => agendaState(agenda, history));
        summary.textContent = finished ? 'Evento finalizado.' : agendas.length && agendas.every(terminal)
            ? 'Agendas encerradas. O Evento aguarda o fechamento e a conferência das avaliações obrigatórias.'
            : 'Evento em acompanhamento. Ainda há Agendas abertas.';
        return;
    }

    const { agendas, agendasAbertas, agendasCanceladas, agendasTerminadas, avaliacoesObrigatoriasPendentes, motivosBloqueioEvento } = progress;
    const totals = {
        abertas: agendasAbertas.length,
        resolvidas: agendasTerminadas.length,
        canceladas: agendasCanceladas.length,
        presencas: agendasAbertas.reduce((total, agenda) => total + agenda.pending, 0),
        avaliacoes: avaliacoesObrigatoriasPendentes.length,
    };
    summary.textContent = finished ? 'Evento finalizado.' : status === 'cancelado' ? 'Evento cancelado.'
        : motivosBloqueioEvento.length ? 'O Evento tem condições de encerramento a verificar.' : 'Evento pronto para finalizar.';
    for (const [name, total] of Object.entries(totals)) {
        root.querySelector(`[data-closure-total="${name}"]`).textContent = total;
    }
    optionalText(root, '[data-closure-event-reason]', motivosBloqueioEvento.join(' '));
    optionalText(root, '[data-closure-event-history]', closureHistory(history, 'finalizarEvento'));
    root.querySelector('[data-closure-action="finalizarEvento"]').disabled = motivosBloqueioEvento.length > 0;
    for (const agenda of agendas) {
        const link = root.querySelector(`[data-closure-open-agenda="${agenda.id}"]`);
        link.hidden = terminal(agenda);
    }
}

function init(root) {
    const fixture = JSON.parse(root.querySelector('[data-participation-fixture]').textContent);
    const dialog = root.querySelector('[data-participation-dialog]');
    const form = dialog.querySelector('form');
    const reason = form.elements.namedItem('reason');
    const decisionReason = form.elements.namedItem('decisionReason');
    const note = form.elements.namedItem('nota');
    const noteError = dialog.querySelector('[data-evaluation-error]');
    const dialogTitle = dialog.querySelector('#participation-dialog-title');
    const feedback = root.querySelector('[data-participation-feedback]');
    const error = root.querySelector('[data-participation-error]');
    const buttons = [...root.querySelectorAll('[data-participation-action], [data-closure-action], [data-evaluation-action]')];
    const key = fixture.storageId ?? fixture.id;
    let pending;

    function prepareDialog(action, target) {
        const reviewing = evaluationAction(action);
        if (decisionReason) {
            decisionReason.disabled = !['deferida', 'indeferida'].includes(action);
            decisionReason.required = !decisionReason.disabled;
            decisionReason.closest('label').hidden = decisionReason.disabled;
        }
        form.reset();
        note.disabled = !reviewing;
        note.closest('label').hidden = !reviewing;
        note.removeAttribute('aria-invalid');
        noteError.hidden = true;
        dialogTitle.textContent = reviewing ? (action === 'avaliarOng' ? 'Avaliar ONG' : 'Avaliar voluntário') : 'Revisar decisão';
        if (reason) {
            reason.disabled = action !== 'justificar';
            reason.required = !reason.disabled;
            reason.closest('label').hidden = reason.disabled;
        }
        dialog.querySelector('[data-participation-review]').textContent = target;
        dialog.showModal();
        if (reviewing) note.focus();
        else if (reason && !reason.disabled) reason.focus();
    }

    function render() {
        try {
            const now = Date.now();
            const history = historyWithExperience(fixture, now);
            const progress = closureProgress(fixture, history, now);
            const ledger = experienceLedger(fixture, history);
            renderClosure(root, fixture, history, progress);
            renderEvaluations(root, fixture, history, progress);
            for (const agenda of progress.agendas) {
                const section = root.querySelector(`[data-participation-agenda="${agenda.id}"]`);
                const { records } = agenda;
                if (fixture.historicalOnly) {
                    section.hidden = !records.some(record => record.withdrawal || ['presente', 'ausente'].includes(record.presence) || ledger.has(record.id));
                }
                renderAgendaExperience(section, agenda, fixture, progress.status, ledger, now);
                const state = section.querySelector('[data-participation-agenda-state]');
                state.textContent = { finalizada: 'Agenda finalizada', cancelada: 'Agenda cancelada' }[agenda.state] ?? state.dataset.activeLabel;
                const totals = section.querySelector('[data-participation-totals]');
                if (totals) {
                    totals.hidden = agenda.state === 'cancelada';
                    const withdrawals = records.filter(record => record.withdrawal).length;
                    const summary = [
                        countLabel(agenda.approved, 'aprovação ativa', 'aprovações ativas'),
                        countLabel(agenda.resolved, 'comparecimento resolvido', 'comparecimentos resolvidos'),
                        countLabel(agenda.pending, 'pendente', 'pendentes'),
                    ];
                    if (withdrawals) summary.push(`${withdrawals} com desistência`);
                    totals.textContent = summary.join(' · ');
                    section.querySelector('[data-closure-action]').disabled = Boolean(agenda.reason);
                    optionalText(section, '[data-closure-agenda-reason]', agenda.reason);
                    optionalText(section, '[data-closure-agenda-history]', closureHistory(history, 'finalizarAgenda', agenda.id));
                }
                for (const record of records) {
                    const card = section.querySelector(`[data-participation-record="${record.id}"], [data-promoted-record="${record.id}"]`);
                    renderRecord(card, record, agenda, fixture, history);
                    const allowed = allowedActions(fixture, agenda, record, now);
                    const controls = card.querySelector('[data-record-actions]');
                    controls.hidden = allowed.length === 0;
                    for (const button of controls.querySelectorAll('[data-participation-action]')) {
                        button.hidden = !allowed.includes(button.dataset.participationAction);
                    }
                }
            }
            error.hidden = true;
        } catch {
            for (const button of buttons) button.disabled = true;
            error.textContent = 'Não foi possível consultar o histórico. Recarregue a página para tentar novamente.';
            error.hidden = false;
        }
    }

    for (const button of buttons) {
        button.addEventListener('click', () => {
            if (dialog.open) return;
            const evaluation = button.dataset.evaluationAction;
            if (evaluation) {
                const personId = Number(button.dataset.person);
                try {
                    const history = readHistory(sessionStorage, key);
                    if (!evaluationDecision(fixture, history, evaluation, personId, 1, Date.now())) {
                        render();
                        return;
                    }
                    const subject = evaluation === 'avaliarOng' ? fixture.title : fixture.records.find(record => record.personId === personId).name;
                    pending = { evaluation, personId };
                    prepareDialog(evaluation, `${subject}. ${actions[evaluation][0]}`);
                } catch {
                    render();
                }
                return;
            }
            const closure = button.dataset.closureAction;
            if (closure) {
                const agendaId = Number(button.closest('[data-participation-agenda]')?.dataset.participationAgenda);
                const agenda = fixture.agendas.find(item => item.id === agendaId);
                const target = closure === 'finalizarEvento' ? fixture.id : agendaId;
                try {
                    const history = readHistory(sessionStorage, key);
                    if (!closureDecision(fixture, history, closure, target, Date.now())) {
                        render();
                        return;
                    }
                    pending = { closure, target };
                    const progress = closureProgress(fixture, history, Date.now());
                    const warning = progress.agendas.find(item => item.id === agendaId)?.monitoringPending
                        ? ' Há monitoria pendente nesta Agenda, necessária para o certificado estudantil.' : '';
                    prepareDialog(closure, `${actions[closure][0]} ${agenda ? `Agenda ${agenda.number}.` : fixture.title + '.'}${warning}`);
                } catch {
                    render();
                }
                return;
            }
            const recordId = Number(button.closest('[data-participation-record]').dataset.participationRecord);
            const action = button.dataset.participationAction;
            pending = { recordId, action };
            const record = fixture.records.find(item => item.id === recordId);
            const agenda = fixture.agendas.find(item => item.id === record.agendaId);
            const detail = action === 'desistir' ? ` ${withdrawalLabels[withdrawalFor(agenda, Date.now())]}.` : '';
            prepareDialog(action, `${actions[action][0]} ${fixture.actor.type === 'pessoa' ? '' : `${record.name} · `}Agenda ${agenda.number}.${detail}`);
        });
    }

    note.addEventListener('change', () => {
        note.removeAttribute('aria-invalid');
        noteError.hidden = true;
    });
    form.addEventListener('submit', event => {
        event.preventDefault();
        if (!pending || !dialog.open) return;
        const { recordId, action, closure, target, evaluation, personId } = pending;
        const nota = Number(note.value);
        if (evaluation && !validNote(nota)) {
            note.setAttribute('aria-invalid', 'true');
            noteError.hidden = false;
            note.focus();
            return;
        }
        let message;
        try {
            const history = readHistory(sessionStorage, key);
            const now = Date.now();
            let entry;
            if (evaluation) entry = evaluationDecision(fixture, history, evaluation, personId, nota, now);
            else if (closure) entry = closureDecision(fixture, history, closure, target, now);
            else entry = participationDecision(fixture, history, recordId, action, now, reason?.value, decisionReason && !decisionReason.disabled ? decisionReason.value : null);
            if (entry) {
                if (entry.action === 'finalizarAgenda') {
                    const agenda = closureProgress(fixture, history, now).agendas.find(item => item.id === entry.agendaId);
                    entry.xp = experienceCredits(fixture, { ...agenda, ...entry.change }, agenda.records, entry.time);
                }
                sessionStorage.setItem(storageKey(key), JSON.stringify([...history, entry]));
                const baseRecord=fixture.records.find(record=>record.id===entry.recordId);
                const ownerId=1; // Same synthetic owner as VisualThreeFixtures::person().
                const personalRecord=(baseRecord && baseRecord.personId===ownerId) || entry.personId===ownerId;
                const kind=entry.action;
                if (personalRecord && ['presente','ausente','desistir','justificar','deferida','indeferida','avaliarVoluntario'].includes(kind)) {
                    const context=new URL(location.href).searchParams.get('contexto');
                    const recipient=fixture.actor.type==='pessoa' && ['pessoa','organizador','organizador-vinculado'].includes(context)?context+':1':'pessoa:1';
                    publishNotice(kind,key+':'+history.length,[recipient],{event:fixture.id,example:new URL(location.href).searchParams.get('exemplo'),agenda:baseRecord?.agendaId,record:baseRecord?.id});
                }
                if (kind==='justificar') publishNotice('pendencias',key+':'+history.length,['ong:1'],{event:fixture.id,example:new URL(location.href).searchParams.get('exemplo')});
            }
            message = entry ? `${actions[evaluation ?? closure ?? action][1]}.` : 'Esta decisão não está disponível na situação atual.';
        } catch {
            message = 'Não foi possível registrar a decisão. Tente novamente.';
        }
        pending = null;
        dialog.close();
        render();
        document.dispatchEvent(new Event('participation:changed'));
        feedback.textContent = message;
        feedback.focus({ preventScroll: true });
    });
    dialog.addEventListener('close', () => {
        // The native close event can arrive after a subsequent action reopens the dialog.
        if (!dialog.open) pending = null;
    });

    const openAgenda = () => {
        const target = document.getElementById(location.hash.slice(1));
        if (target?.matches('[data-participation-agenda]') && root.contains(target)) target.open = true;
    };
    for (const link of root.querySelectorAll('[data-closure-open-agenda]')) {
        link.addEventListener('click', () => { document.getElementById(`agenda-${link.dataset.closureOpenAgenda}`).open = true; });
    }
    openAgenda();
    window.addEventListener('hashchange', openAgenda);
    window.addEventListener('pageshow', render);
    render();
}

if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('[data-participation]').forEach(init));
    document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('[data-xp-profile]').forEach(initExperienceProfile));
    window.addEventListener('pageshow', () => {
        renderSelections();
        renderClosureBadges();
    });
}
