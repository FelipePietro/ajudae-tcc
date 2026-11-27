import { requestJson } from '../core/http.js';

const base = '/gestao/eventos';
const groups = { habilidades: ['habilidade_id', 'nome_habilidade'], causas: ['causa_id', 'nome_causa'], recursos: ['recurso_id', 'nome_recurso'] };
const fields = ['nm_evento', 'descricao_evento', 'modalidade_evento', 'cat_evento_id', 'vagas_evento', 'logradouro_evento', 'cep_evento', 'cidade_evento', 'bairro_evento', 'uf_evento', 'compl_evento'];
const address = fields.slice(5, 10);
const labels = { ativo: 'Publicado', 'aguardando a confirmação': 'Aguardando aprovação', reprovado: 'Recusado', cancelado: 'Cancelado', finalizado: 'Finalizado' };
const terminal = status => ['cancelado', 'finalizado'].includes(status);
const $ = (root, selector) => root.querySelector(selector);
const $$ = (root, selector) => [...root.querySelectorAll(selector)];
const el = (tag, text = '', cls = '') => Object.assign(document.createElement(tag), { textContent: text, className: cls });
const say = (root, selector, text) => { const node = $(root, selector); if (node) node.textContent = text; };
const date = value => value ? new Date(value).toLocaleString('pt-BR') : '—';
const local = value => { if (!value) return ''; const d = new Date(value); if (!Number.isFinite(d.getTime())) return ''; return new Date(d.getTime() - d.getTimezoneOffset()*60000).toISOString().slice(0,16); };
const iso = value => new Date(value).toISOString(); // O instante local é convertido para UTC (offset ISO 8601 Z).
const clone = (root, selector) => $(root, selector).content.firstElementChild.cloneNode(true);
const request = (root, url, method = 'GET', body, idempotencyKey) =>
    requestJson(url, { method, body, csrfToken: root.dataset.csrf, idempotencyKey });
const errorText = error => [error.status === 503 ? 'Armazenamento temporariamente indisponível. Tente novamente.' : error.message, ...Object.entries(error.errors ?? {}).flatMap(([key, messages]) => messages.map(message => `${key}: ${message}`))].join(' · ');
const eventUrl = id => `${base}/${encodeURIComponent(id)}`;
const agendaUrl = id => `/gestao/agendas/${encodeURIComponent(id)}`;
const normalize = text => String(text ?? '').normalize('NFD').replace(/\p{Diacritic}/gu, '').toLowerCase();

function setAddress(form) {
    const online = form.elements.modalidade_evento.value === 'online';
    for (const name of [...address, 'compl_evento']) {
        const input = form.elements[name];
        input.disabled = online;
        input.required = !online && name !== 'compl_evento';
    }
}

function fillCatalogs(form, catalogs, event = {}) {
    const select = form.elements.cat_evento_id;
    select.replaceChildren(new Option('Selecione', ''), ...catalogs.categorias.map(item => new Option(item.nome_categoria, item.cat_evento_id)));
    select.value = event.cat_evento_id ?? '';
    const container = $(form, '[data-event-catalogs]');
    container.replaceChildren();
    for (const [group, [id, name]] of Object.entries(groups)) {
        const skillContainer = group === 'habilidades' && $(form, '[data-create-skills]');
        const dest = skillContainer || container;
        if (skillContainer) dest.replaceChildren();
        const fieldset = el('fieldset', '', 'min-w-0');
        fieldset.append(el('legend', group[0].toUpperCase()+group.slice(1), 'font-semibold'));
        const selected = new Set((event[group] ?? []).map(item => String(item[id])));
        for (const item of catalogs[group]) {
            const label = el('label', '', 'flex items-center gap-2 text-sm mt-2 break-words');
            const input = Object.assign(document.createElement('input'), { type: 'checkbox', name: `${group}[]`, value: String(item[id]), checked: selected.has(String(item[id])) });
            label.append(input, document.createTextNode(item[name])); fieldset.append(label);
        }
        dest.append(fieldset);
    }
}

export function eventPayload(form) {
    const result = {};
    for (const name of fields) {
        const input = form.elements[name];
        if (input.disabled) continue;
        const value = input.value.trim();
        result[name] = ['cat_evento_id', 'vagas_evento'].includes(name) ? Number(value) : name === 'cep_evento' ? value.replace(/\D/g, '') : value || null;
    }
    for (const group of Object.keys(groups)) result[group] = $$(form, `input[name="${group}[]"]:checked`).map(input => Number(input.value));
    return result;
}

