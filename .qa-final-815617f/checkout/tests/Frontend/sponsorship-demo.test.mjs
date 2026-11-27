import test from 'node:test';
import assert from 'node:assert/strict';
import { applicantCase, applicantExample, decideRequest, eligibility, enroll, hasAccepted, readSponsorship, registrationRequest, requiredReason, resolveReferral, resubmit, saveSponsorship, sponsors } from '../../resources/js/sponsorship-demo.js';
import { availableActions, decide } from '../../resources/js/admin-demo.js';

const storage = new Map();
globalThis.sessionStorage = {
    getItem: key => storage.get(key) ?? null,
    setItem: (key, value) => storage.set(key, value),
};

test('eligibility checks boundaries, capacity, lack of data, blocks and volunteer role', () => {
    const minimum = { ...sponsors.principal.metrics, days: 60, attendance: 10, rating: 4.5, active: 1 };
    assert.equal(eligibility(minimum).label, 'Elegível');
    for (const changed of [{ days: 59 }, { attendance: 9 }, { rating: 4.4 }, { absences: 1 }, { withdrawals: 2 }, { depends: true }, { volunteer: false }]) {
        assert.equal(eligibility({ ...minimum, ...changed }).label, 'Não elegível');
    }
    assert.equal(eligibility(minimum, 1).label, 'Não elegível');
    assert.equal(eligibility({ ...minimum, rating: null }).label, 'Aguardando dados');
    assert.equal(eligibility({ ...minimum, blocked: true }).label, 'Bloqueado');
});

test('registration requires successful completion, accepts an empty code and stores allowlisted preferences only once', () => {
    storage.clear();
    assert.equal(resolveReferral(sponsors.principal.code).sponsor, 'principal');
    assert.equal(readSponsorship().enrolled, false);
    for (const code of ['bad','R8LM-V2CP-7JKS','T6DQ-B4ZR-3MNH',sponsors.insuficiente.code,sponsors.lotado.code]) {
        assert.ok(enroll(code).error);
        assert.equal(readSponsorship().enrolled, false);
    }
    assert.equal(enroll('').state.requests.length, 0);
    storage.clear();
    const state = enroll(sponsors.principal.code.toLowerCase(), { causas: ['1','1','private'], cpf: ['000'] }).state;
    assert.equal(state.requests[0].status, 'pendente');
    assert.equal(hasAccepted(state), false);
    assert.deepEqual(state.preferences, { causas: ['1'], habilidades: [], recursos: [] });
    assert.equal(enroll(sponsors.alternativa.code).state.requests.length, 1);
});

test('acceptance is scoped and separate from approval; refusal and pending stay blocked only when required', () => {
    storage.clear();
    const state = enroll(sponsors.principal.code).state;
    state.account = 'em_analise';
    const record = { id: applicantCase, kind: 'pessoa', status: state.account, lock: 'proprio', documents: ['aprovado','aprovado'] };
    assert.ok(availableActions(record, 'completa').aprovar);
    assert.equal(decide({ ...record, blocked: requiredReason }, 'aprovar', '', 'completa'), null);
    assert.ok(availableActions(record, 'completa')['pedir-padrinho']);
    assert.equal(decideRequest(state, 'alternativa', true), null);
    decideRequest(state, 'principal', true);
    assert.equal(state.account, 'em_analise');
    assert.equal(hasAccepted(state), true);
    assert.equal(decideRequest(state, 'principal', true), null);
    assert.ok(decide({ ...record, acceptedReferral: true }, 'aprovar', '', 'revisao'));
});

test('private correction requires an administrative decision and preserves refused requests', () => {
    storage.clear();
    const state = enroll(sponsors.principal.code, { causas: ['1'] }).state;
    decideRequest(state, 'principal', false);
    assert.equal(resubmit(sponsors.alternativa.code, state), null);
    state.required = true;
    state.account = 'correcao_solicitada';
    assert.ok(resubmit('', state).error);
    assert.ok(resubmit('T6DQ-B4ZR-3MNH', state).error);
    const updated = resubmit(sponsors.alternativa.code, state).state;
    assert.deepEqual(updated.requests.map(row => row.status), ['recusada','pendente']);
    assert.deepEqual(updated.preferences, {});
    assert.equal(updated.account, 'aguardando_validacao');
    assert.equal(hasAccepted(updated), false);
});

test('applicant examples are independent of the tab and can be replayed', () => {
    storage.clear();
    const registration = enroll(sponsors.principal.code).state;
    for (const [example, status, account] of [
        ['pendente', 'pendente', 'aguardando_validacao'],
        ['ativo', 'ativo', 'em_analise'],
        ['recusado', 'recusada', 'aguardando_validacao'],
        ['correcao', 'recusada', 'correcao_solicitada'],
        ['sem-indicacao', undefined, 'aguardando_validacao'],
    ]) {
        assert.equal(applicantExample(example).requests[0]?.status, status);
        assert.equal(applicantExample(example).account, account);
    }
    const updated = resubmit(sponsors.alternativa.code, applicantExample('correcao')).state;
    assert.deepEqual(updated.requests.map(row => row.status), ['recusada', 'pendente']);
    assert.equal(applicantExample('correcao').requests.length, 1);
    assert.equal(applicantExample('inexistente'), null);
    assert.deepEqual(readSponsorship({ strict: true }), registration);
});

