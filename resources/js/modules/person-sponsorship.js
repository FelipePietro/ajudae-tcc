import { requestJson } from '../core/http.js';

const base = '/conta/pessoa/padrinhos';
const labels = {
    pendente: 'Aguardando resposta', aceita: 'Aceita',
    recusada: 'Recusada', cancelada: 'Cancelada', ativo: 'Ativo',
    encerrado: 'Encerrado', concluido: 'Concluído',
};
const dateLabel = value => value ? new Date(value).toLocaleDateString('pt-BR') : '';

export function initializePersonSponsorship() {
    const root = document.querySelector('[data-web-sponsorship]');
    if (!root) return;

    const profile = root.closest('[data-own-profile]');
    const personId = Number(profile.dataset.personId);
    const csrfToken = profile.dataset.csrf;
    const feedback = root.querySelector('[data-sponsor-feedback]');
    const eligibility = root.querySelector('[data-sponsor-eligibility]');
    const code = root.querySelector('[data-sponsor-code]');
    const shareLink = root.querySelector('[data-sponsor-link]');
    const issue = root.querySelector('[data-sponsor-issue]');
    const revoke = root.querySelector('[data-sponsor-revoke]');
    const referral = root.querySelector('[data-sponsor-referral]');
    const directory = root.querySelector('[data-sponsor-directory]');
    const pages = new Map();
    let busy = false;
    let visited = false;

    const get = async (path) => (await requestJson(path)).data;
    const write = (path, method = 'POST', body) => requestJson(`${base}/${path}`, { method, body, csrfToken });

    async function run(task, loading = 'Carregando apadrinhamento…', errorSlot = feedback) {
        if (busy) return;
        busy = true;
        root.setAttribute('aria-busy', 'true');
        root.querySelectorAll('button').forEach(button => { button.disabled = true; });
        feedback.textContent = loading;
        if (errorSlot !== feedback) errorSlot.textContent = '';
        try {
            feedback.textContent = await task() ?? '';
        } catch (error) {
            if (errorSlot !== feedback) feedback.textContent = '';
            errorSlot.textContent = error.errors?.codigo_padrinho?.[0] ?? error.errors?.motivo?.[0] ?? error.message;
        } finally {
            root.querySelectorAll('button').forEach(button => { button.disabled = false; });
            root.removeAttribute('aria-busy');
            busy = false;
        }
    }

    function action(container, label, operation) {
        const button = root.querySelector('[data-sponsor-action]').content.firstElementChild.cloneNode(true);
        button.textContent = label;
        button.disabled = busy;
        button.addEventListener('click', operation);
        container.append(button);
    }

    function mutate(path, { method = 'POST', body, reset, errorSlot } = {}) {
        run(async () => {
            const { message } = await write(path, method, body);
            reset?.();
            const refreshMessage = await refresh().catch(error => error.message);
            feedback.focus();
            return [message, refreshMessage].filter(Boolean).join(' ');
        }, 'Registrando…', errorSlot);
    }

    function showLink(row, container, link) {
        run(async () => {
            const linkPath = `vinculos/${link.acompanhamento_id}`;
            const details = await get(`${base}/${linkPath}`);
            const content = root.querySelector('[data-sponsor-vinculo]').content.cloneNode(true);
            const progress = details.progresso;
            const eligibleAt = progress.elegivel_por_tempo_em;
            const count = `${progress.participacoes_no_vinculo} de ${progress.participacoes_necessarias} participações confirmadas.`;
            const date = eligibleAt ? `Prazo mínimo em ${dateLabel(eligibleAt)}.` : '';
            content.querySelector('[data-sponsor-progress]').textContent = [count, date, details.motivo_encerramento].filter(Boolean).join(' ');
            const completion = content.querySelector('[data-sponsor-completion]');
            completion.hidden = details.status !== 'ativo' || Number(details.apadrinhado_id) !== personId
                || !eligibleAt || Date.parse(eligibleAt) > Date.now()
                || progress.participacoes_no_vinculo < progress.participacoes_necessarias;
            completion.querySelector('button').addEventListener('click', () => mutate(`${linkPath}/solicitar-conclusao`));
            const end = content.querySelector('[data-sponsor-end]');
            end.hidden = details.status !== 'ativo';
            content.querySelectorAll('button').forEach(button => { button.disabled = busy; });
            end.addEventListener('submit', event => {
                event.preventDefault();
                mutate(`${linkPath}/encerrar`, {
                    body: { motivo: end.elements.motivo.value.trim() },
                    errorSlot: row.querySelector('[data-sponsor-row-feedback]'),
                });
            });
            container.replaceChildren(content);
            container.hidden = false;
        }, 'Consultando vínculo…', row.querySelector('[data-sponsor-row-feedback]'));
    }

    function renderRow(kind, item) {
        const row = root.querySelector('[data-sponsor-row]').content.firstElementChild.cloneNode(true);
        const title = row.querySelector('[data-sponsor-label]');
        const summary = row.querySelector('[data-sponsor-summary]');
        const actions = row.querySelector('[data-sponsor-actions]');
        if (kind === 'elegiveis') {
            title.textContent = item.nm_pessoa;
            summary.textContent = [item.cidade_pessoa, item.uf_pessoa].filter(Boolean).join(' · ');
            action(actions, 'Solicitar apadrinhamento', () =>
                mutate('solicitacoes', { body: { padrinho_id: item.pessoa_id } }));
        } else {
            const isSponsor = Number(item.padrinho_id) === personId;
            title.textContent = isSponsor ? 'Como padrinho' : 'Como apadrinhado';
            summary.textContent = [labels[item.status] ?? item.status, dateLabel(item.solicitada_em ?? item.aceito_em),
                item.decidida_em && `Resposta em ${dateLabel(item.decidida_em)}`].filter(Boolean).join(' · ');
            if (kind === 'vinculos') {
                const detail = row.appendChild(document.createElement('div'));
                detail.hidden = true;
                action(actions, 'Consultar vínculo', () => showLink(row, detail, item));
            } else if (item.status === 'pendente') {
                const path = `solicitacoes/${item.solicitacao_padrinho_id}`;
                if (kind === 'recebidas') {
                    action(actions, 'Aceitar', () => mutate(`${path}/aceitar`));
                    action(actions, 'Recusar', () => mutate(`${path}/recusar`));
                } else if (!isSponsor) {
                    action(actions, 'Cancelar solicitação', () => mutate(`${path}/cancelar`));
                }
            }
        }
        return row;
    }

    function renderPage(kind, page, append = false) {
        const list = root.querySelector(`[data-sponsor-list="${kind}"]`);
        if (!append) list.replaceChildren();
        list.append(...page.data.map(item => renderRow(kind, item)));
        if (!list.children.length) {
            const empty = document.createElement('li');
            empty.textContent = 'Nenhum registro disponível.';
            list.append(empty);
        }
        pages.set(kind, page.next_page_url ? page.current_page + 1 : null);
        root.querySelector(`[data-sponsor-more="${kind}"]`).hidden = !pages.get(kind);
    }

    async function loadPage(kind, page, append = false) {
        const history = ['solicitacoes', 'vinculos'].includes(kind);
        const data = await get(`${base}/${history ? 'me' : kind}?page=${page}`);
        renderPage(kind, history ? data[kind] : data, append);
    }

    async function loadCode() {
        code.value = '';
        shareLink.value = '';
        code.closest('[data-sponsor-share]').hidden = true;
        issue.hidden = true;
        revoke.hidden = true;
        eligibility.textContent = '';
        try {
            const result = await get(`${base}/codigo`);
            code.value = result.codigo ?? '';
            if (result.codigo) {
                const destination = new URL(root.dataset.registrationUrl, location.origin);
                destination.hash = `codigo=${result.codigo}`;
                shareLink.value = destination.href;
            }
            code.closest('[data-sponsor-share]').hidden = !result.codigo;
            issue.hidden = Boolean(result.codigo);
            revoke.hidden = !result.codigo;
        } catch (error) {
            if (error.status !== 409) throw error;
            eligibility.textContent = error.message;
        }
    }

    async function refresh() {
        const { status_validacao: status, bloqueado_ate: blockedUntil } = await get('/conta/pessoa/validacao');
        referral.hidden = status !== 'correcao_solicitada' || (blockedUntil && Date.parse(blockedUntil) > Date.now());
        directory.hidden = status !== 'aprovado';
        root.querySelector('[data-sponsor-application]').hidden = referral.hidden && directory.hidden;
        const tasks = [
            loadCode(), loadPage('recebidas', 1),
            get(`${base}/me`).then(history => {
                renderPage('solicitacoes', history.solicitacoes);
                renderPage('vinculos', history.vinculos);
            }),
        ];
        if (!directory.hidden) tasks.push(loadPage('elegiveis', 1));
        const results = await Promise.allSettled(tasks);
        const errors = new Set(results.filter(result => result.status === 'rejected').map(result => result.reason.message));
        return [...errors].join(' ');
    }

    issue.addEventListener('click', () => mutate('codigo'));
    revoke.addEventListener('click', () => mutate('codigo', { method: 'DELETE' }));
    root.querySelectorAll('[data-sponsor-copy]').forEach(button => button.addEventListener('click', async () => {
        const copyLink = button.dataset.sponsorCopy === 'link';
        const input = copyLink ? shareLink : code;
        try {
            await navigator.clipboard.writeText(input.value);
            feedback.textContent = copyLink ? 'Link copiado.' : 'Código copiado.';
        } catch {
            input.classList.remove('sr-only');
            input.select();
            feedback.textContent = copyLink ? 'Copie o link selecionado.' : 'Copie o código selecionado.';
        }
    }));
    referral.addEventListener('submit', event => {
        event.preventDefault();
        mutate('indicacao', {
            body: { codigo_padrinho: referral.elements.codigo_padrinho.value.trim() },
            reset: () => referral.reset(),
            errorSlot: referral.querySelector('[role="alert"]'),
        });
    });
    root.querySelectorAll('[data-sponsor-more]').forEach(button => button.addEventListener('click', () => {
        const kind = button.dataset.sponsorMore;
        run(() => loadPage(kind, pages.get(kind), true));
    }));
    root.querySelector('[data-sponsor-refresh]').addEventListener('click', () => run(refresh));
    const loadVisibleTab = () => {
        if (visited || location.hash !== '#apadrinhamento') return;
        visited = true;
        run(refresh);
    };
    window.addEventListener('hashchange', loadVisibleTab);
    loadVisibleTab();
}
