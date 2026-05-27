import { bfDispatchRealtimeEvent, bfMetaContent } from '../utils/dom.js';
import { bfRealtimeStore } from '../stores/realtimeStore.js';

/**
 * @param {import('laravel-echo').Echo} echo
 */
export function registerTrackingChannels(echo) {
    const token = bfMetaContent('bf-tracking-token');
    const orderId = bfMetaContent('bf-order-id');

    if (!token && !orderId) {
        return;
    }

    const dispatchTracking = (payload) => {
        bfRealtimeStore.recordEvent('tracking', payload);
        bfDispatchRealtimeEvent('bf:order-tracking-updated', { tracking: payload?.tracking ?? payload });
    };

    const dispatchLocation = (payload) => {
        bfRealtimeStore.recordEvent('tracking', payload);
        bfDispatchRealtimeEvent('bf:courier-location-updated', { location: payload?.location ?? payload });
    };

    if (token) {
        echo.channel(`tracking.${token}`).listen('.order.tracking.updated', (event) => {
            dispatchTracking(event.tracking ?? event);
        });
    }

    if (orderId) {
        echo.private(`orders.${orderId}`)
            .listen('.order.tracking.updated', (event) => dispatchTracking(event.tracking ?? event))
            .listen('.courier.location.updated', (event) => dispatchLocation(event.location ?? event));
    }
}
