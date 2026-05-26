import { bfDispatchRealtimeEvent } from '../utils/dom.js';
import { bfRealtimeLog } from '../utils/logger.js';
import { bfRealtimeStore } from '../stores/realtimeStore.js';

/**
 * Canal público store.catalog — disponibilidad sin stock numérico.
 * @param {import('laravel-echo').Echo} echo
 */
export function registerStoreCatalogChannel(echo) {
    echo.channel('store.catalog').listen('.product.availability.updated', (payload) => {
        bfRealtimeStore.recordEvent('availability', payload);
        bfRealtimeLog('info', `Availability #${payload?.product_id ?? '?'}`);
        bfDispatchRealtimeEvent('bf:product-availability-updated', payload);
    });
}
