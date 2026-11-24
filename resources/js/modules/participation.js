import { requestJson } from '../core/http.js';

const formatDate = new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short' });

function renderParticipation(root, event) {
    const card = root.querySelector('[data-participation-template]').content.cloneNode(true);
    card.querySelector('[data-event-title]').textContent = event.titulo;
    const agendas = card.querySelector('[data-event-agendas]');
    for (const agenda of event.agendas) {
        const item = document.createElement('li');
        item.textContent = `${formatDate.format(new Date(agenda.inicio))} · ${agenda.situacao}`;
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
    const root = document.querySelector('[data-web-participation]');
    if (!root) return;

    const status = root.querySelector('[data-participation-status]');
    const list = root.querySelector('[data-participation-list]');
    const button = root.querySelector('[data-participation-next]');
    let next = root.dataset.url;
    async function load() {
        if (!next || button.disabled) return;
        button.disabled = true;
        status.textContent = 'Carregando participações…';
        list.setAttribute('aria-busy', 'true');
        try {
            const { data: page } = await requestJson(next);
            for (const event of page.data) list.append(renderParticipation(root, event));
            next = page.next_page_url;
            status.textContent = list.children.length ? '' : 'Você ainda não tem participações para acompanhar.';
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
