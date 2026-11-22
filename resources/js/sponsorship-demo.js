import { publishNotice } from './notifications-demo.js';
// Synthetic registrations are isolated by opaque IDs. No account fields or credentials.
export const applicantCase = 'pessoa-indicacao';
export const requiredReason = 'É necessário indicar um padrinho elegível.';
export const accountLabels = {
    aguardando_validacao: 'Aguardando validação', em_analise: 'Em análise',
    correcao_solicitada: 'Correção solicitada', aprovado: 'Cadastro aprovado',
    reprovado: 'Cadastro reprovado', reprovado_definitivamente: 'Cadastro reprovado definitivamente',
};
const key = 'ajudae.apadrinhamento.v1';
const standard = { days: 82, attendance: 13, rating: 4.7, absences: 0, withdrawals: 1, active: 0, depends: false, blocked: false, volunteer: true };
export const sponsors = {
    principal: { name: 'Marina de Exemplo', code: 'Q7HF-M9XR-2KVT', metrics: standard },
    alternativa: { name: 'Rui de Exemplo', code: 'W3BN-8PTD-5JQF', metrics: { ...standard, days: 65, attendance: 11, rating: 4.5 } },
    insuficiente: { name: 'Pessoa de Exemplo', code: 'X6CK-P4YT-9NVH', metrics: { ...standard, days: 52, attendance: 9, rating: 4.3 } },
    lotado: { name: 'Pessoa de Exemplo', code: 'G2SD-A6ZQ-8MNL', metrics: { ...standard, active: 2 } },
    bloqueado: { name: 'Pessoa de Exemplo', metrics: { ...standard, blocked: true } },
    semdados: { name: 'Pessoa de Exemplo', metrics: { ...standard, rating: null } },
};
const retired = 'R8LM-V2CP-7JKS';
const ownCode = 'T6DQ-B4ZR-3MNH';

export function eligibility(metrics, added = 0) {
    const m = metrics;
    const criteria = [
        ['Voluntário aprovado', m.volunteer, m.volunteer ? 'Sim' : 'Não'],
        ['Cadastro aprovado há pelo menos 60 dias', m.days === null ? null : m.days >= 60, m.days === null ? 'Sem dados' : m.days + ' dias'],
        ['Ao menos 10 presenças confirmadas', m.attendance === null ? null : m.attendance >= 10, m.attendance === null ? 'Sem dados' : String(m.attendance)],
        ['Média de avaliações de pelo menos 4,5 de 5', m.rating === null ? null : m.rating >= 4.5, m.rating === null ? 'Sem dados' : m.rating.toLocaleString('pt-BR')],
        ['Nenhuma ausência injustificada nos últimos 90 dias', m.absences === null ? null : m.absences === 0, m.absences === null ? 'Sem dados' : String(m.absences)],
        ['Até uma desistência tardia penalizável nos últimos 90 dias', m.withdrawals === null ? null : m.withdrawals <= 1, m.withdrawals === null ? 'Sem dados' : String(m.withdrawals)],
        ['Capacidade para novo vínculo', m.active === null ? null : m.active + added < 2, m.active === null ? 'Sem dados' : (m.active + added) + ' de 2 vínculos ativos'],
        ['Sem acompanhamento como apadrinhado', !m.depends, m.depends ? 'Vínculo ativo' : 'Sem vínculo'],
        ['Sem bloqueio ativo', !m.blocked, m.blocked ? 'Bloqueio ativo' : 'Sem bloqueio'],
    ];
    return { criteria, label: m.blocked ? 'Bloqueado' : criteria.some(([, ok]) => ok === false) ? 'Não elegível' : criteria.some(([, ok]) => ok === null) ? 'Aguardando dados' : 'Elegível' };
}

const empty = () => ({ enrolled: false, required: false, account: 'aguardando_validacao', requests: [], preferences: {} });
const validRegistrationId = id => typeof id === 'string' && /^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i.test(id);

