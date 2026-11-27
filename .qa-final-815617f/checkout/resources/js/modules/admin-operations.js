import { card, controls, element, lazyDetails, localDate, privateDownload } from './web-operations.js';

const reason = { name: 'motivo', label: 'Motivo', type: 'textarea', attributes: { required: '', maxlength: 2000 } };
const states = {
    aguardando_validacao: 'Aguardando validação',
    em_analise: 'Em análise',
    correcao_solicitada: 'Correção solicitada',
    reprovado_definitivamente: 'Reprovação definitiva',
    concluida: 'Concluída',
};
const state = value => states[value] ?? value;
const campaignDate = value => value?.slice(0, 19);

function personDocument(document, target, refresh, canDecide) {
    const item = card(document.tipo?.replaceAll('_', ' ') ?? 'Certidão de antecedentes', [
        ['Situação', state(document.status)], ['Enviado em', localDate(document.uploaded_at ?? document.enviado_em)],
        ['Motivo da rejeição', document.rejection_reason],
    ]);
    const id = document.documento_pessoa_id;
    if (document.download_url) {
        privateDownload(item, document.download_url, 'Baixar documento');
    } else if (document.download_requires_assignment) {
        item.append(element('p', 'Para baixar, assuma a análise e mantenha a atribuição dentro do prazo.'));
    }
    if (canDecide && document.ativo !== false && !(document.tipo === 'antecedentes_criminais' && document.status !== 'pendente')) {
        target(item, 'Aprovar documento', '/documentos/' + id + '/status', refresh, { method: 'PATCH', body: { status: 'aprovado' } });
        target(item, 'Rejeitar documento', '/documentos/' + id + '/status', refresh, { method: 'PATCH', body: { status: 'rejeitado' }, fields: [reason] });
    }
    return item;
}

function person(row, refresh, ctx) {
    const item = card(row.nm_pessoa, [['Situação', state(row.status_validacao)]]);
    const base = '/validacoes-pessoa/' + row.pessoa_id;
    item.append(lazyDetails('Revisar cadastro e documentos', detail => {
        const load = () => ctx.read(detail, ctx.admin(base), data => {
            detail.append(card('Cadastro', [
                ['Situação', state(data.status_validacao)], ['E-mail', data.email_pessoa],
                ['CPF', data.cpf_pessoa], ['RG', data.rg_pessoa],
                ['Em análise pelo administrador', data.admin_analisando_id],
            ]));
            const reviewing = data.status_validacao === 'em_analise'
                && Number(data.admin_analisando_id) === ctx.actor;
            if (['aguardando_validacao', 'em_analise'].includes(data.status_validacao)) {
                ctx.action(detail, 'Assumir análise', base + '/assumir', refresh);
            }
            if (reviewing) {
                ctx.action(detail, 'Aprovar Pessoa', base + '/aprovar', refresh);
                ctx.action(detail, 'Solicitar correção', base + '/solicitar-correcao', refresh, { fields: [reason] });
                ctx.action(detail, 'Exigir padrinho', base + '/exigir-padrinho', refresh, { fields: [reason] });
                ctx.action(detail, 'Reprovar Pessoa', base + '/reprovar', refresh, { fields: [reason] });
                if (ctx.master) {
                    ctx.action(detail, 'Reprovar definitivamente', base + '/reprovar-definitivamente', refresh, { fields: [reason] });
                }
            }
            const documents = data.documentos ?? [];
            detail.append(element('h4', 'Documentos', 'font-semibold'));
            if (!documents.length) detail.append(element('p', 'Nenhum documento enviado.'));
            const feedback = element('p');
            feedback.setAttribute('role', 'status');
            feedback.tabIndex = -1;
            detail.append(feedback);
            for (const document of documents) {
                detail.append(personDocument(document, ctx.action, async message => {
                    await load();
                    const status = detail.querySelector('[role="status"]');
                    if (status) {
                        status.textContent = message;
                        status.focus();
                    }
                }, reviewing || data.status_validacao === 'aprovado'));
            }
            if (data.historico_validacao?.length) {
                detail.append(element('h4', 'Histórico de validação', 'font-semibold'));
                for (const entry of data.historico_validacao) {
                    detail.append(card(state(entry.status_novo), [
                        ['Data', localDate(entry.created_at)], ['Administrador', entry.admin_id], ['Motivo', entry.motivo],
                    ]));
                }
            }
        });
        load();
    }));
    return item;
}

