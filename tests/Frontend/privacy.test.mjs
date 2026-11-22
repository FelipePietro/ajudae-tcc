import test from 'node:test';
import assert from 'node:assert/strict';
import { clock, initialAccount, deleteAccount, requestDecision, readPrivacy } from '../../resources/js/privacy-demo.js';
const config = { clock, scenario:'livre', owner:'pessoa:1', blockers:[] };
test('seven-day window is fixed, reversible strictly before deadline, idempotent and preserves audit', () => {
 const active=initialAccount('livre'),pending=deleteAccount(active,'solicitar',config);
 assert.equal(pending.status,'pendente'); assert.equal(pending.requested,clock);
 assert.equal(deleteAccount(pending,'solicitar',config),null);
 assert.equal(deleteAccount(active,'solicitar',{...config,blockers:['Agenda ativa']}),null);
 const undo=deleteAccount(pending,'reverter',{...config,clock:'2026-11-25T11:59:59.999Z'});
 assert.equal(undo.status,'ativa');assert.equal(undo.history.length,2);assert.equal(pending.history.length,1);
 assert.equal(deleteAccount(pending,'reverter',{...config,clock:'2026-11-25T12:00:00.000Z'}),null);
 assert.equal(deleteAccount(pending,'reverter',{...config,clock:'2026-11-26T12:00:00.000Z'}),null);
 assert.equal(deleteAccount(initialAccount('anonimizada'),'reverter',config),null);
});
test('terminal fictional state is never reached by time or deletion action',()=>{
 assert.equal(initialAccount('prazo-encerrado').status,'pendente');
 assert.equal(deleteAccount(initialAccount('livre'),'anonimizar',config),null);
});
test('read-only grant cannot assume or decide; response requires analysis and catalog justification',()=>{
 const r={id:'EXEMPLO-P-1',owner:'pessoa:1',type:'acesso',status:'pendente',history:[]};
 assert.equal(requestDecision(r,'assumir',null,false),null);
 assert.equal(requestDecision(r,'concluida','orientacao',true),null);
 const review=requestDecision(r,'assumir',null,true);assert.equal(review.status,'em_analise');
 assert.equal(requestDecision(review,'concluida','',true),null);
 assert.equal(requestDecision(review,'concluida','__proto__',true),null);
 for(const status of ['concluida','recusada']){const done=requestDecision(review,status,'orientacao',true);assert.equal(done.status,status);assert.equal(done.history.length,2);assert.equal(requestDecision(done,status,'orientacao',true),null);}
 assert.equal(requestDecision({...r,owner:'pessoa:2'},'assumir',null,true),null);
});
test('storage projection rejects foreign identities, duplicates, unknown types, URLs and secret fields',()=>{
 const row={id:'EXEMPLO-P-1',owner:'pessoa:1',type:'acesso',status:'pendente',history:[],secret:'do-not-copy',url:'https://example.invalid/private'};
 const store=readPrivacy({getItem:()=>JSON.stringify({requests:[row,row,{...row,id:'EXEMPLO-P-2',owner:'pessoa:2'},{...row,id:'EXEMPLO-P-3',type:'__proto__'}],accounts:{'pessoa:2:livre':initialAccount('livre')}})});
 assert.equal(store.requests.length,1);assert.equal(Object.keys(store.accounts).length,0);
 assert.doesNotMatch(JSON.stringify(store),/do-not-copy|https:|pessoa:2/);
 assert.throws(()=>readPrivacy({getItem:()=>'{broken'}));
 assert.throws(()=>readPrivacy({getItem:()=>'{"requests":null,"accounts":{}}'}));
});