export function readSponsorship({ strict = false, registrationId = null } = {}) {
    try {
        if (registrationId !== null && !validRegistrationId(registrationId)) throw new Error('Invalid registration ID');
        const stored = sessionStorage.getItem(key);
        if (stored === null) return empty();
        const raw = JSON.parse(stored);
        const state = registrationId === null || raw?.registrationId === registrationId ? raw : raw?.registrations?.[registrationId];
        if (!state && registrationId !== null) return empty();
        if (typeof state?.enrolled === 'boolean' && Array.isArray(state.requests)
            && Object.hasOwn(accountLabels, state.account)
            && (registrationId === null || state.registrationId === registrationId)
            && state.requests.every(row => row && Object.hasOwn(sponsors, row.sponsor) && Object.hasOwn(requestLabels, row.status))) {
            return state;
        }
        throw new Error('Invalid sponsorship state');
    } catch (error) {
        if (strict) throw error;
        return empty();
    }
}

export function saveSponsorship(state, { activate = false } = {}) {
    try {
        const currentState = readSponsorship();
        const id = state.registrationId;
        if (id !== undefined && !validRegistrationId(id)) return false;
        const old = id ? readSponsorship({ registrationId: id }) : currentState;
        let next = state;
        const active = !id || activate || currentState.registrationId === id;
        if (id) {
            const { registrations: ignored, ...snapshot } = state;
            const { registrations = {}, ...previous } = currentState;
            // One write keeps the active projection and per-registration histories together.
            next = { ...(active ? snapshot : previous), registrations: {
                ...registrations,
                ...(!previous.registrationId && previous.enrolled ? { legacy: previous } : {}),
                ...(validRegistrationId(previous.registrationId) ? { [previous.registrationId]: previous } : {}),
                [id]: snapshot,
            } };
        }
        sessionStorage.setItem(key, JSON.stringify(next));
        if (!active) return true; // An archived applicant must not notify the active persona.
        const previous=old.requests.at(-1),current=state.requests.at(-1);
        if(current && JSON.stringify(previous)!==JSON.stringify(current)){
            const id='sponsorship:'+current.date+':'+current.status;
            if(current.status==='pendente'){
                publishNotice('pedido',id,['pessoa:1']);
                if(current.sponsor==='principal')publishNotice('padrinho',id,['padrinho-principal']);
            }
            if(current.status==='ativo')publishNotice('aceite',id,['pessoa:1']);
            if(current.status==='recusada')publishNotice('recusa',id,['pessoa:1']);
        }
        if(old.account!==state.account && ['correcao_solicitada','em_analise','aprovado','reprovado'].includes(state.account))publishNotice(state.account==='correcao_solicitada'?'correcao':state.account,'account:'+state.account,['pessoa:1']);
        return true;
    } catch {
        return false;
    }
}

export function resolveReferral(code, state = empty()) {
    const value = String(code ?? '').trim().toUpperCase();
    if (!value) return {};
    if (value === ownCode) return { error: 'Seu próprio código não pode ser usado para solicitar apadrinhamento.' };
    const sponsor = Object.keys(sponsors).find(id => sponsors[id].code === value);
    if (value === retired || !sponsor || eligibility(sponsors[sponsor].metrics, activeFor(state, sponsor)).label !== 'Elegível') {
        return { error: 'Código indisponível. Confira o código ou peça outro à pessoa que indicou você.' };
    }
    return { sponsor, name: sponsors[sponsor].name };
}

const activeFor = (state, id) => {
    const accepted = state.requests.filter(row => row.sponsor === id && ['ativo', 'conclusao_pendente'].includes(row.status)).length;
    return accepted + Number(id === 'principal' && state.historicalConclusion !== 'aprovada');
};
export const latestRequest = state => state.requests.at(-1);
export const requestLabels = {
    pendente: 'Aguardando o aceite do padrinho', recusada: 'Indicação recusada',
    ativo: 'Vínculo ativo', conclusao_pendente: 'Conclusão pendente de revisão',
    concluido: 'Vínculo concluído', encerrado: 'Vínculo encerrado', substituida: 'Indicação substituída',
};
export const hasAccepted = state => ['ativo', 'conclusao_pendente', 'concluido'].includes(latestRequest(state)?.status);
const openRequest = sponsor => ({ sponsor, status: 'pendente', date: new Date().toISOString() });
const allowedPreferences = { causas: ['1','2','3'], habilidades: ['1','2','3'], recursos: ['1','2','3'] };

