// Synthetic workflow only. Tab storage provides continuity, never security or legal persistence.
export const privacyKey = 'ajudae.privacidade.v1';
export const clock = '2026-11-18T12:00:00.000Z';
export const types = { acesso: 'Consultar ou obter dados', informacao: 'Informações sobre tratamento', correcao: 'Análise de correção', portabilidade: 'Portabilidade' };
export const states = { pendente: 'Recebido', em_analise: 'Em análise', concluida: 'Concluído', recusada: 'Negado' };
const answers = { orientacao: 'Orientação sobre os canais e dados disponíveis.', complemento: 'Necessária análise complementar da titularidade e do escopo.' };
const owners = ['pessoa:1', 'ong:1'];
const scenarios = ['livre', 'pendencias', 'pendente', 'prazo-encerrado', 'anonimizada', 'vazio', 'falha', 'carregando'];
export function initialAccount(scenario) {
    return { status: ['pendente', 'prazo-encerrado'].includes(scenario) ? 'pendente' : scenario === 'anonimizada' ? 'anonimizada' : 'ativa',
        requested: ['pendente', 'prazo-encerrado'].includes(scenario) ? '2026-11-14T12:00:00.000Z' : null, history: [] };
}
export function deleteAccount(account, action, config) {
    const now = Date.parse(config.scenario === 'prazo-encerrado' ? '2026-11-22T12:00:00.000Z' : config.clock);
    if (action === 'solicitar' && (account.status !== 'ativa' || config.blockers.length)) return null;
    if (action === 'reverter' && (account.status !== 'pendente' || now >= Date.parse(account.requested) + 7 * 86400000)) return null;
    if (!['solicitar', 'reverter'].includes(action)) return null;
    const status = action === 'solicitar' ? 'pendente' : 'ativa';
    return { status, requested: action === 'solicitar' ? config.clock : null,
        history: [...account.history, { from: account.status, to: status, actor: config.owner, at: new Date(now).toISOString() }] };
}
export function requestDecision(row, action, answer, canRespond) {
    if (!canRespond || !row || !owners.includes(row.owner)) return null;
    const next = action === 'assumir' && row.status === 'pendente' ? 'em_analise'
        : row.status === 'em_analise' && ['concluida', 'recusada'].includes(action) && Object.hasOwn(answers, answer) ? action : null;
    if (!next) return null;
    return { ...row, status: next, history: [...row.history, { from: row.status, to: next, actor: 'administrador:1', at: clock, answer: action === 'assumir' ? null : answer }] };
}
export function readPrivacy(storage) {
    // Strictly project known metadata. Never render arbitrary strings or URLs from storage.
    const raw = JSON.parse(storage.getItem(privacyKey) || '{"requests":[],"accounts":{}}');
    if (!raw || !Array.isArray(raw.requests) || typeof raw.accounts !== 'object' || !raw.accounts) throw new Error('Estado inválido');
    const validHistory = h => Array.isArray(h) && h.length <= 100 && h.every(e => e && ['ativa', 'pendente', 'anonimizada', ...Object.keys(states)].includes(e.from) && ['ativa', ...Object.keys(states)].includes(e.to) && [...owners, 'administrador:1'].includes(e.actor) && Number.isFinite(Date.parse(e.at)) && (!e.answer || Object.hasOwn(answers, e.answer)));
    const requests = raw.requests.filter(r => r && /^EXEMPLO-(P|O)-[1-9][0-9]*$/.test(r.id) && owners.includes(r.owner) && scenarios.includes(r.scenario || 'livre') && Object.hasOwn(types, r.type) && Object.hasOwn(states, r.status) && validHistory(r.history))
        .map(r => ({ id: r.id, owner: r.owner, scenario: r.scenario || 'livre', type: r.type, status: r.status, history: r.history.map(({ from, to, actor, at, answer }) => ({ from, to, actor, at, answer })) }));
    const accounts = {};
    for (const [key, a] of Object.entries(raw.accounts)) {
        if (!/^(pessoa:1|ong:1):(livre|pendencias|pendente|prazo-encerrado|anonimizada|vazio|falha|carregando)$/.test(key) || !a || !['ativa', 'pendente', 'anonimizada'].includes(a.status) || !validHistory(a.history) || (a.status === 'pendente' && !Number.isFinite(Date.parse(a.requested)))) continue;
        accounts[key] = { status: a.status, requested: a.status === 'pendente' ? a.requested : null, history: a.history.map(({ from, to, actor, at }) => ({ from, to, actor, at })) };
    }
    return { requests: [...new Map(requests.map(r => [r.id, r])).values()], accounts };
}