function organization(row, refresh, ctx) {
    const item = card(row.nome_fantasia, [
        ['Situação', state(row.status_ong)], ['CNPJ', row.cnpj_ong], ['Responsável', row.nome_responsavel_ong],
    ]);
    const download = row.documento_identidade?.download;
    if (download) privateDownload(item, download, 'Baixar identidade do responsável');
    const path = '/validacoes-ong/' + row.ong_id + '/status';
    ctx.action(item, 'Aprovar ONG', path, refresh, { method: 'PATCH', body: { status_ong: 'ativo' } });
    ctx.action(item, 'Reprovar ONG', path, refresh, { method: 'PATCH', body: { status_ong: 'reprovado' } });
    return item;
}

function promotion(row, refresh, ctx) {
    const id = row.solicitacao_organizador_id;
    const base = '/solicitacoes-organizador/' + id;
    const item = card('Solicitação nº ' + id, [
        ['Situação', state(row.status)], ['Solicitada em', localDate(row.solicitada_em)],
    ]);
    item.append(lazyDetails('Revisar solicitação', detail => {
        ctx.read(detail, ctx.admin(base), data => {
            detail.append(card(data.pessoa?.nm_pessoa ?? 'Pessoa nº ' + data.pessoa_id, [
                ['Situação', state(data.status)], ['Justificativa', data.justificativa],
                ['Decidida em', data.decidida_em && localDate(data.decidida_em)], ['Motivo da decisão', data.motivo_decisao],
            ]));
            if (data.status === 'pendente') ctx.action(detail, 'Iniciar análise', base + '/iniciar-analise', refresh);
            if (data.status === 'em_analise' && Number(data.admin_id) === ctx.actor) {
                ctx.action(detail, 'Aprovar promoção', base + '/aprovar', refresh);
                ctx.action(detail, 'Reprovar promoção', base + '/reprovar', refresh, { fields: [reason] });
            }
        });
    }));
    return item;
}

function sponsorship(row, refresh, ctx) {
    const id = row.conclusao_padrinho_id;
    const item = card('Pedido nº ' + id, [
        ['Situação', state(row.status)], ['Padrinho', row.padrinho_id],
        ['Pessoa acompanhada', row.apadrinhado_id], ['Solicitado em', localDate(row.solicitada_em)],
    ]);
    if (row.status === 'pendente') {
        const base = '/padrinhos/conclusoes/' + id;
        ctx.action(item, 'Aprovar conclusão', base + '/aprovar', refresh);
        ctx.action(item, 'Reprovar conclusão', base + '/reprovar', refresh, { fields: [reason] });
    }
    return item;
}

function campaignFields(values = {}) {
    return [
        { name: 'nome', label: 'Nome', value: values.nome, attributes: { required: '', maxlength: 120 } },
        { name: 'multiplicador', label: 'Multiplicador', type: 'number', value: values.multiplicador, attributes: { required: '', min: 0.000001, step: 0.000001 } },
        { name: 'inicio', label: 'Início (UTC)', type: 'datetime-local', value: campaignDate(values.inicio), attributes: { required: '', step: 1 } },
        { name: 'fim', label: 'Fim (UTC)', type: 'datetime-local', value: campaignDate(values.fim), attributes: { required: '', step: 1 } },
        { name: 'cat_evento_id', label: 'Código da categoria do Evento', type: 'number', value: values.cat_evento_id, attributes: { required: '', min: 1 } },
        { name: 'ativa', label: 'Situação', type: 'boolean', value: String(values.ativa ?? false), options: { false: 'Inativa', true: 'Ativa' } },
    ];
}

function campaign(row, refresh, ctx) {
    const item = card(row.nome, [
        ['Situação', row.ativa ? 'Ativa' : 'Inativa'], ['Multiplicador', row.multiplicador],
        ['Início', localDate(row.inicio)], ['Fim', localDate(row.fim)], ['Categoria', row.cat_evento_id],
    ]);
    const base = '/campanhas-xp/' + row.campanha_xp_id;
    ctx.action(item, 'Editar campanha', base, refresh, { method: 'PATCH', fields: campaignFields(row) });
    ctx.action(item, row.ativa ? 'Desativar campanha' : 'Ativar campanha', base + '/status', refresh, {
        method: 'PATCH', body: { ativa: !row.ativa },
    });
    return item;
}

const privacyResponse = { name: 'resposta', label: 'Resposta ao titular', type: 'textarea', attributes: { required: '', maxlength: 4000 } };
const privacyPath = id => '/solicitacoes-lgpd/' + id + '/status';