export function enroll(code, preferences = {}, registrationId = null) {
    let state;
    try { state = readSponsorship({ strict: true, registrationId }); }
    catch { return { error: 'Não foi possível consultar o cadastro. Tente novamente.' }; }
    if (state.enrolled) return { state };
    const result = resolveReferral(code, state);
    if (result.error) return result;
    if (registrationId !== null) state.registrationId = registrationId;
    state.enrolled = true;
    state.preferences = Object.fromEntries(Object.entries(allowedPreferences).map(([group, values]) => [
        group, values.filter(value => preferences[group]?.includes(value)),
    ]));
    if (result.sponsor) state.requests.push(openRequest(result.sponsor));
    return saveSponsorship(state, { activate: true }) ? { state } : { error: 'Não foi possível registrar o pedido. O estado anterior foi preservado. Tente novamente.' };
}

export function registrationRequest(state, registrationId) {
    const request = latestRequest(state);
    return validRegistrationId(registrationId) && state.enrolled && state.registrationId === registrationId
        && request && Object.hasOwn(sponsors, request.sponsor) && Object.hasOwn(requestLabels, request.status) ? request : null;
}

export function applicantExample(name) {
    const state = { ...empty(), enrolled: true };
    const request = { sponsor: 'principal', status: 'pendente', date: '2026-11-10T15:00:00Z' };
    const decidedAt = '2026-11-12T15:00:00Z';
    switch (name) {
        case 'sem-indicacao': return state;
        case 'pendente': return { ...state, requests: [request] };
        case 'ativo': return { ...state, account: 'em_analise', requests: [{ ...request, status: 'ativo', decidedAt }] };
        case 'recusado': return { ...state, requests: [{ ...request, status: 'recusada', decidedAt }] };
        case 'correcao': return { ...state, account: 'correcao_solicitada', required: true, requests: [{ ...request, status: 'recusada', decidedAt }] };
        default: return null;
    }
}

export function resubmit(code, state) {
    if (!state.enrolled || state.account !== 'correcao_solicitada' || !state.required) return null;
    const previous = latestRequest(state);
    if (hasAccepted(state)) {
        if (String(code ?? '').trim()) return { error: 'O vínculo aceito será mantido. Reapresente o cadastro sem informar outro código.' };
        return { state: { ...state, account: 'aguardando_validacao', reason: '' } };
    }
    const result = resolveReferral(code, state);
    if (result.error || !result.sponsor) return result.error ? result : { error: 'Informe o código de um padrinho elegível.' };
    const requests = [...state.requests];
    if (previous?.status === 'pendente' && previous.sponsor !== result.sponsor) {
        requests[requests.length - 1] = { ...previous, status: 'substituida', decidedAt: new Date().toISOString() };
    }
    if (!(previous?.status === 'pendente' && previous.sponsor === result.sponsor)) {
        requests.push(openRequest(result.sponsor));
    }
    return { state: { ...state, requests, preferences: previous?.sponsor === result.sponsor ? state.preferences : {},
        account: 'aguardando_validacao', reason: '' } };
}

export function decideRequest(state, sponsor, accept) {
    const request = latestRequest(state);
    if (!request || request.sponsor !== sponsor || request.status !== 'pendente' || !sponsors[sponsor]) return null;
    if (accept && (['reprovado', 'reprovado_definitivamente'].includes(state.account)
        || eligibility(sponsors[sponsor].metrics, activeFor(state, sponsor)).label !== 'Elegível')) return null;
    request.status = accept ? 'ativo' : 'recusada';
    request.decidedAt = new Date().toISOString();
    return state;
}

export function displayReferral(state) {
    const request = latestRequest(state);
    return request ? requestLabels[request.status] + ' · ' + sponsors[request.sponsor].name : 'Indicação não informada';
}

