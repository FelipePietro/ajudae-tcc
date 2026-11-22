import { publishNotice } from './notifications-demo.js';
import { applicantCase, displayReferral, hasAccepted, readSponsorship, requiredReason, saveSponsorship } from './sponsorship-demo.js';

// Local administrative journeys. No request, credential or real permission is involved.
const storageKey = 'ajudae.admin-review.v1';
const documentNames = ['Documento de identidade', 'Certidão de antecedentes'];

export function availableActions(record, variant, organizationStatus = 'ativo') {
    const { kind, status, blocked, lock, documents } = record;
    switch (kind) {
        case 'evento':
            return status === 'aguardando a confirmação'
                ? { aprovar: organizationStatus === 'ativo' ? ['publicado'] : null, reprovar: ['reprovado'] } : {};
        case 'ong':
            return status === 'aguardando aprovação'
                ? { aprovar: ['ativo'], reprovar: ['reprovado'] } : { reavaliar: ['aguardando aprovação'] };
        case 'promocao':
            if (status === 'pendente' || (status === 'em_analise' && lock === 'expirado')) {
                return { assumir: ['em_analise'] };
            }
            return status === 'em_analise' && lock !== 'outro'
                ? { aprovar: blocked ? null : ['aprovada'], reprovar: ['reprovada', 'Motivo'] } : {};
        case 'pessoa': {
            if (status === 'aguardando_validacao' || (status === 'em_analise' && lock === 'expirado')) {
                return { assumir: ['em_analise'] };
            }
            if (status !== 'em_analise' || lock === 'outro') return {};
            const actions = {
                aprovar: !blocked && documents?.every(value => value === 'aprovado') ? ['aprovado'] : null,
                corrigir: ['correcao_solicitada', 'Motivo'], reprovar: ['reprovado', 'Motivo'],
            };
            if (record.id === applicantCase && !record.acceptedReferral) {
                actions['pedir-padrinho'] = ['correcao_solicitada', 'Motivo'];
            }
            if (variant !== 'revisao') actions.definitiva = ['reprovado_definitivamente', 'Motivo'];
            documents.forEach((value, index) => {
                if (value === 'pendente') {
                    actions[`doc-${index}-aprovado`] = ['aprovado'];
                    actions[`doc-${index}-rejeitado`] = ['rejeitado', 'Motivo'];
                }
            });
            return actions;
        }
        case 'padrinho':
            return status === 'pendente' ? { aprovar: ['aprovada'], reprovar: ['rejeitada', 'Motivo opcional', 2000, false] } : {};
        case 'lgpd':
            if (variant === 'privacidade') return {};
            if (status === 'pendente') return { assumir: ['em_analise'] };
            return status === 'em_analise'
                ? { aprovar: ['concluida', 'Resposta', 4000], reprovar: ['recusada', 'Resposta', 4000] } : {};
        default:
            return {};
    }
}

export function decide(record, action, input, variant, organizationStatus) {
    const transition = availableActions(record, variant, organizationStatus)[action];
    if (!transition) return null;

    const [next, label, length = 2000, required = true] = transition;
    const reason = label ? input.trim() : '';
    if (label && ((required && !reason) || reason.length > length)) return null;

    if (action.startsWith('doc-')) {
        const index = Number(action.split('-')[1]);
        const documents = [...record.documents];
        documents[index] = next;
        return { patch: { documents }, from: record.documents[index], to: next, reason, subject: documentNames[index] };
    }

    const patch = { status: next, reason, ...(action === 'assumir' && { lock: 'proprio' }) };
    return { patch, from: record.status, to: next, reason };
}

const normalize = text => text.normalize('NFD').replace(/\p{Diacritic}/gu, '').toLowerCase().trim();
const pendingEvent = 'aguardando a confirmação';

export function displayState(record) {
    return record.reanalysis && record.status === pendingEvent ? 'reanalise' : record.status;
}

export function countEventReview(events) {
    const totals = { initial: 0, reanalysis: 0 };
    for (const event of events) {
        if (event.status === pendingEvent) totals[event.reanalysis ? 'reanalysis' : 'initial']++;
    }
    return totals;
}

