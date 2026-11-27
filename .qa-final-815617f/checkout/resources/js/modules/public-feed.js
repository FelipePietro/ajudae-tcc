export function initializePublicFeed() {
    const filters = document.querySelector('[data-public-feed-filters]');
    if (!filters) return;

    const toggle = filters.querySelector('#btn-filtros');
    const panel = filters.querySelector('#painel-filtros');
    const show = open => {
        panel.hidden = !open;
        toggle.setAttribute('aria-expanded', String(open));
    };
    show(Boolean(filters.elements.modalidade.value));
    toggle.addEventListener('click', () => show(panel.hidden));
    filters.addEventListener('keydown', event => {
        if (event.key !== 'Escape' || panel.hidden) return;
        show(false);
        event.preventDefault();
        toggle.focus();
    });
}