export function agendaPayload(row) {
    const start = $(row, '[data-agenda-start]'), end = $(row, '[data-agenda-end]'), capacity = $(row, '[data-agenda-capacity]');
    const minutes = (new Date(end.value) - new Date(start.value))/60000;
    end.setCustomValidity(start.value && end.value && minutes >= 30 ? '' : 'A duração mínima da Agenda é de 30 minutos.');
    if (!start.reportValidity() || !end.reportValidity() || !capacity.reportValidity()) return null;
    return { data_inicio: iso(start.value), data_fim: iso(end.value), vagas_agenda: Number(capacity.value) };
}

export async function validateImage(file) {
    if (!file) return;
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || !/\.(jpe?g|png|webp)$/i.test(file.name) || file.size > 5*1024*1024) throw new Error('Escolha JPEG, PNG ou WebP de até 5 MiB.');
    const url = URL.createObjectURL(file);
    try {
        const img = new Image(); img.src = url; await img.decode();
        const { naturalWidth: w, naturalHeight: h } = img;
        if (!w || !h || w > 8000 || h > 8000 || w*h > 25000000) throw new Error('Imagem deve ter até 8.000 px por lado e 25 megapixels.');
    } catch (error) {
        if (error.name === 'EncodingError') throw new Error('Não foi possível decodificar a imagem. Escolha um arquivo íntegro.');
        throw error;
    } finally { URL.revokeObjectURL(url); }
}

export function multipartEvent(payload, image) {
    const data = new FormData();
    for (const [key, value] of Object.entries(payload)) {
        if (key === 'agendas') value.forEach((agenda, i) => Object.entries(agenda).forEach(([field, val]) => data.append(`agendas[${i}][${field}]`, val)));
        else if (Array.isArray(value)) value.forEach((id, i) => data.append(`${key}[${i}]`, id));
        else if (value !== null) data.append(key, value);
    }
    data.append('imagem_evento', image);
    return data;
}

function showErrors(form, error, status) {
    $$(form, '[data-field-error]').forEach(node => node.remove());
    for (const [key, messages] of Object.entries(error.errors ?? {})) {
        const match = /^agendas\.(\d+)\.(data_inicio|data_fim|vagas_agenda)$/.exec(key);
        const selectors = { data_inicio: '[data-agenda-start]', data_fim: '[data-agenda-end]', vagas_agenda: '[data-agenda-capacity]' };
        const group = key.split('.')[0];
        const input = match ? $$(form, '[data-agenda-row]')[Number(match[1])]?.querySelector(selectors[match[2]]) : selectors[key] ? $(form, selectors[key]) : Object.hasOwn(groups, group) ? $(form, `input[name="${group}[]"]`) : form.elements.namedItem(key);
        if (input instanceof HTMLElement) { const hint = el('p', messages.join(' · '), 'text-sm text-red-700'); hint.dataset.fieldError = ''; input.after(hint); }
    }
    status.textContent = errorText(error);
}

export function eventCreationIntent(scope, storage) {
    const storageKey = 'ajudae:event-creation:' + scope;
    let key;
    return {
        get() {
            if (!key) {
                try { key = storage?.getItem(storageKey); } catch { /* A tentativa continua em memória. */ }
                if (!key) {
                    const bytes = crypto.getRandomValues(new Uint8Array(16));
                    key = Array.from(bytes, byte => byte.toString(16).padStart(2, '0')).join('');
                    try { storage?.setItem(storageKey, key); } catch { /* A tentativa continua em memória. */ }
                }
            }
            return key;
        },
        clear() {
            try {
                if (storage && storage.getItem(storageKey) === key) storage.removeItem(storageKey);
            } catch { /* A tentativa em memória ainda pode ser encerrada. */ }
            key = undefined;
        },
    };
}

export function candidateReliability(summary) {
    if (summary?.formada === true && Number.isInteger(summary.score) && summary.score >= 0 && summary.score <= 100) {
        return 'Confiabilidade: ' + summary.score + ' de 100';
    }
    if (summary?.formada === false && typeof summary.estado === 'string' && summary.estado.trim() !== '') {
        return 'Confiabilidade: ' + summary.estado;
    }
    return null;
}