export function matchesQueue(record, selected, query) {
    const searchable = [record.title, record.description, record.blocked, ...Object.values(record.facts)].join(' ');
    const haystack = normalize(searchable);
    return (!selected.length || selected.includes(displayState(record)))
        && normalize(query).split(/\s+/).every(part => haystack.includes(part));
}

const documentStatus = {
    aprovado: 'Documento validado', rejeitado: 'Documento rejeitado', pendente: 'Validação pendente',
};

function initDocumentPreview(root, findRecord) {
    const dialog = root.querySelector('[data-admin-document-dialog]');
    if (!dialog) return;

    const media = JSON.parse(root.querySelector('[data-admin-media]').textContent);
    const body = dialog.querySelector('[data-admin-document-body]');
    const toggle = dialog.querySelector('[data-admin-pdf-toggle]');
    let cleanup;
    let displayPdf;

    function showImage(source, label) {
        const img = document.createElement('img');
        img.alt = label;
        img.src = source;
        img.addEventListener('error', unavailable, { once: true });
        body.replaceChildren(img);
    }

    function unavailable() {
        const message = document.createElement('p');
        message.setAttribute('role', 'status');
        message.textContent = 'O documento está indisponível ou não pode ser visualizado.';
        body.replaceChildren(message);
        if (toggle) toggle.hidden = true;
    }

    function showPdf(url, nativeViewer) {
        displayPdf = nativeViewer;
        if (nativeViewer) {
            const viewer = document.createElement('iframe');
            viewer.src = url + '#toolbar=0&view=FitH';
            viewer.title = 'PDF da certidão de antecedentes';
            viewer.tabIndex = -1;
            body.replaceChildren(viewer);
        } else if (media['pdf-image']) {
            showImage(media['pdf-image'], 'Página da certidão de antecedentes');
        } else {
            unavailable();
        }
        if (toggle) {
            toggle.hidden = !navigator.pdfViewerEnabled || !media['pdf-image'];
            toggle.textContent = nativeViewer ? 'Ver página em imagem' : 'Abrir visualizador de PDF';
            toggle.onclick = () => showPdf(url, !displayPdf);
        }
    }

    root.addEventListener('click', event => {
        const trigger = event.target.closest('[data-admin-preview]');
        if (!trigger || dialog.open) return;
        const record = findRecord(trigger.closest('[data-admin-record]').dataset.adminRecord);
        const index = Number(trigger.dataset.adminPreview);
        const label = documentNames[index];
        if (!record?.documents?.[index] || !label) return;

        const mime = index === 0 ? 'image/png' : 'application/pdf';
        dialog.querySelector('h2').textContent = label;
        dialog.querySelector('#admin-document-state').textContent = documentStatus[record.documents[index]];
        if (toggle) toggle.hidden = true;

        if (!media[mime] || record.unavailableDocuments?.includes(index)) {
            unavailable();
        } else if (index === 0) {
            showImage(media[mime], label);
        } else {
            const bytes = Uint8Array.from(atob(media[mime].split(',')[1]), char => char.charCodeAt(0));
            const url = URL.createObjectURL(new Blob([bytes], { type: mime }));
            cleanup = () => URL.revokeObjectURL(url);
            showPdf(url, !media['pdf-image'] && navigator.pdfViewerEnabled);
        }
        trigger.focus();
        dialog.showModal();
        body.scrollTop = 0;
    });

    dialog.addEventListener('close', () => {
        body.replaceChildren();
        cleanup?.();
        cleanup = undefined;
        if (toggle) toggle.onclick = null;
    });
}

function loadState(variant) {
    try {
        const state = JSON.parse(sessionStorage.getItem(`${storageKey}.${variant}`));
        if (state?.records && Array.isArray(state.audit)) return state;
    } catch {
        // Storage can be unavailable. The active page remains usable.
    }
    return { records: {}, audit: [] };
}

