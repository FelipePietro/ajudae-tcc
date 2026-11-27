import assert from 'node:assert/strict';
import { spawn } from 'node:child_process';
import { mkdir, readFile, readdir, writeFile } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';
import { createHash } from 'node:crypto';
import { setTimeout as delay } from 'node:timers/promises';

const base='http://127.0.0.1:8893';
const file=name=>new URL(name,import.meta.url);
const f=JSON.parse(await readFile(file('fixture-session.json'),'utf8'));
const docs=JSON.parse(await readFile(file('doc-fixtures.json'),'utf8'));
const profile=fileURLToPath(file('edge-e2e-'+Date.now()+'/'));
const downloads=fileURLToPath(file('downloads/'));
await mkdir(profile,{recursive:true});
await mkdir(downloads,{recursive:true});
const edge=spawn('C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',[
  '--headless=new','--remote-debugging-port=0','--disable-background-networking','--disable-component-update',
  '--no-first-run','--disable-sync','--disable-extensions','--disable-gpu','--user-data-dir='+profile,base+'/login',
],{windowsHide:true,stdio:'ignore'});
const selectedTests=process.argv.slice(2);
const evidenceName=selectedTests.includes('Aprovação')?'browser-evidence-complementar.json':selectedTests.length?'browser-evidence-reteste.json':'browser-evidence.json';
const evidence=[], traffic=[], files=[], pending=new Map(), requests=new Map();
let ws, seq=0, interceptPath='', bversion='';
async function waitFor(label, fn, time=12000) {
  const deadline=Date.now()+time;
  while(Date.now()<deadline) {
    const value=await fn().catch(()=>false);
    if(value) return value;
    await delay(125);
  }
  throw new Error('Tempo esgotado: '+label);
}
function cdp(method,params={}){
  const id=++seq;
  return new Promise((resolve,reject)=>{
    const timer=setTimeout(()=>{pending.delete(id);reject(new Error(method+' expirou'));},7000);
    pending.set(id, p=>{clearTimeout(timer);p.error?reject(new Error(method+': '+p.error.message)):resolve(p.result);});
    ws.send(JSON.stringify({id,method,params}));
  });
}
async function js(expression){
  const r=await cdp('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});
  if(r.exceptionDetails) throw new Error(r.exceptionDetails.exception?.description?.split('\n')[0]??'Erro no DOM');
  return r.result.value;
}
const raw = (fn, ...args) => js('('+fn.toString()+')(...'+JSON.stringify(args)+')');
async function at(path, reload=false){
  const stamp=traffic.length;
  if(reload) await cdp('Page.reload',{ignoreCache:true});
  else await cdp('Page.navigate',{url:base+path});
  await waitFor(path,()=>raw(p=>location.pathname===p && document.readyState==='complete',path.split('#')[0]));
  if(reload) await waitFor('GET após recarga '+path,async()=>traffic.slice(stamp).some(x=>x.method==='GET'&&x.path===path.split('#')[0]));
}
async function click(type,label,scopes=[],prefix=false){
  await waitFor(type+' '+label,()=>raw((t,n,s,p)=>{
    const scope=s.reduce((el,h)=>el&&[...el.querySelectorAll('article')].find(x=>x.querySelector(':scope > h3')?.textContent.trim()===h),document);
    const node=[...(scope?.querySelectorAll(t)??[])].find(x=>x.checkVisibility()&&(p?x.textContent.trim().startsWith(n):x.textContent.trim()===n));
    if(!node) return false;
    node.scrollIntoView({block:'center'});
    node.click();
    return true;
  },type,label,scopes,prefix));
}
const summary=(label,s,pre=false)=>click('summary',label,s,pre);
const button=(label,s)=>click('button',label,s);
async function form(label, values={}, scopes=[]){
  if(Object.keys(values).length) await waitFor('campos de '+label,()=>raw((label,values,scopes)=>{
    const root=scopes.reduce((el,h)=>el&&[...el.querySelectorAll('article')].find(x=>x.querySelector(':scope > h3')?.textContent.trim()===h),document);
    const node=[...(root?.querySelectorAll('button')??[])].find(x=>x.checkVisibility()&&x.textContent.trim()===label);
    if(!node?.form) return false;
    for(const [name,value] of Object.entries(values)){
      const field=node.form.elements.namedItem(name);
      if(!field) throw new Error('Campo ausente: '+name);
      field.value=String(value);
      field.dispatchEvent(new Event('input',{bubbles:true}));
      field.dispatchEvent(new Event('change',{bubbles:true}));
    }
    return true;
  },label,values,scopes));
  await button(label,scopes);
}
async function control(label,values={},scopes=[]){await summary(label,scopes);await form(label,values,scopes);}
async function has(text, selector='body'){return raw((t,s)=>Boolean(document.querySelector(s)?.innerText.includes(t)),text,selector);}
async function show(text, selector='body'){await waitFor('visível: '+text,()=>has(text,selector));}
async function http(method,path,status,action){
 const stamp=traffic.length;
 await action();
 return waitFor(method+' '+path+' '+status,async()=>traffic.slice(stamp).some(x=>x.method===method&&x.path===path&&x.status===status));
}
async function login(suffix,type='pessoa'){
  await cdp('Network.clearBrowserCookies');
  await at('/login');
  await waitFor('formulário de login',()=>raw((suffix,type,password)=>{
    const input=document.querySelector('#login-name');
    if(!input) return false;
    const radio=input.form.querySelector('input[name="tipo_login"][value="'+type+'"]');
    if(!radio)return false;
    radio.click();
    input.value='qa.luiz.'+suffix;
    document.querySelector('#login-password').value=password;
    input.form.querySelector('button[type="submit"]').click();
    return true;
  },suffix,type,f.password));
  await waitFor('sessão '+suffix,()=>raw(()=>location.pathname!=='/login'&&document.readyState==='complete'));
}
async function test(label,persona,fn){
 if(selectedTests.length&&!selectedTests.some(prefix=>label.startsWith(prefix)))return;
 const before=traffic.length;
 try{
   const details=await fn();
   evidence.push({label,persona,success:true,details,traffic:traffic.slice(before)});
 }catch(e){evidence.push({label,persona,success:false,error:e.message,traffic:traffic.slice(before)});}
 await writeFile(file(evidenceName),JSON.stringify({browser:bversion,scenarios:evidence},null,2)+'\n');
 console.log(JSON.stringify({label,success:evidence.at(-1).success,error:evidence.at(-1).error}));
}
const personName='Pessoa sintética qa luiz titular';
const focusEvent='QA presença e encerramento';
const absenceEvent='QA ausência e justificativa';
const register='Pessoa sintética qa luiz pendente';
const agendaTitle='Agenda nº '+f.agenda;
const attendance=[focusEvent,agendaTitle,personName];
const ongName='Organização sintética 2';
const management='/ong/eventos';
const pendingPerson='/admin/validacoes-pessoa/'+f.pendingPerson;
const downloadPath=id=>'/conta/documentos/'+id+'/download';
async function participant(event,agenda){
 await summary('Gerenciar participação',[event]);
 await summary('Acompanhar inscrições',[event,agenda]);
 await show(personName,'[data-participation-management]');
}
try {
 const port=(await waitFor('DevTools do Edge',()=>readFile(profile+'/DevToolsActivePort','utf8'),35000)).split(/\r?\n/)[0];
 const targets=await waitFor('aba local',async()=>{
    const list=await (await fetch('http://127.0.0.1:'+port+'/json/list')).json();
    return list.find(x=>x.type==='page'&&x.url.startsWith(base));
 },20000);
 ws=new WebSocket(targets.webSocketDebuggerUrl);
 await new Promise((resolve,reject)=>{ws.addEventListener('open',resolve,{once:true});ws.addEventListener('error',reject,{once:true});});
 ws.addEventListener('message',msg=>{
   const p=JSON.parse(msg.data);
   if(p.id){const cb=pending.get(p.id);pending.delete(p.id);cb?.(p);return;}
   if(p.method==='Network.requestWillBeSent'&&p.params.request.url.startsWith(base)) requests.set(p.params.requestId,p.params.request.method);
   if(p.method==='Network.responseReceived'&&p.params.response.url.startsWith(base)){
      const u=new URL(p.params.response.url);
      traffic.push({method:requests.get(p.params.requestId),path:u.pathname,status:p.params.response.status});
   }
   if(p.method==='Browser.downloadProgress'&&p.params.state==='completed') files.push(p.params.guid);
   if(p.method==='Fetch.requestPaused'){
      const u=new URL(p.params.request.url);
      if(interceptPath && u.pathname===interceptPath){
        interceptPath='';
        cdp('Fetch.fulfillRequest',{requestId:p.params.requestId,responseCode:503,responseHeaders:[{name:'Content-Type',value:'application/json'}],
          body:Buffer.from(JSON.stringify({message:'Interrupção sintética de QA.'})).toString('base64')}).catch(()=>{});
      }else cdp('Fetch.continueRequest',{requestId:p.params.requestId}).catch(()=>{});
   }
 });
 bversion=(await cdp('Browser.getVersion')).product;
 await cdp('Page.enable');
 await cdp('Network.enable');
 await cdp('Browser.setDownloadBehavior',{behavior:'allowAndName',downloadPath:downloads,eventsEnabled:true});
 await cdp('Emulation.setDeviceMetricsOverride',{width:1280,height:900,deviceScaleFactor:1,mobile:false});
 await test('Histórico, paginação e titularidade','Pessoa titular e Pessoa sem inscrições',async()=>{
   await login('titular');await at('/minhas-participacoes');
   await show('QA Paginação');
   const stamp=traffic.length;
   await button('Carregar mais');
   await waitFor('segunda página',async()=>traffic.slice(stamp).some(x=>x.path==='/minhas-participacoes/dados'&&x.status===200));
   await show(absenceEvent);
   await at('/minhas-participacoes',true);await show('QA Paginação');
   await login('vazio');await at('/minhas-participacoes');
   await show('Você ainda não tem participações');
   assert.equal(await has(absenceEvent),false);
   return 'Primeira e segunda páginas, recarga e estado vazio em sessão distinta.';
 });
 await test('Justificativa enviada pela Pessoa','Titular da ausência',async()=>{
   await login('titular');await at('/minhas-participacoes');await show('QA Paginação');await button('Carregar mais');await show(absenceEvent);
   const path='/gestao/inscricoes/'+f.absence+'/justificativa';
   await http('POST',path,201,async()=>{
      await raw(({event,id})=>{
        const cards=[...document.querySelectorAll('article')].filter(a=>a.innerText.includes(event));
        const root=cards.at(-1)??document;
        const btn=[...root.querySelectorAll('button,summary')].find(x=>x.checkVisibility()&&/justificativa/i.test(x.textContent));
        if(btn)btn.click();
      },{event:absenceEvent,id:f.absence});
      const exact=await waitFor('campo de motivo',()=>raw(()=>[...document.querySelectorAll('textarea')].some(x=>x.checkVisibility())));
      assert.ok(exact);
      await raw(()=>{
        const field=[...document.querySelectorAll('textarea')].find(x=>x.checkVisibility());
        field.value='Ocorrência sintética enviada pelo navegador.';
        field.dispatchEvent(new Event('input',{bubbles:true}));
        const btn=field.form?.querySelector('button[type=submit]')??[...document.querySelectorAll('button')].find(x=>x.checkVisibility()&&/enviar justificativa/i.test(x.textContent));
        btn?.click();
      });
   });
   await at('/minhas-participacoes',true);await show('QA Paginação');await button('Carregar mais');await show(absenceEvent);
   return 'POST 201, recarga do histórico.';
 });
 await test('Presença e monitoria','ONG proprietária',async()=>{
   await login('ong','ong');await at(management);
   await participant(focusEvent,agendaTitle);
   const path='/gestao/inscricoes/'+f.attendee;
   await http('PATCH',path+'/presenca',200,async()=>{await summary('Registrar presença ou ausência',attendance);await form('Salvar presença',{presenca:true},attendance);});
   await show('Inscrição · participou','[data-participation-management]');
   await at(management,true);await participant(focusEvent,agendaTitle);
   await show('Inscrição · participou','[data-participation-management]');
   await summary('Monitoria estudantil',attendance);
   await http('PATCH',path+'/monitoria',200,()=>control('Confirmar monitoria',{},attendance));
   await show('Situação · Validada','[data-participation-management]');
   await at(management,true);await participant(focusEvent,agendaTitle);await summary('Monitoria estudantil',attendance);
   await show('Situação · Validada','[data-participation-management]');
   return 'Presença e monitoria com nova leitura após recarga.';
 });
 await test('Justificativa decidida pela ONG','ONG proprietária',async()=>{
   await login('ong','ong');await at(management);
   const openAbsence=async()=>{
     await waitFor('lista gerencial',()=>has('Gerenciar participação','[data-participation-management]'));
     if(!(await has(absenceEvent,'[data-participation-management]'))) await click('[data-participation-management] button','Próxima');
     await summary('Gerenciar participação',[absenceEvent]);
     await summary('Acompanhar inscrições',[absenceEvent,'Agenda nº 1']);
     await summary('Justificativa da ocorrência',[absenceEvent,'Agenda nº 1']);
   };
   await openAbsence();
   const path='/gestao/inscricoes/'+f.absence+'/justificativa';
   await http('PATCH',path,200,()=>control('Deferir justificativa',{motivo:'Decisão sintética de navegador.'},[absenceEvent]));
   await show('Decisão registrada por','[data-participation-management]');
   await at(management,true);await openAbsence();await show('Decisão registrada por','[data-participation-management]');
   return 'Autoria visível para a gestão após nova consulta.';
 });
 await test('Avaliação e finalizações','ONG proprietária',async()=>{
   await login('ong','ong');await at(management);
   await summary('Gerenciar participação',[focusEvent]);
   await http('PATCH','/gestao/agendas/'+f.agenda+'/finalizar',200,()=>control('Finalizar Agenda',{},[focusEvent,agendaTitle]));
   await at(management,true);await summary('Gerenciar participação',[focusEvent]);await summary('Avaliações dos voluntários',[focusEvent]);
   await http('POST','/gestao/eventos/'+f.event+'/avaliacoes/'+f.person,201,()=>form('Salvar nota',{nota:5},[focusEvent,personName]));
   await show('5 de 5','[data-participation-management]');
   await http('POST','/gestao/eventos/'+f.event+'/finalizar',200,()=>control('Finalizar Evento',{},[focusEvent]));
   await at(management,true);await show('Situação · Finalizado','[data-participation-management]');
   return 'Agenda, nota e Evento fechados na ordem permitida.';
 });
 await test('Cadastro: atribuição, recarga, PDF','Admin de revisão',async()=>{
   await login('admin');await at('/admin/candidatos');
   await summary('Revisar cadastro e documentos',[register]);
   await show('Para baixar, assuma a análise');
   assert.equal(await raw(p=>document.querySelectorAll('a[href$="'+p+'"]').length,downloadPath(docs.registration)),0);
   await http('POST',pendingPerson+'/assumir',200,()=>control('Assumir análise',{},[register]));
   await at('/admin/candidatos',true);await summary('Revisar cadastro e documentos',[register]);
   const n=(await readdir(downloads)).length;
   await waitFor('link de PDF após recarga',()=>raw(p=>{
     const el=document.querySelector('a[href$="'+p+'"]');
     if(!el||!el.checkVisibility())return false;
     el.click();return true;
   },downloadPath(docs.registration)));
   const payload=await waitFor('PDF cadastro',async()=>{
     const names=await readdir(downloads);
     if(names.length<=n)return false;
     for(const name of names){
        if(name.endsWith('.crdownload')) continue;
        const bytes=await readFile(downloads+'/'+name);
        if(createHash('sha256').update(bytes).digest('hex')===docs.pdfHash)return bytes.length;
     }
   });
   return {pdfBytes:payload,hashConferido:true};
 });
 await test('Renovação: PDF e decisão','Admin de revisão',async()=>{
   await login('admin');await at('/admin/candidatos');
   await waitFor('abrir fila de renovação',()=>raw(()=>{
      const e=document.querySelector('[data-operation-renewals] > summary');
      if(!e)return false;e.click();return true;
   }));
   const previous=new Set(await readdir(downloads));
   await waitFor('link da renovação',()=>raw(p=>{
     const el=document.querySelector('a[href$="'+p+'"]');if(!el||!el.checkVisibility())return false;el.click();return true;
   },downloadPath(docs.renewal)));
   await waitFor('novo PDF de renovação',async()=>{
     for(const name of await readdir(downloads)){
        if(previous.has(name)||name.endsWith('.crdownload'))continue;
        if(createHash('sha256').update(await readFile(downloads+'/'+name)).digest('hex')===docs.pdfHash)return true;
     }
     return false;
   });
   await http('PATCH','/admin/documentos/'+docs.renewal+'/status',200,async()=>{
     const action=tag=>waitFor('controle de renovação '+tag,()=>raw((tag,path)=>{
       const article=document.querySelector('a[href$="'+path+'"]')?.closest('article');
       const el=[...(article?.querySelectorAll(tag)??[])].find(x=>x.checkVisibility()&&x.textContent.trim()==='Aprovar documento');
       if(!el)return false;el.click();return true;
     },tag,downloadPath(docs.renewal)));
     await action('summary');
     await action('button');
   });
   await at('/admin/candidatos',true);
   await waitFor('reabrir fila',()=>raw(()=>{const e=document.querySelector('[data-operation-renewals] > summary');if(!e)return false;e.click();return true;}));
   await waitFor('renovação não oferece o link',()=>raw(p=>!document.querySelector('a[href$="'+p+'"]'),downloadPath(docs.renewal)));
   return 'Segundo PDF validado byte a byte pelo SHA-256 e aprovação pela fila.';
 });
 await test('Decisão institucional','Admin de ONG',async()=>{
   await login('admin');await at('/admin/cadastros-ong');await show(ongName);
   await http('PATCH','/admin/validacoes-ong/'+f.pendingOng+'/status',200,()=>control('Aprovar ONG',{},[ongName]));
   await at('/admin/cadastros-ong',true);await show('Nenhum registro encontrado.');
   assert.equal(await has(ongName,'[data-operation-list]'),false);
   return 'ONG saiu da fila após recarga.';
 });
 await test('Decisão de Evento','Admin de Eventos',async()=>{
   await login('admin');await at('/admin/fila-eventos');await show('QA Evento para reprovar');
   const cardName='QA Evento para reprovar';
   await http('PATCH','/admin/fila-eventos/'+f.negativeEvent+'/decisao',200,async()=>{
     await waitFor('botão de reprovação',()=>raw(cardName=>{
       const area=[...document.querySelectorAll('[data-moderation-list] article')].find(x=>x.innerText.includes(cardName));
       const button=area?.querySelector('[data-event-reject]');
       if(!button)return false;button.click();return true;
     },cardName));
     await waitFor('motivo de reprovação',()=>raw(()=>{
       const field=document.querySelector('dialog[open] [name=motivo]');
       if(!field)return false;field.value='Reprovação sintética pela interface.';field.dispatchEvent(new Event('input',{bubbles:true}));
       const confirm=field.form?.querySelector('button[type=submit]')??[...document.querySelectorAll('button')].find(x=>x.checkVisibility()&&/confirmar|reprovar/i.test(x.textContent));
       if(!confirm)return false;confirm.click();return true;
     }));
   });
   await at('/admin/fila-eventos',true);
   await waitFor('Evento fora da fila',async()=>!(await has(cardName)));
   return 'Evento reprovado por formulário.';
 });
 await test('Aprovação de Evento','Admin de Eventos',async()=>{
   await login('admin');await at('/admin/fila-eventos');
   const cardName='QA Evento para moderação';
   await show(cardName);
   await http('PATCH','/admin/fila-eventos/'+f.moderation+'/decisao',200,async()=>{
     await waitFor('controle de aprovação de Evento',()=>raw(title=>{
       const card=[...document.querySelectorAll('[data-moderation-list] article')].find(x=>x.innerText.includes(title));
       const button=card?.querySelector('[data-event-approve]');
       if(!button)return false;button.click();return true;
     },cardName));
     await waitFor('confirmar aprovação',()=>raw(()=>{
       const el=document.querySelector('dialog[open] button[type=submit]');
       if(!el)return false;el.click();return true;
     }));
   });
   await at('/admin/fila-eventos',true);
   await waitFor('fila recarregada',()=>raw(()=>document.querySelector('[data-moderation-list]')?.getAttribute('aria-busy')==='false'));
   assert.equal(await has(cardName,'[data-moderation-list]'),false);
   return 'Evento aprovado com saída da fila após recarga.';
 });
 await test('Aprovação de Pessoa e documentos','Admin de revisão',async()=>{
   await login('admin');await at('/admin/candidatos');
   await summary('Revisar cadastro e documentos',[register]);
   for(const [docId,title] of [[docs.registration,'documento identidade'],[docs.registrationCertificate,'antecedentes criminais']]){
     await http('PATCH','/admin/documentos/'+docId+'/status',200,()=>control('Aprovar documento',{},[register,title]));
     await waitFor('status de documento aprovado',()=>raw(title=>[...document.querySelectorAll('[data-operation-list] article')].some(x=>x.querySelector(':scope > h3')?.textContent.trim()===title&&x.innerText.includes('aprovado')),title));
   }
   await http('POST',pendingPerson+'/aprovar',200,()=>control('Aprovar Pessoa',{},[register]));
   await at('/admin/candidatos',true);
   await waitFor('fila administrativa recarregada',()=>raw(()=>document.querySelector('[data-operation-list]')?.getAttribute('aria-busy')==='false'));
   assert.equal(await has(register,'[data-operation-list]'),false);
   return 'RG e certidão aprovados antes do cadastro, com saída da fila após recarga.';
 });
 await test('Conclusão pela sidebar','Admin exclusivo de apadrinhamento',async()=>{
   await login('padrinho');await at('/admin');
   await waitFor('link de conclusões',()=>raw(()=>{
     const a=document.querySelector('#admin-sidebar a[href$="/admin/conclusoes-padrinho"]');
     if(!a||!a.checkVisibility())return false;a.click();return true;
   }));
   await waitFor('fila de conclusões',()=>raw(()=>location.pathname==='/admin/conclusoes-padrinho'));
   assert.equal(await raw(()=>document.querySelector('#admin-sidebar a[href$="/admin/conclusoes-padrinho"]')?.getAttribute('aria-current')),'page');
   const cardName='Pedido nº '+f.rejection;
   await show(cardName);
   const endpoint='/admin/padrinhos/conclusoes/'+f.rejection+'/reprovar';
   await summary('Reprovar conclusão',[cardName]);
   await button('Reprovar conclusão',[cardName]);
   assert.equal(await raw(()=>Boolean(document.querySelector('textarea[name=motivo]:invalid'))),true);
   await http('POST',endpoint,200,()=>form('Reprovar conclusão',{motivo:'Reprovação sintética de navegador'},[cardName]));
   await at('/admin/conclusoes-padrinho',true);await show('Pedido nº '+f.approval);
   assert.equal(await has(cardName,'[data-operation-list]'),false);
   return 'Link ativo e formulário com validação do campo, decisão e recarga.';
 });
 await test('Promoção','Admin de promoções',async()=>{
   await login('admin');await at('/admin/upgrades-org');
   const cardName='Solicitação nº '+f.promotion;
   const root='/admin/solicitacoes-organizador/'+f.promotion;
   await summary('Revisar solicitação',[cardName]);
   await http('POST',root+'/iniciar-analise',200,()=>control('Iniciar análise',{},[cardName]));
   await at('/admin/upgrades-org',true);await summary('Revisar solicitação',[cardName]);
   await http('POST',root+'/reprovar',200,()=>control('Reprovar promoção',{motivo:'Decisão sintética de navegador'},[cardName]));
   await at('/admin/upgrades-org',true);await summary('Revisar solicitação',[cardName]);
   await show('reprovada','[data-operation-list]');
   return 'Assunção e reprovação confirmadas após recarga.';
 });
 await test('Campanha de XP e falha de rede segura','Admin de XP',async()=>{
   await login('admin');await at('/admin/campanhas-xp');
   const name='QA campanha local final';
   const values={nome:name,multiplicador:2,inicio:'2026-11-27T00:00',fim:'2026-11-29T00:00',cat_evento_id:f.category,ativa:true};
   await http('POST','/admin/campanhas-xp',201,()=>control('Criar campanha',values));
   await show(name,'[data-operation-list]');
   let prior=traffic.length;
   await control('Editar campanha',{...values,multiplicador:2.5},[name]);
   const patch=await waitFor('editar campanha',async()=>traffic.slice(prior).find(x=>x.method==='PATCH'&&x.status===200&&x.path.startsWith('/admin/campanhas-xp/')));
   await http('PATCH',patch.path+'/status',200,()=>control('Desativar campanha',{},[name]));
   await at('/admin/campanhas-xp',true);await show('Situação · Inativa','[data-operation-list]');
   interceptPath='/admin/campanhas-xp/dados';
   await cdp('Fetch.enable',{patterns:[{urlPattern:base+interceptPath+'*',requestStage:'Request'}]});
   await at('/admin/campanhas-xp',true);
   await show('Tentar novamente','[data-operation-list]');
   await cdp('Fetch.disable');
   await button('Tentar novamente');
   await show(name,'[data-operation-list]');
   return 'Criar, editar e desativar. Um GET recebeu 503 sintético e o botão de nova tentativa recuperou a listagem.';
 });
 await test('LGPD e logs','Atendente exclusivo e leitor de logs',async()=>{
   await login('atendente');await at('/admin/responder-lgpd');
   const endpoint='/admin/solicitacoes-lgpd/'+f.privateCase+'/status';
   await http('PATCH',endpoint,200,()=>control('Assumir atendimento',{protocolo:f.privateCase}));
   await http('PATCH',endpoint,200,()=>control('Concluir solicitação',{protocolo:f.privateCase,resposta:'Resposta sintética de navegador.'}));
   await login('admin');await at('/admin/solicitacoes-lgpd');await show('Protocolo nº '+f.privateCase);
   await show('Concluída','[data-operation-list]');
   await at('/admin/log-acoes');await show('lgpd.status_atualizado');
   return 'Resposta recuperada em nova sessão e ação no log.';
 });
 await test('LGPD após recarga','Administrador com leitura e sem a atribuição do protocolo',async()=>{
   await login('admin');await at('/admin/solicitacoes-lgpd');await show('Protocolo nº '+f.privateCase);
   await at('/admin/solicitacoes-lgpd',true);await show('Concluída','[data-operation-list]');
   assert.equal(await has('Resposta sintética de navegador.','[data-operation-list]'),false);
   await at('/admin/log-acoes');await show('lgpd.status_atualizado');
   return 'Estado concluído após recarga, resposta oculta ao leitor sem atribuição e ação auditada.';
 });
 await test('Largura e navegação por teclado','Admin exclusivo de apadrinhamento',async()=>{
   await login('padrinho');await at('/admin/conclusoes-padrinho');await show('Pedido nº '+f.approval);
   const sizes=[];
   for(const width of [360,768,1280]){
     await cdp('Emulation.setDeviceMetricsOverride',{width,height:850,deviceScaleFactor:1,mobile:false});
     await delay(200);
     const metrics=await raw(()=>({viewport:document.documentElement.clientWidth,scroll:document.documentElement.scrollWidth}));
     sizes.push({requested:width,...metrics});
   }
   const focus=await raw(()=>{
     const summary=[...document.querySelectorAll('[data-operation-list] summary')].find(el=>el.checkVisibility());
     summary?.focus();
     return !!summary && document.activeElement===summary;
   });
   await cdp('Input.dispatchKeyEvent',{type:'rawKeyDown',key:'Enter',code:'Enter',windowsVirtualKeyCode:13,nativeVirtualKeyCode:13});
   await cdp('Input.dispatchKeyEvent',{type:'keyUp',key:'Enter',code:'Enter',windowsVirtualKeyCode:13,nativeVirtualKeyCode:13});
   await delay(300);
   const keyboardOpened=await raw(()=>!!document.querySelector('[data-operation-list] details[open]'));
   return {sizes,focus,keyboardOpened};
 });
} finally {
  await writeFile(file(evidenceName),JSON.stringify({browser:bversion,scenarios:evidence},null,2)+'\n');
  ws?.close();
  edge.kill();
}