function createEvent(form) {
    const status = $(form, '#event-editor-status'), fieldsBox = $(form, '[data-create-fields]');
    const editor = $(form, '[data-agenda-editor]'), rows = $(editor, '[data-agenda-rows]');
    const file = form.elements.imagem_evento;
    const conflict = $(form, '[data-creation-conflict]');
    let storage;
    try { storage = window.sessionStorage; } catch { /* Disponibilidade depende do navegador. */ }
    const intent = eventCreationIntent(form.dataset.creationScope, storage);
    $(conflict, '[data-new-creation]').addEventListener('click', () => {
        intent.clear();
        conflict.hidden = true;
        status.textContent = 'Revise os dados antes de enviar o novo Evento.';
        form.elements.nm_evento.focus();
    });
    let busy = false, previewUrl;
    const preview = () => {
        say(form, '#preview-titulo', form.elements.nm_evento.value || 'Prévia do evento');
        say(form, '#preview-categoria', form.elements.cat_evento_id.selectedOptions[0]?.textContent || 'Categoria');
        say(form, '#preview-modalidade', form.elements.modalidade_evento.selectedOptions[0]?.textContent || 'Modalidade');
        say(form, '#preview-vagas', `${form.elements.vagas_evento.value || '—'} vagas informativas`);
        say(form, '#preview-descricao', form.elements.descricao_evento.value || 'O resumo do evento aparecerá aqui.');
        say(form, '#preview-local span:last-child', form.elements.modalidade_evento.value === 'online' ? 'Online' : [form.elements.cidade_evento.value, form.elements.uf_evento.value].filter(Boolean).join(', ') || 'Local a definir');
        const list = $$(rows, '[data-agenda-row]');
        say(form, '#preview-data span:last-child', `${list.length} Agenda(s) · ${date($(list[0] ?? rows, '[data-agenda-start]')?.value)}`);
        say(editor, '[data-agenda-capacity-summary]', `${list.length} Agenda(s) · vagas independentes por Agenda; total do Evento informativo.`);
        const skillList = $(form, '#preview-habilidades');
        skillList.replaceChildren(...$$(form, 'input[name="habilidades[]"]:checked').map(input => el('span', input.parentElement.textContent.trim(), 'rounded-full bg-[#eef7f2] px-2 py-1')));
        $(form, '#preview-habilidades-wrapper').classList.toggle('hidden', !skillList.children.length);
        list.forEach((row, i) => { say(row, '[data-agenda-title]', `Agenda ${i+1}`); $(row, '[data-agenda-remove]').disabled = list.length === 1; });
    };
    const add = () => {
        const row = clone(editor, '[data-agenda-row-template]');
        $(row, '[data-agenda-capacity]').max = '4294967295';
        $(row, '[data-agenda-capacity]').value = '1';
        $(row, '[data-agenda-remove]').addEventListener('click', () => { if (rows.children.length > 1) { row.remove(); preview(); } });
        row.addEventListener('input', () => { $(row, '[data-agenda-end]').setCustomValidity(''); const minutes = (new Date($(row, '[data-agenda-end]').value) - new Date($(row, '[data-agenda-start]').value))/60000; say(row, '[data-agenda-duration]', Number.isFinite(minutes) ? `${minutes} minutos · mínimo 30` : ''); });
        rows.append(row); preview();
    };
    $(editor, '[data-agenda-add]').addEventListener('click', add);
    form.addEventListener('input', preview);
    form.elements.modalidade_evento.addEventListener('change', () => { setAddress(form); preview(); });
    file.addEventListener('change', async () => {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        const img = $(form, '#preview-imagem'), placeholder = $(form, '#preview-imagem-placeholder');
        img.classList.add('hidden'); placeholder.classList.remove('hidden');
        try { const chosen = file.files[0]; await validateImage(chosen); if (!chosen || file.files[0] !== chosen) return; previewUrl = URL.createObjectURL(chosen); img.src = previewUrl; img.classList.remove('hidden'); placeholder.classList.add('hidden'); }
        catch (error) { status.textContent = errorText(error); file.value = ''; }
    });
    form.addEventListener('submit', async event => {
        event.preventDefault(); if (busy) return;
        $$(form, '[data-field-error]').forEach(node => node.remove());
        const agendas = $$(rows, '[data-agenda-row]').map(agendaPayload);
        if (!agendas.length || agendas.some(a => !a)) { status.textContent = 'Revise as Agendas: informe horários válidos e pelo menos 30 minutos.'; return; }
        const payload = { ...eventPayload(form), agendas }; const image = file.files[0];
        busy = true; fieldsBox.disabled = true; conflict.hidden = true; status.textContent = 'Enviando Evento e Agendas…';
        let saved = false;
        try {
            await validateImage(image);
            const response = await request(form, base, 'POST', image ? multipartEvent(payload, image) : payload, intent.get());
            saved = true;
            intent.clear();
            status.textContent = `${response.message} Estado: ${labels[response.data.status_evento] ?? response.data.status_evento}.`;
            const link = el('a', 'Ver Evento na gestão', 'btn-primario'); link.href = `${form.dataset.listUrl}?evento=${encodeURIComponent(response.data.evento_id)}`; status.after(link); link.focus();
        } catch (error) {
            showErrors(form, error, status);
            conflict.hidden = error.status !== 409;
            status.scrollIntoView({ block: 'center' });
        } finally {
            busy = false;
            fieldsBox.disabled = saved;
            if (!saved) setAddress(form);
        }
    });
    const load = async () => {
        try { const { data } = await request(form, `${base}/catalogos`); fillCatalogs(form, data); fieldsBox.disabled = false; setAddress(form); add(); status.textContent = 'Preencha os dados e pelo menos uma Agenda. O Evento será enviado para análise.'; }
        catch (error) { status.textContent = errorText(error); const retry = el('button', 'Tentar novamente', 'btn-secundario'); retry.type = 'button'; retry.addEventListener('click', () => { retry.remove(); load(); }); status.after(retry); }
    };
    load();
}

