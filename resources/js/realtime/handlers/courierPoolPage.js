import { bfRealtimeStore } from '../stores/realtimeStore.js';
import {
    bfInitCourierPoolHandler,
    bfSyncCourierPoolFromFeed,
} from './courierPoolHandler.js';

/** @type {ReturnType<typeof setInterval>|null} */
let pollTimer = null;

/**
 * Panel domiciliario: listener WS + polling de respaldo.
 */
export function bfBootCourierPoolPage() {
    const root = document.querySelector('[data-courier-pool]');
    const feedUrl = root?.dataset.feedUrl;
    if (!root || !feedUrl || pollTimer !== null) {
        return;
    }

    bfInitCourierPoolHandler(root);

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
            const signature = orders
                .map((order) => `${order.id}:${order.status}:${order.updated_at}:${order.courier_id ?? ''}`)
                .join('|');
            const acceptFlag = payload.can_accept ? '1' : '0';
            const fullSignature = `${signature}|accept:${acceptFlag}`;

            const isFirstSnapshot = lastSignature === '' && orders.length > 0;
            const hasChanges = lastSignature !== '' && fullSignature !== lastSignature;

            if (isFirstSnapshot || hasChanges) {
                await bfSyncCourierPoolFromFeed(orders, Boolean(payload.can_accept));
            }

            lastSignature = fullSignature;

            if (payload.generated_at) {
                since = payload.generated_at;
            }
        } catch {
            // fallback silencioso
        }
    };

    poll();
    pollTimer = window.setInterval(poll, intervalMs);
}

/**
 * Resync explícito (notificación operacional o reconexión).
 */
export async function bfRefreshCourierPoolFromFeed() {
    const root = document.querySelector('[data-courier-pool]');
    const feedUrl = root?.dataset.feedUrl;
    if (!feedUrl) {
        return;
    }

    try {
        const response = await fetch(feedUrl, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            return;
        }

        const payload = await response.json();
        await bfSyncCourierPoolFromFeed(payload.orders ?? [], Boolean(payload.can_accept));
    } catch {
        // polling cubrirá
    }
}

/**
 * @param {object} notification
 */
export function bfMaybeRefreshCourierPoolFromNotification(notification) {
    if (notification?.type !== 'order_ready_for_delivery') {
        return;
    }

    if (!document.querySelector('[data-courier-pool]')) {
        return;
    }

    void bfRefreshCourierPoolFromFeed();
}
