import assert from 'node:assert/strict';
import test from 'node:test';
import { availableActions, countEventReview, decide, displayState, matchesQueue } from '../../resources/js/admin-demo.js';

test('event review supports decisions only while pending and approval requires an active owner', () => {
    const record = { kind: 'evento', status: 'aguardando a confirmação' };
    assert.equal(decide(record, 'aprovar', '', 'completa', 'reprovado'), null);
    assert.equal(decide(record, 'reprovar', 'ignored', 'completa').reason, '');
    assert.deepEqual(decide(record, 'aprovar', '', 'completa').patch, { status: 'publicado', reason: '' });
    assert.equal(decide({ ...record, status: 'cancelado' }, 'aprovar', '', 'completa'), null);
});

test('person approval requires review ownership, required documents and when applicable a godparent', () => {
    const waiting = { kind: 'pessoa', status: 'aguardando_validacao', documents: ['pendente', 'aprovado'] };
    const record = { ...waiting, ...decide(waiting, 'assumir', '', 'completa').patch };
    assert.equal(decide(record, 'aprovar', '', 'completa'), null);
    assert.equal(decide(record, 'doc-0-rejeitado', '  ', 'completa'), null);
    assert.equal(decide(record, 'corrigir', 'x'.repeat(2001), 'completa'), null);
    const reviewed = { ...record, ...decide(record, 'doc-0-aprovado', '', 'completa').patch };
    assert.equal(decide(reviewed, 'aprovar', '', 'completa').to, 'aprovado');
    assert.equal(decide({ ...reviewed, blocked: 'Aguardando padrinho' }, 'aprovar', '', 'completa'), null);
    assert.deepEqual(availableActions({ ...reviewed, lock: 'outro' }, 'completa'), {});
    assert.deepEqual(Object.keys(availableActions({ ...reviewed, lock: 'expirado' }, 'completa')), ['assumir']);
    assert.equal(decide(reviewed, 'definitiva', 'Motivo', 'revisao'), null);
});

test('promotion and LGPD respect scope, reason and terminal outcomes', () => {
    const pending = { kind: 'promocao', status: 'pendente' };
    const review = { ...pending, ...decide(pending, 'assumir', '', 'completa').patch };
    assert.equal(decide(pending, 'aprovar', '', 'completa'), null);
    assert.equal(decide(review, 'reprovar', '  Critério não atendido.  ', 'completa').reason, 'Critério não atendido.');
    assert.equal(decide({ ...review, blocked: true }, 'aprovar', '', 'completa'), null);
    assert.equal(decide({ ...review, status: 'aprovada' }, 'reprovar', 'Motivo', 'completa'), null);
    const lgpd = { kind: 'lgpd', status: 'em_analise' };
    assert.equal(decide(lgpd, 'aprovar', 'Resposta', 'privacidade'), null);
    assert.equal(decide(lgpd, 'aprovar', '', 'completa'), null);
    assert.equal(decide(lgpd, 'aprovar', 'Resposta', 'completa').to, 'concluida');
});

test('ONG reenters review and padrinho refusal accepts an optional reason', () => {
    const organization = { kind: 'ong', status: 'ativo' };
    assert.equal(decide(organization, 'reavaliar', '', 'completa').to, 'aguardando aprovação');
    const godparent = { kind: 'padrinho', status: 'pendente' };
    assert.equal(decide(godparent, 'reprovar', '', 'completa').to, 'rejeitada');
    assert.equal(decide({ kind: 'ocorrencia', status: 'historico' }, 'aprovar', '', 'completa'), null);
});


test('event review counts distinguish initial approval and reanalysis and ignore completed decisions', () => {
    const initial = { status: 'aguardando a confirmação' };
    const revised = { ...initial, reanalysis: true };
    assert.deepEqual(countEventReview([initial, revised, { status: 'cancelado' }, { status: 'finalizado' }]), { initial: 1, reanalysis: 1 });
    assert.equal(displayState(revised), 'reanalise');
    const decided = { ...revised, ...decide({ ...revised, kind: 'evento' }, 'aprovar', '', 'completa').patch };
    assert.equal(displayState(decided), 'publicado');
    assert.deepEqual(countEventReview([initial, decided]), { initial: 1, reanalysis: 0 });
    assert.deepEqual(countEventReview([]), { initial: 0, reanalysis: 0 });
});

test('queue status is OR, words are AND, and the same filter reflects a decision', () => {
    const event = { kind: 'evento', status: 'aguardando a confirmação', title: 'Educação comunitária', description: 'Encontro', facts: { 'Responsável': 'Sara' } };
    assert.equal(matchesQueue(event, ['publicado', 'aguardando a confirmação'], '  EDUCACAO    sara '), true);
    assert.equal(matchesQueue(event, ['reprovado', 'cancelado'], 'educação'), false);
    assert.equal(matchesQueue(event, [], 'educação impossível'), false);
    const updated = { ...event, ...decide(event, 'aprovar', '', 'completa').patch };
    assert.equal(matchesQueue(updated, ['aguardando a confirmação'], ''), false);
    assert.equal(matchesQueue(updated, ['publicado', 'finalizado'], ''), true);
});
