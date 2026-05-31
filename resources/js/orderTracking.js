/**
 * Seguimiento de pedido: WS realtime + polling fallback 12s + mapa en vivo.
 */
import { bfRealtimeStore } from './realtime/stores/realtimeStore.js';
import { bfPatchTrackingPage } from './realtime/utils/trackingUi.js';
import { bfInitCustomerTrackingMap, bfUpdateCustomerTrackingMap } from './trackingMap.js';

document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-order-tracking]');
    if (!root) {
        return;
    }

    const feedUrl = root.dataset.feedUrl;
    if (!feedUrl) {
        return;
    }

    const destLat = root.dataset.destLat ? Number(root.dataset.destLat) : null;
    const destLng = root.dataset.destLng ? Number(root.dataset.destLng) : null;

    const courierLat = root.dataset.courierLat ? Number(root.dataset.courierLat) : null;
    const courierLng = root.dataset.courierLng ? Number(root.dataset.courierLng) : null;

    bfInitCustomerTrackingMap({
        initialPhase: root.dataset.mapPhase ?? 'waiting',
        destination: { lat: destLat, lng: destLng },
        courierLocation: courierLat != null && courierLng != null
            ? { lat: courierLat, lng: courierLng }
            : null,
    });

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
            map_phase: payload.map_phase,
            destination: payload.destination ?? {
                lat: order.shipping_latitude,
                lng: order.shipping_longitude,
            },
            courier_location: payload.courier_location ?? null,
            maps_api_key: payload.maps_api_key ?? null,
        };

        root.dataset.orderStatus = order.status ?? '';
        bfPatchTrackingPage(tracking);
        bfUpdateCustomerTrackingMap(tracking);

        const locPending = document.getElementById('tracking-map-loc-pending');
        if (locPending) {
            const live = tracking.status === 'picked_up' || tracking.status === 'in_transit';
            const hasLoc = tracking.courier_location?.lat != null;
            locPending.classList.toggle('hidden', !live || hasLoc);
        }

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
