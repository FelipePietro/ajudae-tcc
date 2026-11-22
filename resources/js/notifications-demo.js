// Internal synthetic notices only. Query/storage are presentation state, never server authorization.
export const unreadCountLabel = count => count > 99 ? '99+' : String(count);
export const NOTICE_KEY = 'ajudae.notificacoes.v1';
export const catalog = {
    candidatura: ['Candidaturas', 'Candidatura enviada', 'A candidatura está aguardando seleção humana.', 'applications'],
    selecionada: ['Candidaturas', 'Candidatura selecionada', 'Consulte a situação da sua candidatura e a Agenda.', 'applications'],
    recusada: ['Candidaturas', 'Candidatura não selecionada', 'A seleção foi concluída. Você pode acompanhar outras oportunidades.', 'applications'],
    desistida: ['Candidaturas', 'Desistência registrada', 'Consulte o histórico e as condições da sua Agenda.', 'applications'],
    recebida: ['Candidaturas', 'Nova candidatura recebida', 'Há uma candidatura aguardando sua análise.', 'candidates'],
    alteracao: ['Eventos', 'Data, horário ou local alterados', 'Confira a Agenda. Você pode desistir sem penalidade até o início quando houver mudança unilateral de data, horário ou local.', 'applications'],
    edicao: ['Eventos', 'Evento atualizado', 'Confira as informações atualizadas e as condições da Agenda.', 'applications'],
    cancelamento: ['Eventos', 'Atividade cancelada', 'Não há penalidade para o voluntário. A Agenda cancelada não gera XP nem carga horária.', 'applications'],
    presente: ['Participação', 'Presença confirmada', 'A presença foi registrada por Agenda. XP depende também do encerramento da Agenda.', 'participation'],
    ausente: ['Participação', 'Ausência registrada', 'Confira a ocorrência e, quando disponível, encaminhe sua justificativa.', 'participation'],
    desistir: ['Participação', 'Desistência registrada', 'A desistência permanece no histórico da sua Agenda.', 'participation'],
    justificar: ['Participação', 'Justificativa encaminhada', 'A decisão depende da análise do responsável.', 'participation'],
    deferida: ['Participação', 'Justificativa aceita', 'A ocorrência permanece no histórico, com a penalidade revertida.', 'participation'],
    indeferida: ['Participação', 'Justificativa recusada', 'Consulte a situação da ocorrência na sua participação.', 'participation'],
    avaliarVoluntario: ['Participação', 'Avaliação registrada', 'A avaliação referente ao Evento foi registrada.', 'participation'],
    certificado: ['Certificados', 'Certificado emitido', 'Seu certificado está disponível na sua área de certificados.', 'certificates'],
    pedido: ['Apadrinhamento', 'Indicação de padrinho enviada', 'Aguarde o aceite do padrinho e a análise administrativa do cadastro.', 'sponsorship'],
    padrinho: ['Apadrinhamento', 'Solicitação de apadrinhamento recebida', 'Confira o pedido antes de aceitar ou recusar o acompanhamento.', 'sponsor'],
    aceite: ['Apadrinhamento', 'Apadrinhamento aceito', 'O aceite não aprova o cadastro automaticamente. Acompanhe a análise administrativa.', 'sponsorship'],
    recusa: ['Apadrinhamento', 'Apadrinhamento recusado', 'Acompanhe as orientações do seu cadastro.', 'sponsorship'],
    correcao: ['Cadastro', 'Correção solicitada', 'Confira as orientações na sua área privada.', 'profile'],
    em_analise: ['Cadastro', 'Cadastro em análise', 'Aguarde a decisão da equipe responsável.', 'profile'],
    aprovado: ['Cadastro', 'Cadastro aprovado', 'Consulte a situação atual na sua área privada.', 'profile'],
    reprovado: ['Cadastro', 'Cadastro não aprovado', 'Consulte as orientações na sua área privada.', 'profile'],
    renovacao: ['Cadastro', 'Renovação cadastral próxima', 'Confira o prazo de renovação na sua área privada. O aviso não revoga automaticamente suas permissões.', 'profile'],
    convite: ['Vínculos', 'Convite de organização recebido', 'Acompanhe o convite na sua área de atuação.', 'workspace'],
    resposta: ['Vínculos', 'Convite respondido', 'Confira a situação do vínculo na área de organizadores.', 'links'],
    designacao: ['Vínculos', 'Responsabilidade atualizada', 'Confira os Eventos sob sua responsabilidade.', 'workspace'],
    pendencias: ['Gestão', 'Pendências da atividade', 'Revise presença, monitoria, avaliações e condições de encerramento.', 'management'],
    reanalise: ['Gestão', 'Evento aguardando aprovação', 'Acompanhe a análise antes de receber novas candidaturas.', 'events'],
    filaPessoa: ['Administração', 'Cadastro de Pessoa aguardando análise', 'Consulte a fila autorizada de cadastros.', 'adminPessoa', 'usuarios.validar'],
    filaOng: ['Administração', 'Cadastro de ONG aguardando análise', 'Consulte a fila autorizada de organizações.', 'adminOng', 'ongs.validar'],
    filaEvento: ['Administração', 'Evento aguardando análise', 'Consulte a fila autorizada de Eventos.', 'adminEvento', 'eventos.aprovar'],
    filaPromocao: ['Administração', 'Promoção aguardando análise', 'Consulte a fila autorizada de solicitações.', 'adminPromocao', 'organizadores.validar'],
    filaLgpd: ['Privacidade', 'Solicitação de privacidade recebida', 'Consulte apenas a fila de solicitações permitida para sua função.', 'adminLgpd', 'lgpd.visualizar'],
};
const personal = ['candidatura','selecionada','recusada','desistida','alteracao','cancelamento','presente','ausente','deferida','indeferida','avaliarVoluntario','certificado','pedido','aceite','recusa','correcao','em_analise','aprovado','reprovado','renovacao'];
const organizer = ['convite','designacao','recebida','pendencias','reanalise'];
export function allowedKind(config, kind) {
    const meta = catalog[kind];
    if (!meta) return false;
    if (config.context === 'administrador') return Boolean(meta[4] && config.permissions.includes(meta[4]));
    if (meta[4]) return false;
    if (config.role === 'padrinho') return config.context === 'pessoa' && kind === 'padrinho';
    if (config.context === 'ong') return ['recebida','resposta','pendencias','reanalise'].includes(kind);
    if (['organizador','organizador-vinculado'].includes(config.context)) return [...organizer,'certificado', 'em_analise','aprovado','reprovado','correcao'].includes(kind);
    return config.context === 'pessoa' && [...personal,'edicao','desistir','justificar'].includes(kind);
}
export function seeds(config) {
    if (config.scenario === 'vazio') return [];
    return Object.keys(catalog).filter(kind => allowedKind(config, kind)).map((kind, i) => ({
        id: `historico:${config.identity}:${kind}`, recipient:config.identity, kind, event:config.context==='organizador'?8:config.context==='organizador-vinculado'?1:7, example:'certificados',
        time:Date.UTC(2026,10,17,15,0)-i*3600000, read:i>2, source:'historico', missing:config.scenario==='destino-ausente' && i===0,
    }));
}
export function readNotices(storage) {
    const raw = storage.getItem(NOTICE_KEY);
    if (raw === null) return [];
    const rows=JSON.parse(raw);
    if (!Array.isArray(rows) || rows.some(row=>!row || typeof row.id!=='string' || typeof row.recipient!=='string' || !catalog[row.kind] || typeof row.read!=='boolean' || !Number.isFinite(row.time))) throw Error('Invalid inbox');
    return rows;
}
export function mergeNotice(rows, notice) {
    if (!catalog[notice.kind] || rows.some(row=>row.id===notice.id && row.recipient===notice.recipient)) return rows;
    return [...rows, notice];
}
export function inbox(rows, config) {
    return rows.filter(row=>row.recipient===config.identity && allowedKind(config,row.kind)).sort((a,b)=>b.time-a.time || a.id.localeCompare(b.id));
}
export function markRead(rows, config, id=null) {
    return rows.map(row=>row.recipient===config.identity && allowedKind(config,row.kind) && (id===null || id===row.id) ? {...row,read:true}:row);
}
export function filtered(rows, {search='',status='todas',type='todos'}={}) {
    const normalize=s=>s.normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase();
    return rows.filter(row=>(status==='todas'||row.read===(status==='lidas')) && (type==='todos'||catalog[row.kind][0]===type) && normalize(catalog[row.kind].slice(0,3).join(' ')).includes(normalize(search.trim())));
}
// Destination is reconstructed from an enum, never a stored URL or external redirect.
export function destination(row, config) {
    if (row.missing || !allowedKind(config,row.kind)) return null;
    if(row.event!==undefined && ![1,7,8].includes(Number(row.event)))return null;
    const event=Number(row.event??7);
    const context=config.context;
    if(['candidates','management'].includes(catalog[row.kind][3]) && ((context==='organizador' && event!==8) || (context==='organizador-vinculado' && event!==1)))return null;
    const params=new URLSearchParams({contexto:context});
    if(config.context==='administrador')params.set('visao',config.variant);
    const example=typeof row.example==='string' && /^(certificados(?:-(?:jornada|monitoria|ausente|ativo))?|justificativas|avaliacoes|fechamento|confiabilidade)$/.test(row.example) ? row.example:null;
    let path;
    switch(catalog[row.kind][3]){
        case 'applications': path='/minhas-candidaturas';params.set('evento',String(event));params.set('cenario',row.kind==='candidatura'?'candidatura-pendente':row.kind==='selecionada'?'candidatura-aprovada':row.kind==='recusada'?'candidatura-rejeitada':row.kind==='desistida'?'desistencia':row.kind==='alteracao'?'isencao':row.kind==='cancelamento'?'cancelado':'participacao');break;
        case 'candidates':path='/ong/candidatos';params.set('evento',String(event));break;
        case 'participation':path='/minhas-participacoes';break;
        case 'certificates':path='/meus-certificados';break;
        case 'profile':path='/perfil';params.set('area','privada');break;
        case 'sponsorship':path='/perfil';params.set('area','privada');params.set('apadrinhamento','solicitante');break;
        case 'sponsor':path='/perfil';params.set('area','privada');params.set('apadrinhamento','padrinho');break;
        case 'workspace':path='/organizador';if(row.kind==='convite')params.set('jornada','convite-pendente');break;
        case 'links':path='/ong/organizadores';break;
        case 'management':path=`/ong/eventos/${event}/presenca`;break;
        case 'events':path='/ong/eventos';break;
        case 'adminPessoa':path='/admin/usuarios';break;
        case 'adminOng':path='/admin/cadastros-ong';break;
        case 'adminEvento':path='/admin/fila-eventos';break;
        case 'adminPromocao':path='/admin/upgrades-org';break;
        case 'adminLgpd':path='/admin/solicitacoes-lgpd';break;
        default:return null;
    }
    if (example && ['participation','certificates','management'].includes(catalog[row.kind][3]))params.set('exemplo',example);
    const anchor=catalog[row.kind][3]==='participation' && Number.isInteger(row.agenda) && Math.floor(row.agenda/100)===event ? '#agenda-'+row.agenda:'';
    return path+'?'+params+anchor;
}
// Called ONLY after a successful local action; never replay histories on page load.
export function publishNotice(kind, key, recipients, detail={}) {
    if (typeof window==='undefined' || !catalog[kind]) return false;
    try{
        let rows=readNotices(sessionStorage);
        for(const recipient of recipients) rows=mergeNotice(rows,{id:`acao:${key}:${kind}`,recipient,kind,event:detail.event??7,example:detail.example??null,agenda:Number.isInteger(detail.agenda)?detail.agenda:null,record:Number.isInteger(detail.record)?detail.record:null,time:detail.time??Date.now(),read:false});
        sessionStorage.setItem(NOTICE_KEY,JSON.stringify(rows));
        document.dispatchEvent(new Event('notifications:changed'));
        return true;
    }catch{document.dispatchEvent(new Event('notifications:failed'));return false;}
}
function init(){
    const source=document.querySelector('[data-notification-config]');if(!source)return;
    const config=JSON.parse(source.textContent),root=document.querySelector('[data-notification-center]');
    const scenario=root?config.scenario:'historico';
    if(root && ['vazio','destino-ausente'].includes(scenario))config.identity+=`:cenario:${scenario}`;
    let page=1,failed=false;
    const controls=root?.querySelector('form');
    const noticeMessage=(message)=>{if(root){const el=root.querySelector('[data-notification-message]');el.textContent=message;el.hidden=!message;}};
    const fail=()=>{failed=true;document.querySelectorAll('[data-notification-entry]').forEach(el=>el.setAttribute('aria-label','Notificações indisponíveis'));noticeMessage('Não foi possível carregar os avisos desta aba. Recarregue a página para tentar novamente.');root?.querySelector('[data-notification-all]').setAttribute('disabled','');document.querySelectorAll('[data-notification-count]').forEach(el=>{el.textContent='';el.hidden=true;});};
    document.querySelectorAll('[data-notification-entry]').forEach(link=>{
        const url=new URL(link.href,location.href);url.searchParams.set('contexto',config.context);
        if(config.context==='administrador')url.searchParams.set('visao',config.variant);
        if(config.role==='padrinho')url.searchParams.set('papel','padrinho');
        link.href=url.href;
        const badge=document.createElement('span');badge.dataset.notificationCount='';badge.className='notification-count';badge.setAttribute('aria-hidden','true');badge.hidden=true;(link.querySelector('[data-notification-icon]')??link).append(badge);
    });
    if(controls){
        for(const type of new Set(Object.keys(catalog).filter(kind=>allowedKind(config,kind)).map(kind=>catalog[kind][0]))){const option=new Option(type,type);controls.elements.tipo.add(option);}
        controls.addEventListener('submit',event=>event.preventDefault());
        controls.addEventListener('input',()=>{page=1;render();});
        root.querySelector('[data-notification-reset]').addEventListener('click',()=>{controls.reset();page=1;render();});
        root.querySelector('[data-notification-all]').addEventListener('click',()=>update(null));
        root.querySelector('[data-notification-previous]').addEventListener('click',()=>{page--;render(true);});
        root.querySelector('[data-notification-next]').addEventListener('click',()=>{page++;render(true);});
    }
    function update(id){try{sessionStorage.setItem(NOTICE_KEY,JSON.stringify(markRead(readNotices(sessionStorage),config,id)));document.dispatchEvent(new Event('notifications:changed'));return true;}catch{fail();return false;}}
    function render(focus=false){
        if(failed)return;
        try{
            const rows=inbox(readNotices(sessionStorage),config);
            const unread=rows.filter(row=>!row.read).length;
            document.querySelectorAll('[data-notification-entry]').forEach(link=>{link.setAttribute('aria-label',`Notificações: ${unread} ${unread===1?'não lida':'não lidas'}`);const badge=link.querySelector('[data-notification-count]');badge.textContent=unreadCountLabel(unread);badge.hidden=!unread;});
            if(!root)return;
            const blocked=['falha','indisponivel','carregando'].includes(scenario);
            root.querySelector('[data-notification-all]').disabled=!unread||blocked;
            root.querySelector('[data-notification-summary]').textContent=blocked?'Consulta indisponível':`${unread} não lida${unread===1?'':'s'} · ${rows.length} aviso${rows.length===1?'':'s'}`;
            if(blocked){noticeMessage(scenario==='carregando'?'Carregando notificações…':'Não foi possível carregar os avisos. Recarregue a página ou retorne mais tarde.');return;}
            const matches=filtered(rows,{search:controls.elements.busca.value,status:controls.elements.leitura.value,type:controls.elements.tipo.value});
            const pages=Math.max(1,Math.ceil(matches.length/8));page=Math.max(1,Math.min(page,pages));
            noticeMessage(!rows.length?'Você ainda não tem notificações.':!matches.length?'Nenhum aviso encontrado com esses filtros.':'');
            const list=root.querySelector('[data-notification-list]');list.replaceChildren();
            for(const row of matches.slice((page-1)*8,page*8)){
                const el=root.querySelector('template').content.firstElementChild.cloneNode(true),meta=catalog[row.kind];
                el.dataset.noticeId=row.id;el.dataset.unread=String(!row.read);
                el.querySelector('[data-notification-type]').textContent=meta[0];el.querySelector('h2').textContent=meta[1];el.querySelector('[data-notification-body]').textContent=meta[2]+(Number.isInteger(row.agenda) && Number.isInteger(row.record)?` Agenda ${row.agenda%100} · inscrição ${row.record}.`:'');
                el.querySelector('[data-notification-status]').textContent=row.read?'Lida':'Não lida';
                el.querySelector('[data-notification-origin]').textContent=row.source==='historico'?'Histórico de notificações':'Atividade recente';
                const time=el.querySelector('time');time.dateTime=new Date(row.time).toISOString();time.textContent=new Intl.DateTimeFormat('pt-BR',{dateStyle:'short',timeStyle:'short',timeZone:'America/Sao_Paulo'}).format(row.time);
                const read=el.querySelector('[data-notification-read]');read.disabled=row.read;read.setAttribute('aria-label',`Marcar como lida: ${meta[1]}`);
                read.addEventListener('click',()=>{if(update(row.id)){const current=[...list.children].find(item=>item.dataset.noticeId===row.id);(current?.querySelector('h2')??root.querySelector('[data-notification-message]')).focus();}});
                const open=el.querySelector('[data-notification-open]'),target=destination(row,config);
                open.disabled=!target;open.setAttribute('aria-label',`Ver detalhes: ${meta[1]}`);el.querySelector('[data-notification-unavailable]').hidden=Boolean(target);
                open.addEventListener('click',()=>{if(update(row.id))location.assign(target);});list.append(el);
            }
            root.querySelector('[data-notification-pagination]').hidden=matches.length<=8;
            root.querySelector('[data-notification-page]').textContent=`Página ${page} de ${pages}`;
            root.querySelector('[data-notification-previous]').disabled=page===1;root.querySelector('[data-notification-next]').disabled=page===pages;
            if(focus)root.querySelector('h1').setAttribute('tabindex','-1');
            if(focus)root.querySelector('h1').focus();
        }catch{fail();}
    }
    document.addEventListener('notifications:changed',()=>render());document.addEventListener('notifications:failed',fail);
    try{
        // Seed once per identity, with a separate initialization marker even for an empty inbox.
        const marker=`${NOTICE_KEY}.seed.${config.identity}${config.context==='administrador'?'.'+config.variant:''}`;
        if(!sessionStorage.getItem(marker)){
            let rows=readNotices(sessionStorage);for(const row of seeds({...config,scenario}))rows=mergeNotice(rows,row);
            sessionStorage.setItem(NOTICE_KEY,JSON.stringify(rows));sessionStorage.setItem(marker,'1');
        }
        render();
    }catch{fail();}
}
if(typeof document!=='undefined')document.addEventListener('DOMContentLoaded',init);
