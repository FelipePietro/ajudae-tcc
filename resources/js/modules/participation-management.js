import { card, controls, element, lazyDetails, localDate } from './web-operations.js';

const note = {
    name: 'nota', label: 'Nota', type: 'number', value: '', options: { '': 'Selecione', 1: '1', 2: '2', 3: '3', 4: '4', 5: '5' },
    attributes: { required: '' },
};
const reason = { name: 'motivo', label: 'Motivo da decisão', type: 'textarea', attributes: { required: '', maxlength: 2000 } };
const labels = { ativo: 'Ativo', finalizado: 'Finalizado', cancelado: 'Cancelado', nao_aplicavel: 'Não se aplica', pendente: 'Pendente', validada: 'Validada', rejeitada: 'Rejeitada' };

function historyCard(title, payload, fields) {
    return card(title, fields.map(([label, key, type]) => [label, type === 'date' ? localDate(payload[key]) : labels[payload[key]] ?? payload[key]]));
}

function monitoring(inscription, agenda, url, ui) {
    return lazyDetails('Monitoria estudantil', content => {
        async function load(message) {
            await ui.read(content, url, data => {
                const status = element('p', message);
                status.setAttribute('role', 'status');
                status.tabIndex = -1;
                content.append(status, historyCard('Monitoria estudantil', data, [
                    ['Situação', 'status'], ['Última decisão', 'validado_em', 'date'],
                ]));
                if (message) status.focus();
                if (data.status !== 'pendente' || !inscription.presenca_confirmada_em || !inscription.presenca) return;
                for (const confirmed of [true, false]) {
                    const label = confirmed ? 'Confirmar monitoria' : 'Rejeitar monitoria';
                    const required = !confirmed || agenda.status_ativo === 'finalizado';
                    content.append(lazyDetails(label, section => {
                        section.append(ui.form({
                            label, url, method: 'PATCH', body: { confirmada: confirmed }, complete: load,
                            fields: [{ name: 'justificativa', label: 'Justificativa', type: 'textarea',
                                attributes: required ? reason.attributes : { maxlength: 2000 } }],
                        }));
                    }));
                }
            });
        }
        load();
    });
}

function justification(url, ui) {
    return lazyDetails('Justificativa da ocorrência', content => {
        async function load(message) {
            await ui.read(content, url, data => {
                const status = element('p', message);
                status.setAttribute('role', 'status');
                status.tabIndex = -1;
                content.append(status, historyCard('Protocolo nº ' + data.justificativa_id, data, [
                    ['Situação', 'status'], ['Motivo enviado', 'motivo'], ['Enviada em', 'enviada_em', 'date'],
                    ['Motivo da decisão', 'motivo_decisao'], ['Decidida em', 'decidido_em', 'date'],
                ]));
                const author = data.decidido_por;
                if (author) {
                    const role = author.tipo === 'ong' ? 'ONG' : 'Organizador';
                    content.append(element('p', 'Decisão registrada por ' + (author.nome ?? role), 'break-words'));
                }
                if (message) status.focus();
                if (data.status !== 'pendente') return;
                for (const [label, granted] of [['Deferir justificativa', true], ['Indeferir justificativa', false]]) {
                    content.append(lazyDetails(label, section => {
                        section.append(ui.form({
                            label, url, method: 'PATCH', body: { deferida: granted }, fields: [reason], complete: load,
                        }));
                    }));
                }
            }, () => content.append(element('p', 'Nenhuma justificativa enviada para esta inscrição.')));
        }
        load();
    });
}