export function bindRegistrationReferral(wizard) {
    const input = wizard.querySelector('[data-referral-code]');
    if (!input) return null;
    const issue = wizard.querySelector('[data-referral-message]');
    const consent = wizard.querySelector('[data-referral-consent]');
    const existingId = history.state?.ajudaeRegistrationId;
    const registrationId = validRegistrationId(existingId) ? existingId : crypto.randomUUID();
    history.replaceState({ ...history.state, ajudaeRegistrationId: registrationId }, '', location.href);
    const currentState = () => readSponsorship({ strict: true, registrationId });
    function complete(state) {
        const request = registrationRequest(state, registrationId);
        const link = wizard.querySelector('[data-registration-sponsorship]');
        if (link) {
            link.hidden = !request;
            if (request) {
                const url = new URL(link.href, location.origin);
                url.searchParams.set('cadastro', registrationId);
                link.href = url;
            }
        }
        wizard.querySelector('[data-referral-complete]').textContent = request
            ? request.status === 'pendente'
                ? 'Indicação registrada. Aguardando o aceite de ' + sponsors[request.sponsor].name + '. A análise cadastral e documental continua.'
                : 'Acompanhe o pedido e a análise cadastral.'
            : 'Revisão cadastral concluída. O cadastro não foi enviado para análise.';
    }
    function render() {
        let result;
        try { result = resolveReferral(input.value, currentState()); }
        catch { result = { error: 'Não foi possível consultar o cadastro. Tente novamente.' }; }
        issue.textContent = result.error || (result.name ? 'Indicação de ' + result.name + '. O pedido dependerá do aceite após concluir o cadastro.' : '');
        input.setAttribute('aria-invalid', Boolean(result.error));
        consent.closest('label').hidden = !result.sponsor;
        return result;
    }
    input.addEventListener('input', () => {
        const url = new URL(location.href);
        const value = input.value.trim();
        // Only the public referral is eligible to appear in navigation history.
        if (/^[a-z0-9]{4}(-[a-z0-9]{4}){2}$/i.test(value)) url.searchParams.set('padrinho', value.toUpperCase());
        else url.searchParams.delete('padrinho');
        history.replaceState(history.state, '', url);
        render();
    });
    render();
    return {
        completed: () => {
            try {
                const state = currentState();
                if (!state.enrolled || state.registrationId !== registrationId) return false;
                complete(state);
                return true;
            } catch { return false; }
        },
        validate: () => !render().error,
        review: () => { const result = render(); return result.name ? result.name + ' · depende de aceite' : 'Sem indicação'; },
        finish: () => {
            const preferences = {};
            if (consent.checked && render().sponsor) {
                wizard.querySelectorAll('[data-preference]:checked').forEach(option => {
                    (preferences[option.dataset.group] ??= []).push(option.value);
                });
            }
            const result = enroll(input.value, preferences, registrationId);
            if (result.error) return result.error;
            complete(result.state);
            return null;
        },
    };
}

