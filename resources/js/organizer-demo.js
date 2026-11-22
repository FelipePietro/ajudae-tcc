import { publishNotice } from './notifications-demo.js';
// Local synthetic presentation. No HTTP, browser storage, tokens or model writes.
const terminal = event => ['finalizado', 'cancelado'].includes(event.status_evento);
const activeLink = (state, personId) => state.links.find(link => link.pessoa_id === personId && link.status === 'ativo');
export function restriction(state, link, action) {
    if (!link) return 'Convite não encontrado.';
    if (action === 'accept') {
        if (link.status !== 'convite_pendente') return 'Este convite já foi respondido.';
        if (state.person.role_pessoa !== 'organizador' || state.person.status_validacao !== 'aprovado') return 'Somente organizadores aprovados podem aceitar.';
        if (activeLink(state, link.pessoa_id)) return 'Você já possui vínculo ativo com uma ONG.';
        if (state.events.some(event => event.pessoa_id === link.pessoa_id && event.ong_id === null && !terminal(event))) return 'Conclua ou cancele seus Eventos independentes antes de aceitar.';
    }
    if (action === 'end') {
        if (link.status !== 'ativo') return 'Somente vínculos ativos podem ser encerrados.';
        if (state.events.some(event => event.ong_id === link.ong_id && event.responsavel_pessoa_id === link.pessoa_id && !terminal(event))) return 'Há Evento não encerrado sob esta responsabilidade. A organização precisa substituir o responsável antes de encerrar o vínculo.';
    }
    return '';
}

export function transition(source, action) {
    const state = structuredClone(source);
    const institution = state.context === 'ong';
    if (action.type === 'request') {
        if (state.context !== 'pessoa' || !state.eligible || ['pendente', 'em_analise', 'aprovada'].includes(state.promotion.status)) throw Error('Não é possível abrir outra solicitação agora.');
        if (!action.justification.trim() || action.justification.length > 2000) throw Error('Informe uma justificativa com até 2.000 caracteres.');
        state.promotion.status = 'pendente'; state.eligible = false;
        const openCriterion=state.criteria.find(item=>item.label==='Sem solicitação aberta');if(openCriterion){openCriterion.met=false;openCriterion.value='Análise ainda não concluída';}
        // Deliberately do not retain the justification in state or URLs.
    } else if (action.type === 'invite') {
        if (!institution || state.organizations[0].status_ong !== 'ativo') throw Error('Somente a conta ativa da organização pode convidar.');
        const person = state.people.find(person => person.pessoa_id === action.personId);
        if (!person || person.role_pessoa !== 'organizador' || person.status_validacao !== 'aprovado') throw Error('Selecione um organizador aprovado.');
        if (activeLink(state, action.personId)) throw Error('Este organizador já possui vínculo ativo.');
        if (state.links.some(link => link.ong_id === 1 && link.pessoa_id === action.personId && link.status === 'convite_pendente')) throw Error('Já existe convite pendente para este organizador.');
        state.links.push({vinculo_ong_organizador_id: Math.max(0,...state.links.map(link=>link.vinculo_ong_organizador_id))+1, ong_id:1, pessoa_id:action.personId, status:'convite_pendente'});
    } else if (action.type === 'responsibility') {
        const event = state.events.find(event => event.evento_id === action.eventId);
        if (!institution || !event || event.ong_id !== 1) throw Error('A organização não é proprietária deste Evento.');
        if (terminal(event) || event.started) throw Error('A responsabilidade não pode ser alterada após o início ou encerramento.');
        if (action.personId !== null) {
            const person = state.people.find(person => person.pessoa_id === action.personId);
            if (!person || person.role_pessoa !== 'organizador' || person.status_validacao !== 'aprovado' || !state.links.some(link => link.pessoa_id===action.personId && link.ong_id===1 && link.status==='ativo')) throw Error('O responsável precisa ser organizador aprovado e vinculado à organização.');
        }
        if (event.responsavel_pessoa_id !== action.personId) {
            event.responsavel_pessoa_id = action.personId;
            if (['ativo','reprovado'].includes(event.status_evento)) event.status_evento = 'aguardando a confirmação';
        }
    } else {
        const link = [...state.links].reverse().find(link => link.ong_id === action.ongId && link.pessoa_id === action.personId);
        if (!link || (institution ? link.ong_id !== 1 : link.pessoa_id !== state.person.pessoa_id)) throw Error('Este vínculo não pertence à conta em uso.');
        if (action.type === 'accept' || action.type === 'decline') {
            if (institution || !['organizador','organizador-vinculado'].includes(state.context) || link.status !== 'convite_pendente') throw Error('Somente o organizador destinatário pode responder ao convite pendente.');
            const reason = action.type === 'accept' ? restriction(state, link, 'accept') : '';
            if (reason) throw Error(reason);
            link.status = action.type === 'accept' ? 'ativo' : 'recusado';
        } else if (action.type === 'end') {
            const reason = restriction(state, link, 'end'); if (reason) throw Error(reason);
            link.status = 'encerrado';
        } else throw Error('Ação indisponível.');
    }
    return state;
}