function refreshReports(root, events) {
    if (!root.querySelector('[data-admin-report]')) return;

    const published = events.filter(event => event.status === 'publicado');
    const monthRows = [...root.querySelectorAll('[data-report-month]')];
    const byMonth = monthRows.map(row => published.filter(event => event.starts.startsWith(row.dataset.reportMonth)).length);
    const max = Math.max(1, ...byMonth);
    monthRows.forEach((row, index) => {
        row.querySelector('.rel-bar').style.height = (100 * byMonth[index] / max) + '%';
        row.querySelector('[data-report-value]').textContent = byMonth[index];
    });
    root.querySelectorAll('[data-report-category]').forEach(row => {
        const count = events.filter(event => event.category === row.dataset.reportCategory).length;
        const percent = Math.round(100 * count / Math.max(1, events.length));
        row.querySelector('.rel-hbar span').style.width = percent + '%';
        row.querySelector('[data-report-value]').textContent = count + ' (' + percent + '%)';
    });
    root.querySelectorAll('[data-report-ong]').forEach(row => {
        const owned = events.filter(event => event.ong === row.dataset.reportOng);
        row.querySelector('[data-report-all]').textContent = owned.length;
        row.querySelector('[data-report-published]').textContent = owned.filter(event => event.status === 'publicado').length;
        row.querySelector('[data-report-finished]').textContent = owned.filter(event => event.status === 'finalizado').length;
    });
}

