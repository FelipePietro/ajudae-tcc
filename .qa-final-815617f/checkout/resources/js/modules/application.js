import { requestJson } from '../core/http.js';

const dates = new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short' });
const format = value => dates.format(new Date(value));

export function initializeApplication() {
    const wizard = document.querySelector('[data-web-application]');
    if (!wizard) return;

    const steps = [...wizard.querySelectorAll('[data-web-step]')];
    const labels = [...document.querySelectorAll('[data-application-step-label]')];
    const list = wizard.querySelector('[data-agendas-list]');
    const feedback = wizard.querySelector('[data-feedback]');
    const next = wizard.querySelector('[data-next]');
    const retry = wizard.querySelector('[data-retry]');
    const confirm = wizard.querySelector('[data-confirm]');
    const letter = wizard.querySelector('textarea');
    const student = wizard.querySelector('[name="declarou_estudante"]');
    let selected;

    function show(index) {
        steps.forEach((step, position) => { step.hidden = position !== index; });
        labels.forEach((label, position) => {
            label.dataset.stepState = position === index ? 'current' : position < index ? 'complete' : 'future';
            const circle = label.querySelector('[data-progress-circle]');
            circle.classList.toggle('border-[#1a3a2a]', position <= index);
            circle.classList.toggle('border-gray-300', position > index);
            for (const element of [circle, label.querySelector('[data-progress-label]')]) {
                element.classList.toggle('text-[#1a3a2a]', position <= index);
                element.classList.toggle('text-gray-400', position > index);
            }
            if (position === index) label.setAttribute('aria-current', 'step');
            else label.removeAttribute('aria-current');
        });
        feedback.textContent = '';
        steps[index].querySelector('h2').focus();
    }

    async function load() {
        next.disabled = true;
        retry.hidden = true;
        selected = null;
        list.querySelectorAll('label').forEach(label => label.remove());
        feedback.textContent = 'Carregando Agendas…';
        try {
            const { data } = await requestJson(wizard.dataset.agendas);
            for (const agenda of data) {
                const label = document.createElement('label');
                label.className = 'flex items-start gap-2 rounded-xl border border-gray-200 p-3';
                const input = document.createElement('input');
                input.type = 'radio';
                input.name = 'agenda';
                input.disabled = Boolean(agenda.impedimento);
                const horario = `${format(agenda.inicio)} até ${format(agenda.fim)}`;
                const description = document.createElement('span');
                const vagas = agenda.disponiveis === 1 ? '1 vaga disponível' : `${agenda.disponiveis} vagas disponíveis`;
                description.textContent = `${horario} · ${agenda.impedimento ?? vagas}`;
                input.addEventListener('change', () => {
                    selected = { id: agenda.id, horario };
                    next.disabled = false;
                });
                label.append(input, description);
                list.append(label);
            }
            feedback.textContent = data.length ? '' : 'Ainda não há Agendas para este evento.';
        } catch (error) {
            feedback.textContent = error.message;
            retry.hidden = false;
        }
    }

    next.addEventListener('click', () => show(1));
    retry.addEventListener('click', load);
    wizard.querySelectorAll('[data-go]').forEach(button => button.addEventListener('click', () => show(Number(button.dataset.go))));
    wizard.querySelector('[data-review]').addEventListener('click', () => {
        wizard.querySelector('[data-review-agenda]').textContent = selected.horario;
        wizard.querySelector('[data-review-student]').textContent = student.checked ? 'Solicitada' : 'Não solicitada';
        wizard.querySelector('[data-review-letter]').textContent = letter.value.trim() || 'Sem carta.';
        show(2);
    });
    confirm.addEventListener('click', async () => {
        if (confirm.disabled) return;
        confirm.disabled = true;
        wizard.querySelectorAll('[data-go]').forEach(button => { button.disabled = true; });
        feedback.textContent = 'Enviando candidatura…';
        try {
            await requestJson(wizard.dataset.submit, {
                method: 'POST',
                csrfToken: wizard.dataset.csrf,
                body: { agenda_id: selected.id, declarou_estudante: student.checked, carta_apresentacao: letter.value.trim() || null },
            });
            letter.value = '';
            show(3);
        } catch (error) {
            if (error.status === 409) {
                show(0);
                retry.hidden = false;
                next.disabled = true;
            }
            feedback.textContent = Object.values(error.errors ?? {}).flat()[0] ?? error.message;
        } finally {
            confirm.disabled = false;
            wizard.querySelectorAll('[data-go]').forEach(button => { button.disabled = false; });
        }
    });
    wizard.querySelector('[data-another]').addEventListener('click', () => { show(0); load(); });
    load();
}

export function initializeApplicationHistory() {
    const root = document.querySelector('[data-application-history]');
    if (!root) return;
    const list = root.querySelector('[data-history-list]');
    const status = root.querySelector('[data-history-status]');
    const more = root.querySelector('[data-history-more]');
    const dialog = root.querySelector('dialog');
    const withdraw = dialog.querySelector('[data-withdraw]');
    let next = root.dataset.url;
    let pending;

    async function load() {
        more.disabled = true;
        status.textContent = 'Carregando candidaturas…';
        try {
            const { data } = await requestJson(next);
            for (const row of data.data) {
                const card = root.querySelector('template').content.cloneNode(true);
                card.querySelector('h2').textContent = row.evento;
                card.querySelector('[data-time]').textContent = format(row.inicio);
                const state = card.querySelector('[data-state]');
                state.textContent = row.status;
                const letter = card.querySelector('details');
                letter.hidden = !row.carta;
                letter.querySelector('p').textContent = row.carta ?? '';
                const button = card.querySelector('button');
                button.hidden = !row.desistir_url;
                button.addEventListener('click', () => {
                    pending = { url: row.desistir_url, state, button };
                    dialog.showModal();
                });
                list.append(card);
            }
            next = data.next_page_url;
            more.textContent = 'Carregar mais';
            status.textContent = list.children.length ? '' : 'Você ainda não tem candidaturas.';
        } catch (error) {
            status.textContent = error.message;
            more.textContent = 'Tentar novamente';
        } finally {
            more.hidden = !next;
            more.disabled = false;
        }
    }

    withdraw.addEventListener('click', async () => {
        if (withdraw.disabled || !pending) return;
        withdraw.disabled = true;
        const action = pending;
        const errorMessage = dialog.querySelector('[data-withdraw-error]');
        errorMessage.textContent = '';
        try {
            const response = await requestJson(action.url, { method: 'DELETE', csrfToken: root.dataset.csrf });
            dialog.close();
            action.state.textContent = 'desistente';
            action.button.hidden = true;
            status.textContent = response.message;
            status.tabIndex = -1;
            status.focus();
        } catch (error) {
            errorMessage.textContent = error.message;
        } finally {
            withdraw.disabled = false;
        }
    });
    dialog.addEventListener('close', () => {
        pending = null;
        dialog.querySelector('[data-withdraw-error]').textContent = '';
    });
    more.addEventListener('click', load);
    load();
}
