import test from 'node:test';
import assert from 'node:assert/strict';
import { reliabilitySummary, rankingRows } from '../../resources/js/participation-reliability-core.js';
import { participationDecision, applyHistory } from '../../resources/js/participation-demo.js';
import { experienceLedger } from '../../resources/js/participation-xp.js';
const time = Date.parse('2026-11-15T12:00:00Z');
const make = (states, closed = true) => ({ id: 7, eventStatus: 'ativo', writable: true, actor: { type: 'pessoa', id: 1 },
    agendas: states.map((_, i) => ({ id: i + 1, number: i + 1, state: closed ? 'finalizada' : 'ativa', initiallyClosed: closed, end: new Date(time + i * 1000).toISOString(), start: new Date(time - 10000).toISOString() })),
    records: states.map((state, i) => ({ id: i + 1, agendaId: i + 1, personId: 1, selection: 'aprovada', presence: state, withdrawal: null })) });
const summary = (fixture, history = [], id = 1) => reliabilitySummary(fixture, history, id);
const three = ['presente', 'presente', 'presente'];
test('RN027 oculta score para 0/2 e isola a identidade; terceira confirmação libera', () => {
    for (const count of [0, 2]) assert.equal(summary(make(three.slice(0, count))).score, null);
    assert.equal(summary(make(three)).score, 100);
    assert.equal(summary(make(three), [], 99).score, null);
});
test('penalidades -20/-10/-5, ocorrência justificada e cancelamento sem penalidade', () => {
    const fixture = make([...three, 'ausente', 'pendente', 'pendente', 'ausente', 'ausente']);
    fixture.records[4].withdrawal = 'tardia'; fixture.records[5].withdrawal = 'normal';
    fixture.records[6].justification = { status: 'deferida' };
    fixture.agendas[7].state = 'cancelada';
    const result = summary(fixture);
    assert.equal(result.score, 65); assert.equal(result.rows.length, 7); assert.equal(result.rows[6].reversed, true);
    fixture.eventStatus = 'cancelado'; assert.equal(summary(fixture).score, null);
});
test('piso, teto, sequência e +3 só após Agenda concluída, sem duplicar inscrição', () => {
    const fixture = make([...three, ...Array(6).fill('ausente'), 'presente']);
    assert.equal(summary(fixture).score, 3);
    fixture.records.push(fixture.records.at(-1)); assert.equal(summary(fixture).score, 3);
    fixture.agendas.at(-1).initiallyClosed = false; fixture.agendas.at(-1).state = 'ativa';
    assert.equal(summary(fixture).score, 0);
    const history = [{ action: 'finalizarAgenda', eventId: 7, agendaId: 10, time: time + 10000 }];
    assert.equal(summary(fixture, history).score, 3);
    assert.equal(summary(make(three, false)).score, 100);
});
test('deferimento reverte uma vez, preserva histórico e não altera XP; indeferimento mantém', () => {
    const fixture = make([...three, 'ausente']);
    const pending = { recordId: 4, change: { justification: { status: 'em_analise', reason: 'transporte' } }, action: 'justificar', time, actor: { type: 'pessoa', id: 1 } };
    const accepted = { ...pending, action: 'deferida', change: { justification: { status: 'deferida', reason: 'transporte' } } };
    const history = [pending, accepted, accepted, accepted, accepted, accepted];
    assert.equal(summary(fixture, history).score, 100); assert.equal(summary(fixture, history).rows.at(-1).type, 'Ausência');
    assert.equal(summary(fixture, [pending]).score, 80);
    assert.equal(summary(fixture, [{ ...accepted, change: { justification: { status: 'indeferida' } } }]).score, 80);
    assert.equal(experienceLedger(fixture, history).size, 0);
    const manager = { ...fixture, actor: { type: 'ong', id: 1 } };
    assert.equal(participationDecision(manager, history, 4, 'deferida', time, ''), null);
    assert.equal(applyHistory(fixture.records[3], history).presence, 'ausente');
});
test('replay remove efeito com saturação e não soma crédito artificial', () => {
    const fixture = make([...three, 'ausente', ...Array(7).fill('presente'), 'ausente']);
    assert.equal(summary(fixture).score, 80);
    assert.equal(summary(fixture, [{ recordId: 4, change: { justification: { status: 'deferida' } } }]).score, 80);
});
const people = Array.from({ length: 18 }, (_, n) => ({ id: n + 1, name: `Pessoa ${n + 1}`, city: n % 2 ? 'São Paulo' : 'Guarulhos', approved: true, role: n === 3 ? 'organizador' : 'voluntario', rating: 5 - n / 10, score: 100 - n }));
const ledger = new Map(people.map(person => [person.id, { personId: person.id, total: person.id * 100 }]));
test('RN028 ordena XP exclusivo entre aprovadas e preserva organizador; exclui LGPD', () => {
    const population = people.map(person => ({ ...person, approved: person.id !== 18, deleted: person.id === 17, anonymous: person.id === 16 }));
    const result = rankingRows(population, ledger, { perPage: 20 });
    assert.equal(result.rows[0].id, 15); assert.equal(result.rows.at(-1).id, 1);
    assert.ok(result.rows.some(row => row.id === 4));
    assert.ok(result.rows.every(row => !('rating' in row) && !('score' in row)));
    assert.equal(rankingRows(population, ledger, { city: 'Campinas' }).total, 0);
});
test('desempate por ID, cidade, busca conserva posição e paginação', () => {
    const tied = new Map([[1, { personId: 1, total: 500 }], [2, { personId: 2, total: 500 }]]);
    assert.deepEqual(rankingRows(people, tied).rows.slice(0, 2).map(row => row.id), [1, 2]);
    const result = rankingRows(people, ledger, { city: 'Guarulhos', search: 'pessoa 1', page: 999 });
    assert.ok(result.rows.every(row => row.city === 'Guarulhos'));
    assert.ok(result.rows.every(row => row.position === rankingRows(people, ledger, { city: 'Guarulhos' }).scope.find(p => p.id === row.id).position));
    assert.equal(rankingRows(people, ledger, { page: 2 }).rows[0].position, 11);
    assert.equal(rankingRows(people, ledger, { search: 'inexistente' }).pages, 1);
});
