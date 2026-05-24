/**
 * Seguimiento de pedido para clientes (polling).
 */
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-order-tracking]');
    if (!root) {
        return;
    }

    const feedUrl = root.dataset.feedUrl;
    if (!feedUrl) {
        return;
    }

    const statusLabel = document.getElementById('tracking-status-label');
    const timeline = document.getElementById('tracking-timeline');
    let since = new Date().toISOString();

    const renderTimeline = (entries) => {
        if (!timeline || !Array.isArray(entries)) {
            return;
        }

        timeline.innerHTML = entries
            .map(
                (entry) => `
                <li class="bf-ops-timeline__item">
                    <span class="bf-ops-timeline__dot"></span>
                    <div>
                        <p class="font-medium text-sm">${entry.to_status_label ?? entry.to_status}</p>
                        <p class="text-xs text-[var(--bf-muted)]">${formatDate(entry.created_at)}</p>
                    </div>
                </li>`,
            )
            .join('');
    };

    const formatDate = (iso) => {
        if (!iso) {
            return '';
        }
        const date = new Date(iso);
        return date.toLocaleString('es-CO', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    const poll = async () => {
        try {
            const url = new URL(feedUrl, window.location.origin);
            url.searchParams.set('since', since);

            const response = await fetch(url.toString(), {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            const order = payload.order ?? {};

            if (statusLabel && order.status_label) {
                statusLabel.textContent = order.status_label;
            }

            if (Array.isArray(payload.timeline) && payload.timeline.length > 0) {
                renderTimeline(payload.timeline);
            }

            if (payload.generated_at) {
                since = payload.generated_at;
            }

            if (order.status === 'delivered' || order.status === 'cancelled') {
                return;
            }
        } catch {
            // ignore
        }
    };

    poll();
    window.setInterval(poll, 12000);
});
