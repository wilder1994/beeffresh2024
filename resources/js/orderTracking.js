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
    let pollTimer = null;

    const formatDate = (iso) => {
        if (!iso) {
            return '';
        }

        return new Date(iso).toLocaleString('es-CO', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            timeZone: 'America/Bogota',
        });
    };

    const renderTimeline = (entries) => {
        if (!timeline || !Array.isArray(entries)) {
            return;
        }

        timeline.innerHTML = entries
            .map((entry) => {
                const isUpcoming = entry.state === 'upcoming';
                const label = entry.label ?? entry.to_status_label ?? entry.to_status ?? '';
                const dateMarkup = isUpcoming
                    ? '<p class="text-xs text-[var(--bf-muted)]">Pendiente</p>'
                    : entry.created_at
                      ? `<p class="text-xs text-[var(--bf-muted)]">${formatDate(entry.created_at)}</p>`
                      : '';

                return `
                <li class="bf-ops-timeline__item${isUpcoming ? ' bf-ops-timeline__item--upcoming' : ''}">
                    <span class="bf-ops-timeline__dot"></span>
                    <div>
                        <p class="font-medium text-sm${isUpcoming ? ' text-[var(--bf-muted)]' : ''}">${label}</p>
                        ${dateMarkup}
                    </div>
                </li>`;
            })
            .join('');
    };

    const poll = async () => {
        try {
            const response = await fetch(feedUrl, {
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

            if (Array.isArray(payload.timeline)) {
                renderTimeline(payload.timeline);
            }

            if (order.status === 'delivered' || order.status === 'cancelled') {
                if (pollTimer !== null) {
                    window.clearInterval(pollTimer);
                }
            }
        } catch {
            // ignore
        }
    };

    poll();
    pollTimer = window.setInterval(poll, 12000);
});
