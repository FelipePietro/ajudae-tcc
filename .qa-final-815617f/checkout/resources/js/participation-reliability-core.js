const clamp = value => Math.max(0, Math.min(100, value));

/** RN027: confirmation unlocks display; only concluded attendance earns +3. */
export function reliabilitySummary(fixture, history, personId) {
    const rows = [];
    let confirmed = 0;
    const seen = new Set();
    for (const base of fixture.records) {
        if (base.personId !== personId || base.selection !== 'aprovada' || seen.has(`${fixture.id}:${base.agendaId}:${base.personId}`)) continue;
        seen.add(`${fixture.id}:${base.agendaId}:${base.personId}`);
        const agenda = fixture.agendas.find(item => item.id === base.agendaId);
        if (!agenda || agenda.state === 'cancelada' || fixture.eventStatus === 'cancelado') continue;
        const entries = history.filter(entry => entry.recordId === base.id);
        const record = entries.reduce((current, entry) => ({ ...current, ...entry.change }), base);
        const closure = history.find(entry => entry.action === 'finalizarAgenda' && entry.agendaId === agenda.id && entry.eventId === fixture.id);
        const completed = agenda.initiallyClosed || Boolean(closure);
        let type, delta;
        if (record.withdrawal) {
            type = record.withdrawal === 'tardia' ? 'Desistência tardia' : record.withdrawal === 'normal' ? 'Desistência normal' : 'Desistência sem penalidade';
            delta = record.withdrawal === 'tardia' ? -10 : record.withdrawal === 'normal' ? -5 : 0;
        } else if (record.presence === 'presente') {
            confirmed++;
            if (!completed) continue;
            type = 'Participação concluída'; delta = 3;
        } else if (record.presence === 'ausente') {
            type = 'Ausência'; delta = -20;
        } else continue;
        const original = entries.find(entry => ['presente', 'ausente', 'desistir'].includes(entry.action));
        const at = delta === 3 ? (closure?.time ?? Date.parse(agenda.end)) : (original?.time ?? Date.parse(agenda.end));
        const reversed = delta < 0 && record.justification?.status === 'deferida';
        rows.push({ id: base.id, agenda: agenda.number, agendaId: agenda.id, type, delta, effective: reversed ? 0 : delta,
            reversed, at, justification: record.justification?.status ?? null,
            decisions: entries.filter(entry => ['justificar', 'deferida', 'indeferida'].includes(entry.action)) });
    }
    rows.sort((a, b) => a.at - b.at || a.id - b.id);
    const formed = confirmed >= 3;
    // Remove the accepted occurrence's effect and replay chronology: never add a repeated refund.
    return { formed, confirmed, score: formed ? rows.reduce((score, row) => clamp(score + row.effective), 100) : null, rows };
}

/** XP snapshots are the only source; secondary ordering is a stable identity key. */
export function rankingRows(people, ledger, { city = '', search = '', page = 1, perPage = 10 } = {}) {
    const totals = new Map();
    for (const row of ledger.values()) totals.set(row.personId, (totals.get(row.personId) ?? 0) + row.total);
    const scope = people.filter(person => person.approved && !person.deleted && !person.anonymous
        && ['voluntario', 'organizador'].includes(person.role) && (!city || person.city === city))
        .map(person => ({ id: person.id, name: person.name, city: person.city, xp: totals.get(person.id) ?? 0 }))
        .sort((a, b) => b.xp - a.xp || a.id - b.id)
        .map((person, index) => ({ ...person, position: index + 1 }));
    const normalize = value => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('pt-BR');
    const filtered = scope.filter(person => normalize(person.name).includes(normalize(search.trim())));
    const pages = Math.max(1, Math.ceil(filtered.length / perPage));
    const current = Math.max(1, Math.min(pages, Number.isSafeInteger(page) ? page : 1));
    return { rows: filtered.slice((current - 1) * perPage, current * perPage), top: scope.slice(0, 3), scope, total: filtered.length, page: current, pages };
}
