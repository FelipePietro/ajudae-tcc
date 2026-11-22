import {test} from 'node:test';
import assert from 'node:assert/strict';
import {execFileSync} from 'node:child_process';
import {transition,resolveInviteIdentity,invitationUrl} from '../../resources/js/organizer-demo.js';

const fixtures=JSON.parse(execFileSync(process.env.AJUDAE_PHP || 'php',['tests/Frontend/organizer-fixtures.php'],{encoding:'utf8',env:{...process.env,SESSION_DRIVER:'array',CACHE_STORE:'array',DB_HOST:'127.0.0.1',DB_PORT:'1',DB_URL:''}}));

test('invitation URL survives refresh with only fixture IDs and preserves context',()=>{
 const href='http://127.0.0.1:8000/ong/organizadores?contexto=ong&jornada=ong-vazia#convites';
 const url=new URL(invitationUrl(href,new Set([3,1,1,99,'private@example.test'])));
 assert.equal(url.searchParams.get('convites'),'1,3');
 assert.equal(url.searchParams.get('contexto'),'ong');
 assert.equal(url.searchParams.get('jornada'),'ong-vazia');
 assert.equal(url.hash,'#convites');
 assert.equal(new URL(invitationUrl(url.href,[])).searchParams.has('convites'),false);
});
test('promotion validates justification, becomes pending once and does not retain text',()=>{
    assert.throws(()=>transition(fixtures.elegivel,{type:'request',justification:'   '}),/justificativa/);
    assert.throws(()=>transition(fixtures.elegivel,{type:'request',justification:'x'.repeat(2001)}),/justificativa/);
    const next=transition(fixtures.elegivel,{type:'request',justification:'Quero apoiar minha comunidade.'});
    assert.equal(next.promotion.status,'pendente');assert.equal(JSON.stringify(next).includes('Quero apoiar'),false);
    assert.throws(()=>transition(next,{type:'request',justification:'Outra solicitação'}),/outra solicitação/);
    assert.deepEqual(next.person,fixtures.elegivel.person);
});
test('pending invitation grants no membership, acceptance is exclusive and refuses conflicting independent work',()=>{
    const before=fixtures['convite-pendente'];assert.equal(before.links.some(link=>link.status==='ativo'),false);
    const next=transition(before,{type:'accept',ongId:1,personId:1});assert.equal(next.links.find(link=>link.ong_id===1).status,'ativo');
    assert.throws(()=>transition(next,{type:'accept',ongId:1,personId:1}),/pendente/);
    next.links.push({vinculo_ong_organizador_id:99,ong_id:2,pessoa_id:1,status:'convite_pendente'});
    assert.throws(()=>transition(next,{type:'accept',ongId:2,personId:1}),/vínculo ativo/);
    assert.throws(()=>transition(fixtures['convite-bloqueado'],{type:'accept',ongId:1,personId:1}),/Eventos independentes/);
    const refused=transition(before,{type:'decline',ongId:1,personId:1});assert.equal(refused.links.find(link=>link.ong_id===1).status,'recusado');
});
test('ONG cannot accept its own invitations, duplicate invitations and unrelated actions are blocked',()=>{
    const state=fixtures['ong-vinculos'];assert.throws(()=>transition(state,{type:'accept',ongId:1,personId:1}),/organizador destinatário/);
    assert.throws(()=>transition(fixtures['convite-pendente'],{type:'invite',personId:1}),/conta ativa/);
    const once=transition(state,{type:'invite',personId:3});assert.throws(()=>transition(once,{type:'invite',personId:3}),/convite pendente/);
    assert.equal(once.links.filter(link=>link.pessoa_id===3).length,2);assert.equal(once.links.find(link=>link.pessoa_id===3).status,'encerrado');
});
test('responsibility reassignment preserves ownership, forces reapproval and unlocks permitted membership ending',()=>{
    const state=fixtures['ong-vinculos'];assert.throws(()=>transition(state,{type:'end',ongId:1,personId:1}),/Evento não encerrado/);
    assert.throws(()=>transition(state,{type:'responsibility',eventId:1,personId:3}),/vinculado/);
    const reassigned=transition(state,{type:'responsibility',eventId:1,personId:null});
    const event=reassigned.events.find(event=>event.evento_id===1);assert.equal(event.ong_id,1);assert.equal(event.pessoa_id,null);assert.equal(event.status_evento,'aguardando a confirmação');
    const ended=transition(reassigned,{type:'end',ongId:1,personId:1});assert.equal(ended.links.find(link=>link.pessoa_id===1).status,'encerrado');
    const started=structuredClone(state);started.events[0].started=true;assert.throws(()=>transition(started,{type:'responsibility',eventId:1,personId:null}),/início/);
    const terminal=structuredClone(state);terminal.events[0].status_evento='finalizado';assert.throws(()=>transition(terminal,{type:'responsibility',eventId:1,personId:null}),/encerramento/);
    const free=transition(fixtures['vinculo-livre'],{type:'end',ongId:1,personId:1});assert.equal(free.links.find(link=>link.ong_id===1).status,'encerrado');
});

test('identifier lookup is trimmed, exact, synthetic and never treated as a backend email contract',()=>{
 const people=fixtures['ong-vinculos'].people;
 assert.equal(resolveInviteIdentity(people,' SARA.EXEMPLO ').pessoa_id,2);
 assert.equal(resolveInviteIdentity(people,' rafael.exemplo@example.test ').pessoa_id,3);
 for(const value of ['','  ','a@','missing@example.test','inexistente','https://example.test']) assert.throws(()=>resolveInviteIdentity(people,value));
 assert.throws(()=>resolveInviteIdentity(people,'x'.repeat(65)),/64 caracteres/);
 const person=resolveInviteIdentity(people,'rafael.exemplo');const next=transition(fixtures['ong-vinculos'],{type:'invite',personId:person.pessoa_id});
 assert.equal(next.links.at(-1).status,'convite_pendente');
 assert.throws(()=>transition(next,{type:'invite',personId:person.pessoa_id}),/convite pendente/);
});
