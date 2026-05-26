/**
 * Operaciones pedidos — Fase 1: parche DOM + websocket; polling fallback sin reload.
 */
import {
    bfHandleOrderUpdated,
    bfInitOperationsGridHandler,
    bfPatchOrdersFromFeed,
} from './realtime/handlers/operationsHandler.js';

document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-ops-polling]');
    if (!root) {
        return;
    }

    const feedUrl = root.dataset.feedUrl;
    if (!feedUrl) {
        return;
    }

    bfInitOperationsGridHandler(root);

    let lastSignature = '';
    let since = new Date().toISOString();
    const intervalMs = 15000;

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
            const orders = payload.orders ?? [];
            const signature = orders.map((o) => `${o.id}:${o.status}:${o.updated_at}:${o.courier_id ?? ''}`).join('|');

            if (lastSignature !== '' && signature !== lastSignature) {
                for (const order of orders) {
                    await bfHandleOrderUpdated(order);
                }
                bfPatchOrdersFromFeed(orders);
            }

            lastSignature = signature;
            if (payload.generated_at) {
                since = payload.generated_at;
            }
        } catch {
            // fallback silencioso
        }
    };

    poll();
    window.setInterval(poll, intervalMs);
});