function initAdmin(root) {
    const source = root.querySelector('[data-admin-data]');
    if (!source) return;

    const { variant } = root.dataset;
    const { records, statuses, tones, counts, pending, events } = JSON.parse(source.textContent);
    let state = loadState(variant);
    const dialog = root.querySelector('[data-admin-dialog]');
    const filter = root.querySelector('[data-admin-filter]');
    const search = root.querySelector('[data-admin-search]');
    const checkboxes = [...root.querySelectorAll('[data-admin-status-filter]')];
    const selectedStates = () => checkboxes.filter(input => input.checked).map(input => input.value);
    const results = root.querySelector('[data-admin-results]');
    const feedback = root.querySelector('[data-admin-feedback]');
    const cards = records.map(record => [record, root.querySelector(`[data-admin-record="${record.id}"]`)]);
    const tableSearch = root.querySelector('[data-admin-table-search]');
    const table = root.querySelector('[data-admin-table-rows]');
    const tableEmpty = root.querySelector('[data-admin-table-empty]');
    let selection;

    function renderTableSearch() {
        if (!table) return;
        const rows = [...table.rows];
        const query = normalize(tableSearch.value);
        for (const row of rows) row.hidden = (row.hasAttribute('data-admin-referral-user') && !sponsorship.enrolled) || !normalize(row.textContent).includes(query);
        tableEmpty.hidden = rows.some(row => !row.hidden) || (!rows.length && table.hasAttribute('data-admin-log'));
    }
    tableSearch?.addEventListener('input', renderTableSearch);

    let sponsorship = readSponsorship();
    const current = record => {
        const merged = { ...record, ...state.records[record.id] };
        if (record.id !== applicantCase) return merged;
        return {
            ...merged,
            status: sponsorship.account,
            lock: 'proprio',
            reason: sponsorship.reason,
            documents: sponsorship.documents ?? record.documents,
            acceptedReferral: hasAccepted(sponsorship),
            blocked: sponsorship.required && !hasAccepted(sponsorship) ? requiredReason + ' A aprovação depende do aceite.' : null,
        };
    };
    const statusFor = (id, original) => id === applicantCase
        ? (sponsorship.enrolled ? sponsorship.account : null)
        : state.records[id]?.status ?? original;
    const orgStatus = record => record.ong ? statusFor(record.ong, counts['cadastros-ong']?.[record.ong] ?? 'ativo') : 'ativo';
    const setChip = (node, status, label = statuses[status]) => {
        node.textContent = label;
        for (const name of [...node.classList]) {
            if (name.startsWith('admin-tag-')) node.classList.remove(name);
        }
        node.classList.add('admin-tag-' + (tones[status] ?? 'ouro'));
    };
    initDocumentPreview(root, id => {
        const record = records.find(entry => entry.id === id);
        return record && current(record);
    });

    function renderCounts() {
        const totals = Object.fromEntries(Object.entries(counts).map(([key, rows]) => [
            key, Object.entries(rows).filter(([id, status]) => pending.includes(statusFor(id, status))).length,
        ]));
        document.querySelectorAll('[data-admin-count]').forEach(node => {
            const total = totals[node.dataset.adminCount] ?? 0;
            node.textContent = total;
            if (node.classList.contains('ong-badge')) node.hidden = total === 0;
        });
        const currentEvents = events.map(event => current(event));
        const eventTotals = countEventReview(currentEvents);
        root.querySelectorAll('[data-admin-event-total]').forEach(node => {
            node.textContent = eventTotals[node.dataset.adminEventTotal];
        });
        root.querySelectorAll('[data-admin-metric]').forEach(node => {
            const [queue, status] = node.dataset.adminMetric.split('|');
            node.textContent = Object.entries(counts[queue] ?? {})
                .filter(([id, base]) => status === '*' ? statusFor(id, base) !== null : statusFor(id, base) === status).length;
        });
        refreshReports(root, currentEvents);
        root.querySelectorAll('[data-admin-dashboard-event]').forEach(node => {
            node.hidden = statusFor(node.dataset.adminDashboardEvent, pendingEvent) !== pendingEvent;
        });
        const emptyDashboard = root.querySelector('[data-admin-dashboard-empty]');
        if (emptyDashboard) emptyDashboard.hidden = eventTotals.initial + eventTotals.reanalysis > 0;
        const total = root.querySelector('[data-admin-total]');
        if (total) total.textContent = Object.values(totals).reduce((sum, value) => sum + value, 0);
        root.querySelectorAll('[data-admin-user-status]').forEach(node => {
            const id = node.dataset.adminUserStatus;
            const status = statusFor(id, counts.candidatos[id]);
            if (id === applicantCase) node.closest('tr').hidden = !status;
            if (status) setChip(node, status);
        });
        root.querySelectorAll('[data-admin-people-count]').forEach(node => {
            node.textContent = root.querySelectorAll('[data-admin-user-status]').length - Number(!sponsorship.enrolled && Boolean(root.querySelector('[data-admin-referral-user]')));
        });
    }

    function renderRecords() {
        if (!cards.length) return;

        let visible = 0;
        let total = 0;
        const active = filter?.value ? [filter.value] : selectedStates();
        for (const [base, card] of cards) {
            if (base.id === applicantCase && !sponsorship.enrolled) {
                card.hidden = true;
                continue;
            }
            total++;
            const record = current(base);
            const actions = availableActions(record, variant, orgStatus(record));
            setChip(card.querySelector('[data-admin-status]'), displayState(record));
            const referral = card.querySelector('[data-admin-referral]');
            if (referral) referral.textContent = displayReferral(sponsorship);
            card.querySelectorAll('[data-admin-document]').forEach(node => {
                const value = record.documents[node.dataset.adminDocument];
                setChip(node, value, documentStatus[value]);
            });
            card.querySelectorAll('[data-admin-action]').forEach(button => {
                const action = button.dataset.adminAction;
                button.hidden = !(action in actions);
                button.disabled = !actions[action];
            });
            const heading = card.querySelector('.admin-decision-heading');
            if (heading) {
                heading.hidden = !card.querySelector('[data-admin-card-actions] [data-admin-action]:not([hidden])');
            }
            const reason = card.querySelector('[data-admin-reason]');
            reason.hidden = !record.reason;
            const reasonLabel = record.kind === 'lgpd' ? 'Resposta' : 'Justificativa';
            reason.textContent = record.reason ? `${reasonLabel}: ${record.reason}` : '';
            const note = card.querySelector('[data-admin-block]');
            const missing = record.kind === 'pessoa' && record.status === 'em_analise' && !record.documents?.every(value => value === 'aprovado');
            const organizationBlocked = record.kind === 'evento' && actions.aprovar === null;
            const message = organizationBlocked ? 'A organização precisa estar ativa para aprovar a publicação.'
                : record.status === 'em_analise' ? record.blocked || (missing && 'Os documentos obrigatórios precisam estar aprovados.') : '';
            note.hidden = !message;
            note.textContent = message || '';
            const linked = card.querySelector('[data-admin-linked-status]');
            if (linked) linked.textContent = statuses[orgStatus(record)];
            const publicLink = card.querySelector('[data-admin-public]');
            if (publicLink) publicLink.hidden = !['ativo', 'publicado'].includes(record.status);
            const follow = card.querySelector('[data-admin-promotion-link]');
            if (follow) {
                const url = new URL(follow.href);
                url.searchParams.set('jornada', record.status.replace('_', '-'));
                follow.href = url;
            }
            card.hidden = !matchesQueue(record, active, search.value);
            if (!card.hidden) visible++;
        }
        root.querySelector('[data-admin-empty]').hidden = visible > 0;
        if (results) {
            results.textContent = `${visible} de ${total} registros · ${active.length ? `${active.length} selecionadas` : 'Todas as situações'}`;
        }
    }

    function renderLog() {
        const log = root.querySelector('[data-admin-log]');
        if (!log) return;
        const template = root.querySelector('[data-admin-log-entry]');
        log.replaceChildren();
        const audit = log.matches('ul') ? state.audit.slice(-6) : state.audit;
        for (const entry of [...audit].reverse()) {
            const fragment = template.content.cloneNode(true);
            fragment.querySelector('[data-log-title]').textContent = entry.title;
            const action = fragment.querySelector('[data-log-action]');
            if (action.classList.contains('admin-tag')) setChip(action, entry.to);
            action.textContent = `${entry.action} · ${statuses[entry.from]} → ${statuses[entry.to]}`;
            const actor = fragment.querySelector('[data-log-actor]');
            if (actor) actor.textContent = entry.actor;
            const time = fragment.querySelector('time');
            time.dateTime = entry.date;
            time.textContent = new Date(entry.date).toLocaleString('pt-BR', { timeZone: 'America/Sao_Paulo' });
            const reason = fragment.querySelector('[data-log-reason]');
            if (reason) {
                reason.hidden = !entry.reason;
                reason.textContent = entry.reason;
            }
            log.append(fragment);
        }
        root.querySelector('[data-admin-no-logs]').hidden = Boolean(state.audit.length);
        const total = root.querySelector('[data-admin-log-count]');
        if (total) total.textContent = state.audit.length;
    }

    search?.addEventListener('input', renderRecords);
    filter?.addEventListener('change', renderRecords);
    checkboxes.forEach(input => input.addEventListener('change', renderRecords));
    root.querySelector('[data-admin-clear]')?.addEventListener('click', () => {
        search.value = '';
        checkboxes.forEach(input => { input.checked = false; });
        renderRecords();
    });
    if (dialog) {
        const form = dialog.querySelector('form');
        const field = form.querySelector('textarea');
        const motive = form.querySelector('[data-admin-motive]');
        field.addEventListener('input', () => field.setCustomValidity(''));

        root.addEventListener('click', event => {
            const button = event.target.closest('[data-admin-action]');
            if (!button || button.disabled || dialog.open) return;
            const id = button.closest('[data-admin-record]').dataset.adminRecord;
            const record = current(records.find(entry => entry.id === id));
            const action = button.dataset.adminAction;
            const transition = availableActions(record, variant, orgStatus(record))[action];
            if (!transition) return;

            const [, label, length = 2000, required = true] = transition;
            const document = action.startsWith('doc-') ? documentNames[Number(action.split('-')[1])] : null;
            selection = { record, action, caption: button.textContent.trim(), card: button.closest('article') };
            dialog.querySelector('[data-admin-decision-title]').textContent = selection.caption;
            dialog.querySelector('[data-admin-decision-subject]').textContent = document ? record.title + ' · ' + document : record.title;
            motive.hidden = !label;
            motive.querySelector('span').textContent = label ?? '';
            field.disabled = !label;
            field.required = Boolean(label && required);
            field.maxLength = length;
            form.reset();
            field.setCustomValidity('');
            if (action === 'pedir-padrinho') field.value = requiredReason;
            dialog.showModal();
            if (label) field.focus();
            else dialog.querySelector('[type="submit"]').focus();
        });

        form.addEventListener('submit', event => {
            event.preventDefault();
            if (!selection || !dialog.open) return;
            const { record, action, caption, card } = selection;
            const result = decide(current(record), action, field.value, variant, orgStatus(record));
            if (!result) {
                if (!field.disabled) {
                    field.setCustomValidity('Preencha o campo antes de confirmar.');
                    field.reportValidity();
                }
                return;
            }

            const previousState = structuredClone(state);

            if (record.id === applicantCase) {
                const next = {
                    ...sponsorship, account: result.patch.status ?? sponsorship.account,
                    documents: result.patch.documents ?? sponsorship.documents,
                    reason: result.patch.reason ?? sponsorship.reason,
                    required: sponsorship.required || action === 'pedir-padrinho',
                };
                if (!saveSponsorship(next)) {
                    dialog.close();
                    feedback.textContent = 'Não foi possível registrar a decisão. O estado anterior foi preservado.';
                    feedback.tabIndex = -1;
                    dialog.addEventListener('close', () => feedback.focus(), { once: true });
                    feedback.focus();
                    return;
                }
                sponsorship = next;
            } else {
                state.records[record.id] = { ...state.records[record.id], ...result.patch };
            }
            if (record.id === 'padrinho-38' && result.patch.status) {
                sponsorship.historicalConclusion = result.patch.status;
                saveSponsorship(sponsorship);
            }
            state.audit.push({
                title: result.subject ? `${record.title} · ${result.subject}` : record.title,
                action: caption, actor: variant === 'revisao' ? 'Equipe de revisão' : 'Equipe de administração',
                date: new Date().toISOString(), from: result.subject ? result.from : displayState(record), to: result.to, reason: result.reason,
            });
            let saved = true;
            try {
                sessionStorage.setItem(`${storageKey}.${variant}`, JSON.stringify(state));
                if (record.id === 'pessoa-indicacao' && result.patch.status && ['em_analise','aprovado','reprovado','correcao_solicitada'].includes(result.patch.status))publishNotice(result.patch.status==='correcao_solicitada'?'correcao':result.patch.status,record.id+':'+result.patch.status,['pessoa:1']);
            } catch {
                saved = false;
            }
            dialog.close();
            if (!saved) {
                state = previousState;
                renderRecords();
                renderCounts();
                feedback.textContent = record.id === applicantCase || record.id === 'padrinho-38'
                    ? 'Não foi possível guardar o log da decisão. Consulte a situação do acompanhamento antes de repetir.'
                    : 'Não foi possível registrar a decisão. O estado anterior foi preservado. Tente novamente.';
                feedback.tabIndex = -1;
                dialog.addEventListener('close', () => feedback.focus(), { once: true });
                feedback.focus();
                return;
            }
            renderRecords();
            renderCounts();
            feedback.textContent = `${record.title} · ${statuses[result.to]}. Histórico atualizado.`;
            if (card.hidden) {
                (results ?? search).focus();
            } else {
                card.querySelector('[data-admin-status]').focus({ preventScroll: true });
            }
        });
        dialog.addEventListener('close', () => {
            selection = undefined;
            form.reset();
        });
    }

    const render = () => {
        renderRecords();
        renderCounts();
        renderLog();
        renderTableSearch();
    };
    render();
    root.dataset.adminReady = 'true';
    window.addEventListener('pageshow', event => {
        if (!event.persisted) return;
        state = loadState(variant);
        sponsorship = readSponsorship();
        render();
    });
}

if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-admin-review]').forEach(initAdmin);
    });
}
