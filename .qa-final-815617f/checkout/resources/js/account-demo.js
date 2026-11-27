import { bindRegistrationReferral } from './sponsorship-demo.js';

// Visual block 1: DOM-only actions. No fetch, storage, file bytes or real credentials.
const bootAccountDemo = () => {
    if (!document.querySelector('[data-visual-one]')) return;
    const all = (selector, root = document) => [...root.querySelectorAll(selector)];
    const clearSecrets = () => all('[data-secret]').forEach(input => { input.value = ''; input.type = 'password'; });
    const announce = (element, text) => { if (element) { element.textContent = text; element.hidden = false; } };
    window.addEventListener('pagehide', () => { clearSecrets(); all('input[type=file]').forEach(input => { input.value = ''; }); });
    window.addEventListener('pageshow', clearSecrets);

    all('[data-document]').forEach(card => {
        const input=card.querySelector('[data-local-file]');const feedback=card.querySelector('[data-file-feedback]');const clear=card.querySelector('[data-file-clear]');const send=card.querySelector('[data-file-send]');
        let selected=false;
        const discard=()=>{selected=false;delete card.dataset.selected;if(input)input.value='';if(clear)clear.hidden=true;if(send)send.hidden=true;};
        const select=(example=false)=>{
            const file=input?.files?.[0];
            if(!example && !file)return;
            if(card.hasAttribute('data-document-review') && (!['application/pdf','image/jpeg','image/png'].includes(file.type) || file.size>10*1024*1024)){
                discard();announce(feedback,'Escolha PDF, JPEG ou PNG de até 10 MiB.');return;
            }
            selected=true;card.dataset.selected='true';
            // Metadata only; clear FileList immediately. Never read file contents or create a preview.
            announce(feedback,example?'Item conferido sem arquivo. Nenhum arquivo enviado.':`${file.name} · ${file.name.split('.').pop().toUpperCase()} · ${(file.size/1024).toFixed(1)} KiB. Arquivo não enviado.`);
            if(input)input.value='';if(clear)clear.hidden=false;if(send)send.hidden=false;
        };
        input?.addEventListener('change',()=>select());
        card.querySelector('[data-file-example]')?.addEventListener('click',()=>select(true));
        clear?.addEventListener('click',()=>{discard();announce(feedback,'Seleção descartada. Nenhum arquivo enviado.');input?.focus();});
        send?.addEventListener('click',()=>{
            if(!selected)return;discard();const badge=card.querySelector('[data-document-status]');if(badge){badge.textContent='Revisão preparada';badge.className='account-badge warning';}
            announce(feedback,'Revisão preparada. Nenhum arquivo foi enviado ou armazenado.');
        });
        window.addEventListener('pagehide',()=>{discard();if(feedback)feedback.textContent='Nenhum arquivo enviado.';});
    });

    all('[data-preferences]').forEach(root => {
        all('[data-preference]', root).forEach(input => {
            input.addEventListener('change', () => {
                const group = input.closest('[data-preference-group]');
                const level = input.closest('.account-preference-option').querySelector('[data-level]');
                if (level) level.disabled = !input.checked;
                group.querySelector('[data-preference-empty]').hidden = !!group.querySelector('[data-preference]:checked');
                announce(root.querySelector('[data-preference-feedback]'), 'Preferências selecionadas.');
            });
        });
    });

    const wizard = document.querySelector('[data-account-wizard]');
    if (wizard) {
        const form = wizard.querySelector('form');
        const steps = all('[data-step]', form);
        const nav = all('[data-step-nav]', wizard);
        const next = wizard.querySelector('[data-wizard-next]');
        const back = wizard.querySelector('[data-wizard-back]');
        const error = wizard.querySelector('[data-wizard-error]');
        const seed = JSON.parse(wizard.querySelector('[data-demo-seed]').textContent);
        const referral = bindRegistrationReferral(wizard);
        let current = 0;
        let reached = 0;
        const review = () => {
            const target = form.querySelector('[data-review]');
            target.replaceChildren();
            const values = [
                ['Nome de apresentação', form.querySelector('[name=nm_pessoa],[name=nome_fantasia]')?.value || 'Não informado'],
                ['Localidade', form.querySelector('[name=cidade_pessoa],[name=cidade_ong]')?.value || 'Não informada'],
                ['Login da conta', form.querySelector('[name=login_pessoa],[name=login_ong]')?.value || 'Não informado'],
                ['Arquivos', 'Nenhum arquivo enviado ou retido'], ['Senha', 'Descartada ao sair da etapa de acesso'],
            ];
            if (wizard.dataset.actor === 'pessoa') values.splice(3, 0, ['Preferências selecionadas', String(form.querySelectorAll('[data-preference]:checked').length)]);
            if (referral) values.splice(4, 0, ['Apadrinhamento', referral.review()]);
            values.forEach(([label, value]) => { const row = document.createElement('div'); const dt = document.createElement('dt'); const dd = document.createElement('dd'); dt.textContent = label; dd.textContent = value; row.append(dt, dd); target.append(row); });
        };
        const show = index => {
            clearSecrets(); current = index; error.hidden = true;
            steps.forEach((step, i) => { step.hidden = i !== current; });
            nav.forEach((button, i) => { button.disabled = i > reached; button.setAttribute('aria-current', i === current ? 'step' : 'false'); });
            wizard.querySelector('[data-step-count]').textContent = `Passo ${current + 1} de ${steps.length}`;
            wizard.querySelector('[data-step-progress]').value = current + 1;
            back.disabled = current === 0;
            next.textContent = current === steps.length - 1 ? 'Concluir revisão →' : 'Continuar →';
            if (current === steps.length - 1) review();
            steps[current].querySelector('h1').focus();
        };
        const validate = () => {
            let valid = true;
            all('[data-demo-field],[data-secret]', steps[current]).forEach(input => {
                let message = '';
                if (!input.checkValidity()) message = input.validity.valueMissing ? 'Preencha este campo para continuar.' : 'Confira o formato informado.';
                if (input.type === 'date' && input.value && input.value > input.max) message = 'O cadastro é exclusivo para maiores de 18 anos.';
                if (input.id === 'demo-password-confirm' && input.value !== form.querySelector('#demo-password').value) message = 'As senhas precisam coincidir.';
                input.setAttribute('aria-invalid', message ? 'true' : 'false');
                const slot = input.closest('.account-field')?.querySelector('[data-field-error]');
                if (slot) slot.textContent = message;
                if (message) valid = false;
            });
            if (steps[current].querySelector('[data-referral-code]') && referral && !referral.validate()) {
                valid = false;
                announce(error, 'Confira o código de indicação ou deixe-o em branco.');
            }
            if (all('[data-required-selection]', steps[current]).some(card => card.dataset.selected !== 'true')) {
                valid = false;
                announce(error, 'Confira os itens obrigatórios antes de continuar.');
            }
            if (current === steps.length - 1 && !form.querySelector('[data-demo-terms]').checked) { valid = false; announce(error, 'Confirme a leitura para concluir a revisão. Nenhum aceite será registrado.'); }
            if (!valid) { if (error.hidden) announce(error, 'Revise os campos indicados antes de continuar.'); error.focus(); }
            return valid;
        };
        next.addEventListener('click', () => {
            if (!validate()) return;
            if (current === steps.length - 1) {
                const issue = referral?.finish();
                if (issue) { announce(error, issue); error.focus(); return; }
                clearSecrets(); all('input[type=file]', form).forEach(input => { input.value = ''; });
                nav.forEach(button => { button.disabled = true; }); form.hidden = true; const complete = wizard.querySelector('[data-completion]'); complete.hidden = false; complete.focus(); return;
            }
            reached = Math.max(reached, current + 1); show(current + 1);
        });
        back.addEventListener('click', () => show(Math.max(0, current - 1)));
        nav.forEach((button, i) => button.addEventListener('click', () => { if (i <= reached) show(i); }));
        wizard.querySelector('[data-fill-example]')?.addEventListener('click', () => {
            all('[data-demo-field]', form).forEach(input => { if (Object.hasOwn(seed, input.name)) { input.value = seed[input.name]; input.setAttribute('aria-invalid', 'false'); const slot = input.closest('.account-field')?.querySelector('[data-field-error]'); if (slot) slot.textContent = ''; } });
            error.hidden = true;
        });
        wizard.querySelector('[data-cnpj-demo]')?.addEventListener('click', () => announce(wizard.querySelector('[data-cnpj-feedback]'), `Informações para conferir: ${seed.nome_fantasia}. Nenhuma consulta externa foi realizada.`));
        form.addEventListener('submit', event => event.preventDefault());
        if (referral?.completed()) {
            show(steps.length - 1);
            nav.forEach(button => { button.disabled = true; });
            form.hidden = true;
            const complete = wizard.querySelector('[data-completion]');
            complete.hidden = false;
            complete.focus();
        }
    }

    const tabs = all('[data-tab]');
    const selectTab = name => {
        if (!tabs.some(tab => tab.dataset.tab === name)) return;
        tabs.forEach(tab => { if (tab.dataset.tab === name) tab.setAttribute('aria-current', 'page'); else tab.removeAttribute('aria-current'); });
        all('[data-tab-panel]').forEach(panel => { panel.hidden = panel.dataset.tabPanel !== name; });
    };
    tabs.forEach(tab => tab.addEventListener('click', () => selectTab(tab.dataset.tab)));
    all('[data-open-tab]').forEach(link => link.addEventListener('click', () => selectTab(link.dataset.openTab)));
    selectTab(window.location.hash.slice(1) || 'resumo');
    window.addEventListener('hashchange', () => selectTab(window.location.hash.slice(1)));
    all('[data-demo-resubmit]').forEach(button => button.addEventListener('click', () => announce(button.nextElementSibling, 'Revisão concluída. Nenhum documento foi enviado para análise.')));

    const login = document.querySelector('[data-demo-login]');
    if (login) {
        const secret = login.querySelector('[data-secret]');
        const feedback = login.querySelector('[data-login-feedback]');
        login.querySelector('[data-password-toggle]').addEventListener('click', event => { secret.type = secret.type === 'password' ? 'text' : 'password'; event.currentTarget.textContent = secret.type === 'password' ? 'Mostrar' : 'Ocultar'; event.currentTarget.setAttribute('aria-label', `${secret.type === 'password' ? 'Mostrar' : 'Ocultar'} senha`); });
        let entering = false;
        login.addEventListener('submit', event => {
            event.preventDefault();
            if (entering) return;
            const invalid = !login.querySelector('[name=login]').value.trim() || !secret.value;
            if (invalid || login.querySelector('[data-login-error]')?.checked) { clearSecrets(); announce(feedback, invalid ? 'Preencha login e senha para continuar.' : 'Não foi possível entrar com os dados informados.'); feedback.focus(); return; }
            entering = true;
            login.querySelector('[data-login-enter]').disabled = true;
            clearSecrets(); const type = login.querySelector('[name=tipo_login]:checked').value; window.location.href = login.querySelector('[data-login-targets]').dataset[type];
        });
        window.addEventListener('pageshow', () => { entering = false; login.querySelector('[data-login-enter]').disabled = false; });
        login.querySelector('[data-recovery]').addEventListener('click', event => { event.preventDefault(); announce(login.querySelector('[data-recovery-feedback]'), 'A recuperação de senha não está disponível no momento.'); });
        all('[name=tipo_login]', login).forEach(radio => radio.addEventListener('change', () => { clearSecrets(); feedback.hidden = true; login.querySelector('[name=login]').value = radio.value === 'ong' ? 'ong.exemplo' : 'alex.exemplo'; }));
    }

    all('[data-demo-edit]').forEach(form => {
        form.addEventListener('submit', event => {
            event.preventDefault();
            let invalid = null;
            all('[data-demo-field]', form).forEach(input => {
                const valid = input.checkValidity();
                input.setAttribute('aria-invalid', String(!valid));
                const message = input.closest('.account-field')?.querySelector('[data-field-error]');
                if (message) message.textContent = valid ? '' : input.validity.valueMissing ? 'Preencha este campo.' : 'Confira o formato informado.';
                if (!valid && !invalid) invalid = input;
            });
            announce(form.querySelector('[data-edit-feedback]'), invalid ? 'Revise os campos obrigatórios e os formatos informados.' : 'Revisão concluída. As alterações não foram enviadas.');
            if (invalid) { invalid.setAttribute('aria-invalid', 'true'); invalid.focus(); }
        });
    });
    const dialog = document.querySelector('[data-password-dialog]');
    if (dialog) {
        const open = document.querySelector('[data-open-password]');
        open.addEventListener('click', () => { clearSecrets(); dialog.showModal(); });
        dialog.querySelector('[data-close-password]')?.addEventListener('click', () => dialog.close());
        dialog.addEventListener('close', () => { clearSecrets(); dialog.querySelector('[data-change-feedback]').textContent = ''; open.focus(); });
        dialog.addEventListener('cancel', clearSecrets);
        dialog.querySelector('[data-change-password]').addEventListener('click', () => { const values = all('[data-secret]', dialog).map(input => input.value); const valid = values[0].length >= 8 && values[0] === values[1]; clearSecrets(); announce(dialog.querySelector('[data-change-feedback]'), valid ? 'Campos conferidos e descartados. Sua senha não foi alterada.' : 'Use duas senhas iguais com pelo menos 8 caracteres. Os campos foram descartados.'); });
    }
};
document.addEventListener('DOMContentLoaded', bootAccountDemo);
