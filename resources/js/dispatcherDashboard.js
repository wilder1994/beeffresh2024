document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-dispatcher-dashboard]');
    const feedUrl = root?.dataset.feedUrl;
    if (!feedUrl) {
        return;
    }

    window.setInterval(async () => {
        try {
            await fetch(feedUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
        } catch {
            // respaldo silencioso
        }
    }, 60000);
});