function initProfile(root) {
    const { sponsorshipRole: role, sponsorshipScenario: scenario, sponsorshipSignup: signup } = root.dataset;
    const actions = root.querySelector('[data-sponsorship-actions]');
    const dialog = root.querySelector('[data-sponsorship-dialog]');
    const feedback = root.querySelector('[data-sponsorship-feedback]');
    let accept;
    function set(selector, value) {
        const node = root.querySelector(selector);
        if (node) node.textContent = value;
    }
    function show(selector, value) {
        const node = root.querySelector(selector);
        if (node) node.hidden = !value;
    }
    const registrationId = role === 'solicitante' ? new URL(location.href).searchParams.get('cadastro') : null;
    let example = registrationId === null ? applicantExample(root.dataset.applicantExample) : null;
    const currentState = () => example ?? readSponsorship({ strict: role === 'solicitante', registrationId });
    const date = value => new Date(value).toLocaleDateString('pt-BR', { timeZone: 'America/Sao_Paulo' });
    const correction = root.querySelector('[data-sponsorship-correction]');
    const correctionInput = correction?.querySelector('input');
    const correctionError = correction?.querySelector('[role="alert"]');

    function renderApplicant(state, request) {
        show('[data-applicant-example-links]', new URL(location.href).searchParams.get('inspecao') === '1' && (!state.enrolled || Boolean(example)));
        show('[data-sponsorship-unregistered]', !state.enrolled);
        show('[data-sponsorship-own]', state.enrolled);
        if (!state.enrolled) return;

        const accepted = hasAccepted(state);
        const canCorrect = state.required && state.account === 'correcao_solicitada';
        const requestStatus = root.querySelector('[data-sponsorship-status]');
        requestStatus.textContent = request ? requestLabels[request.status] : 'Indicação não informada';
        requestStatus.className = 'account-badge ' + (accepted ? 'success' : request?.status === 'recusada' ? 'danger' : request ? 'warning' : 'info');
        set('[data-sponsorship-account]', accountLabels[state.account]);
        show('[data-sponsorship-correction]', canCorrect);
        show('[data-sponsorship-correction-field]', !accepted);
        correctionInput.disabled = accepted;
        show('[data-sponsorship-required]', state.required && !accepted);
        show('[data-sponsorship-progress]', request?.status === 'ativo');
        const sponsor = request && sponsors[request.sponsor];
        show('[data-sponsorship-mentor-row]', Boolean(sponsor));
        set('[data-sponsorship-mentor]', sponsor?.name ?? '');
        show('[data-sponsorship-applicant-code-row]', Boolean(sponsor?.code && request.status === 'pendente'));
        set('[data-sponsorship-applicant-code]', request?.status === 'pendente' ? sponsor.code ?? '' : '');
        for (const [field, value] of [['requested', request?.date], ['decided', request?.decidedAt]]) {
            const node = root.querySelector('[data-sponsorship-' + field + ']');
            show('[data-sponsorship-' + field + '-row]', Boolean(value));
            node.dateTime = value ?? '';
            node.textContent = value ? date(value) : '';
        }

        let next = 'Acompanhe a situação cadastral.';
        if (canCorrect) next = accepted ? 'Reapresente o cadastro para análise. O vínculo aceito será mantido.' : 'Informe um código elegível e reapresente o cadastro.';
        else if (request?.status === 'pendente') next = 'Aguarde a resposta do padrinho indicado e acompanhe a análise do cadastro.';
        else if (request?.status === 'recusada') next = 'Se a administração solicitar correção, você poderá informar outro código.';
        else if (!request) next = 'Acompanhe a análise. Se for necessário indicar um padrinho, a administração solicitará correção.';
        set('[data-sponsorship-next]', next);

        const history = root.querySelector('[data-sponsorship-history]');
        history.replaceChildren(...state.requests.map(row => {
            const item = document.createElement('li');
            const status = row.status === 'encerrado' && !row.decidedAt ? 'substituida' : row.status;
            item.textContent = sponsors[row.sponsor].name + ' · ' + requestLabels[status] + ' · ' + date(row.decidedAt ?? row.date);
            return item;
        }));
        show('[data-sponsorship-history-section]', state.requests.length > 0);
    }

    function render() {
        let state;
        try {
            state = currentState();
        } catch {
            show('[data-sponsorship-content]', false);
            show('[data-sponsorship-error]', true);
            return;
        }

        show('[data-sponsorship-error]', false);
        show('[data-sponsorship-content]', true);
        const request = latestRequest(state);
        if (role === 'solicitante') {
            renderApplicant(state, request);
            return;
        }

        const sponsor = sponsors[scenario];
        const active = activeFor(state, scenario);
        const metrics = { ...sponsor.metrics };
        if (root.dataset.profileApproved !== 'true') {
            metrics.volunteer = false;
            metrics.blocked ||= root.dataset.profileBlocked === 'true';
        }
        const result = eligibility(metrics, active);
        set('[data-sponsorship-eligibility]', result.label);
        const list = root.querySelector('[data-sponsorship-criteria]');
        list.replaceChildren(...result.criteria.map(([title, ok, value]) => {
            const item = document.createElement('li');
            item.textContent = title + ' · ' + value + ' · ' + (ok === null ? 'Aguardando dados' : ok ? 'Atendido' : 'Não atendido');
            return item;
        }));
        const share = result.label === 'Elegível' && Boolean(sponsor.code);
        show('[data-sponsorship-share]', share);
        set('[data-sponsorship-code]', share ? sponsor.code : '');
        const url = root.querySelector('[data-sponsorship-link]');
        let href;
        if (share) {
            href = new URL(signup, location.origin);
            href.searchParams.set('padrinho', sponsor.code);
            url.href = href;
            url.textContent = href;
        } else {
            url.removeAttribute('href');
            url.textContent = '';
        }
        const received = state.requests.filter(row => row.sponsor === scenario);
        const matching = request?.sponsor === scenario && state.enrolled;
        const history = root.querySelector('[data-sponsorship-received-history]');
        history.replaceChildren(...received.filter(row => row !== request).map(row => {
            const item = document.createElement('li');
            item.textContent = root.dataset.sponsorshipApplicant + ' · ' + requestLabels[row.status] + ' · ' + date(row.date);
            return item;
        }));
        show('[data-sponsorship-received-history]', Boolean(history.childElementCount));
        show('[data-sponsorship-history-links]', scenario === 'principal');
        set('[data-sponsorship-historical-conclusion]', state.historicalConclusion === 'aprovada'
            ? 'Vínculo concluído' : state.historicalConclusion === 'rejeitada' ? 'Vínculo ativo · conclusão recusada' : 'Conclusão pendente de revisão');
        show('[data-sponsorship-received]', matching);
        show('[data-sponsorship-no-requests]', !received.length);
        const linked = matching && ['ativo', 'conclusao_pendente', 'concluido', 'encerrado'].includes(request.status) && request.decidedAt;
        show('[data-sponsorship-active]', linked);
        show('[data-sponsorship-empty-links]', !linked && scenario !== 'principal');
        if (linked) {
            set('[data-sponsorship-link-state]', requestLabels[request.status]);
            set('[data-sponsorship-link-progress]', '0 de 6 meses completos · 0 de 5 participações confirmadas');
        }
        if (matching) {
            set('[data-sponsorship-request-state]', requestLabels[request.status]);
            set('[data-sponsorship-request-date]', date(request.date));
        }
        if (actions) {
            actions.hidden = !matching || request.status !== 'pendente';
            actions.querySelector('[data-sponsorship-decide="accept"]').disabled = !share;
        }
        root.querySelectorAll('[data-sponsorship-preference]').forEach(node => {
            node.hidden = !matching || !state.preferences[node.dataset.group]?.includes(node.dataset.option);
        });
        show('[data-sponsorship-no-preferences]', !Object.values(state.preferences).some(values => values.length));
    }

    root.querySelectorAll('[data-sponsorship-copy]').forEach(button => button.addEventListener('click', async () => {
        const link = root.querySelector('[data-sponsorship-link]');
        const value = button.dataset.sponsorshipCopy === 'code' ? root.querySelector('[data-sponsorship-code]').textContent : link.getAttribute('href');
        try {
            await navigator.clipboard.writeText(value);
            feedback.textContent = 'Copiado para a área de transferência.';
        } catch {
            const target = button.dataset.sponsorshipCopy === 'code' ? root.querySelector('[data-sponsorship-code]') : link;
            const range = document.createRange();
            range.selectNodeContents(target);
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
            feedback.textContent = 'Texto selecionado. Use o comando de copiar do navegador.';
        }
    }));
    root.querySelectorAll('[data-sponsorship-decide]').forEach(button => button.addEventListener('click', () => {
        accept = button.dataset.sponsorshipDecide === 'accept';
        dialog.querySelector('h2').textContent = accept ? 'Aceitar pedido de apadrinhamento?' : 'Recusar pedido de apadrinhamento?';
        set('[data-sponsorship-confirm-text]', accept ? 'O aceite inicia o vínculo. A avaliação cadastral continua com a administração.' : 'A Pessoa poderá acompanhar a recusa e corrigir a indicação quando houver solicitação administrativa.');
        button.focus();
        dialog.showModal();
    }));
    dialog?.querySelector('[data-sponsorship-confirm]')?.addEventListener('click', () => {
        const state = decideRequest(readSponsorship(), scenario, accept);
        const saved = state && saveSponsorship(state);
        dialog.close();
        render();
        feedback.textContent = saved ? (accept ? 'Aceite registrado no histórico.' : 'Recusa registrada no histórico.') : 'O pedido não está mais disponível para esta decisão.';
        feedback.focus();
    });
    function clearCorrectionError() {
        correctionError.hidden = true;
        correctionInput.removeAttribute('aria-invalid');
    }

    correctionInput?.addEventListener('input', clearCorrectionError);
    correction?.addEventListener('submit', event => {
        event.preventDefault();
        feedback.textContent = '';
        clearCorrectionError();

        let outcome;
        try {
            outcome = resubmit(correctionInput.value, currentState());
        } catch {
            render();
            return;
        }
        if (example && outcome?.state) example = outcome.state;
        const saved = outcome?.state && (example || saveSponsorship(outcome.state));
        if (!saved) {
            correctionError.textContent = outcome?.error ?? 'Não foi possível registrar a correção.';
            correctionError.hidden = false;
            if (!correctionInput.disabled) {
                correctionInput.setAttribute('aria-invalid', 'true');
                correctionInput.focus();
            }
            return;
        }
        correction.reset();
        render();
        feedback.textContent = example ? 'Correção registrada.' : 'Correção registrada no histórico. O cadastro não foi enviado para análise.';
        feedback.focus();
    });
    if (root.dataset.profileState === 'falha') {
        show('[data-sponsorship-loading]', false);
        show('[data-sponsorship-error]', true);
        return;
    }
    render();
    show('[data-sponsorship-loading]', false);
    window.addEventListener('pageshow', event => { if (event.persisted) render(); });
}

if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-sponsorship-profile]').forEach(initProfile);
    });
}