function privacy(row, refresh, ctx) {
    const id = row.solicitacao_lgpd_id;
    const item = card('Protocolo nº ' + id, [
        ['Tipo', row.tipo], ['Situação', state(row.status)], ['Registrado em', localDate(row.created_at)],
        ['Responsável', row.respondida_por ?? 'Sem responsável'], ['Descrição', row.descricao], ['Resposta', row.resposta],
    ]);
    if (!ctx.canRespond) return item;
    if (row.respondida_por == null) {
        ctx.action(item, 'Assumir atendimento', privacyPath(id), refresh, { method: 'PATCH', body: { status: 'em_analise' } });
    } else if ((ctx.master || Number(row.respondida_por) === ctx.actor) && ['pendente', 'em_analise'].includes(row.status)) {
        for (const [label, status] of [['Concluir solicitação', 'concluida'], ['Recusar solicitação', 'recusada']]) {
            ctx.action(item, label, privacyPath(id), refresh, { method: 'PATCH', fields: [privacyResponse], body: { status } });
        }
    }
    return item;
}

function respondWithoutListing(container, ctx) {
    const protocol = { name: 'protocolo', label: 'Número do protocolo', type: 'number', attributes: { required: '', min: 1 } };
    const actions = [
        ['Assumir atendimento', 'em_analise', []],
        ['Concluir solicitação', 'concluida', [privacyResponse]],
        ['Recusar solicitação', 'recusada', [privacyResponse]],
    ];
    container.append(element('p', 'Informe o protocolo encaminhado para atendimento.'));
    for (const [label, status, fields] of actions) {
        const details = lazyDetails(label, section => {
            section.append(ctx.form({
                label, method: 'PATCH', url: payload => {
                    const id = payload.protocolo;
                    delete payload.protocolo;
                    return ctx.admin(privacyPath(id));
                },
                fields: [protocol, ...fields], body: { status },
            }));
        });
        container.append(details);
    }
}

function audit(row) {
    return card(row.acao, [
        ['Data', localDate(row.created_at)], ['Administrador', row.pessoa_id],
        ['Recurso', row.recurso_type?.split('\\').at(-1)], ['Identificador', row.recurso_id],
    ]);
}

const pages = {
    candidatos: ['/validacoes-pessoa/dados', person],
    'cadastros-ong': ['/validacoes-ong/dados', organization],
    'upgrades-org': ['/solicitacoes-organizador/dados', promotion],
    'campanhas-xp': ['/campanhas-xp/dados', campaign],
    'solicitacoes-lgpd': ['/solicitacoes-lgpd/dados', privacy],
    'log-acoes': ['/log-acoes/dados', audit],
    'conclusoes-padrinho': ['/padrinhos/conclusoes/dados', sponsorship],
};

export function initializeAdminOperations() {
    const root = document.querySelector('[data-admin-operations]');
    if (!root) return;

    const ui = controls(root);
    const url = path => root.dataset.base + path;
    const admin = path => url('/admin' + path);
    const ctx = {
        ...ui, url, admin, actor: Number(root.dataset.actor), master: root.dataset.master === '1',
        canRespond: root.dataset.privacyRespond === '1',
        action(container, label, path, complete, config = {}) {
            container.append(lazyDetails(label, section => {
                section.append(ui.form({ label, url: admin(path), method: 'POST', complete, ...config }));
            }));
        },
    };
    const kind = root.dataset.adminOperations;
    const create = root.querySelector('[data-operation-create]');
    const filter = root.querySelector('[data-operation-filter]');
    const list = root.querySelector('[data-operation-list]');
    if (kind === 'solicitacoes-lgpd' && root.dataset.privacyRead !== '1') {
        if (ctx.canRespond) respondWithoutListing(create, ctx);
        return;
    }

    const [path, render] = pages[kind];
    let query = {};
    const first = ui.paginator(list, {
        url: admin(path), render: (row, refresh) => render(row, refresh, ctx), query: () => query,
        arrayPageSize: ['candidatos', 'cadastros-ong'].includes(kind) ? 20 : undefined,
    });
    filter?.addEventListener('submit', event => {
        event.preventDefault();
        query = Object.fromEntries(Array.from(new FormData(filter), ([key, value]) => [key, value.trim()]));
        first();
    });

    if (kind === 'campanhas-xp') {
        ctx.action(create, 'Criar campanha', '/campanhas-xp', first, { fields: campaignFields() });
    }
    const auxiliary = [
        ['[data-operation-renewals]', '/antecedentes/renovacoes', (row, refresh) => {
            const item = card(row.nome_pessoa ?? 'Pessoa nº ' + row.pessoa_id);
            item.append(personDocument(row, ctx.action, refresh, row.status === 'pendente'));
            return item;
        }],
    ];
    for (const [selector, path, render] of auxiliary) {
        const details = root.querySelector(selector);
        details?.addEventListener('toggle', () => {
            const section = details.querySelector('div');
            if (details.open && !section.hasChildNodes()) ui.paginator(section, { url: admin(path), render });
        });
    }
}
