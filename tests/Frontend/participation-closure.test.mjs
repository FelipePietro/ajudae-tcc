import test from 'node:test';
import assert from 'node:assert/strict';
import { closureDecision, closureProgress, participationDecision } from '../../resources/js/participation-demo.js';

const end = Date.parse('2026-11-16T10:00:00-03:00');
const first = { id: 701, start: '2026-11-16T09:00:00-03:00', end: new Date(end).toISOString(), state: 'ativa', minutes: 60 };
const second = { ...first, id: 702, state: 'cancelada' };
const approved = { id: 70111, agendaId: 701, personId: 11, selection: 'aprovada', presence: 'pendente', withdrawal: null };
const fixture = { id: 7, actor: { type: 'ong', id: 1 }, eventStatus: 'ativo', writable: true, canClose: true, agendas: [first, second], records: [] };
const agendaDecision = (event, history = [], now = end) => closureDecision(event, history, 'finalizarAgenda', first.id, now);
const eventDecision = (event, history = []) => closureDecision(event, history, 'finalizarEvento', event.id, end);

test('an empty Agenda can close at its end and a canceled sibling is terminal', () => {
    assert.equal(agendaDecision(fixture, [], end - 1), null);
    assert.equal(eventDecision(fixture), null);
    const entry = agendaDecision(fixture);
    assert.deepEqual(entry, {
        action: 'finalizarAgenda', eventId: 7, agendaId: 701, actor: fixture.actor,
        time: end, minutes: 60, previous: { state: 'ativa' }, change: { state: 'finalizada' },
    });
    const history = JSON.parse(JSON.stringify([entry]));
    assert.deepEqual(closureProgress(fixture, history, end).agendasTerminadas, [701, 702]);
    assert.equal(agendaDecision(fixture, history), null);
    assert.equal(closureDecision(fixture, [], 'finalizarAgenda', 702, end), null);
    const eventEntry = eventDecision(fixture, history);
    assert.deepEqual(eventEntry.change, { state: 'finalizado' });
    assert.equal(eventDecision(fixture, [...history, eventEntry]), null);
});

test('only active approvals require attendance, with absence and withdrawal preserving their history', () => {
    const event = { ...fixture, records: [
        approved,
        { ...approved, id: 70112, personId: 12, presence: 'ausente' },
        { ...approved, id: 70113, personId: 13, presence: 'presente', student: true, monitoring: 'pendente' },
        { ...approved, id: 70114, personId: 14, withdrawal: 'tardia' },
        { ...approved, id: 70115, personId: 15, selection: 'pendente' },
    ] };
    assert.equal(agendaDecision(event), null);
    const absent = participationDecision(event, [], approved.id, 'ausente', end);
    const [agenda] = closureProgress(event, [absent], end).agendas;
    assert.deepEqual([agenda.approved, agenda.resolved, agenda.pending, agenda.monitoringPending], [3, 3, 0, true]);
    const closure = agendaDecision(event, [absent]);
    assert.ok(closure);
    assert.equal(participationDecision(event, [absent, closure], 70113, 'validada', end), null);
    assert.equal(eventDecision(event, [absent, closure]), null);
});

test('two confirmed attendances require a single valid review for the matching Person and Event', () => {
    const present = { ...approved, presence: 'presente' };
    const event = { ...fixture, agendas: [
        { ...first, state: 'finalizada' },
        { ...second, state: 'finalizada' },
        { ...second, id: 703 },
    ], records: [
        present, { ...present, id: 70211, agendaId: 702 },
        { ...present, id: 70312, agendaId: 703, personId: 12 },
    ], avaliacoesConcluidas: [
        { eventId: 8, personId: 11, nota: 4 },
        { eventId: 7, personId: 11, nota: 6 },
        { eventId: 7, personId: 12, nota: 5 },
    ] };
    const progress = closureProgress(event, [], end);
    assert.deepEqual(progress.pessoasPresentesDistintas, [11]);
    assert.deepEqual(progress.avaliacoesObrigatoriasPendentes, [11]);
    assert.equal(eventDecision(event), null);
    // Contract test input only. The rendered demonstration creates no reviews.
    const evaluated = { ...event, avaliacoesConcluidas: [...event.avaliacoesConcluidas, { eventId: 7, personId: 11, nota: 4 }] };
    assert.ok(eventDecision(evaluated));
});

test('unauthorized actors, invalid targets, terminal Events and Events without agendas cannot close', () => {
    for (const actor of ['pessoa', 'administrador', 'visitante']) {
        const event = { ...fixture, actor: { type: actor, id: 1 } };
        assert.equal(agendaDecision(event), null);
        assert.equal(eventDecision(event), null);
    }
    assert.equal(agendaDecision({ ...fixture, canClose: false }), null);
    assert.equal(agendaDecision({ ...fixture, eventStatus: 'cancelado' }), null);
    assert.equal(agendaDecision({ ...fixture, eventStatus: 'finalizado' }), null);
    assert.equal(eventDecision({ ...fixture, agendas: [] }), null);
    assert.equal(closureDecision(fixture, [], 'reabrir', first.id, end), null);
    assert.equal(closureDecision(fixture, [], 'finalizarAgenda', 999, end), null);
    assert.equal(closureDecision(fixture, [], 'finalizarEvento', 999, end), null);
});
