import { requestJson } from '../core/http.js';

const labels = {
    convite_pendente: 'Convite pendente',
    ativo: 'Vínculo ativo',
    recusado: 'Convite recusado',
    encerrado: 'Vínculo encerrado',
};

export function initializeOrganizerLinks() {
    const root = document.querySelector('[data-web-organizer-links]');
    if (!root) return;

    const feedback = root.querySelector('#organizer-feedback');
    const more = root.querySelector('[data-links-more]');
    const dialog = root.querySelector('dialog');
    const dialogFeedback = dialog.querySelector('[data-dialog-feedback]');
    const confirm = dialog.querySelector('[data-link-confirm]');
    const groups = Object.fromEntries([...root.querySelectorAll('[data-link-status]')].map(
        list => [list.dataset.linkStatus, list],
    ));
    const csrfToken = root.dataset.csrf;
    let next = root.dataset.url;
    let selected;
    let pendingLoad;
    let resetLists = false;

    function addAction(row, selector, action) {
        const button = row.querySelector(selector);
        button.hidden = false;
        row.querySelector('[data-link-actions]').hidden = false;
        button.addEventListener('click', () => {
            selected = action;
            dialog.querySelector('h2').textContent = action.title;
            dialogFeedback.textContent = '';
            dialog.showModal();
        });
    }

    function addLink(link) {
        const row = root.querySelector('template').content.cloneNode(true);
        row.querySelector('[data-link-name]').textContent = link.nome;
        row.querySelector('[data-link-label]').textContent = labels[link.status] ?? link.status;
        if (link.responder) {
            addAction(row, '[data-link-accept]', { url: link.responder, method: 'POST', body: { aceitar: true }, title: 'Aceitar este convite?' });
            addAction(row, '[data-link-decline]', { url: link.responder, method: 'POST', body: { aceitar: false }, title: 'Recusar este convite?' });
        }
        if (link.encerrar) {
            addAction(row, '[data-link-end]', { url: link.encerrar, method: 'DELETE', title: 'Encerrar este vínculo?' });
        }
        (groups[link.status] ?? groups.historico).append(row);
    }

    async function load(message = '', reset = false) {
        if (reset) {
            next = root.dataset.url;
            resetLists = true;
        }
        pendingLoad?.abort();
        const current = new AbortController();
        pendingLoad = current;
        more.disabled = true;
        feedback.textContent = 'Carregando vínculos…';
        try {
            const { data } = await requestJson(next, { signal: current.signal });
            if (pendingLoad !== current) return;
            if (resetLists) {
                Object.values(groups).forEach(list => list.replaceChildren());
                resetLists = false;
            }
            data.data.forEach(addLink);
            Object.values(groups).forEach(list => { list.nextElementSibling.hidden = Boolean(list.children.length); });
            next = data.next_page_url;
            more.textContent = 'Carregar mais';
            feedback.textContent = message;
        } catch (error) {
            if (pendingLoad !== current || error.name === 'AbortError') return;
            feedback.textContent = message ? `${message} ${error.message}` : error.message;
            more.textContent = 'Tentar novamente';
        } finally {
            if (pendingLoad === current) {
                more.hidden = !next;
                more.disabled = false;
            }
        }
    }

    const form = root.querySelector('[data-web-organizer-invite]');
    form?.addEventListener('submit', async event => {
        event.preventDefault();
        const button = form.querySelector('button[type="submit"]');
        if (button.disabled) return;
        button.disabled = true;
        feedback.textContent = 'Enviando solicitação…';
        try {
            const { message } = await requestJson(form.action, {
                method: 'POST',
                body: { identificador: form.elements.identificador.value.trim() },
                csrfToken,
            });
            form.reset();
            await load(message, true);
        } catch (error) {
            feedback.textContent = error.errors?.identificador?.[0] ?? error.message;
        } finally {
            button.disabled = false;
        }
    });

    confirm.addEventListener('click', async () => {
        if (confirm.disabled || !selected) return;
        confirm.disabled = true;
        dialogFeedback.textContent = 'Registrando decisão…';
        try {
            const { url, method, body } = selected;
            const { message } = await requestJson(url, { method, body, csrfToken });
            dialog.close();
            await load(message, true);
            feedback.focus();
        } catch (error) {
            dialogFeedback.textContent = error.message;
        } finally {
            confirm.disabled = false;
        }
    });
    dialog.addEventListener('cancel', event => {
        if (confirm.disabled) event.preventDefault();
    });
    dialog.querySelector('form').addEventListener('submit', event => {
        if (confirm.disabled) event.preventDefault();
    });
    more.addEventListener('click', () => load());
    load();
}
