import './navigation-accessibility.js';
import '../css/components/accessibility.css';

const selectTab = (root, focus = false) => {
    const tabs = [...root.querySelectorAll('[data-profile-tab]')];
    const selected = tabs.find(tab => tab.hash === location.hash) ?? tabs[0];

    for (const tab of tabs) {
        const active = tab === selected;
        if (active) tab.setAttribute('aria-current', 'location');
        else tab.removeAttribute('aria-current');
        const panel = document.getElementById(tab.hash.slice(1));
        panel.hidden = !active;
        if (active && focus) panel.focus();
    }
};

document.addEventListener('DOMContentLoaded', () => {
    const profile = document.querySelector('[data-own-profile]');
    if (profile) {
        selectTab(profile);
        window.addEventListener('hashchange', () => selectTab(profile, true));
    }

    const panelLinks = [...document.querySelectorAll('[data-panel-section-link]')];
    if (panelLinks.length) {
        const selectSection = () => {
            for (const link of panelLinks) {
                if (link.pathname === location.pathname && link.hash === location.hash) {
                    link.setAttribute('aria-current', 'location');
                } else {
                    link.removeAttribute('aria-current');
                }
            }
        };
        selectSection();
        window.addEventListener('hashchange', selectSection);
    }

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
});

// Conceal a cached document until the server has revalidated the current session.
window.addEventListener('pagehide', event => {
    if (event.persisted) document.body.style.visibility = 'hidden';
});
window.addEventListener('pageshow', event => {
    if (event.persisted) location.reload();
});
