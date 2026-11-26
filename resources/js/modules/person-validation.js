import { requestJson } from '../core/http.js';
import { validatePrivateDocument } from './private-document-file.js';

const validationLabels = {
    aguardando_validacao: ['Aguardando validação', 'info'],
    em_analise: ['Cadastro em análise', 'info'],
    correcao_solicitada: ['Correção solicitada', 'danger'],
    aprovado: ['Cadastro aprovado', 'success'],
    reprovado: ['Cadastro reprovado', 'danger'],
    reprovado_definitivamente: ['Cadastro reprovado definitivamente', 'danger'],
};
const documentLabels = {
    pendente: ['Aguardando análise', 'warning'],
    aprovado: ['Validado', 'success'],
    rejeitado: ['Precisa de correção', 'danger'],
};
const localDate = value => new Date(value).toLocaleDateString('pt-BR');

export function initializePersonValidation() {
    const root = document.querySelector('[data-own-profile]');
    if (!root) return;

    const documents = [...root.querySelectorAll('[data-private-document]')];
    const feedback = [...root.querySelectorAll('[data-document-feedback]')];
    const resubmit = root.querySelector('[data-validation-resubmit]');
    const csrfToken = root.dataset.csrf;
    const base = '/conta/pessoa';
    let busy = false;
    let loaded = false;

    function report(message) {
        feedback.forEach(slot => { slot.textContent = message; });
    }

    function render(person, antecedents) {
        const [status, tone] = validationLabels[person.status_validacao] ?? ['Situação indisponível', 'info'];
        for (const card of root.querySelectorAll('[data-live-validation]')) {
            card.className = `account-status ${tone}`;
            card.querySelector('h2').textContent = status;
            card.querySelector('.account-status-symbol').textContent = tone === 'success' ? '✓' : '◷';
        }

        const blocked = person.bloqueado_ate && Date.parse(person.bloqueado_ate) > Date.now();
        const block = root.querySelector('[data-validation-block]');
        block.hidden = !blocked;
        block.textContent = blocked ? `Novo envio bloqueado até ${localDate(person.bloqueado_ate)}.` : '';
        root.querySelector('[data-validation-sponsor]').hidden = !person.padrinho_exigido || person.status_validacao === 'aprovado';
        resubmit.hidden = person.role_pessoa !== 'voluntario'
            || blocked || !['correcao_solicitada', 'reprovado'].includes(person.status_validacao);

        for (const article of documents) {
            const type = article.dataset.privateDocument;
            const current = person.documentos.find(document => document.tipo === type);
            const [label, documentTone] = documentLabels[current?.status] ?? ['Nenhum documento enviado', 'info'];
            const state = article.querySelector('[data-document-state]');
            state.className = `account-badge ${documentTone}`;
            state.textContent = label;
            const reason = article.querySelector('[data-document-reason]');
            reason.hidden = !current?.rejection_reason;
            reason.textContent = current?.rejection_reason ?? '';
            const download = article.querySelector('[data-private-download]');
            download.hidden = !current || Boolean(current.arquivo_excluido_em);
            if (download.hidden) download.removeAttribute('href');
            else download.href = `/conta/documentos/${current.documento_pessoa_id}/download`;
            article.querySelector('form').hidden = person.status_validacao === 'reprovado_definitivamente';

            if (type === 'antecedentes_criminais') {
                const renewal = article.querySelector('[data-document-renewal]');
                const validUntil = antecedents?.ultima_aprovacao?.valido_ate;
                renewal.hidden = !validUntil;
                renewal.textContent = !validUntil ? '' : antecedents.renovacao_necessaria
                    ? `Certidão vencida em ${localDate(validUntil)}. Envie uma certidão atualizada.`
                    : `Última certidão aprovada válida até ${localDate(validUntil)}.`;
            }
        }
    }

    async function run(operation = null, errorSlot = null) {
        if (busy) return;
        busy = true;
        const controls = [...documents.map(article => article.querySelector('button')), resubmit,
            ...root.querySelectorAll('[data-document-refresh]')];
        controls.forEach(control => { control.disabled = true; });
        if (errorSlot) errorSlot.textContent = '';
        let message = '';
        try {
            if (operation) {
                report('Enviando…');
                message = (await operation()).message;
            }
            report('Atualizando a situação…');
            const { data: person } = await requestJson(`${base}/validacao`);
            render(person, null);
            loaded = true;
            try {
                const { data: antecedents } = await requestJson(`${base}/antecedentes`);
                render(person, antecedents);
                report(message);
            } catch (error) {
                report([message, `Situação da certidão: ${error.message}`].filter(Boolean).join(' '));
            }
        } catch (error) {
            report([message, errorSlot && !message ? '' : error.message].filter(Boolean).join(' '));
            if (errorSlot && !message) errorSlot.textContent = error.errors?.arquivo?.[0] ?? error.message;
        } finally {
            controls.forEach(control => { control.disabled = false; });
            busy = false;
        }
    }

    for (const article of documents) {
        const form = article.querySelector('form');
        const input = form.elements.arquivo;
        const error = form.querySelector('[data-document-error]');
        input.addEventListener('change', () => {
            input.removeAttribute('aria-invalid');
            error.textContent = '';
        });
        form.addEventListener('submit', event => {
            event.preventDefault();
            const invalid = validatePrivateDocument(input.files[0]);
            if (invalid) {
                error.textContent = invalid;
                input.setAttribute('aria-invalid', 'true');
                input.focus();
                return;
            }
            run(async () => {
                const response = await requestJson(`${base}/documentos/${article.dataset.privateDocument}`,
                    { method: 'POST', body: new FormData(form), csrfToken });
                form.reset();
                return response;
            }, error);
        });
    }
    resubmit.addEventListener('click', () => run(() =>
        requestJson(`${base}/validacao/reenviar`, { method: 'POST', csrfToken })));
    root.querySelectorAll('[data-document-refresh]').forEach(button =>
        button.addEventListener('click', () => run()));

    const loadVisibleTab = () => {
        if (!loaded && ['#documentos', '#validacao'].includes(location.hash)) run();
    };
    window.addEventListener('hashchange', loadVisibleTab);
    loadVisibleTab();
}
