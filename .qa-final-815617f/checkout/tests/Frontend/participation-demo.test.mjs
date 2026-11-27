import test from 'node:test';
import assert from 'node:assert/strict';
import { allowedActions, applyHistory, participationDecision } from '../../resources/js/participation-demo.js';

const start = Date.parse('2026-11-17T09:00:00-03:00');
const day = 24 * 60 * 60 * 1000;
const agenda = { id: 701, state: 'ativa', start: new Date(start).toISOString() };
const record = { id: 70101, agendaId: 701, personId: 1, selection: 'aprovada', presence: 'pendente', student: true, monitoring: 'pendente', withdrawal: null, justification: null };
const manager = { writable: true, actor: { type: 'ong', id: 1 }, agendas: [agenda], records: [record] };
const holder = { ...manager, actor: { type: 'pessoa', id: 1 } };

test('attendance only happens once on a started active Agenda for a selected record', () => {
    assert.deepEqual(allowedActions(manager, agenda, record, start - 1), []);
    const entry = participationDecision(manager, [], record.id, 'presente', start);
    assert.deepEqual(entry.change, { presence: 'presente' });
    assert.deepEqual(entry.previous, { presence: 'pendente' });
    assert.equal(participationDecision(manager, [entry], record.id, 'ausente', start), null);
    assert.equal(participationDecision(holder, [], record.id, 'presente', start), null);
    assert.equal(participationDecision(manager, [], 999, 'presente', start), null);
    assert.deepEqual(allowedActions(manager, { ...agenda, state: 'cancelada' }, record, start), []);
    assert.deepEqual(allowedActions(manager, { ...agenda, state: 'finalizada' }, record, start), []);
    assert.deepEqual(allowedActions(manager, agenda, { ...record, selection: 'pendente' }, start), []);
    assert.deepEqual(allowedActions({ ...manager, writable: false }, agenda, record, start), []);
    assert.deepEqual(allowedActions({ ...manager, actor: { type: 'organizador', id: 1 } }, agenda, record, start), []);
});

test('monitoring requires student attendance and is resolved independently for each Agenda', () => {
    assert.equal(participationDecision(manager, [], record.id, 'validada', start), null);
    const attendance = participationDecision(manager, [], record.id, 'presente', start);
    const validation = participationDecision(manager, [attendance], record.id, 'validada', start);
    const otherAgenda = { ...record, id: 70201, agendaId: 702 };
    assert.deepEqual(validation.change, { monitoring: 'validada' });
    assert.equal(applyHistory(record, [attendance, validation]).presence, 'presente');
    assert.equal(applyHistory(otherAgenda, [attendance, validation]).monitoring, 'pendente');
    assert.deepEqual(allowedActions(manager, agenda, { ...record, student: false, presence: 'presente' }, start), []);
    assert.deepEqual(allowedActions(manager, agenda, { ...record, presence: 'ausente' }, start), []);
    const denial = participationDecision(manager, [attendance], record.id, 'rejeitada', start);
    assert.equal(applyHistory(record, [attendance, denial]).monitoring, 'rejeitada');
});

test('an accepted absence justification preserves the attendance outcome and decision trail', () => {
    const absence = participationDecision(manager, [], record.id, 'ausente', start);
    assert.equal(participationDecision(holder, [absence], record.id, 'justificar', start, 'invalid'), null);
    const request = participationDecision(holder, [absence], record.id, 'justificar', start, 'transporte');
    assert.equal(request.change.justification.status, 'em_analise');
    const approval = participationDecision(manager, [absence, request], record.id, 'deferida', start);
    assert.equal(approval.previous.justification.status, 'em_analise');
    const history = [absence, request, approval];
    assert.equal(applyHistory(record, history).presence, 'ausente');
    assert.equal(applyHistory(record, history).justification.status, 'deferida');
    assert.equal(participationDecision(manager, history, record.id, 'indeferida', start), null);
    assert.equal(participationDecision(holder, history, record.id, 'justificar', start, 'transporte'), null);
});

test('withdrawal observes the exact 24-hour boundary, exemptions and subsequent justification', () => {
    const normal = participationDecision(holder, [], record.id, 'desistir', start - day);
    const late = participationDecision(holder, [], record.id, 'desistir', start - day + 1);
    const exemptHolder = { ...holder, agendas: [{ ...agenda, exemption: true }] };
    const exempt = participationDecision(exemptHolder, [], record.id, 'desistir', start - 1);
    assert.equal(normal.change.withdrawal, 'normal');
    assert.equal(late.change.withdrawal, 'tardia');
    assert.equal(exempt.change.withdrawal, 'isenta');
    assert.equal(participationDecision(holder, [], record.id, 'desistir', start), null);
    assert.equal(participationDecision(exemptHolder, [exempt], record.id, 'justificar', start, 'transporte'), null);
    assert.equal(participationDecision(manager, [late], record.id, 'ausente', start), null);

    const request = participationDecision(holder, [late], record.id, 'justificar', start, 'horario');
    const approval = participationDecision(manager, [late, request], record.id, 'deferida', start);
    assert.equal(applyHistory(record, [late, request, approval]).withdrawal, 'tardia');
    const denial = participationDecision(manager, [late, request], record.id, 'indeferida', start);
    assert.equal(applyHistory(record, [late, request, denial]).justification.status, 'indeferida');
});
