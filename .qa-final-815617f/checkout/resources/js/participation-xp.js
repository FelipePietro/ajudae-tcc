const baseXp = 100;
const eligibleEvent = status => ['ativo', 'finalizado'].includes(status);
const validId = id => Number.isSafeInteger(id) && id > 0;
const number = new Intl.NumberFormat('pt-BR', { maximumFractionDigits: 6 });
const date = new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short', timeZone: 'America/Sao_Paulo' });

export function experienceAmount(agenda, fixture, at) {
    if (!Number.isSafeInteger(agenda.minutes) || agenda.minutes < 30 || !Number.isFinite(at)) return null;
    const start = Date.parse(agenda.start);
    const end = Date.parse(agenda.end);
    if (end - start !== agenda.minutes * 60_000) return null;

    const durationFactor = agenda.minutes <= 60 ? agenda.minutes / 60 : 1 + (agenda.minutes - 60) / 600;
    const campaigns = (fixture.campaigns ?? []).filter(campaign => campaign.active === true
        && campaign.categoryId === fixture.categoryId && validId(campaign.categoryId)
        && Number.isFinite(campaign.factor) && campaign.factor > 0
        && Date.parse(campaign.configuredAt) <= at
        && start >= Date.parse(campaign.start) && start <= Date.parse(campaign.end));
    const thematicFactor = Math.min(3, campaigns.reduce((factor, campaign) => factor * campaign.factor, 1));
    const durationXp = agenda.minutes <= 60 ? (baseXp * agenda.minutes) / 60 : baseXp + (agenda.minutes - 60) / 6;
    // Normalize binary noise around a decimal half before rounding the XP to an integer.
    const total = Math.round(Number((durationXp * thematicFactor).toPrecision(15)));
    if (!Number.isSafeInteger(total) || total < 0) return null;

    return { base: baseXp, minutes: agenda.minutes, durationFactor, thematicFactor, campaigns, total };
}

export function experienceCredits(fixture, agenda, records, closedAt) {
    if (!eligibleEvent(fixture.eventStatus) || agenda.state !== 'finalizada' || !(closedAt >= Date.parse(agenda.end))) return [];
    const amount = experienceAmount(agenda, fixture, closedAt);
    if (!amount) return [];
    return records.filter(record => record.agendaId === agenda.id && validId(record.id) && validId(record.personId)
        && record.selection === 'aprovada' && !record.withdrawal && record.presence === 'presente')
        .map(record => ({
            ...amount,
            eventId: fixture.id,
            eventTitle: fixture.title,
            agendaId: agenda.id,
            agendaNumber: agenda.number,
            recordId: record.id,
            personId: record.personId,
            creditedAt: closedAt,
        }));
}

export function validExperience(entry) {
    return Array.isArray(entry.xp) && entry.xp.every(row => row
        && row.eventId === entry.eventId && row.agendaId === entry.agendaId
        && validId(row.recordId) && validId(row.personId) && validId(row.agendaNumber)
        && row.base === baseXp && Number.isSafeInteger(row.total) && row.total >= 0
        && Number.isSafeInteger(row.minutes) && row.minutes >= 30
        && Number.isFinite(row.durationFactor) && row.durationFactor >= 0.5
        && Number.isFinite(row.thematicFactor) && row.thematicFactor >= 0 && row.thematicFactor <= 3
        && Number.isFinite(row.creditedAt) && Math.abs(row.creditedAt) <= 8.64e15
        && typeof row.eventTitle === 'string' && Array.isArray(row.campaigns)
        && row.campaigns.every(campaign => campaign && typeof campaign.name === 'string' && Number.isFinite(campaign.factor)));
}

export function experienceLedger(fixture, history) {
    const registry = new Map(fixture.records.map(record => [record.id, record]));
    const ledger = new Map();
    for (const entry of history) {
        if (!['finalizarAgenda', 'creditarXp'].includes(entry.action) || entry.eventId !== fixture.id) continue;
        for (const row of entry.xp ?? []) {
            const record = registry.get(row.recordId);
            if (record?.agendaId === row.agendaId && record.personId === row.personId && !ledger.has(row.recordId)) {
                ledger.set(row.recordId, row);
            }
        }
    }
    return ledger;
}

