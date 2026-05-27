import { bfDispatchRealtimeEvent, bfMetaContent } from '../utils/dom.js';
import { bfRealtimeStore } from '../stores/realtimeStore.js';

/**
 * Canal privado del domiciliario autenticado (panel courier).
 *
 * @param {import('laravel-echo').Echo} echo
 */
export function registerCourierChannels(echo) {
    const courierId = bfMetaContent('bf-courier-id');
    if (!courierId) {
        return;
    }

    echo.private(`couriers.${courierId}`).listen('.courier.location.updated', (event) => {
        bfRealtimeStore.recordEvent('tracking', event);
        bfDispatchRealtimeEvent('bf:courier-location-updated', { location: event.location ?? event });
    });
}
