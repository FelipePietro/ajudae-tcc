// Presentation ergonomics only; no identity, stores or business decisions are changed here.
export function bindDisclosure(button, panel, media, render) {
    if (!button || !panel) return;
    let open = false;
    const update = (value, restore = false) => {
        open = value && media.matches;
        render(open, media.matches);
        button.setAttribute('aria-expanded', String(open));
        button.setAttribute('aria-label', open ? 'Fechar menu' : 'Abrir menu');
        if (restore) button.focus();
    };
    button.addEventListener('click', () => update(!open));
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && open) { event.preventDefault(); update(false, true); }
    });
    document.addEventListener('click', event => {
        if (open && !panel.contains(event.target) && !button.contains(event.target)) update(false);
    });
    panel.addEventListener('focusout', event => {
        if (open && event.relatedTarget && !panel.contains(event.relatedTarget) && event.relatedTarget !== button) update(false);
    });
    media.addEventListener('change', () => update(false));
    update(false);
}

export function bindDrawer({ trigger, panel, close, overlay, checkbox = false }) {
    if (!trigger || !panel || !close) return;
    const media = matchMedia('(max-width: 1023px)');
    let open = false, previousOverflow, isolated = [];
    const restoreBackground = () => {
        for (const [node, wasInert] of isolated) node.inert = wasInert;
        isolated = [];
        if (previousOverflow !== undefined) { document.body.style.overflow = previousOverflow; previousOverflow = undefined; }
    };
    const update = (value, restore = false) => {
        open = value && media.matches;
        restoreBackground();
        trigger.setAttribute('aria-expanded', String(open));
        trigger.setAttribute('aria-label', open ? 'Fechar menu' : 'Abrir menu');
        if (checkbox) trigger.checked = open;
        else {
            panel.classList.toggle('-translate-x-full', !open);
            panel.classList.toggle('translate-x-0', open);
            overlay?.classList.toggle('hidden', !open);
        }
        panel.inert = media.matches && !open;
        if (media.matches && !open) panel.setAttribute('aria-hidden', 'true');
        else panel.removeAttribute('aria-hidden');
        if (open) {
            panel.setAttribute('role', 'dialog');
            panel.setAttribute('aria-modal', 'true');
            // Inert only the background, preserving pre-existing inert states.
            for (let node = panel; node.parentElement && node !== document.body; node = node.parentElement) {
                for (const sibling of node.parentElement.children) {
                    if (sibling === node || sibling === overlay || ['SCRIPT', 'STYLE', 'LINK'].includes(sibling.tagName)) continue;
                    isolated.push([sibling, sibling.inert]); sibling.inert = true;
                }
            }
            previousOverflow = document.body.style.overflow;
            document.body.style.overflow = 'hidden';
            close.focus();
            // Checkbox-driven visibility may settle after the native key/click default action.
            requestAnimationFrame(() => { if (open && !panel.contains(document.activeElement)) close.focus(); });
        } else {
            panel.removeAttribute('role'); panel.removeAttribute('aria-modal');
            if (restore && media.matches) trigger.focus();
        }
    };
    if (checkbox) {
        trigger.setAttribute('role', 'button');
        trigger.addEventListener('change', () => update(trigger.checked));
        trigger.addEventListener('keydown', event => {
            if (event.key === 'Enter') { event.preventDefault(); update(!open); }
        });
    } else trigger.addEventListener('click', () => update(!open));
    close.addEventListener('click', () => update(false, true));
    overlay?.addEventListener('click', event => {
        if (checkbox) event.preventDefault();
        update(false, true);
    });
    panel.addEventListener('click', event => {
        if (!open || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        const link = event.target.closest('a[data-panel-section-link]');
        if (!link || link.pathname !== location.pathname || link.search !== location.search) return;
        const target = document.getElementById(link.hash.slice(1));
        if (!target) return;
        update(false);
        target.focus({ preventScroll: true });
    });
    panel.addEventListener('keydown', event => {
        if (!open || event.key !== 'Tab') return;
        const nodes = [...panel.querySelectorAll('a[href],button:not([disabled]),input:not([disabled]),[tabindex="0"]')]
            .filter(node => node.getClientRects().length && getComputedStyle(node).visibility !== 'hidden');
        const first = nodes[0], last = nodes.at(-1);
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
    });
    document.addEventListener('keydown', event => {
        if (open && event.key === 'Escape') { event.preventDefault(); update(false, true); }
    });
    media.addEventListener('change', () => update(false));
    update(false);
}

document.addEventListener('DOMContentLoaded', () => {
    const publicMenu = document.querySelector('#btn-menu-publico');
    bindDisclosure(publicMenu, document.querySelector('#menu-publico-mobile'),
        matchMedia(publicMenu?.dataset.menuBreakpoint === '1023' ? '(max-width: 1023px)' : '(max-width: 767px)'), (open) => document.querySelector('#menu-publico-mobile').classList.toggle('hidden', !open));
    const personal = document.querySelector('#navbar-toggle');
    bindDisclosure(personal, document.querySelector('#navbar-links'), matchMedia('(max-width: 1023px)'), open => {
        document.querySelector('#navbar-links').classList.toggle('navbar-links-aberto', open);
        personal.textContent = open ? '✕' : '☰';
    });
    bindDrawer({ trigger: document.querySelector('#btn-abrir-menu-ong'), panel: document.querySelector('#sidebar-ong'), close: document.querySelector('#btn-fechar-menu-ong'), overlay: document.querySelector('#overlay-menu-ong') });
    bindDrawer({ trigger: document.querySelector('#admin-menu'), panel: document.querySelector('#admin-sidebar'), close: document.querySelector('[data-admin-menu-close]'), overlay: document.querySelector('#admin-menu ~ .ong-menu-bg'), checkbox: true });
    const main = document.querySelector('main');
    if (main && !document.querySelector('.account-skip')) {
        main.id ||= 'main-content';
        main.setAttribute('tabindex', '-1');
        const skip = document.createElement('a');
        skip.className = 'global-skip'; skip.href = `#${main.id}`; skip.textContent = 'Ir para o conteúdo';
        document.body.prepend(skip);
    }
    // Scrollable data tables retain their native table semantics and gain a named keyboard region.
    document.querySelectorAll('.admin-tabela').forEach(table => {
        const region = table.parentElement;
        if (region && region.scrollWidth > region.clientWidth) {
            region.tabIndex = 0; region.setAttribute('role', 'region');
            region.setAttribute('aria-label', 'Tabela — role horizontalmente para consultar todas as colunas');
        }
    });
});
