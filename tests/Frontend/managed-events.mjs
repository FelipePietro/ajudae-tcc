import test from 'node:test';
import assert from 'node:assert/strict';
import { candidateReliability, eventCreationIntent, eventPayload, agendaPayload, multipartEvent, validateImage } from '../../resources/js/modules/managed-events.js';

const input = (value, disabled = false) => ({ value, disabled });
const form = (mode, checked = {}) => ({
    elements: {
        nm_evento: input(' Oficina '), descricao_evento: input('Descrição'), modalidade_evento: input(mode),
        cat_evento_id: input('2'), vagas_evento: input('5'), logradouro_evento: input('Rua A', mode === 'online'),
        cep_evento: input('01234-567', mode === 'online'), cidade_evento: input('Guarulhos', mode === 'online'),
        bairro_evento: input('Centro', mode === 'online'), uf_evento: input('SP', mode === 'online'), compl_evento: input('', mode === 'online'),
        ong_id: input('999'), status_evento: input('ativo'),
    },
    querySelectorAll: selector => (checked[selector.match(/name="(\w+)\[\]/)[1]] ?? []).map(value => ({value})),
});
function row(start, end, count) {
    const startInput = { value: start, reportValidity: () => Boolean(start) };
    const endInput = { value: end, validityMessage: '', setCustomValidity(message) { this.validityMessage = message; }, reportValidity() { return Boolean(end) && !this.validityMessage; } };
    const capacity = { value: count, reportValidity: () => Number.isInteger(Number(count)) && Number(count) >= 1 && Number(count) <= 4294967295 };
    const mapping = { '[data-agenda-start]': startInput, '[data-agenda-end]': endInput, '[data-agenda-capacity]': capacity };
    return { querySelector: selector => mapping[selector] };
}

test('payload usa apenas campos permitidos, CEP numérico e listas de IDs', () => {
    const payload = eventPayload(form('presencial', {habilidades: ['8','9'], causas: ['3']}));
    assert.equal(payload.nm_evento, 'Oficina'); assert.equal(payload.cep_evento, '01234567');
    assert.equal(payload.cat_evento_id, 2); assert.equal(payload.vagas_evento, 5);
    assert.deepEqual(payload.habilidades, [8,9]); assert.deepEqual(payload.causas, [3]); assert.deepEqual(payload.recursos, []);
    assert.equal(payload.compl_evento, null); assert.ok(!('ong_id' in payload)); assert.ok(!('status_evento' in payload));
});
test('evento online não envia endereço desabilitado', () => {
    const payload = eventPayload(form('online'));
    for (const key of ['logradouro_evento','cep_evento','cidade_evento','bairro_evento','uf_evento','compl_evento']) assert.ok(!(key in payload));
});
test('Agenda de 29 minutos não gera payload e 30 minutos gera ISO com offset', () => {
    assert.equal(agendaPayload(row('2026-12-04T09:00','2026-12-04T09:29','10')), null);
    const result = agendaPayload(row('2026-12-04T09:00','2026-12-04T09:30','10'));
    assert.equal(result.data_inicio, new Date('2026-12-04T09:00').toISOString());
    assert.equal(Date.parse(result.data_fim) - Date.parse(result.data_inicio), 30*60000); assert.equal(result.vagas_agenda,10);
});
test('vagas inválidas são rejeitadas, capacidade da Agenda independe do Evento', () => {
    assert.equal(agendaPayload(row('2026-12-04T09:00','2026-12-04T10:00','0')), null);
    assert.equal(agendaPayload(row('2026-12-04T09:00','2026-12-04T10:00','1.5')), null);
    assert.equal(agendaPayload(row('2026-12-04T09:00','2026-12-04T10:00','50')).vagas_agenda,50);
});
test('multipart aninha múltiplas Agendas e imagem_evento, sem campos de titularidade', () => {
    const payload = eventPayload(form('online', {habilidades:['8']}));
    payload.agendas = [agendaPayload(row('2026-12-04T09:00','2026-12-04T10:00','50')),agendaPayload(row('2026-12-05T09:00','2026-12-05T10:00','60'))];
    const image = new File(['synthetic'], 'banner.png', {type:'image/png'});
    const body = multipartEvent(payload,image);
    assert.equal(body.get('vagas_evento'),'5'); assert.equal(body.get('agendas[0][vagas_agenda]'),'50');
    assert.equal(body.get('agendas[1][vagas_agenda]'),'60'); assert.equal(body.get('habilidades[0]'),'8');
    assert.equal(body.get('imagem_evento').name,'banner.png'); assert.equal(body.has('ong_id'),false);
});
test('imagem opcional e rejeição antecipada de formato, extensão ou tamanho inválido', async () => {
    await validateImage(undefined);
    await assert.rejects(validateImage(new File(['x'],'banner.gif',{type:'image/gif'})),/JPEG, PNG ou WebP/);
    await assert.rejects(validateImage(new File(['x'],'banner.txt',{type:'image/png'})),/JPEG, PNG ou WebP/);
    await assert.rejects(validateImage(new File([new Uint8Array(5*1024*1024+1)],'banner.png',{type:'image/png'})),/5 MiB/);
});

test('a mesma criação conserva a chave entre tentativas e ao reabrir a página', () => {
    const data = new Map();
    const storage = {
        getItem: key => data.get(key),
        setItem: (key, value) => data.set(key, value),
        removeItem: key => data.delete(key),
    };
    const draft = eventCreationIntent('ong:1', storage);
    const key = draft.get();
    assert.match(key, /^[a-f0-9]{32}$/);
    assert.equal(draft.get(), key);
    assert.equal(eventCreationIntent('ong:1', storage).get(), key);
    assert.notEqual(eventCreationIntent('pessoa:1', storage).get(), key);
    draft.clear();
    assert.notEqual(eventCreationIntent('ong:1', storage).get(), key);
});

test('confiabilidade exibe a pontuação formada ou o estado informado pelo contrato', () => {
    assert.equal(candidateReliability({ formada: true, score: 80 }), 'Confiabilidade: 80 de 100');
    assert.equal(candidateReliability({ formada: true, score: 0 }), 'Confiabilidade: 0 de 100');
    assert.equal(candidateReliability({ formada: false, score: null, estado: 'Em formação — 2 de 3' }),
        'Confiabilidade: Em formação — 2 de 3');
    assert.equal(candidateReliability({ formada: true, score: null }), null);
    assert.equal(candidateReliability({ formada: false, score: 80 }), null);
    assert.equal(candidateReliability(undefined), null);
});
