import test from 'node:test';
import assert from 'node:assert/strict';
import { closureDecision, closureProgress, evaluationDecision } from '../../resources/js/participation-demo.js';

const now = Date.parse('2026-11-16T14:00:00Z');
const agenda = { id: 701, state: 'finalizada' };
const second = { id: 702, state: 'finalizada' };
const canceled = { id: 703, state: 'cancelada' };
const entry = (personId, agendaId, overrides = {}) => ({
    id: agendaId * 100 + personId, personId, agendaId, selection: 'aprovada', presence: 'presente', ...overrides,
});
const manager = {
    id: 7, actor: { type: 'ong', id: 1 }, eventStatus: 'ativo', canClose: true, canReviewVolunteers: true, writable: true,
    agendas: [agenda, second, canceled],
    records: [entry(11, 701), entry(11, 702), entry(12, 702), entry(13, 703), entry(14, 701, { presence: 'ausente' }),
        entry(15, 701, { withdrawal: 'tardia' })],
};
const person = {
    ...manager, actor: { type: 'pessoa', id: 12 }, canReviewVolunteers: false, canClose: false, reviewOng: 1,
    agendas: [second], eventAgendas: manager.agendas, records: [entry(12, 702)],
};
const volunteer = (id, history = [], fixture = manager, note = 4) => evaluationDecision(fixture, history, 'avaliarVoluntario', id, note, now);
const ong = (history = [], fixture = person, id = 12) => evaluationDecision(fixture, history, 'avaliarOng', id, 5, now);
const close = history => closureDecision(manager, history, 'finalizarEvento', 7, now);

test('one immutable volunteer review per Person and Event unlocks closure only after the last valid review', () => {
    const first = volunteer(11);
    assert.equal(close([]), null);
    assert.equal(first.personId, 11);
    assert.equal(first.change.nota, 4);
    assert.equal(volunteer(11, [first]), null);
    const last = volunteer(12, [first]);
    assert.deepEqual([[], [first], [first, last]].map(history =>
        closureProgress(manager, history, now).avaliacoesObrigatoriasPendentes.length), [2, 1, 0]);
    assert.equal(close([first]), null);
    const closed = close([first, last]);
    assert.ok(closed);
    const history = JSON.parse(JSON.stringify([first, last, closed]));
    assert.equal(closureProgress(manager, history, now).avaliacoesConcluidas.size, 2);
    assert.equal(volunteer(12, history), null);
    assert.equal(close(history), null);
});

test('rejects invalid notes, non-participants, canceled Agendas, self-review, and a manager with no evaluation assignment', () => {
    for (const note of [null, '', 0, 6, 1.5, '5', NaN]) assert.equal(volunteer(11, [], manager, note), null);
    for (const id of [13, 14, 15, 701, 99]) assert.equal(volunteer(id), null);
    assert.equal(volunteer(11, [], { ...manager, canReviewVolunteers: false }), null);
    assert.equal(volunteer(11, [], { ...manager, actor: { type: 'pessoa', id: 22 } }), null);
    assert.equal(volunteer(11, [], { ...manager, actor: { type: 'organizador', id: 11 } }), null);
    assert.ok(volunteer(11, [], { ...manager, actor: { type: 'organizador', id: 22 } }));
    assert.equal(volunteer(11, [], { ...manager, agendas: [agenda, { ...second, state: 'ativa' }] }), null);
    assert.equal(volunteer(11, [], { ...manager, eventStatus: 'finalizado' }), null);
});

test('optional ONG feedback has its own key, checks all Agendas, and contributes nothing to closure', () => {
    const optional = ong();
    assert.equal(optional.ongId, 1);
    assert.equal(ong([optional]), null);
    assert.equal(ong([], person, 11), null);
    assert.equal(ong([], { ...person, writable: false }), null);
    assert.equal(ong([], { ...person, reviewOng: null }), null);
    assert.equal(ong([], { ...person, records: [entry(12, 702, { presence: 'ausente' })] }), null);
    assert.equal(ong([], { ...person, eventAgendas: [second, { ...agenda, state: 'ativa' }] }), null);
    assert.equal(ong([], { ...person, eventStatus: 'finalizado' }), null);
    assert.equal(close([optional]), null);
    assert.ok(volunteer(12, [optional]));
    assert.ok(ong([volunteer(12)]));
    assert.ok(close([volunteer(11), volunteer(12)]));
});