function bootPrivacy() {
    let store, failed = false;
    try { store = readPrivacy(sessionStorage); } catch { store = { requests: [], accounts: {} }; failed = true; }
    const root = document.querySelector('[data-privacy-root]');
    if (!root) { applyPublicState(store); return; }
    const config = JSON.parse(root.querySelector('[data-privacy-config]').textContent);
    const feedback = root.querySelector('[data-privacy-feedback]');
    const announce = message => { feedback.textContent = message; feedback.focus(); };
    const key = `${config.owner}:${config.scenario}`;
    let account = store.accounts[key] || initialAccount(config.scenario);
    const unavailable = failed || ['falha', 'carregando'].includes(config.scenario);
    if (unavailable) announce(failed ? 'Não foi possível consultar o histórico. Nenhuma alteração será registrada.' : config.scenario === 'falha' ? 'Não foi possível carregar seus pedidos. Nenhuma alteração foi enviada.' : 'Carregando pedidos. Aguarde antes de fazer alterações.');
    const save = next => {
        try { sessionStorage.setItem(privacyKey, JSON.stringify(next)); store = next; return true; }
        catch { announce('Não foi possível registrar a alteração. O estado anterior foi preservado.'); return false; }
    };
    const text = (parent, tag, value) => { const el = document.createElement(tag); el.textContent = value; parent.append(el); return el; };
    const historyLabel = (event, deletion = false) => `${event.at.slice(0, 10).split('-').reverse().join('/')} · ${event.actor === 'administrador:1' ? 'Análise administrativa' : 'Titular'} · ${deletion ? ({ ativa: 'Ativa', pendente: 'Exclusão pendente' })[event.from] || event.from : states[event.from] || event.from} → ${deletion ? ({ ativa: 'Ativa', pendente: 'Exclusão pendente' })[event.to] || event.to : states[event.to] || event.to}${event.answer ? ' · ' + answers[event.answer] : ''}`;
    const renderAccount = () => {
        const panel = root.querySelector('[data-privacy-deletion]'); if (!panel) return;
        const pending = account.status === 'pendente';
        const now = Date.parse(config.scenario === 'prazo-encerrado' ? '2026-11-22T12:00:00.000Z' : config.clock);
        const deadline = Date.parse(account.requested) + 7 * 86400000;
        const expired = pending && now >= deadline;
        panel.querySelector('[data-privacy-deletion-state]').textContent = pending
            ? `Exclusão pendente · ${expired ? 'Prazo reversível encerrado' : Math.ceil((deadline - now) / 86400000) + ' dias restantes'} · prazo para cancelar: ${new Date(deadline).toLocaleDateString('pt-BR', { timeZone: 'UTC' })}, 12:00 UTC. ${expired ? 'Nenhuma exclusão definitiva foi executada; análise e processamento futuros.' : 'Você pode cancelar dentro desta janela.'}`
            : account.status === 'anonimizada' ? 'Perfil indisponível. O histórico apresenta a conta como anonimizada; esta consulta não executou exclusão de dados.' : 'Conta ativa. Nenhuma exclusão pendente.';
        const blockers = panel.querySelector('[data-privacy-blockers]'); blockers.replaceChildren();
        config.blockers.forEach(b => text(blockers, 'li', b));
        const form = panel.querySelector('[data-privacy-delete-form]');
        if (form) { form.hidden = account.status !== 'ativa'; form.querySelector('button').disabled = unavailable || config.blockers.length > 0; }
        const undo = panel.querySelector('[data-privacy-undo]'); undo.hidden = !pending; undo.disabled = expired || unavailable;
        const history = panel.querySelector('[data-privacy-deletion-history]'); history.replaceChildren();
        account.history.forEach(e => text(history, 'li', historyLabel(e, true)));
    };
    const renderRequests = () => {
        const list = root.querySelector('[data-privacy-request-list]'); if (!list) return;
        const filter = root.querySelector('[data-privacy-filter]').value;
        list.replaceChildren();
        const rows = unavailable ? [] : store.requests.filter(r => r.scenario === config.scenario && (config.admin || r.owner === config.owner) && (!filter || r.status === filter));
        if (!rows.length) text(list, 'p', unavailable ? 'Pedidos indisponíveis neste estado.' : 'Nenhum pedido nesta situação.');
        for (const row of rows) {
            const card = root.querySelector('[data-privacy-request-template]').content.firstElementChild.cloneNode(true);
            card.dataset.privacyRequestId = row.id;
            card.querySelector('h3').textContent = `${row.id} · ${types[row.type]}${config.admin ? ' · ' + (row.owner === 'ong:1' ? 'Organização de exemplo' : 'Pessoa de exemplo') : ''}`;
            card.querySelector('[data-request-status]').textContent = states[row.status];
            row.history.forEach(e => text(card.querySelector('ol'), 'li', historyLabel(e)));
            card.querySelectorAll('[data-request-action]').forEach(b => {
                const action = b.dataset.requestAction;
                b.hidden = !config.respond || (action === 'assumir' ? row.status !== 'pendente' : row.status !== 'em_analise');
                b.addEventListener('click', () => {
                    if (!config.respond) return;
                    if (action === 'assumir') { commitDecision(row.id, action); return; }
                    selected = { id: row.id, action, trigger: b };
                    dialog.querySelector('form').reset(); dialog.showModal();
                });
            }); list.append(card);
        }
    };
    let selected;
    const dialog = root.querySelector('[data-privacy-dialog]');
    const commitDecision = (id, action, answer) => {
        const current = store.requests.find(r => r.id === id);
        const updated = requestDecision(current, action, answer, config.respond && !unavailable);
        if (!updated) return false;
        if (!save({ ...store, requests: store.requests.map(r => r.id === id ? updated : r) })) return false;
        renderRequests(); announce('Revisão registrada no histórico. A resposta não foi enviada.'); return true;
    };
    dialog?.querySelector('form').addEventListener('submit', e => {
        e.preventDefault(); if (!selected) return;
        if (commitDecision(selected.id, selected.action, dialog.querySelector('select').value)) dialog.close();
    });
    dialog?.addEventListener('close', () => {
        // Rendering replaces the card; focus its equivalent action or the feedback after completion.
        const trigger = root.querySelector(`[data-privacy-request-id="${selected?.id}"] [data-request-action="${selected?.action}"]`);
        if (trigger && !trigger.hidden) trigger.focus(); else feedback.focus();
        selected = null;
    });
    root.querySelector('[data-privacy-filter]')?.addEventListener('change', renderRequests);
    const requestForm = root.querySelector('[data-privacy-request]');
    if (requestForm) {
        requestForm.querySelector('button').disabled = unavailable || account.status === 'anonimizada';
        requestForm.addEventListener('submit', e => {
            e.preventDefault(); const type = requestForm.elements.tipo.value;
            if (unavailable || account.status === 'anonimizada' || !Object.hasOwn(types, type)) return;
            if (store.requests.some(r => r.scenario === config.scenario && r.owner === config.owner && r.type === type && ['pendente', 'em_analise'].includes(r.status))) { announce('Já existe um pedido deste tipo em aberto. Acompanhe o histórico.'); return; }
            const prefix = `EXEMPLO-${config.owner === 'ong:1' ? 'O' : 'P'}-`;
            const sequence = Math.max(0, ...store.requests.filter(r => r.id.startsWith(prefix)).map(r => Number(r.id.slice(prefix.length)))) + 1;
            const row = { id: prefix + sequence, owner: config.owner, scenario: config.scenario, type, status: 'pendente', history: [{ from: 'ativa', to: 'pendente', actor: config.owner, at: clock }] };
            if (save({ ...store, requests: [...store.requests, row] })) { renderRequests(); announce(`${row.id}: revisão registrada. A solicitação não foi enviada.`); }
        });
    }
    root.querySelector('[data-privacy-delete-form]')?.addEventListener('submit', e => {
        e.preventDefault();
        if (unavailable || e.currentTarget.querySelector('input').value !== 'EXCLUIR') return;
        const next = deleteAccount(account, 'solicitar', config);
        if (next && save({ ...store, accounts: { ...store.accounts, [key]: next } })) { account = next; window.location.assign(config.pending); }
    });
    root.querySelector('[data-privacy-undo]')?.addEventListener('click', () => {
        if (unavailable) return;
        const next = deleteAccount(account, 'reverter', config);
        if (next && save({ ...store, accounts: { ...store.accounts, [key]: next } })) { account = next; renderAccount(); announce('Solicitação retirada do histórico. Conta ativa; nenhuma exclusão foi efetivada.'); }
    });
    if (!unavailable && !store.accounts[key] && ['pendente', 'prazo-encerrado', 'anonimizada'].includes(config.scenario) && !config.admin) save({ ...store, accounts: { ...store.accounts, [key]: account } });
    renderAccount(); renderRequests();
}

