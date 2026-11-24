import { requestJson } from '../core/http.js';

function fieldError(field, message) {
    const slot = field.closest('.account-field')?.querySelector('[data-field-error]');
    if (slot) slot.textContent = message;
    if (message) field.setAttribute('aria-invalid', 'true');
    else field.removeAttribute('aria-invalid');
}

export function initializePessoaRegistration() {
    const wizard = document.querySelector('[data-web-registration]');
    if (!wizard) return;

    const form = wizard.querySelector('form');
    const steps = [...form.querySelectorAll('[data-step]')];
    const navigation = [...wizard.querySelectorAll('[data-step-nav]')];
    const next = form.querySelector('[data-wizard-next]');
    const back = form.querySelector('[data-wizard-back]');
    const error = wizard.querySelector('[data-wizard-error]');
    const progress = wizard.querySelector('[data-step-progress]');
    const count = wizard.querySelector('[data-step-count]');
    const credentials = steps.findIndex(step => step.contains(form.elements.senha_pessoa));
    const lastStep = steps.length - 1;
    let current = 0;
    let reached = 0;
    let sending = false;
    let completed = false;

    const clearPasswords = () => {
        form.elements.senha_pessoa.value = '';
        form.elements.senha_pessoa_confirmation.value = '';
    };
    window.addEventListener('pagehide', clearPasswords);

    function controls() {
        navigation.forEach((button, index) => {
            button.disabled = sending || completed || index > reached;
            button.setAttribute('aria-current', index === current ? 'step' : 'false');
        });
        back.disabled = current === 0 || sending;
        next.disabled = sending;
        next.textContent = sending ? 'Criando conta…' : current === lastStep ? 'Criar conta' : 'Continuar →';
    }

    function review() {
        const summary = form.querySelector('[data-review]');
        summary.replaceChildren();
        for (const [label, value] of [
            ['Nome', form.elements.nm_pessoa.value],
            ['Localidade', `${form.elements.cidade_pessoa.value}, ${form.elements.uf_pessoa.value}`],
            ['Apadrinhamento', form.elements.codigo_padrinho.value ? 'Código de indicação informado' : 'Sem código de indicação'],
        ]) {
            const row = document.createElement('div');
            const term = document.createElement('dt');
            const details = document.createElement('dd');
            term.textContent = label;
            details.textContent = value;
            row.append(term, details);
            summary.append(row);
        }
    }

    function show(index) {
        if (index < credentials && current >= credentials) {
            clearPasswords();
            reached = Math.min(reached, credentials);
        }
        current = index;
        steps.forEach((step, position) => { step.hidden = position !== current; });
        progress.value = current + 1;
        count.textContent = `Passo ${current + 1} de ${steps.length}`;
        error.hidden = true;
        if (current === lastStep) review();
        controls();
        steps[current].querySelector('h1').focus();
    }

    function showError(message, field = null) {
        error.textContent = message;
        error.hidden = false;
        (field ?? error).focus();
    }

    function validate(index) {
        const fields = [...steps[index].querySelectorAll('input, select, textarea')];
        let firstInvalid = null;
        for (const field of fields) {
            let message = '';
            if (!field.checkValidity()) {
                message = field.validity.valueMissing ? 'Preencha este campo para continuar.' : 'Confira o formato informado.';
            } else if (field.name === 'senha_pessoa_confirmation' && field.value !== form.elements.senha_pessoa.value) {
                message = 'As senhas precisam coincidir.';
            }
            fieldError(field, message);
            if (message) firstInvalid ??= field;
        }
        return firstInvalid;
    }

    async function send() {
        for (const index of steps.keys()) {
            const field = validate(index);
            if (field) {
                show(index);
                showError('Revise o campo indicado antes de continuar.', field);
                return;
            }
        }
        sending = true;
        controls();
        form.setAttribute('aria-busy', 'true');
        error.hidden = true;

        try {
            const fields = new FormData(form);
            const csrfToken = fields.get('_token');
            fields.delete('_token');
            await requestJson(form.action, { method: 'POST', body: fields, csrfToken });
            completed = true;
            form.reset();
            form.hidden = true;
            const confirmation = wizard.querySelector('[data-completion]');
            confirmation.hidden = false;
            confirmation.focus();
        } catch (failure) {
            let first;
            for (const [name, messages] of Object.entries(failure.errors ?? {})) {
                const field = form.elements.namedItem(name);
                if (!(field instanceof HTMLElement)) continue;
                fieldError(field, messages[0]);
                first ??= field;
            }
            if (first) show(steps.indexOf(first.closest('[data-step]')));
            showError(failure.message, first);
        } finally {
            sending = false;
            form.removeAttribute('aria-busy');
            controls();
        }
    }

    function advance() {
        if (sending || completed) return;
        const invalid = validate(current);
        if (invalid) {
            showError('Revise o campo indicado antes de continuar.', invalid);
            return;
        }
        if (current === lastStep) {
            send();
            return;
        }
        reached = Math.max(reached, current + 1);
        show(current + 1);
    }

    next.addEventListener('click', advance);
    back.addEventListener('click', () => show(current - 1));
    form.addEventListener('submit', event => {
        event.preventDefault();
        advance();
    });
    navigation.forEach((button, index) => button.addEventListener('click', () => {
        if (index < current) {
            show(index);
            return;
        }
        for (let position = current; position < index; position++) {
            const field = validate(position);
            if (field) {
                show(position);
                showError('Revise o campo indicado antes de continuar.', field);
                return;
            }
        }
        show(index);
    }));
}
