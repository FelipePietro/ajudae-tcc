import { requestJson } from '../core/http.js';

export function initializeRanking() {
    const root = document.querySelector('[data-web-ranking]');
    if (!root) return;
    const city = root.querySelector('[data-ranking-city]');
    const search = root.querySelector('[data-ranking-search]');
    const previous = root.querySelector('[data-ranking-prev]');
    const next = root.querySelector('[data-ranking-next]');
    const status = root.querySelector('[data-ranking-count]');
    const empty = root.querySelector('[data-ranking-empty]');
    const rows = root.querySelector('[data-ranking-rows]');
    const podium = root.querySelector('[data-podium-section]');
    let page = 1;
    let running;
    let timer;

    const populate = (target, record) => {
        target.querySelector('[data-name]').textContent = record.nm_pessoa;
        target.querySelector('[data-city]').textContent = record.cidade_pessoa ?? '';
        target.querySelector('[data-xp]').textContent = `${record.xp_pessoa} XP`;
    };

    async function load(number = 1) {
        running?.abort();
        running = new AbortController();
        const params = new URLSearchParams({ page: number });
        if (city.value) params.set('cidade', city.value);
        if (search.value.trim()) params.set('busca', search.value.trim());
        previous.disabled = true;
        next.disabled = true;
        status.textContent = 'Carregando classificação…';
        try {
            const { data } = await requestJson(`${root.dataset.webRanking}?${params}`, { signal: running.signal });
            if (city.options.length === 1) {
                for (const name of data.cidades) city.add(new Option(name, name));
            }
            rows.replaceChildren();
            for (const record of data.ranking.data) {
                const row = root.querySelector('template').content.cloneNode(true);
                populate(row, record);
                row.querySelector('[data-position]').textContent = `${record.posicao}º`;
                rows.append(row);
            }
            podium.hidden = data.podio.length === 0 || search.value.trim() !== '';
            root.querySelector('[data-ranking-scope]').textContent = city.value || 'Ranking global';
            for (const slot of podium.querySelectorAll('[data-podium]')) {
                const record = data.podio[Number(slot.dataset.podium)];
                slot.hidden = !record;
                if (!record) continue;
                populate(slot, record);
                slot.querySelector('[data-initials]').textContent = record.nm_pessoa.trim().split(/\s+/).slice(0, 2)
                    .map(word => word[0]).join('').toUpperCase();
            }
            page = data.ranking.current_page;
            previous.disabled = page <= 1;
            next.disabled = !data.ranking.next_page_url;
            empty.hidden = data.ranking.data.length > 0;
            status.textContent = `Página ${page} de ${data.ranking.last_page}`;
        } catch (error) {
            if (error.name === 'AbortError') return;
            status.textContent = error.message;
        }
    }

    city.addEventListener('change', () => load());
    search.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => load(), 250);
    });
    previous.addEventListener('click', () => load(page - 1));
    next.addEventListener('click', () => load(page + 1));
    load();
}
