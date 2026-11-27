import test from 'node:test';
import assert from 'node:assert/strict';
import { applyHistory, participationDecision, allowedActions, readHistory, justificationPresentation } from '../../resources/js/participation-demo.js';
import { reliabilitySummary } from '../../resources/js/participation-reliability-core.js';
const now=Date.parse('2026-11-17T15:00:00Z');
const fixture=()=>({id:7,storageId:'7.confiabilidade',eventStatus:'ativo',writable:true,actor:{type:'pessoa',id:1},
 agendas:[701,702,703,704,705].map(id=>({id,number:id-700,state:'finalizada',initiallyClosed:true,start:new Date(now-3600000).toISOString(),end:new Date(now-1800000).toISOString()})),
 records:[701,702,703,704,705].map(id=>({id:id*100+1,agendaId:id,personId:1,selection:'aprovada',presence:id<=703?'presente':id===704?'ausente':'pendente',withdrawal:id===705?'tardia':null,justification:null}))});
const agenda=(model,id)=>model.agendas.find(a=>a.id===id);
test('mesmo store compartilha inscrição entre Pessoa e gestão; Agenda5 não contamina Agenda4',()=>{
 const model=fixture();const entry=participationDecision(model,[],70501,'justificar',now,'transporte');
 const storage={getItem:key=>key==='ajudae.participacao.v1.7.confiabilidade'?JSON.stringify([entry]):null};
 const history=readHistory(storage,model.storageId);
 const pending=applyHistory(model.records[4],history);const other=applyHistory(model.records[3],history);
 assert.equal(pending.justification.status,'em_analise');assert.equal(other.justification,null);
 const manager={...model,actor:{type:'ong',id:1}};
 assert.deepEqual(allowedActions(manager,agenda(model,705),pending,now),['deferida','indeferida']);
 assert.deepEqual(allowedActions(manager,agenda(model,704),other,now),[]);
 assert.match(justificationPresentation(other).status,/Nenhuma justificativa.*Agenda/);
 assert.match(justificationPresentation(pending).reason,/Imprevisto no deslocamento/);
 assert.deepEqual(readHistory(storage,'7'),[]);
});
test('aceite da ausência é único, conserva motivo, fundamento e presença; +20 sem XP',()=>{
 const model=fixture(),manager={...model,actor:{type:'ong',id:1}};
 const sent=participationDecision(model,[],70401,'justificar',now,'transporte');
 const accepted=participationDecision(manager,[sent],70401,'deferida',now+1,undefined,'analise');
 assert.ok(accepted);const history=[sent,accepted];const record=applyHistory(model.records[3],history);
 assert.equal(record.presence,'ausente');assert.equal(record.justification.reason,'transporte');
 assert.equal(record.justification.decisionReason,'analise');assert.ok(!Object.hasOwn(accepted,'xp'));
 assert.equal(reliabilitySummary(model,[],1).score,70);assert.equal(reliabilitySummary(model,history,1).score,90);
 assert.equal(participationDecision(manager,history,70401,'deferida',now+2,undefined,'analise'),null);
 assert.equal(participationDecision(manager,[sent],70401,'deferida',now+2,undefined,''),null);
 assert.match(justificationPresentation(record).decision,/Análise da justificativa/);
});
test('recusa mantém tardia e outros titulares/eventos/atores não decidem',()=>{
 const model=fixture(),manager={...model,actor:{type:'ong',id:1}};
 const sent=participationDecision(model,[],70501,'justificar',now,'imprevisto');
 const refused=participationDecision(manager,[sent],70501,'indeferida',now+1,undefined,'analise');
 assert.equal(reliabilitySummary(model,[sent,refused],1).score,reliabilitySummary(model,[],1).score);
 assert.equal(applyHistory(model.records[4],[sent,refused]).withdrawal,'tardia');
 for(const actor of [{type:'pessoa',id:1},{type:'pessoa',id:99},{type:'visitante'},{type:'administrador',id:4}]) assert.equal(participationDecision({...model,actor},[sent],70501,'deferida',now),null);
 assert.equal(participationDecision({...manager,writable:false},[sent],70501,'deferida',now),null);
 assert.equal(participationDecision({...manager,records:[]},[sent],70501,'deferida',now),null);
});
test('projeção separa ausência/justificativa e só publica categorias conhecidas',()=>{
 const invalid=justificationPresentation({presence:'ausente',justification:{status:'em_analise',reason:'texto privado inesperado',decisionReason:'segredo'}});
 assert.equal(invalid.status,'Justificativa em análise');assert.equal(invalid.reason,'');assert.equal(invalid.decision,'');
 assert.equal(justificationPresentation({presence:'presente'}).visible,false);
});