// Identifiers are fixture aliases only, never an API contract or a real account search.
export function resolveInviteIdentity(people, input) {
    const value=String(input).trim().toLowerCase();
    if(!value) throw Error('Informe o nome de usuário ou e-mail.');
    if(value.includes('@')) {
        if(value.length>128 || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) throw Error('Informe um e-mail válido.');
    } else if(value.length>64) throw Error('O nome de usuário deve ter até 64 caracteres.');
    const person=people.find(person=>person.demo_username===value || person.demo_email===value);
    if(!person) throw Error('Organizador não localizado. Confira o usuário ou e-mail.');
    return person;
}

// Preserve only the existing disposable invitation IDs on refresh, never identity text.
export function invitationUrl(href, ids) {
    const url = new URL(href);
    const safe = [...new Set([...ids].filter(id => [1, 2, 3].includes(id)))].sort();
    if (safe.length) url.searchParams.set('convites', safe.join(','));
    else url.searchParams.delete('convites');
    return url.href;
}

if (typeof document !== 'undefined') document.addEventListener('DOMContentLoaded', () => {
    // Explicit disposable URL state: only synthetic IDs 1..3. No browser storage or raw identifier.
    const raw=new URL(location.href).searchParams.get('convites') || '';
    const carriedIds=new Set(/^[123](,[123]){0,2}$/.test(raw)?raw.split(',').map(Number):[]);
    const carryInvites=(scenario=null)=>{document.querySelectorAll('a[href]').forEach(link=>{
        if(link.getAttribute('href').startsWith('#'))return;
        const url=new URL(link.href,location.href);if(url.origin!==location.origin)return;
        if(carriedIds.size)url.searchParams.set('convites',[...carriedIds].sort().join(','));else url.searchParams.delete('convites');
        if(scenario && !url.searchParams.has('jornada'))url.searchParams.set('jornada',scenario);
        link.href=url.href;
    });history.replaceState(history.state,'',invitationUrl(location.href,carriedIds));};
    carryInvites(document.querySelector('[data-nav-journey]')?.dataset.navJourney || null);
    document.querySelectorAll('[data-organizer-root]').forEach(root => {
        const source = root.querySelector('[data-organizer-state]'); if (!source) return;
        let state = JSON.parse(source.textContent); let pending = null;
        const dialog = root.querySelector('[data-organizer-confirm]');
        const feedback = root.querySelector('[data-organizer-feedback]');
        const tell = message => { feedback.textContent=message; feedback.focus(); };
        const labels = {convite_pendente:'Convite pendente',ativo:'Vínculo ativo',recusado:'Convite recusado',encerrado:'Vínculo encerrado'};
        const render = () => {
            root.querySelectorAll('[data-organizer-link]').forEach(row => {
                const [ongId, personId] = row.dataset.organizerLink.split('-').map(Number);
                const link = [...state.links].reverse().find(link => link.ong_id===ongId && link.pessoa_id===personId);
                const group=row.dataset.linkGroup;
                row.hidden = !link || (group==='sent' && !['convite_pendente','recusado'].includes(link.status)) || (group==='active' && link.status!=='ativo'); if (!link) return;
                row.querySelector('[data-link-status]').textContent = labels[link.status];
                const acceptReason = restriction(state,link,'accept'); const endReason = restriction(state,link,'end');
                row.querySelector('[data-link-restriction]').textContent = link.status==='ativo' ? endReason : (link.status==='convite_pendente' && state.context!=='ong' ? acceptReason : '');
                row.querySelectorAll('[data-link-action]').forEach(button => {
                    const action = button.dataset.linkAction;
                    button.hidden = action==='end' ? link.status!=='ativo' : link.status!=='convite_pendente';
                    button.disabled = action==='end' ? !!endReason : action==='accept' && !!acceptReason;
                });
            });
            root.querySelectorAll('[data-links-history]').forEach(list=>{
                list.replaceChildren(); state.links.filter(link=>['recusado','encerrado'].includes(link.status)).forEach(link=>{
                    const item=document.createElement('li');const person=state.people.find(person=>person.pessoa_id===link.pessoa_id);const ong=state.organizations.find(ong=>ong.ong_id===link.ong_id);
                    item.textContent=person.nm_pessoa+' · '+ong.nome_fantasia+' · '+labels[link.status];list.append(item);
                });
                if(!list.children.length){const item=document.createElement('li');item.textContent='Nenhum vínculo encerrado ou convite recusado.';list.append(item);}
            });
            root.querySelectorAll('[data-link-section]').forEach(section=>section.querySelector('[data-group-empty]').hidden=[...section.querySelectorAll('[data-organizer-link]')].some(row=>!row.hidden));
            carryInvites(state.scenario);
            const linked = !!activeLink(state,state.person.pessoa_id);
            root.querySelectorAll('[data-linked-workspace]').forEach(node=>{
                node.hidden=!linked;
                const url=new URL(node.href);url.searchParams.set('jornada',state.events.some(event=>event.ong_id===1 && event.responsavel_pessoa_id===state.person.pessoa_id)?'vinculado':'vinculado-sem-eventos');node.href=url.href;
            });
            root.querySelectorAll('[data-independent-workspace]').forEach(node=>{
                node.hidden=linked;const last=[...state.links].reverse().find(link=>link.ong_id===1 && link.pessoa_id===state.person.pessoa_id);
                const url=new URL(node.href);url.searchParams.set('jornada',last?.status==='encerrado'?'encerrado':last?.status==='recusado'?'convite-recusado':state.scenario);node.href=url.href;
            });
            root.querySelectorAll('[data-links-empty]').forEach(node=>node.hidden=state.links.length>0);
            root.querySelectorAll('[data-organizer-responsibility]').forEach(form=>{
                const event=state.events.find(event=>event.evento_id===Number(form.dataset.organizerResponsibility));
                form.closest('[data-responsibility-row]').querySelector('[data-responsibility-status]').textContent=event.status_evento==='ativo'?'Publicado':event.status_evento==='aguardando a confirmação'?'Aprovação pendente':event.status_evento==='finalizado'?'Finalizado':'Cancelado';
                form.querySelectorAll('option[value]').forEach(option=>{if(option.value)option.disabled=!state.links.some(link=>link.ong_id===1 && link.pessoa_id===Number(option.value) && link.status==='ativo');});
            });
            root.querySelectorAll('dl.info-list > div').forEach((row,index)=>{const criterion=state.criteria[index];if(criterion)row.querySelector('dd').textContent=(criterion.met?'✓ ':'○ ')+criterion.value;});
            if(state.promotion.status==='pendente') {
                root.querySelectorAll('[data-promotion-form]').forEach(form=>form.hidden=true);
                const status=root.querySelector('[data-promotion-status]');if(status)status.textContent='Aguardando análise';
                const follow=root.querySelector('[data-promotion-follow]');if(follow)follow.hidden=false;
            }
        };
        const review = (action,message) => {
            try { transition(state,action); pending=action; dialog.querySelector('[data-organizer-confirm-text]').textContent=message; dialog.querySelector('h2').textContent=action.type==='request'?'Revisar solicitação':'Revise sua decisão'; const reviewText=dialog.querySelector('[data-organizer-review]');reviewText.hidden=action.type!=='request';reviewText.textContent=action.type==='request'?action.justification.trim():''; dialog.showModal(); }
            catch(error) { tell(error.message); }
        };
        root.querySelectorAll('[data-link-action]').forEach(button=>button.addEventListener('click',()=>{
            const [ongId,personId]=button.closest('[data-organizer-link]').dataset.organizerLink.split('-').map(Number);
            review({type:button.dataset.linkAction,ongId,personId},button.textContent.trim()+'?');
        }));
        root.querySelectorAll('[data-organizer-invite]').forEach(form=>form.addEventListener('submit',event=>{
            event.preventDefault();const input=form.querySelector('[data-invite-identity]');const error=form.querySelector('[data-invite-error]');error.textContent='';input.removeAttribute('aria-invalid');input.value=input.value.trim();
            try{const person=resolveInviteIdentity(state.people,input.value);const action={type:'invite',personId:person.pessoa_id};transition(state,action);review(action,'Preparar convite para '+person.nm_pessoa+'?');}
            catch(problem){error.textContent=problem.message;input.setAttribute('aria-invalid','true');input.focus();}
        }));
        root.querySelectorAll('[data-organizer-responsibility]').forEach(form=>form.addEventListener('submit',event=>{event.preventDefault();review({type:'responsibility',eventId:Number(form.dataset.organizerResponsibility),personId:form.querySelector('select').value?Number(form.querySelector('select').value):null},'Alterar o responsável antes do início? O Evento poderá precisar de nova aprovação; as candidaturas são mantidas.');}));
        const promotion=root.querySelector('[data-promotion-form]');
        promotion?.addEventListener('submit',event=>{event.preventDefault();const text=promotion.querySelector('textarea').value;const error=promotion.querySelector('[data-promotion-error]');error.textContent='';try{transition(state,{type:'request',justification:text});review({type:'request',justification:text},'Solicitar a condição definitiva de organizador, mantendo seu histórico e deixando de participar de novas Agendas como voluntário?');}catch(problem){error.textContent=problem.message;promotion.querySelector('textarea').focus();}});
        dialog?.querySelector('[data-organizer-confirm-yes]').addEventListener('click',()=>{if(!pending)return;try{state=transition(state,pending);
            const recipient=pending.personId ?? state.person.pessoa_id;
            const key='organizer:'+pending.type+':'+(pending.ongId??1)+':'+recipient+':'+(pending.eventId??0);
            if(pending.type==='invite')publishNotice('convite',key,['organizador:'+recipient]);
            if(['accept','decline','end'].includes(pending.type))publishNotice('resposta',key,['ong:1']);
            if(pending.type==='responsibility')publishNotice('designacao',key,['organizador-vinculado:'+recipient]);
            if(pending.type==='request')publishNotice('filaPromocao',key,['administrador:1']);if(pending.type==='invite'){carriedIds.add(pending.personId);root.querySelector('[data-invite-identity]').value='';}else if(['accept','decline','end'].includes(pending.type)){carriedIds.delete(pending.personId);}root.querySelectorAll('textarea').forEach(node=>node.value='');pending=null;dialog.close();render();tell('Revisão concluída. Nenhum dado foi enviado.');}catch(error){dialog.close();pending=null;tell(error.message);}});
        dialog?.querySelector('[data-organizer-confirm-no]').addEventListener('click',()=>{pending=null;dialog.close();});
        dialog?.addEventListener('cancel',()=>pending=null);
        dialog?.addEventListener('close',()=>{pending=null;dialog.querySelector('[data-organizer-review]').textContent='';});
        render();
    });
});
