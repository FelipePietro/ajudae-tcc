const scheduledModes = new Set();

export function startApplication(mode, initialize) {
    if (scheduledModes.has(mode)) return;
    scheduledModes.add(mode);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize, { once: true });
    } else {
        initialize();
    }
}
