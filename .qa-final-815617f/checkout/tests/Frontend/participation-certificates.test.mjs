import test from 'node:test';
import assert from 'node:assert/strict';
import { certificateEligibility, issueCertificate, publicCertificate, certificatePdf } from '../../resources/js/participation-certificates.js';

const fixture = { id: 7, title: 'Oficina', certificateIssuer: 'ONG de Exemplo', eventStatus: 'finalizado', actor: { type: 'pessoa', id: 1 }, agendas: [
    { id: 701, state: 'finalizada', minutes: 30 }, { id: 702, state: 'finalizada', minutes: 90 },
    { id: 703, state: 'cancelada', minutes: 120 }, { id: 704, state: 'finalizada', minutes: 60 },
], records: [701, 702, 703, 704].map((agendaId, i) => ({ id: agendaId * 100 + 1, agendaId, personId: 1, name: 'Pessoa de Exemplo', selection: 'aprovada', presence: i === 3 ? 'ausente' : 'presente', student: true, monitoring: 'pendente' })) };
const code = 'AJD-' + 'A'.repeat(32);
const issue = (event = fixture, modality = 'geral', existing = null) => issueCertificate(event, [], 1, modality, existing, code, 123456789);
test('30+90 minutes; cancellation, absence, unrelated person and other Event excluded', () => {
    assert.equal(certificateEligibility(fixture, [], 1).minutes, 120);
    assert.equal(certificateEligibility(fixture, [], 2).allowed, false);
    assert.equal(certificateEligibility({ ...fixture, records: [] }, [], 1).allowed, false);
    assert.equal(certificateEligibility({ ...fixture, records: fixture.records.map(r => ({ ...r, presence: 'pendente' })) }, [], 1).allowed, false);
});
test('active or canceled Event blocks; closed Event does not require optional ONG review, XP or reliability', () => {
    for (const eventStatus of ['ativo', 'cancelado']) assert.equal(issue({ ...fixture, eventStatus }), null);
    assert.equal(issue().minutes, 120);
    const history = [{ action: 'finalizarEvento', eventId: 7 }];
    assert.equal(certificateEligibility({ ...fixture, eventStatus: 'ativo' }, history, 1).allowed, true);
    assert.equal(certificateEligibility({ ...fixture, eventStatus: 'ativo' }, [{ action: 'finalizarEvento', eventId: 8 }], 1).allowed, false);
});
test('student monitoring does not block general; all pertinent Agendas must validate student modality', () => {
    assert.equal(issue(fixture, 'estudantil'), null);
    const confirmed = { ...fixture, records: fixture.records.map(r => ({ ...r, monitoring: 'validada' })) };
    assert.equal(issue(confirmed, 'estudantil').minutes, 120);
    assert.equal(issue(fixture, 'inventada'), null);
});
test('replay keeps frozen snapshot, code and minutes even after fixture changes', () => {
    const original = issue();
    assert.equal(issue({ ...fixture, agendas: [] }, 'estudantil', original), original);
    assert.equal(issue(fixture, 'geral', { ...original, personId: 2 }), null);
    assert.equal(issueCertificate(fixture, [], 1, 'geral', null, 'guessable', 1), null);
});
test('minimal public projection and real PDF byte structure', () => {
    const record = issue();
    const projection = publicCertificate({ ...record, cpf: 'private', letter: 'private' });
    for (const key of ['personId', 'eventId', 'cpf', 'letter']) assert.equal(key in projection, false);
    const pdf = Buffer.from(certificatePdf(record)).toString('latin1');
    assert.match(pdf, /AJUDAE/);
    assert.match(pdf, /\/Title \(Ajudae/);
    assert.doesNotMatch(pdf, /ajudaê/i);
    assert.match(pdf, /^%PDF-1.4/); assert.match(pdf, /120 minutos/); assert.match(pdf, /%%EOF$/);
    const offset = Number(pdf.match(/startxref\n(\d+)/)[1]); assert.equal(pdf.slice(offset, offset + 4), 'xref');
});