test('correction replaces a pending indication once and protects an accepted link', () => {
    const pending = { ...applicantExample('pendente'), required: true, account: 'correcao_solicitada' };
    assert.equal(resubmit(sponsors.principal.code, pending).state.requests.length, 1);
    const corrected = resubmit(sponsors.alternativa.code, pending).state;
    assert.deepEqual(corrected.requests.map(row => row.status), ['substituida', 'pendente']);
    assert.equal(pending.requests[0].status, 'pendente');
    assert.equal(decideRequest(corrected, 'principal', true), null);

    const active = { ...applicantExample('ativo'), required: true, account: 'correcao_solicitada' };
    assert.ok(resubmit(sponsors.alternativa.code, active).error);
    assert.deepEqual(resubmit('', active).state.requests, active.requests);
});

test('the private applicant view can distinguish missing and damaged storage', () => {
    storage.clear();
    assert.equal(readSponsorship({ strict: true }).enrolled, false);
    enroll('');
    const [name] = storage.keys();
    storage.set(name, '{');
    assert.throws(() => readSponsorship({ strict: true }));
    assert.equal(readSponsorship().enrolled, false);
    storage.clear();
});

const registrationA = 'a1111111-1111-4111-8111-111111111111';
const registrationB = 'b2222222-2222-4222-8222-222222222222';
test('completion exposes a request only for the exact registered applicant, never by code or a legacy fixture', () => {
    storage.clear();
    const without = enroll('', {}, registrationA).state;
    assert.equal(registrationRequest(without, registrationA), null);
    assert.equal(without.account, 'aguardando_validacao');
    const withCode = enroll(sponsors.principal.code, {}, registrationB).state;
    assert.equal(registrationRequest(withCode, registrationB).status, 'pendente');
    assert.equal(registrationRequest(withCode, registrationA), null);
    assert.equal(registrationRequest(applicantExample('pendente'), registrationB), null);
    assert.equal(registrationRequest({ ...withCode, enrolled: false }, registrationB), null);
    assert.equal(registrationRequest({ ...withCode, requests: [{ sponsor: 'principal', status: 'unknown' }] }, registrationB), null);
});

test('a new registration in the same tab cannot inherit or erase an older pending, accepted or refused request', () => {
    for (const accepted of [null, true, false]) {
        storage.clear();
        const original = enroll(sponsors.principal.code, {}, registrationA).state;
        if (accepted !== null) saveSponsorship(decideRequest(original, 'principal', accepted));
        const before = readSponsorship({ registrationId: registrationA });
        const next = enroll('', {}, registrationB).state;
        assert.equal(registrationRequest(next, registrationB), null);
        const archived = readSponsorship({ registrationId: registrationA, strict: true });
        assert.equal(registrationRequest(archived, registrationA).status, accepted === null ? 'pendente' : accepted ? 'ativo' : 'recusada');
        assert.deepEqual(archived.requests, before.requests);
        assert.equal(archived.account, 'aguardando_validacao');
        assert.equal(readSponsorship().registrationId, registrationB);
        assert.equal(readSponsorship().requests.length, 0);
        // An update to the old applicant must not replace the active applicant.
        archived.reason = 'Orientação de revisão';
        assert.equal(saveSponsorship(archived), true);
        assert.equal(readSponsorship().registrationId, registrationB);
        assert.equal(registrationRequest(readSponsorship({ registrationId: registrationB }), registrationB), null);
    }
});

test('invalid referral or failed write creates no request for the new registration and preserves previous histories', () => {
    storage.clear();
    enroll(sponsors.principal.code, {}, registrationA);
    assert.ok(enroll('invalido', {}, registrationB).error);
    assert.equal(registrationRequest(readSponsorship({ registrationId: registrationB }), registrationB), null);
    assert.equal(readSponsorship().registrationId, registrationA);
    const setItem = sessionStorage.setItem;
    try {
        sessionStorage.setItem = () => { throw new Error('Quota'); };
        assert.ok(enroll(sponsors.principal.code, {}, registrationB).error);
        assert.equal(registrationRequest(readSponsorship({ registrationId: registrationB }), registrationB), null);
        assert.equal(readSponsorship().registrationId, registrationA);
    } finally { sessionStorage.setItem = setItem; }
    assert.ok(enroll('', {}, 'invalid-registration').error);
});

test('legacy history remains stored when the first explicitly scoped registration starts', () => {
    storage.clear();
    const previous = enroll(sponsors.principal.code).state;
    enroll('', {}, registrationA);
    assert.deepEqual(readSponsorship().registrations.legacy.requests, previous.requests);
    assert.equal(readSponsorship().requests.length, 0);
});