function participant(row, event, agenda, ui, base, refresh) {
    const item = card(row.pessoa?.nm_pessoa ?? 'Participante nº ' + row.pessoa_id, [
        ['Inscrição', row.status_inscricao], ['Presença registrada em', row.presenca_confirmada_em && localDate(row.presenca_confirmada_em)],
    ]);
    const url = base + '/inscricoes/' + row.inscricao_id;
    if (event.status_evento === 'ativo' && agenda.status_ativo === 'ativo'
        && !row.presenca_confirmada_em && ['aprovado', 'participando'].includes(row.status_inscricao)) {
        item.append(lazyDetails('Registrar presença ou ausência', section => {
            section.append(ui.form({
                label: 'Salvar presença', url: url + '/presenca', method: 'PATCH', complete: refresh,
                fields: [{ name: 'presenca', label: 'Presença', type: 'boolean', value: row.presenca == null ? '' : String(row.presenca),
                    options: { '': 'Selecione', true: 'Presente', false: 'Ausente' }, attributes: { required: '' } }],
            }));
        }));
    }
    if (row.declarou_estudante) item.append(monitoring(row, agenda, url + '/monitoria', ui));
    if (['não participou', 'desistente'].includes(row.status_inscricao)) item.append(justification(url + '/justificativa', ui));
    return item;
}

function agendaCard(agenda, event, ui, base, refresh) {
    const item = card('Agenda nº ' + agenda.agenda_id, [
        ['Situação', labels[agenda.status_ativo] ?? agenda.status_ativo],
        ['Início', localDate(agenda.data_inicio)], ['Fim', localDate(agenda.data_fim)],
    ]);
    const url = base + '/agendas/' + agenda.agenda_id;
    item.append(lazyDetails('Acompanhar inscrições', section => {
        ui.paginator(section, {
            url: url + '/inscricoes', empty: 'Nenhuma inscrição nesta Agenda.',
            render: (row, reload) => participant(row, event, agenda, ui, base, reload),
        });
    }));
    if (event.status_evento === 'ativo' && agenda.status_ativo === 'ativo') {
        item.append(lazyDetails('Finalizar Agenda', section => {
            section.append(element('p', 'Confirme as presenças e ausências após o término da Agenda.'));
            section.append(ui.form({ label: 'Finalizar Agenda', url: url + '/finalizar', method: 'PATCH', complete: refresh }));
        }));
    }
    return item;
}

function ratings(url, ui) {
    return lazyDetails('Avaliações dos voluntários', section => {
        ui.paginator(section, {
            url, select: data => data.participantes, empty: 'Nenhum participante presente em Agenda finalizada.',
            render: (row, refresh, data) => {
                const item = card(row.nm_pessoa, [['Avaliação', row.nota == null ? 'Aguardando nota' : row.nota + ' de 5']]);
                if (row.nota == null && data.pode_avaliar) {
                    item.append(ui.form({ label: 'Salvar nota', url: url + '/' + row.pessoa_id, method: 'POST', fields: [note], complete: refresh }));
                }
                return item;
            },
        });
    });
}

function eventCard(row, refresh, ui, base) {
    const item = card(row.nm_evento, [['Situação', labels[row.status_evento] ?? row.status_evento]]);
    const url = base + '/eventos/' + row.evento_id;
    item.append(lazyDetails('Gerenciar participação', section => {
        ui.read(section, url, event => {
            const agendas = event.agendas ?? [];
            if (!agendas.length) section.append(element('p', 'Este Evento não possui Agendas.'));
            for (const agenda of agendas) section.append(agendaCard(agenda, event, ui, base, refresh));
            section.append(ratings(url + '/avaliacoes', ui));
            if (event.status_evento === 'ativo' && agendas.length && agendas.every(agenda => ['finalizado', 'cancelado'].includes(agenda.status_ativo))) {
                section.append(lazyDetails('Finalizar Evento', content => {
                    content.append(element('p', 'Registre todas as avaliações obrigatórias antes do encerramento.'));
                    content.append(ui.form({ label: 'Finalizar Evento', url: url + '/finalizar', method: 'POST', complete: refresh }));
                }));
            }
        });
    }));
    return item;
}

export function initializeParticipationManagement() {
    const root = document.querySelector('[data-participation-management]');
    if (!root) return;
    const ui = controls(root);
    const base = root.dataset.base + '/gestao';
    const section = root.querySelector('[data-managed-participation-list]');
    ui.paginator(section, {
        url: base + '/eventos', empty: 'Nenhum Evento sob sua gestão.',
        render: (row, refresh) => eventCard(row, refresh, ui, base),
    });
}
