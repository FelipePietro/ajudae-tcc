import { publishNotice } from './notifications-demo.js';
// Presentation-only state. No API, database, storage, credentials or file access.
document.addEventListener('DOMContentLoaded', () => {
    const source = document.querySelector('[data-event-flow]');
    if (!source) return;
    const flow = JSON.parse(source.textContent);
    ['.periodo-btn','.status-btn'].forEach(selector=>{
        const buttons=[...document.querySelectorAll(selector)];
        buttons.forEach(button=>button.addEventListener('click',()=>buttons.forEach(item=>item.setAttribute('aria-pressed',String(item===button)))));
    });
    const find = selector => document.querySelector(selector);
    const all = (selector, root = document) => [...root.querySelectorAll(selector)];
    const say = (selector, message) => { const node = find(selector); if (node) node.textContent = message; };
    const terminal = ['cancelado', 'finalizado'].includes(flow.event_status);
    const wizard = find('[data-application-wizard]');
    if (wizard) {
        let current = 0; let chosen = null;
        const letter = wizard.querySelector('[data-application-letter]');
        const describe = agenda => `Agenda ${agenda.agenda_id} · ${new Date(agenda.data_inicio).toLocaleString('pt-BR')}–${new Date(agenda.data_fim).toLocaleTimeString('pt-BR', {hour:'2-digit', minute:'2-digit'})} · ${Math.max(0,agenda.vagas_agenda-agenda.ocupadas)} vagas disponíveis`;
        const validate = () => {
            const selected = wizard.querySelector('[name=agenda_escolhida]:checked');
            const agenda = flow.agendas.find(item => String(item.agenda_id) === selected?.value);
            const error = !agenda ? 'Escolha uma Agenda disponível.' : !flow.eligible ? 'A candidatura é exclusiva para voluntários aprovados.'
                : agenda.inscricao ? 'Você já possui uma candidatura nesta Agenda.'
                : flow.event_status !== 'ativo' || agenda.status_ativo !== 'ativo' ? 'Esta Agenda não está disponível.'
                : Date.now() >= new Date(agenda.data_inicio).getTime()-3600000 ? 'As candidaturas encerram uma hora antes do início.'
                : agenda.ocupadas >= agenda.vagas_agenda ? 'Todas as vagas foram preenchidas.' : '';
            say('[data-application-feedback]',error);
            if (!error) chosen = agenda;
            return !error;
        };
        const highlight = () => all('[data-agenda-option]',wizard).forEach(card => card.classList.toggle('agenda-selected',!!card.querySelector('input:checked')));
        all('[name=agenda_escolhida]',wizard).forEach(input => input.addEventListener('change',highlight));
        const show = step => {
            current=step; say('[data-application-feedback]','');
            all('[data-application-step]',wizard).forEach(node=>{node.hidden=Number(node.dataset.applicationStep)!==step;});
            all('[data-application-step-label]').forEach(node=>{
                const index=Number(node.dataset.applicationStepLabel);const done=index<step;const active=index===step;
                node.dataset.stepState=done?'complete':active?'current':'future';
                if(active)node.setAttribute('aria-current','step');else node.removeAttribute('aria-current');
                const circle=node.querySelector('[data-progress-circle]');circle.textContent=done?'✓':String(index+1);
                circle.className='w-8 h-8 rounded-full border-2 flex items-center justify-center text-sm '+(done?'bg-[#1a3a2a] border-[#1a3a2a] text-white':active?'bg-white border-[#1a3a2a] text-[#1a3a2a] font-semibold':'bg-white border-gray-300 text-gray-400');
                node.querySelector('[data-progress-label]').className='text-[10px] sm:text-xs text-center leading-tight '+(active?'text-[#1a3a2a] font-semibold':done?'text-[#1a3a2a]':'text-gray-400');
            });
            all('[data-progress-connector]').forEach(node=>{node.classList.toggle('bg-[#1a3a2a]',Number(node.dataset.progressConnector)<step);node.classList.toggle('bg-gray-300',Number(node.dataset.progressConnector)>=step);});
            if(chosen) ['[data-selected-agenda]','[data-review-agenda]','[data-confirmed-agenda]'].forEach(selector=>say(selector,describe(chosen)));
            if(step===2){say('[data-review-letter]',letter.value.trim());wizard.querySelector('[data-review-letter-section]').hidden=!letter.value.trim();}
            wizard.querySelector(`[data-application-step="${step}"] h2`).focus();
        };
        wizard.querySelector('[data-application-next]').addEventListener('click',()=>{if(validate())show(1);});
        wizard.querySelector('[data-apply-agenda]').addEventListener('click',()=>{if(validate())show(2);});
        all('[data-application-back]',wizard).forEach(button=>button.addEventListener('click',()=>show(Math.max(0,current-1))));
        wizard.querySelector('[data-application-change]').addEventListener('click',()=>show(0));
        wizard.querySelector('[data-application-confirm]').addEventListener('click',()=>{
            if(current!==2 || !validate())return;
            publishNotice('candidatura','application:'+chosen.agenda_id,['pessoa:1'],{event:chosen.evento_id});
            publishNotice('recebida','application:'+chosen.agenda_id,[Number(chosen.evento_id)===8?'organizador:1':'ong:1'],{event:chosen.evento_id});
            chosen.inscricao='pendente'; // State stays only in memory. The optional text is never copied to a URL or fixture.
            const radio=wizard.querySelector('[name=agenda_escolhida]:checked');radio.disabled=true;radio.checked=false;
            wizard.querySelector('[data-my-applications]').href=`/minhas-candidaturas?cenario=candidatura-pendente&evento=${chosen.evento_id}&agenda=${chosen.agenda_id}`;
            letter.value='';say('[data-review-letter]','');show(3);highlight();
        });
        wizard.querySelector('[data-application-another]').addEventListener('click',()=>{letter.value='';say('[data-review-letter]','');show(0);});
        window.addEventListener('pagehide',()=>{letter.value='';say('[data-review-letter]','');});
        highlight();
    }

    // One shared postcode formatter for create/edit. Format only; no address lookup.
    all('[data-event-postcode]').forEach(input=>{
        const hint=document.createElement('p');hint.id='event-postcode-hint';hint.className='text-xs mt-2 text-neutral-500';hint.textContent='Oito dígitos, como 01234-567. O formato não comprova o endereço.';input.after(hint);
        const format=()=>{const digits=input.value.replace(/\D/g,'').slice(0,8);input.value=digits.length>5?`${digits.slice(0,5)}-${digits.slice(5)}`:digits;input.setCustomValidity(input.value && digits.length!==8?'Informe um CEP com oito dígitos.':'');};
        input.addEventListener('input',format,true);input.addEventListener('change',format);format();
    });
    const editor = find('[data-agenda-editor]');
    if (editor) {
        const rows = editor.querySelector('[data-agenda-rows]');
        const template = editor.querySelector('[data-agenda-row-template]');
        const agendas = flow.agendas.map(item => ({ ...item }));
        const form = find('[data-event-editor]');
        let structureChanged = false;
        const initial = new Map(all('[name]', form).map(input => [input.name, input.value]));
        const localTime = value => {
            const date = new Date(value); if (Number.isNaN(date.getTime())) return '';
            return `${date.getFullYear()}-${String(date.getMonth()+1).padStart(2,'0')}-${String(date.getDate()).padStart(2,'0')}T${String(date.getHours()).padStart(2,'0')}:${String(date.getMinutes()).padStart(2,'0')}`;
        };
        const validate = () => {
            let message = '';
            all('[data-agenda-row]', rows).forEach((row, index) => {
                const agenda = agendas[index]; const start = row.querySelector('[data-agenda-start]');
                const end = row.querySelector('[data-agenda-end]'); const capacity = row.querySelector('[data-agenda-capacity]');
                const minutes = (new Date(end.value) - new Date(start.value))/60000;
                const valid = start.value && end.value && Number.isFinite(minutes) && minutes >= 30;
                end.setCustomValidity(valid ? '' : 'A duração mínima de uma Agenda é 30 minutos.');
                const enough = Number.isInteger(Number(capacity.value)) && Number(capacity.value) >= Math.max(1, agenda.ocupadas);
                capacity.setCustomValidity(enough ? '' : 'As vagas não podem ser menores que as já ocupadas.');
                row.querySelector('[data-agenda-duration]').textContent = valid ? `${minutes} minutos · ${agenda.ocupadas} vaga(s) já ocupada(s)` : 'Informe pelo menos 30 minutos entre início e término.';
                if (!valid || !enough) message = 'Revise duração e vagas das Agendas.';
                agenda.data_inicio = start.value; agenda.data_fim = end.value; agenda.vagas_agenda = Number(capacity.value);
            });
            const preview = find('#preview-data span:last-child,#preview-edicao-data span:last-child');
            if (preview && agendas[0]) preview.textContent = `${agendas.length} Agenda(s) · ${new Date(agendas[0].data_inicio).toLocaleString('pt-BR')}`;
            say('[data-agenda-capacity-summary]', `${agendas.length} Agenda(s) · soma das capacidades: ${agendas.reduce((sum,item)=>sum+item.vagas_agenda,0)} vagas. Não representa pessoas únicas. Evento: ${form?.querySelector('[name=vagas_evento]')?.value || '—'} vagas informativas, independentes das Agendas.`);
            say('[data-agenda-error]', message); return !message;
        };
        const render = () => {
            rows.replaceChildren();
            agendas.forEach((agenda, index) => {
                const row = template.content.firstElementChild.cloneNode(true);
                row.querySelector('[data-agenda-title]').textContent = `Agenda ${index+1}`;
                row.querySelector('[data-agenda-start]').value = localTime(agenda.data_inicio);
                row.querySelector('[data-agenda-end]').value = localTime(agenda.data_fim);
                row.querySelector('[data-agenda-capacity]').value = agenda.vagas_agenda;
                const remove = row.querySelector('[data-agenda-remove]');
                remove.disabled = terminal || agenda.status_ativo !== 'ativo' || agenda.has_inscricoes || agendas.length === 1;
                if (agenda.has_inscricoes) remove.title = 'Agenda com candidaturas deve preservar seu histórico.';
                all('input', row).forEach(input => { input.disabled = terminal || agenda.status_ativo !== 'ativo'; input.addEventListener('input', () => { structureChanged = true; validate(); }); });
                remove.addEventListener('click', () => { if (remove.disabled) return; agendas.splice(index,1); structureChanged = true; render(); });
                rows.append(row);
            });
            validate();
        };
        editor.querySelector('[data-agenda-add]').disabled = terminal;
        editor.querySelector('[data-agenda-add]').addEventListener('click', () => {
            if (terminal) return;
            agendas.push({ agenda_id: null, data_inicio: flow.agendas[0].data_inicio, data_fim: flow.agendas[0].data_fim, vagas_agenda: 1, ocupadas: 0, has_inscricoes: false, status_ativo:'ativo' });
            structureChanged = true; render();
        });
        form?.addEventListener('submit', event => {
            event.preventDefault(); event.stopImmediatePropagation();
            if (terminal) { say('[data-agenda-error]', 'Este evento está encerrado e não permite alterações.'); return; }
            if (!validate() || !form.checkValidity()) { form.reportValidity(); return; }
            const simple = new Set(['nm_evento','imagem_evento','cat_evento_id','modalidade_evento','vagas_evento','_token','_method']);
            const sensitive = structureChanged || all('[name]', form).some(input => !simple.has(input.name) && (input.type === 'checkbox' ? input.checked !== input.defaultChecked : initial.get(input.name) !== input.value));
            const pending = sensitive || flow.event_status !== 'ativo' || !form.querySelector('[name=_method]');
            const changedPlace=all('[name]',form).some(input=>/local|endereco|cidade|cep|bairro|logradouro/i.test(input.name) && initial.get(input.name)!==input.value);
            const changedTime=agendas.some((a,i)=>new Date(a.data_inicio).getTime()!==new Date(flow.agendas[i]?.data_inicio).getTime() || new Date(a.data_fim).getTime()!==new Date(flow.agendas[i]?.data_fim).getTime());
            if(form.querySelector('[name=_method]') && (sensitive || all('[name]',form).some(input=>initial.get(input.name)!==input.value)))publishNotice(changedTime||changedPlace?'alteracao':'edicao','edit:'+flow.event_id+':'+JSON.stringify(agendas.map(a=>[a.data_inicio,a.data_fim,a.vagas_agenda]))+':'+JSON.stringify(['nm_evento','cidade_evento','bairro_evento','uf_evento','cep_evento','logradouro_evento'].map(name=>new FormData(form).get(name))),['pessoa:1'],{event:flow.event_id});
            if(pending)publishNotice('reanalise','edit:'+flow.event_id,[(new URL(location.href).searchParams.get('contexto')||'ong')+':1'],{event:flow.event_id});
            say('[data-event-status]', pending ? 'Aprovação pendente' : 'Publicado');
            say('[data-event-guidance]', pending ? 'Revisão preparada. Alterações sensíveis exigem nova aprovação; candidaturas existentes são mantidas. Nenhuma alteração foi enviada.' : 'Revisão preparada. Título e imagem não exigem nova aprovação. Nenhuma alteração foi enviada.');
            const modal = find('#modal-sucesso-evento,#modal-sucesso-edicao');
            if (modal) modal.showModal();
        }, true);
        if (terminal && form) all('input,textarea,select,button[type=submit]', form).forEach(input => { input.disabled = true; });
        form?.querySelector('[name=vagas_evento]')?.addEventListener('input',validate);
        render();
    }

    all('[data-event-disclosure]').forEach(details=>{const summary=details.querySelector(':scope > summary');if(summary){summary.setAttribute('aria-expanded',String(details.open));details.addEventListener('toggle',()=>summary.setAttribute('aria-expanded',String(details.open)));}});
    all('[data-candidate-management]').forEach(manager => {
        const managerFlow=JSON.parse(manager.querySelector('[data-candidate-flow]').textContent);
        const terminal=['cancelado','finalizado'].includes(managerFlow.event_status);
        const say=(selector,message)=>{const node=manager.querySelector(selector);if(node)node.textContent=message;};
        const dialog = manager.querySelector('[data-candidate-confirm]');
        let pending = null;
        const update = () => {
            say('[data-pending-count]', String(all('[data-candidate][data-state=pendente]', manager).length));
            say('[data-approved-count]', String(all('[data-candidate][data-state=aprovado]', manager).length));
            managerFlow.agendas.forEach(agenda => {
                const group = manager.querySelector(`[data-candidate-agenda="${agenda.agenda_id}"]`);
                if (group) group.querySelector('[data-capacity-summary]').textContent = `${agenda.ocupadas} de ${agenda.vagas_agenda} vagas ocupadas · ${Math.max(0,agenda.vagas_agenda-agenda.ocupadas)} disponíveis`;
            });
        };
        all('[data-candidate-action]', manager).forEach(button => button.addEventListener('click', () => {
            const card = button.closest('[data-candidate]');
            const agenda = managerFlow.agendas.find(item => String(item.agenda_id) === card.dataset.agendaId);
            if (terminal || agenda.status_ativo !== 'ativo' || card.dataset.state !== 'pendente') return;
            if (button.dataset.candidateAction === 'aprovado' && agenda.ocupadas >= agenda.vagas_agenda) { say('[data-candidate-feedback]', 'Não há vagas disponíveis nesta Agenda.'); return; }
            pending = { card, agenda, target:button.dataset.candidateAction, button };
            dialog.querySelector('[data-candidate-confirm-text]').textContent = button.dataset.candidateAction === 'aprovado' ? 'Marcar esta candidatura como aprovada?' : 'Marcar esta candidatura como recusada?';
            dialog.showModal();
        }));
        dialog.querySelector('[data-cancel-candidate]').addEventListener('click', () => dialog.close());
        dialog.addEventListener('close', () => pending?.button.focus());
        dialog.querySelector('[data-confirm-candidate]').addEventListener('click', () => {
            if (!pending) return;
            const {card,agenda,target}=pending;
            if (target === 'aprovado' && agenda.ocupadas >= agenda.vagas_agenda) { dialog.close(); say('[data-candidate-feedback]', 'Não há vagas disponíveis nesta Agenda.'); return; }
            if (card.dataset.state !== 'pendente') return;
            publishNotice(target==='aprovado'?'selecionada':'recusada','selection:'+card.dataset.candidate,['pessoa:'+card.dataset.personId],{event:Math.floor(Number(agenda.agenda_id)/100)});
            card.dataset.state=target; if (target==='aprovado') agenda.ocupadas++;
            card.querySelector('[data-candidate-state]').textContent=target==='aprovado'?'Aprovada':'Recusada';
            all('[data-candidate-action]',card).forEach(button=>{button.disabled=true;});
            update(); dialog.close(); say('[data-candidate-feedback]','Seleção revisada. A decisão não foi enviada.');
        });
    });

    const own=find('[data-own-applications]');
    if (own) {
        const dialog=own.querySelector('[data-withdraw-dialog]'); let selected=null;
        const filter=()=>{ let count=0; all('[data-own-application]',own).forEach(card=>{card.hidden=own.querySelector('[data-application-filter]').value!=='todos' && card.dataset.state!==own.querySelector('[data-application-filter]').value;if(!card.hidden)count++;});own.querySelector('[data-applications-empty]').hidden=count>0; };
        own.querySelector('[data-application-filter]').addEventListener('change',filter);
        all('[data-withdraw]',own).forEach(button=>button.addEventListener('click',()=>{
            selected=button.closest('[data-own-application]');
            const hours=(new Date(selected.dataset.start)-Date.now())/3600000;
            if(hours<=0 || !['pendente','aprovado'].includes(selected.dataset.state))return;
            const late=hours<24; selected.dataset.late=late?'true':'false';
            const free=selected.dataset.penaltyFree==='true';
            dialog.querySelector('[data-withdraw-explanation]').textContent=(late?'Menos de 24 horas: desistência tardia.':'Pelo menos 24 horas: desistência normal.')+(free?' Há isenção por alteração de data, horário ou local; a classificação temporal é mantida.':'');
            dialog.showModal();
            dialog.querySelector('[data-withdraw-close]').focus();
        }));
        dialog.addEventListener('close',()=>{const trigger=selected?.querySelector('[data-withdraw]');if(trigger?.disabled){selected.tabIndex=-1;selected.focus();}else trigger?.focus();});
        dialog.addEventListener('click',event=>{if(event.target===dialog){const rect=dialog.getBoundingClientRect();if(event.clientX<rect.left||event.clientX>rect.right||event.clientY<rect.top||event.clientY>rect.bottom)dialog.close();}});
        own.querySelector('[data-withdraw-close]').addEventListener('click',()=>dialog.close());
        dialog.querySelector('[data-withdraw-confirm]').addEventListener('click',()=>{
            if(!selected)return;
            publishNotice('desistida','withdraw:'+selected.dataset.ownApplication,['pessoa:1'],{event:flow.event_id});
            selected.dataset.state='desistente'; selected.querySelector('[data-own-state]').textContent='Desistência';
            selected.querySelector('[data-withdraw]').disabled=true;
            selected.querySelector('[data-withdraw-result]').textContent=(selected.dataset.late==='true'?'Desistência tardia. ':'Desistência normal. ')+'Histórico preservado; alteração não enviada.';
            dialog.close();filter();
        });
        filter();
    }
});