export function reconcileExperience(fixture, history, now, applyHistory) {
    if (!eligibleEvent(fixture.eventStatus)) return history;
    const ledger = experienceLedger(fixture, history);
    const additions = [];
    for (const agenda of fixture.agendas) {
        if (agenda.state === 'cancelada') continue;
        const closure = history.findIndex(entry => entry.action === 'finalizarAgenda'
            && entry.eventId === fixture.id && entry.agendaId === agenda.id);
        if (closure < 0 && !agenda.initiallyClosed) continue;
        const closedAt = closure < 0 ? Date.parse(agenda.end) : history[closure].time;
        if (!(closedAt <= now)) continue;
        // Reconstruct comparecimento at closure, even when a legacy history has no XP snapshot.
        const previous = closure < 0 ? [] : history.slice(0, closure);
        const candidates = fixture.records.filter(record => record.agendaId === agenda.id && !ledger.has(record.id))
            .map(record => applyHistory(record, previous));
        const xp = experienceCredits(fixture, { ...agenda, state: 'finalizada' }, candidates, closedAt);
        if (xp.length) additions.push({
            action: 'creditarXp', eventId: fixture.id, agendaId: agenda.id, time: now, xp,
            actor: { type: 'sistema' }, change: { source: closure < 0 ? 'agenda-inicial' : 'encerramento-anterior' },
        });
    }
    return additions.length ? [...history, ...additions] : history;
}

export function experienceSummary(personId, ledger) {
    const rows = [...ledger.values()].filter(row => row.personId === personId)
        .sort((a, b) => b.creditedAt - a.creditedAt || b.recordId - a.recordId);
    return { personId, total: rows.reduce((sum, row) => sum + row.total, 0), level: null, rows };
}

export function renderAgendaExperience(section, agenda, fixture, status, ledger, now) {
    const summary = section.querySelector('[data-agenda-xp]');
    if (summary) {
        const credits = agenda.records.filter(record => ledger.has(record.id));
        const preview = status === 'ativo' && agenda.state === 'ativa' ? experienceAmount(agenda, fixture, now) : null;
        if (credits.length) {
            summary.textContent = `XP concedido a ${number.format(credits.length)} ${credits.length === 1 ? 'participante' : 'participantes'}.`;
        } else if (preview) {
            const campaigns = preview.campaigns.map(campaign => `${campaign.name} (×${number.format(campaign.factor)})`).join(' e ');
            summary.textContent = `Previsão de ${number.format(preview.total)} XP por presença confirmada ao finalizar a Agenda. `
                + (campaigns ? `${campaigns}. Fator temático aplicado ×${number.format(preview.thematicFactor)}.` : 'Sem bônus temático.');
        } else {
            summary.textContent = agenda.state === 'cancelada' ? 'Agenda cancelada. Nenhum XP concedido.'
                : agenda.state === 'finalizada' ? 'Nenhum XP concedido nesta Agenda.' : 'Previsão de XP indisponível nesta situação.';
        }
    }
    for (const record of agenda.records) {
        const label = section.querySelector(`[data-record-xp="${record.id}"]`);
        const row = ledger.get(record.id);
        label.hidden = !row;
        if (row) label.textContent = `+${number.format(row.total)} XP concedidos`;
    }
}

export function renderExperienceProfile(root, { rows, total }) {
    const filter = root.querySelector('[data-xp-filter]');
    const visible = rows.filter(row => filter.value === 'todos'
        || (filter.value === 'campanha' ? row.campaigns.length : !row.campaigns.length));
    root.querySelector('[data-xp-total]').textContent = `${number.format(total)} XP`;
    root.querySelector('[data-xp-empty]').hidden = visible.length > 0;
    root.querySelector('[data-xp-empty]').textContent = rows.length ? 'Nenhum lançamento para este filtro.' : 'Você ainda não recebeu XP por participação.';
    root.querySelector('[data-xp-filter-field]').hidden = rows.length === 0;

    const template = root.querySelector('template');
    const items = visible.map(row => {
        const item = template.content.cloneNode(true);
        const fields = {
            event: row.eventTitle,
            agenda: `Agenda ${row.agendaNumber}`,
            date: date.format(row.creditedAt),
            base: `${number.format(row.base)} XP`,
            duration: `${number.format(row.minutes)} min · ×${number.format(row.durationFactor)}`,
            thematic: `×${number.format(row.thematicFactor)}` + (row.campaigns.length
                ? ` · ${row.campaigns.map(campaign => campaign.name).join(' e ')}` : ''),
            amount: `+${number.format(row.total)} XP`,
        };
        for (const [key, value] of Object.entries(fields)) item.querySelector(`[data-xp-field="${key}"]`).textContent = value;
        const time = item.querySelector('time');
        time.dateTime = new Date(row.creditedAt).toISOString();
        item.querySelector('a').href = `${root.dataset.participationUrl}#agenda-${row.agendaId}`;
        return item;
    });
    root.querySelector('[data-xp-rows]').replaceChildren(...items);
}