function management(root) {
    const list = $(root, '[data-managed-list]'), status = $(root, '[data-list-status]'), more = $(root, '[data-load-more]');
    const mode = root.dataset.mode, organizer = root.dataset.organizer === '1';
    const requestedId = new URLSearchParams(location.search).get('evento');
    let next = `${base}?per_page=20`, loading = false, opened = false, catalogs;
    const records = new Map();
    const button = (label, fn) => { const node = clone(root, '[data-action-button]'); node.textContent = label; node.addEventListener('click', fn); return node; };
    function filter() {
        const events = [...records.values()].map(row => row.event);
        for (const state of ['ativo', 'aguardando a confirmação', 'finalizado']) say(root, `[data-count="${state}"]`, String(events.filter(event => event.status_evento === state).length));
        const search = normalize($(root, '[data-event-search]')?.value), state = $(root, '[data-event-filter]')?.value;
        for (const { event, card } of records.values()) card.hidden = Boolean((state && event.status_evento !== state) || (search && !normalize(`${event.nm_evento} ${event.cidade_evento ?? ''} ${event.bairro_evento ?? ''}`).includes(search)));
        if (!loading) status.textContent = records.size ? `${[...records.values()].filter(row => !row.card.hidden).length} de ${records.size} Evento(s) carregado(s)${next ? ' · carregue mais para consultar outras páginas' : ''}.` : 'Nenhum Evento encontrado.';
    }
    function updateCard(event, card) {
        say(card, '[data-title]', event.nm_evento); say(card, '[data-state]', labels[event.status_evento] ?? event.status_evento);
        say(card, '[data-scope]', organizer ? (event.ong_id ? 'Organizador designado · Evento institucional' : 'Proprietário independente') : 'ONG proprietária');
        say(card, '[data-description]', event.descricao_evento); say(card, '[data-location]', event.modalidade_evento === 'online' ? 'Online' : [event.cidade_evento, event.uf_evento].filter(Boolean).join(', '));
        say(card, '[data-agenda-summary]', `${event.agendas?.length ?? 0} Agenda(s) · ${event.vagas_evento} vagas informativas do Evento`);
        const link = $(card, '[data-public-link]'); link.hidden = event.status_evento !== 'ativo'; link.href = `/eventos/${event.evento_id}`;
        $(card, '[data-open-edit]').hidden = mode === 'candidates';
        if (mode === 'dashboard') {
            $(card, '[data-open-edit]').hidden = true; $(card, '[data-open-candidates]').hidden = true;
            if (!$(card, '[data-dashboard-link]')) { const link = el('a', 'Gerenciar', 'btn-secundario btn-sm'); link.dataset.dashboardLink = ''; link.href = `${root.dataset.listUrl}?evento=${event.evento_id}`; $(card, '[data-actions]').append(link); }
        }
    }
    function put(event) {
        const existing = records.get(String(event.evento_id));
        if (existing) { existing.event = event; updateCard(event, existing.card); return existing.card; }
        const card = clone(root, '[data-event-card]');
        records.set(String(event.evento_id), { event, card }); updateCard(event, card); list.append(card);
        for (const [selector, type] of [['[data-open-edit]', 'edit'], ['[data-open-candidates]', 'candidates']]) $(card, selector).addEventListener('click', () => open(event.evento_id, card, type));
        return card;
    }
    async function load() {
        if (loading || !next) return;
        loading = true; more.disabled = true; status.textContent = 'Carregando Eventos…';
        try {
            const { data } = await request(root, next);
            for (const event of data.data) put(event);
            next = data.next_page_url;
            if (mode === 'dashboard' && !next) {
                const events = [...records.values()].map(row => row.event);
                say(document, '[data-dashboard-active]', String(events.filter(event => event.status_evento === 'ativo').length));
                say(document, '[data-dashboard-completed]', String(events.filter(event => event.status_evento === 'finalizado').length));
            }
            if (requestedId && /^\d+$/.test(requestedId) && !opened && mode !== 'dashboard') {
                opened = true;
                try { const { data: event } = await request(root, eventUrl(requestedId)); const card = put(event); await open(event.evento_id, card, mode === 'candidates' ? 'candidates' : 'edit'); }
                catch (error) { status.textContent = errorText(error); return; }
            }
            more.textContent = 'Carregar mais'; loading = false; filter();
            if (mode === 'dashboard' && next) status.textContent += ' · Carregue todas as páginas para completar os indicadores de Eventos.';
        } catch (error) { status.textContent = errorText(error); more.textContent = 'Tentar novamente'; }
        finally { loading = false; more.hidden = !next; more.disabled = false; }
    }
    async function open(id, card, type) {
        const detail = $(card, '[data-detail]');
        if (detail.dataset.loading === '1' || detail.dataset.mutating === '1') return;
        if (detail.dataset.type === type && !detail.hidden) { detail.hidden = true; detail.replaceChildren(); return; }
        detail.dataset.type = type; detail.dataset.loading = '1'; detail.hidden = false;
        detail.replaceChildren(el('p', 'Carregando detalhes…'));
        try {
            const { data: event } = await request(root, eventUrl(id));
            put(event);
            if (type === 'edit') { catalogs ??= (await request(root, `${base}/catalogos`)).data; renderEdit(detail, event, card); }
            else renderCandidates(detail, event);
        } catch (error) { detail.replaceChildren(el('p', errorText(error), 'text-red-700')); detail.append(button('Tentar novamente', () => { detail.hidden = true; open(id, card, type); })); }
        finally { detail.dataset.loading = '0'; }
    }
    function renderEdit(detail, event, card, notice = '') {
        detail.replaceChildren();
        const feedback = el('p', notice, 'text-sm'); feedback.setAttribute('role', 'status'); detail.append(feedback);
        const owned = !organizer || !event.ong_id, closed = terminal(event.status_evento);
        let busy = false;
        const run = async (url, method, body, form = null) => {
            if (busy) return; busy = true; detail.dataset.mutating = '1';
            const controls = $$(detail, 'button, input, select, textarea').filter(node => !node.disabled); controls.forEach(node => node.disabled = true);
            const actionButtons = $$(card, '[data-actions] button'); actionButtons.forEach(node => node.disabled = true);
            feedback.textContent = 'Salvando…'; let saved = false;
            try {
                const result = await request(root, url, method, body); saved = true;
                const { data: fresh } = await request(root, eventUrl(event.evento_id)); put(fresh);
                renderEdit(detail, fresh, card, `${result?.message ?? 'Agenda excluída.'} Estado do Evento: ${labels[fresh.status_evento] ?? fresh.status_evento}.`);
                filter();
            } catch (error) {
                if (saved) { feedback.textContent = `Alteração enviada, mas não foi possível atualizar a consulta: ${errorText(error)}. Reabra o Evento antes de continuar.`; detail.append(button('Recarregar detalhes', () => { detail.hidden = true; open(event.evento_id, card, 'edit'); })); }
                else { if (form) showErrors(form, error, feedback); else feedback.textContent = errorText(error); if (error.status === 409) detail.append(button('Recarregar dados', () => { detail.hidden = true; open(event.evento_id, card, 'edit'); })); }
                feedback.scrollIntoView({ block: 'nearest' });
            } finally { busy = false; detail.dataset.mutating = '0'; if (!saved) controls.forEach(node => node.disabled = false); actionButtons.forEach(node => node.disabled = false); }
        };
        const cancel = (url, label) => {
            const reason = window.prompt(`Motivo para cancelar ${label} (até 2.000 caracteres):`);
            if (reason === null) return;
            if (!reason.trim() || reason.trim().length > 2000) { feedback.textContent = 'Informe um motivo entre 1 e 2.000 caracteres.'; return; }
            if (window.confirm(`Confirmar cancelamento de ${label}? O histórico será preservado.`)) run(`${url}/cancelar`, 'POST', { motivo: reason.trim() });
        };
        const form = clone(root, '[data-edit-form]'); fillCatalogs(form, catalogs, event);
        for (const name of fields) form.elements[name].value = event[name] ?? '';
        setAddress(form); form.elements.modalidade_evento.addEventListener('change', () => setAddress(form));
        form.addEventListener('submit', e => {
            e.preventDefault(); if (busy) return;
            const payload = eventPayload(form), changes = {};
            for (const name of fields) if (name in payload && String(payload[name] ?? '') !== String(event[name] ?? '')) changes[name] = payload[name];
            for (const [group, [key]] of Object.entries(groups)) {
                const before = (event[group] ?? []).map(item => Number(item[key])).sort((a,b)=>a-b), after = [...payload[group]].sort((a,b)=>a-b);
                if (JSON.stringify(before) !== JSON.stringify(after)) changes[group] = after;
            }
            if (!Object.keys(changes).length) { feedback.textContent = 'Nenhuma alteração para salvar.'; return; }
            if (Object.keys(groups).some(group => group in changes) && !window.confirm('Substituir as listas de associações alteradas pelas opções selecionadas?')) return;
            run(eventUrl(event.evento_id), 'PATCH', changes, form);
        });
        detail.append(form);
        if (!closed) detail.append(button('Cancelar Evento', () => cancel(eventUrl(event.evento_id), 'o Evento')));
        detail.append(el('h3', 'Agendas do Evento', 'font-semibold'));
        const agendaForm = agenda => {
            const row = clone(root, '[data-agenda-form]');
            say(row, '[data-agenda-title]', agenda ? `Agenda ${agenda.agenda_id} · ${agenda.status_ativo}` : 'Nova Agenda');
            $(row, '[data-agenda-start]').value = local(agenda?.data_inicio);
            $(row, '[data-agenda-end]').value = local(agenda?.data_fim);
            $(row, '[data-agenda-capacity]').value = agenda?.vagas_agenda ?? 1;
            row.addEventListener('input', () => $(row, '[data-agenda-end]').setCustomValidity(''));
            row.addEventListener('submit', e => { e.preventDefault(); if (busy) return; const data = agendaPayload(row); if (data) run(agenda ? agendaUrl(agenda.agenda_id) : `${eventUrl(event.evento_id)}/agendas`, agenda ? 'PATCH' : 'POST', data, row); });
            if (agenda && !terminal(agenda.status_ativo) && !closed) {
                const actions = $(row, '[data-agenda-actions]');
                if (owned && event.agendas.length > 1) actions.append(button('Excluir Agenda', () => { if (window.confirm('Excluir Agenda? O servidor só permite se não houver inscrições e se outra Agenda permanecer.')) run(agendaUrl(agenda.agenda_id), 'DELETE'); }));
                if (agenda.status_ativo === 'ativo') actions.append(button('Cancelar Agenda', () => cancel(agendaUrl(agenda.agenda_id), 'a Agenda')));
            }
            if (closed || terminal(agenda?.status_ativo)) $$(row, 'input,button').forEach(node => node.disabled = true);
            return row;
        };
        for (const agenda of event.agendas ?? []) detail.append(agendaForm(agenda));
        if (owned && !closed) detail.append(agendaForm(null));
        if (!owned) detail.append(el('p', 'A criação e exclusão de Agendas são exclusivas do proprietário.', 'text-sm'));
        if ((event.agendas ?? []).length === 1) detail.append(el('p', 'A última Agenda não pode ser excluída.', 'text-sm'));
        const imageForm = clone(root, '[data-image-form]'), img = $(imageForm, '[data-private-image]'), imgStatus = $(imageForm, '[data-image-status]');
        const privateUrl = `${eventUrl(event.evento_id)}/imagem`;
        if (event.imagem_evento_link === privateUrl) {
            img.addEventListener('load', () => { img.hidden = false; imgStatus.textContent = ''; });
            img.addEventListener('error', () => { img.hidden = true; imgStatus.textContent = 'Imagem indisponível. Confira a conexão ou armazenamento.'; });
            img.src = `${privateUrl}?v=${Date.now()}`;
        } else imgStatus.textContent = 'Nenhuma imagem gerenciada disponível.';
        $(imageForm, '[data-remove-image]').disabled = event.imagem_evento_link !== privateUrl;
        imageForm.addEventListener('submit', async e => {
            e.preventDefault(); if (busy) return;
            const file = imageForm.elements.imagem.files[0]; if (!file) return;
            // Lock before asynchronous validation, avoiding duplicate uploads.
            busy = true; detail.dataset.mutating = '1'; const submit = $(imageForm, '[type=submit]'); submit.disabled = true;
            try { await validateImage(file); }
            catch (error) { feedback.textContent = errorText(error); return; }
            finally { busy = false; detail.dataset.mutating = '0'; submit.disabled = false; }
            const data = new FormData(); data.append('imagem', file); run(privateUrl, 'POST', data, imageForm);
        });
        $(imageForm, '[data-remove-image]').addEventListener('click', () => { if (window.confirm('Remover a imagem do Evento?')) run(privateUrl, 'DELETE'); });
        detail.append(imageForm);
        if (closed) $$(detail, 'input, select, textarea, button').forEach(node => node.disabled = true);
    }
    function renderCandidates(detail, event) {
        detail.replaceChildren(el('h3', `Candidaturas · ${event.nm_evento}`, 'font-semibold'));
        for (const agenda of event.agendas ?? []) {
            const section = el('details', '', 'rounded-xl border border-[#dedbd1] p-4');
            const title = el('summary', `Agenda ${agenda.agenda_id} · ${date(agenda.data_inicio)} · ${agenda.vagas_agenda} vagas · ${agenda.status_ativo}`, 'cursor-pointer font-semibold');
            const body = el('div', '', 'space-y-3 mt-3'), feedback = el('p', '', 'text-sm'); feedback.setAttribute('role', 'status');
            const select = document.createElement('select'); select.className = 'rounded-xl border p-2'; select.setAttribute('aria-label', 'Filtrar inscrições por status');
            for (const state of ['', 'pendente', 'aprovado', 'reprovado', 'participando', 'participou', 'não participou']) select.add(new Option(state || 'Todos os status', state));
            const cards = el('div', '', 'space-y-3'); let next, loading = false, busy = false, loaded = false;
            const more = button('Carregar mais', () => load(false)); more.hidden = true;
            const endpoint = `${agendaUrl(agenda.agenda_id)}/inscricoes`;
            async function load(reset, notice = '') {
                if (loading || busy) return; loading = true; select.disabled = true; more.disabled = true;
                if (reset) { cards.replaceChildren(); next = `${endpoint}?per_page=20${select.value ? `&status=${encodeURIComponent(select.value)}` : ''}`; }
                feedback.textContent = 'Carregando candidaturas…';
                try {
                    const { data } = await request(root, next);
                    for (const row of data.data) {
                        const card = el('article', '', 'rounded-xl border border-[#dedbd1] p-3 space-y-2');
                        card.append(el('h4', row.pessoa?.nm_pessoa ?? `Pessoa ${row.pessoa_id}`, 'font-semibold'), el('p', `Status: ${row.status_inscricao}`, 'text-sm'));
                        if (row.match) card.append(el('p', `Compatibilidade: ${row.match.habilidades} habilidade(s), ${row.match.causas} causa(s), ${row.match.recursos} recurso(s)`, 'text-sm'));
                        if (row.pessoa?.xp_pessoa != null) card.append(el('p', `XP: ${row.pessoa.xp_pessoa}`, 'text-sm'));
                        const reliability = candidateReliability(row.confiabilidade);
                        if (reliability !== null) {
                            const indicator = el('p', reliability, 'text-sm');
                            indicator.dataset.candidateReliability = '';
                            card.append(indicator);
                        }
                        if (row.carta_apresentacao) { const letter = el('details'); letter.append(el('summary', 'Carta de apresentação (privada)'), el('p', row.carta_apresentacao, 'whitespace-pre-wrap break-words')); card.append(letter); }
                        const transitions = event.status_evento === 'ativo' && agenda.status_ativo === 'ativo' ? ({ pendente: ['aprovado', 'reprovado'], aprovado: ['participando'] }[row.status_inscricao] ?? []) : [];
                        for (const state of transitions) card.append(button(({ aprovado: 'Aprovar', reprovado: 'Reprovar', participando: 'Marcar participando' })[state], async () => {
                            if (busy || loading || detail.dataset.mutating === '1' || !window.confirm(`Confirmar alteração para ${state}?`)) return;
                            busy = true; detail.dataset.mutating = '1'; const controls = $$(detail, 'button,select').filter(node => !node.disabled); controls.forEach(node => node.disabled = true);
                            let message;
                            try { const result = await request(root, `/gestao/inscricoes/${row.inscricao_id}/selecao`, 'PATCH', { status_inscricao: state }); message = result.message; }
                            catch (error) { message = errorText(error); }
                            try {
                                const { data: fresh } = await request(root, eventUrl(event.evento_id));
                                put(fresh);
                                const currentAgenda = fresh.agendas?.find(item => String(item.agenda_id) === String(agenda.agenda_id));
                                if (!currentAgenda) { feedback.textContent = `${message} A Agenda não está mais disponível.`; cards.replaceChildren(); more.hidden = true; select.disabled = true; return; }
                                Object.assign(event, fresh); Object.assign(agenda, currentAgenda);
                                title.textContent = `Agenda ${agenda.agenda_id} · ${date(agenda.data_inicio)} · ${agenda.vagas_agenda} vagas · ${agenda.status_ativo}`;
                                busy = false;
                                await load(true, message);
                            } catch (error) {
                                feedback.textContent = `${message} Reabra as candidaturas para consultar o estado atualizado: ${errorText(error)}`;
                                cards.replaceChildren(); more.hidden = true; select.disabled = true;
                            } finally {
                                busy = false; detail.dataset.mutating = '0';
                                controls.forEach(node => { if (node !== select || cards.children.length) node.disabled = false; });
                            }
                        }));
                        cards.append(card);
                    }
                    next = data.next_page_url; loaded = true;
                    feedback.textContent = [notice, cards.children.length ? `${cards.children.length} inscrição(ões) carregada(s).` : 'Nenhuma candidatura neste filtro.'].filter(Boolean).join(' ');
                    more.textContent = 'Carregar mais';
                } catch (error) { feedback.textContent = [notice, errorText(error)].filter(Boolean).join(' '); more.textContent = 'Tentar novamente'; }
                finally { loading = false; select.disabled = false; more.disabled = false; more.hidden = !next; }
            }
            select.addEventListener('change', () => load(true)); section.addEventListener('toggle', () => { if (section.open && !loaded && !loading) load(true); });
            body.append(select, feedback, cards, more); section.append(title, body); detail.append(section);
        }
        if (!event.agendas?.length) detail.append(el('p', 'Nenhuma Agenda cadastrada.'));
    }
    $(root, '[data-event-search]')?.addEventListener('input', filter);
    $(root, '[data-event-filter]')?.addEventListener('change', filter);
    more.addEventListener('click', load); load();
}

export function initializeManagedEvents() {
    $$(document, '[data-managed-create]').forEach(createEvent);
    $$(document, '[data-managed-events]').forEach(management);
    $$(document, '[data-public-event-image]').forEach(img => {
        const show = () => { img.hidden = !img.naturalWidth; };
        img.addEventListener('load', show); img.addEventListener('error', show); if (img.complete) show();
    });
    $$(document, '[data-public-agendas]').forEach(async node => {
        try {
            const { data } = await requestJson(node.dataset.publicAgendas);
            node.replaceChildren(...data.map(agenda => el('p', `${date(agenda.inicio)} – ${date(agenda.fim)} · ${agenda.impedimento ?? `${agenda.disponiveis} vagas disponíveis`}`, 'text-sm rounded-xl border border-[#dedbd1] p-3')));
            if (!data.length) node.textContent = 'Nenhuma Agenda disponível.';
        } catch (error) { node.textContent = errorText(error); }
    });
}