function applyPublicState(store) {
    const url = new URL(window.location.href);
    const context = url.searchParams.get('contexto');
    const scenario = url.searchParams.get('privacidade') || 'livre';
    const account = owner => store.accounts[`${owner}:${scenario}`] || initialAccount(scenario);
    const hidden = owner => ['pendente', 'anonimizada'].includes(account(owner).status);
    // Fail closed for this presentation only. The HTML is synthetic; real projection must be server-authorized.
    const publicPerson = url.pathname === '/perfil' && url.searchParams.get('area') !== 'privada' && (!url.searchParams.has('pessoa') || url.searchParams.get('pessoa') === '1');
    const publicOng = url.pathname === '/ong/perfil' && url.searchParams.get('area') !== 'privada' && (!url.searchParams.has('ong') || url.searchParams.get('ong') === '1');
    if ((publicPerson && hidden('pessoa:1')) || (publicOng && hidden('ong:1'))) {
        const main = document.querySelector('main');
        if (main) { main.replaceChildren(); const heading = document.createElement('h1'); heading.textContent = 'Perfil indisponível'; main.append(heading); }
    }
    if (url.pathname === '/ranking' && hidden('pessoa:1')) {
        document.querySelectorAll('[data-ranking-person="1"]').forEach(el => el.remove());
    }
    if (['pessoa', 'organizador', 'organizador-vinculado', 'ong'].includes(context) && hidden(context === 'ong' ? 'ong:1' : 'pessoa:1')) {
        const main = document.querySelector('main') || document.querySelector('[data-application-wizard]'); if (!main) return;
        const note = document.createElement('p'); note.className = 'account-note'; note.textContent = 'Conta indisponível: novas participações e alterações estão suspensas. ';
        const link = document.createElement('a'); link.href = `${context === 'ong' ? '/ong/privacidade' : '/perfil/privacidade'}?contexto=${context}&privacidade=${scenario}`; link.textContent = 'Acompanhar privacidade'; note.append(link); main.prepend(note);
        // Preserve read-only navigation, filters and all historical ledgers.
        const mutationPage = ['/candidatura', '/minhas-candidaturas', '/minhas-participacoes', '/meus-certificados', '/perfil/editar', '/ong/perfil/editar', '/ong/candidatos', '/organizador'].includes(url.pathname) || url.pathname.startsWith('/ong/eventos');
        if (mutationPage) {
            main.querySelectorAll('button:not([data-tab]), input, textarea, select').forEach(el => { el.disabled = true; });
            for (const eventName of ['click', 'submit']) main.addEventListener(eventName, event => {
                if (eventName === 'submit' || event.target.closest('button:not([data-tab])')) { event.preventDefault(); event.stopImmediatePropagation(); }
            }, true);
        }
    }
}
export function privacyHidden(owner, scenario = 'livre') {
    const account = readPrivacy(sessionStorage).accounts[`${owner}:${scenario}`] || initialAccount(scenario);
    return ['pendente', 'anonimizada'].includes(account.status);
}
if (typeof document !== 'undefined') {
    const signature = () => {
        try { return JSON.stringify(readPrivacy(sessionStorage).accounts); } catch { return 'invalid'; }
    };
    let renderedSignature;
    document.addEventListener('DOMContentLoaded', () => { bootPrivacy(); renderedSignature = signature(); });
    // Refresh restored privacy data only when needed; do not reset unrelated in-memory journeys.
    window.addEventListener('pageshow', event => {
        if (event.persisted && (document.querySelector('[data-privacy-root]') || renderedSignature !== signature())) window.location.reload();
    });
}
