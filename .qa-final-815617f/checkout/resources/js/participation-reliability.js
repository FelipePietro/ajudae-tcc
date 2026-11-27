import { privacyHidden } from './privacy-demo.js';
import { historyWithExperience } from './participation-demo.js';
import { experienceLedger } from './participation-xp.js';
import { reliabilitySummary, rankingRows } from './participation-reliability-core.js';
const number = new Intl.NumberFormat('pt-BR');
const date = new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short', timeZone: 'America/Sao_Paulo' });
const decisions = { justificar: 'Justificativa enviada', deferida: 'Justificativa aceita', indeferida: 'Justificativa recusada' };
function initReliability(root) {
    const fixture = JSON.parse(root.querySelector('[data-reliability-fixture]').textContent);
    const render = () => {
        try {
            const history = historyWithExperience(fixture, Date.now());
            const summary = reliabilitySummary(fixture, history, fixture.actor.id);
            root.querySelector('[data-reliability-value]').textContent = summary.formed ? `${summary.score}/100` : 'Em formação';
            root.querySelector('[data-reliability-progress]').textContent = summary.formed ? `${summary.confirmed} participações confirmadas. Histórico por Agenda.` : `${summary.confirmed} de 3 participações confirmadas. O valor ficará disponível após a terceira confirmação.`;
            const template = root.querySelector('template');
            root.querySelector('[data-reliability-rows]').replaceChildren(...summary.rows.map(row => {
                const item = template.content.cloneNode(true);
                item.querySelector('[data-kind]').textContent = `${row.type} · Agenda ${row.agenda} · ${date.format(row.at)}`;
                item.querySelector('[data-effect]').textContent = row.reversed ? 'Penalidade revertida. Ocorrência preservada.' : row.delta === 0 ? 'Sem penalidade.' : `${row.delta > 0 ? '+' : '−'}${Math.abs(row.delta)} pontos${summary.formed ? '' : ' · valor em formação'}.`;
                item.querySelector('[data-justification]').textContent = ({ em_analise: 'Justificativa em análise.', deferida: 'Justificativa aceita pelo responsável.', indeferida: 'Justificativa recusada pelo responsável.' })[row.justification] ?? 'Sem justificativa registrada.';
                item.querySelector('[data-decisions]').replaceChildren(...row.decisions.map(entry => {
                    const li = document.createElement('li');
                    li.textContent = `${decisions[entry.action]} · ${entry.actor.type === 'pessoa' ? 'Titular' : 'Gestão responsável'} · ${date.format(entry.time)}${entry.change.justification?.decisionReason ? ' · ' + ({ analise: 'Análise da justificativa apresentada', esclarecimento: 'Esclarecimento confirmado com o titular' })[entry.change.justification.decisionReason] : ''}`;
                    return li;
                }));
                item.querySelector('[data-link]').href = `${root.dataset.participationUrl}#agenda-${row.agendaId}`;
                return item;
            }));
            root.querySelector('[data-reliability-empty]').hidden = summary.rows.length > 0;
            root.querySelector('[data-reliability-content]').hidden = false;
            root.querySelector('[data-reliability-error]').hidden = true;
        } catch {
            root.querySelector('[data-reliability-content]').hidden = true;
            root.querySelector('[data-reliability-error]').hidden = false;
        }
    };
    window.addEventListener('pageshow', render); render();
}
function initRanking(root) {
    const { fixtures, people, ownId } = JSON.parse(root.querySelector('[data-ranking-fixture]').textContent);
    let page = 1;
    const city = root.querySelector('[data-ranking-city]'), search = root.querySelector('[data-ranking-search]');
    const render = () => {
        try {
            const ledger = new Map();
            for (const fixture of fixtures) {
                const history = historyWithExperience(fixture, Date.now());
                for (const row of experienceLedger(fixture, history).values()) ledger.set(`${row.eventId}:${row.recordId}`, row);
            }
            const result = rankingRows(people.filter(person => person.id !== 1 || !privacyHidden('pessoa:1', new URLSearchParams(location.search).get('privacidade') || 'livre')), ledger, { city: city.value, search: search.value, page });
            page = result.page;
            for (const card of root.querySelectorAll('[data-podium]')) {
                const person = result.top[Number(card.dataset.podium)];
                card.hidden = !person;
                if (!person) continue;
                card.querySelector('[data-name]').textContent = person.name;
                card.querySelector('[data-city]').textContent = person.city;
                card.querySelector('[data-xp]').textContent = `${number.format(person.xp)} XP`;
                card.querySelector('[data-initials]').textContent = person.name.split(' ').filter(Boolean).slice(0, 2).map(word => word[0]).join('').toLocaleUpperCase('pt-BR');
            }
            root.querySelector('[data-podium-section]').hidden = !result.top.length;
            root.querySelector('[data-ranking-scope]').textContent = city.value ? `Ranking de ${city.value}` : 'Ranking global';
            const own = result.scope.find(person => person.id === ownId);
            const position = root.querySelector('[data-ranking-own]');
            if (position) position.textContent = own ? `Sua posição: #${own.position} · ${own.name} · ${number.format(own.xp)} XP` : 'Você não aparece neste recorte do ranking.';
            const template = root.querySelector('template');
            root.querySelector('[data-ranking-rows]').replaceChildren(...result.rows.map(person => {
                const item = template.content.cloneNode(true);
                item.querySelector('[data-position]').textContent = person.position;
                item.querySelector('[data-name]').textContent = person.name;
                item.querySelector('[data-city]').textContent = person.city;
                item.querySelector('[data-xp]').textContent = `${number.format(person.xp)} XP`;
                return item;
            }));
            root.querySelector('[data-ranking-empty]').hidden = result.total > 0;
            root.querySelector('[data-ranking-count]').textContent = `${result.total} pessoas · Página ${page} de ${result.pages}`;
            root.querySelector('[data-ranking-prev]').disabled = page <= 1;
            root.querySelector('[data-ranking-next]').disabled = page >= result.pages;
            root.querySelector('[data-ranking-content]').hidden = false;
            root.querySelector('[data-ranking-error]').hidden = true;
        } catch {
            root.querySelector('[data-ranking-content]').hidden = true;
            root.querySelector('[data-ranking-error]').hidden = false;
        }
    };
    city.addEventListener('change', () => { page = 1; render(); });
    search.addEventListener('input', () => { page = 1; render(); });
    for (const [selector, delta] of [['[data-ranking-prev]', -1], ['[data-ranking-next]', 1]]) root.querySelector(selector).addEventListener('click', () => { page += delta; render(); });
    window.addEventListener('pageshow', render); render();
}
if (typeof document !== 'undefined') document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-reliability]').forEach(initReliability);
    document.querySelectorAll('[data-ranking]').forEach(initRanking);
});
