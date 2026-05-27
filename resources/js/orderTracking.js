/**
 * Seguimiento de pedido: WS realtime + polling fallback 12s.
 */
import { bfRealtimeStore } from './realtime/stores/realtimeStore.js';
import { bfPatchTrackingPage } from './realtime/utils/trackingUi.js';

document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-order-tracking]');
    if (!root) {
        return;
    }

    const feedUrl = root.dataset.feedUrl;
    if (!feedUrl) {
        return;
    }

    let pollTimer = null;
    let pollInFlight = false;

    const pollIntervalMs = () => (bfRealtimeStore.isLiveMode() ? 24000 : 12000);

    const schedulePoll = () => {
        if (pollTimer !== null) {
            window.clearInterval(pollTimer);
        }

        pollTimer = window.setInterval(poll, pollIntervalMs());
    };

    const applyFeed = (payload) => {
        const order = payload.order ?? {};

        const tracking = {
            order_id: order.id,
            status: order.status,
            status_label: order.status_label,
            timeline: payload.timeline,
            courier: order.courier_name ? { name: order.courier_name } : null,
            updated_at: order.updated_at,
        };

        bfPatchTrackingPage(tracking);

        if (order.status === 'delivered' || order.status === 'cancelled') {
            if (pollTimer !== null) {
                window.clearInterval(pollTimer);
                pollTimer = null;
            }
        }
    };

    const poll = async () => {
        if (pollInFlight) {
            return;
        }

        pollInFlight = true;

        try {
            const response = await fetch(feedUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            applyFeed(await response.json());
        } catch {
            // ignore
        } finally {
            pollInFlight = false;
        }
    };

    poll();
    schedulePoll();

    window.addEventListener('bf:realtime-resync', () => {
        poll();
    });

    window.addEventListener('bf:realtime-status', () => {
        schedulePoll();
    });
});
