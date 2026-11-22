import assert from 'node:assert/strict';
import test from 'node:test';
import { applyHistory, closureDecision } from '../../resources/js/participation-demo.js';
import { experienceAmount, experienceCredits, experienceLedger, experienceSummary, reconcileExperience } from '../../resources/js/participation-xp.js';

const start = Date.parse('2026-11-15T09:00:00-03:00');
const now = start + 24 * 60 * 60_000;
const makeAgenda = (minutes, number = 1, state = 'finalizada') => ({
    id: 700 + number, number, state, minutes, start: new Date(start).toISOString(),
    end: new Date(start + minutes * 60_000).toISOString(), initiallyClosed: state === 'finalizada',
});
const makeRecord = (agendaId = 701, overrides = {}) => ({
    id: agendaId * 100 + 1, agendaId, personId: 1, selection: 'aprovada', presence: 'presente', ...overrides,
});
const campaign = (factor, overrides = {}) => ({
    id: 1, factor, name: 'Rede de apoio', active: true, categoryId: 4,
    start: new Date(start).toISOString(), end: new Date(start).toISOString(),
    configuredAt: new Date(start).toISOString(), ...overrides,
});
const fixture = (agendas, records, campaigns = []) => ({
    id: 7, title: 'Oficina', eventStatus: 'ativo', categoryId: 4, agendas, records, campaigns,
    writable: true, canClose: true, actor: { type: 'ong', id: 1 },
});

test('RN025 calcula duração proporcional e arredonda a metade decimal positiva para cima', () => {
    for (const [minutes, expected] of [[30, 50], [45, 75], [49, 82], [60, 100], [61, 100], [63, 101], [75, 103], [90, 105], [150, 115]]) {
        assert.equal(experienceAmount(makeAgenda(minutes), fixture([], []), now).total, expected);
    }
    for (const minutes of [0, 29, 29.5, -30]) assert.equal(experienceAmount(makeAgenda(minutes), fixture([], []), now), null);
    assert.equal(experienceAmount({ ...makeAgenda(60), minutes: 90 }, fixture([], []), now), null);
    assert.equal(experienceAmount(makeAgenda(60), fixture([], []), NaN), null);
});

test('RN026 filtra categoria, vigência no início e data de configuração e aplica o teto à temática', () => {
    const first = campaign(2);
    const other = campaign(1.25, { id: 2 });
    const invalid = [
        campaign(2, { active: false }), campaign(2, { categoryId: 5 }),
        campaign(2, { start: new Date(start + 1).toISOString() }),
        campaign(2, { configuredAt: new Date(now + 1).toISOString() }),
        campaign(2, { end: new Date(start - 1).toISOString() }), campaign(-1), campaign(NaN),
    ];
    const amount = experienceAmount(makeAgenda(90), fixture([], [], [first, other, ...invalid]), now);
    assert.equal(amount.thematicFactor, 2.5);
    assert.equal(amount.total, 263);
    assert.deepEqual(amount.campaigns, [first, other]);
    assert.equal(experienceAmount(makeAgenda(60), fixture([], [], [first, campaign(2, { id: 2 })]), now).total, 300);
    assert.equal(experienceAmount(makeAgenda(90), fixture([], [], [first, campaign(2, { id: 2 })]), now).total, 315);
    assert.equal(experienceAmount(makeAgenda(90), fixture([], [], invalid), now).thematicFactor, 1);
    assert.equal(experienceAmount(makeAgenda(60), fixture([], [], [campaign(1.005)]), now).total, 101);
    assert.equal(experienceAmount(makeAgenda(60), fixture([], [], [campaign(1.004999)]), now).total, 100);
});

test('somente inscrição válida e presente em Agenda finalizada e Evento elegível recebe crédito', () => {
    const agenda = makeAgenda(60);
    const record = makeRecord();
    const model = fixture([agenda], [record]);
    assert.equal(experienceCredits(model, agenda, [
        record, makeRecord(702), makeRecord(701, { id: 70102, presence: 'ausente' }),
        makeRecord(701, { id: 70103, selection: 'pendente' }), makeRecord(701, { id: 70104, withdrawal: 'normal' }),
        makeRecord(701, { id: 70105, presence: 'pendente' }), makeRecord(701, { id: 70106, personId: 0 }),
    ], now).length, 1);
    for (const state of ['ativa', 'cancelada']) assert.deepEqual(experienceCredits(model, { ...agenda, state }, [record], now), []);
    for (const eventStatus of ['cancelado', 'reanalise', 'reprovado']) {
        assert.deepEqual(experienceCredits({ ...model, eventStatus }, agenda, [record], now), []);
    }
    assert.deepEqual(experienceCredits(model, agenda, [record], start), []);
});

test('reconciliação é idempotente, preserva snapshots e limita o extrato à Pessoa selecionada', () => {
    const agendas = [makeAgenda(90, 1, 'ativa'), makeAgenda(30, 2), makeAgenda(60, 3, 'cancelada')];
    const model = fixture(agendas, [makeRecord(), makeRecord(702), makeRecord(703),
        makeRecord(701, { id: 70111, personId: 11 })], [campaign(2), campaign(2, { id: 2 })]);
    assert.deepEqual(experienceSummary(1, experienceLedger(model, [])), { personId: 1, total: 0, level: null, rows: [] });
    const close = closureDecision(model, [], 'finalizarAgenda', 701, now);
    const history = reconcileExperience(model, [close], now, applyHistory);
    assert.equal(closureDecision(model, history, 'finalizarAgenda', 701, now), null);
    assert.strictEqual(reconcileExperience(model, history, now, applyHistory), history);

    const later = { ...model, title: 'Título editado', campaigns: [campaign(1.1)], actor: { type: 'pessoa', id: 1 } };
    assert.strictEqual(reconcileExperience(later, history, now, applyHistory), history);
    const ledger = experienceLedger(later, [...history, ...history, ...history.map(entry => ({ ...entry, eventId: 8 }))]);
    assert.equal(experienceSummary(1, ledger).total, 465);
    assert.equal(experienceSummary(1, ledger).rows.length, 2);
    assert.equal(experienceSummary(1, ledger).rows[0].eventTitle, 'Oficina');
    assert.equal(experienceSummary(11, ledger).total, 315);
    assert.equal(experienceSummary(2, ledger).total, 0);
    assert.equal(experienceLedger({ ...later, records: [makeRecord(701, { personId: 2 })] }, history).size, 0);
});

test('reconciliação usa o comparecimento e a campanha conhecidos no fechamento legado', () => {
    const agenda = makeAgenda(60, 1, 'ativa');
    const record = makeRecord(701, { presence: 'pendente' });
    const model = fixture([agenda], [record], [campaign(2, { configuredAt: new Date(now + 1000).toISOString() })]);
    const presence = { recordId: record.id, action: 'presente', change: { presence: 'presente' } };
    const close = closureDecision(model, [presence], 'finalizarAgenda', 701, now);
    const absent = { recordId: record.id, action: 'ausente', change: { presence: 'ausente' } };
    const history = reconcileExperience(model, [presence, close, absent], now + 2000, applyHistory);
    assert.equal(experienceSummary(1, experienceLedger(model, history)).total, 100);
    assert.equal(experienceSummary(1, experienceLedger(model, history)).rows[0].creditedAt, now);
    assert.strictEqual(reconcileExperience(model, history, now + 2000, applyHistory), history);
    assert.deepEqual(reconcileExperience({ ...model, agendas: [{ ...agenda, state: 'finalizada' }] }, [presence], now, applyHistory), [presence]);
});
