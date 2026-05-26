import { bfDispatchRealtimeEvent, bfMetaContent } from '../utils/dom.js';
import { bfRealtimeLog } from '../utils/logger.js';

/**
 * Seguimiento cliente autenticado — guests siguen con orderTracking.js (poll).
 * @param {import('laravel-echo').Echo} echo
 */
export function registerTrackingChannels(echo) {
    const orderId = bfMetaContent('bf-order-id');

    if (!orderId) {
        return;
    }

    echo.private(`orders.${orderId}`).listen('.order.updated', (payload) => {
        bfRealtimeLog('debug', `orders.${orderId} order.updated`, payload);
        bfDispatchRealtimeEvent('bf:tracking-order-updated', payload);
    });
}
