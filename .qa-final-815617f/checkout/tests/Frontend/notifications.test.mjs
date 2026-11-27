import test from 'node:test';
import assert from 'node:assert/strict';
import {unreadCountLabel,catalog,seeds,mergeNotice,inbox,markRead,filtered,destination,readNotices,NOTICE_KEY} from '../../resources/js/notifications-demo.js';
const person={context:'pessoa',identity:'pessoa:1',role:'titular',permissions:[],scenario:'historico'};
const admin={context:'administrador',identity:'administrador:1',role:'titular',permissions:['usuarios.validar','eventos.aprovar'],variant:'revisao'};
test('recipient isolation, deduplication and read all never touch another identity',()=>{
 const other={...person,context:'ong',identity:'ong:1'};
 let rows=[...seeds(person),...seeds(other)];const count=rows.length;
 for(const row of seeds(person))rows=mergeNotice(rows,row);
 assert.equal(rows.length,count);rows=markRead(rows,person);
 assert.equal(inbox(rows,person).filter(r=>!r.read).length,0);
 assert.equal(inbox(rows,other).filter(r=>!r.read).length,3);
 assert.ok(inbox(rows,person).every(r=>r.recipient==='pessoa:1'));
});
test('admin permissions filter both historical messages and stale links',()=>{
 assert.deepEqual(seeds(admin).map(r=>r.kind),['filaPessoa','filaEvento']);
 assert.equal(destination({kind:'filaLgpd'},admin),null);
 assert.ok(destination({kind:'filaEvento'},admin).includes('visao=revisao'));
 assert.equal(inbox([{...seeds(admin)[0],kind:'filaLgpd'}],admin).length,0);
});
test('filters combine search, type and reading including accents',()=>{
 const rows=seeds(person);assert.ok(filtered(rows,{search:'candidatura',type:'Candidaturas',status:'nao-lidas'}).length);
 assert.equal(filtered(rows,{search:'inexistente'}).length,0);
 assert.equal(filtered(rows,{type:'Eventos'}).length,3);
 assert.equal(seeds({...person,scenario:'vazio'}).length,0);
});
test('destinations are local enums, unavailable or tampered URLs cannot redirect',()=>{
 for(const row of seeds(person)){assert.match(destination({...row,url:'https://evil.invalid',example:'../../private'},person),/^\/(minhas|meus|perfil)/);assert.equal(destination({...row,event:'//evil'},person),null);}
 assert.equal(destination({...seeds(person)[0],missing:true},person),null);
 assert.equal(destination({kind:'filaEvento'},person),null);
 assert.ok(destination({kind:'recusada'},person).includes('candidatura-rejeitada'));
});
test('corrupt state and storage failure are explicit, never successful empty results',()=>{
 assert.deepEqual(readNotices({getItem:()=>null}),[]);
 assert.throws(()=>readNotices({getItem:()=>'{'}));
 assert.throws(()=>readNotices({getItem:()=>JSON.stringify([{kind:'segredo'}])}));
 assert.throws(()=>readNotices({getItem:()=>{throw Error('denied');}}));
 assert.equal(NOTICE_KEY,'ajudae.notificacoes.v1');
});
test('privacy and RN031 messages: no notes, letters, documents or automatic approval',()=>{
 const texts=Object.values(catalog).map(v=>v.slice(1,3).join(' ')).join(' ');
 assert.doesNotMatch(texts,/CPF|token|carta|nota [1-5]|https?:/i);
 assert.match(catalog.aceite[2],/não aprova/);assert.match(catalog.presente[2],/encerramento/);
 assert.match(catalog.cancelamento[2],/Não há penalidade/);assert.match(catalog.alteracao[2],/até o início/);
});

test('linked organizer never opens another responsible event',()=>{const linked={...person,context:'organizador-vinculado',identity:'organizador-vinculado:1'};assert.equal(destination({kind:'pendencias',event:7},linked),null);assert.match(destination({kind:'pendencias',event:1},linked),/eventos\/1\/presenca/);});

test('badge count label keeps exact accessible counts and caps only the visual label',()=>{for(const [count,label] of [[0,'0'],[1,'1'],[9,'9'],[10,'10'],[99,'99'],[100,'99+']])assert.equal(unreadCountLabel(count),label);});

test('invitation notice opens the existing pending invitation journey',()=>{const config={...person,context:'organizador',identity:'organizador:1'};assert.equal(new URL(destination({kind:'convite'},config),'http://localhost').searchParams.get('jornada'),'convite-pendente');});

test('participation notice returns to its own Agenda without accepting foreign anchors',()=>{assert.equal(new URL(destination({kind:'deferida',event:7,agenda:704},person),'http://localhost').hash,'#agenda-704');assert.equal(new URL(destination({kind:'deferida',event:7,agenda:705},person),'http://localhost').hash,'#agenda-705');assert.equal(new URL(destination({kind:'deferida',event:7,agenda:804},person),'http://localhost').hash,'');});
