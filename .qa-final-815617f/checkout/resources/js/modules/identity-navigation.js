const selectTab = (root, focus = false) => {
    const tabs = [...root.querySelectorAll('[data-profile-tab]')];
    const selected = tabs.find(tab => tab.hash === location.hash) ?? tabs[0];

    for (const tab of tabs) {
        const active = tab === selected;
        if (active) tab.setAttribute('aria-current', 'location');
        else tab.removeAttribute('aria-current');
        const panel = document.getElementById(tab.hash.slice(1));
        if (!panel) continue;
        panel.hidden = !active;
        if (active && focus) panel.focus();
    }
};

export function initializeIdentityNavigation() {
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
}
