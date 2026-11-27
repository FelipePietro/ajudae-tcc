import { requestJson } from '../core/http.js';

const labels = {
    emitido: 'Certificado disponível.',
    cancelado: 'Evento cancelado.',
    aguarda_evento: 'O certificado depende da finalização do Evento.',
    aguarda_avaliacao: 'A avaliação da sua participação ainda está pendente.',
    aguarda_monitoria: 'A monitoria estudantil ainda precisa ser confirmada nas Agendas correspondentes.',
    disponivel: 'Você já pode gerar seu certificado.',
};
const date = new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeZone: 'UTC' });
const number = new Intl.NumberFormat('pt-BR', { maximumFractionDigits: 2 });

function showSummary(root, certificate) {
    const fields = {
        name: certificate.nome_participante,
        title: certificate.nome_evento,
        duration: `${number.format(certificate.total_minutos / 60)} h (${certificate.total_minutos} min)`,
        issuer: certificate.nome_responsavel,
        date: date.format(new Date(certificate.emitido_em)),
        code: certificate.codigo_verificacao,
    };
    for (const [key, value] of Object.entries(fields)) {
        const target = root.querySelector(`[data-certificate-${key}]`);
        if (target) target.textContent = value ?? '—';
    }
}

function initializeList(root) {
    const list = root.querySelector('[data-web-certificate-list]');
    const status = root.querySelector('[data-web-certificate-status]');
    const template = root.querySelector('[data-web-certificate-template]');
    const more = root.querySelector('[data-web-certificate-more]');
    const dialog = root.querySelector('[data-web-certificate-dialog]');
    let trigger;
    let next = root.dataset.url;

    dialog.addEventListener('close', () => trigger?.focus());

    function render(row) {
        const card = template.content.firstElementChild.cloneNode(true);
        const reason = card.querySelector('[data-web-certificate-reason]');
        const issue = card.querySelector('[data-web-certificate-issue]');
        const result = card.querySelector('[data-web-certificate-result]');
        const preview = card.querySelector('[data-web-certificate-preview]');
        card.querySelector('[data-web-certificate-title]').textContent = row.evento;
        reason.textContent = labels[row.situacao];

        function showIssued(certificate) {
            showSummary(result, certificate);
            result.querySelector('[data-web-certificate-download]').href = certificate.pdf_url;
            result.querySelector('[data-web-certificate-verify]').href = certificate.verificar_url;
            result.hidden = false;
            issue.hidden = true;
            preview.addEventListener('click', () => {
                showSummary(dialog, certificate);
                dialog.querySelector('[data-web-certificate-download]').href = certificate.pdf_url;
                trigger = preview;
                dialog.showModal();
            });
        }

        if (row.certificado) showIssued(row.certificado);
        if (row.emitir_url) {
            issue.hidden = false;
            issue.addEventListener('click', async () => {
                issue.disabled = true;
                reason.textContent = 'Gerando certificado…';
                try {
                    const { data } = await requestJson(row.emitir_url, {
                        method: 'POST',
                        csrfToken: root.dataset.csrf,
                    });
                    showIssued(data);
                    reason.textContent = labels.emitido;
                    preview.focus();
                } catch (error) {
                    reason.textContent = error.message;
                } finally {
                    issue.disabled = false;
                }
            });
        }
        return card;
    }

    async function load() {
        if (!next || more.disabled) return;
        more.disabled = true;
        status.textContent = 'Carregando certificados…';
        list.setAttribute('aria-busy', 'true');
        try {
            const { data } = await requestJson(next);
            list.append(...data.data.map(render));
            next = data.next_page_url;
            status.textContent = list.children.length ? '' : 'Nenhum certificado ou participação concluída para exibir.';
            more.textContent = 'Carregar mais';
            if (!next && document.activeElement === more) {
                status.tabIndex = -1;
                status.focus();
            }
        } catch (error) {
            status.textContent = error.message;
            more.textContent = 'Tentar novamente';
        } finally {
            list.removeAttribute('aria-busy');
            more.hidden = !next;
            more.disabled = false;
        }
    }

    more.addEventListener('click', load);
    load();
}

function initializeVerification(root) {
    const form = root.querySelector('form');
    const input = form.elements.codigo;
    const button = form.querySelector('button[type="submit"]');
    const message = root.querySelector('[data-verification-message]');
    const result = root.querySelector('[data-certificate-result]');

    form.addEventListener('submit', async submit => {
        submit.preventDefault();
        if (button.disabled) return;
        button.disabled = true;
        message.textContent = 'Verificando certificado…';
        result.hidden = true;
        try {
            const url = new URL(root.dataset.url, window.location.href);
            url.searchParams.set('codigo', input.value.trim());
            const { data } = await requestJson(url);
            showSummary(result, data);
            message.textContent = 'Certificado válido.';
            result.hidden = false;
        } catch (error) {
            message.textContent = error.status === 404
                ? 'Nenhum certificado encontrado para este código.'
                : error.errors?.codigo?.[0] ?? error.message;
        } finally {
            button.disabled = false;
        }
    });
    const code = new URLSearchParams(window.location.search).get('codigo');
    if (code !== null) {
        input.value = code;
        form.requestSubmit();
    }
}

export function initializeCertificates() {
    const list = document.querySelector('[data-web-certificates]');
    if (list) initializeList(list);
    const verification = document.querySelector('[data-web-certificate-verification]');
    if (verification) initializeVerification(verification);
}
