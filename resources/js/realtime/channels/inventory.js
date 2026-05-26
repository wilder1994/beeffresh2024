import { bfDispatchRealtimeEvent, bfMetaContent } from '../utils/dom.js';
import { bfRealtimeLog } from '../utils/logger.js';
import { bfRealtimeStore } from '../stores/realtimeStore.js';

/**
 * @param {import('laravel-echo').Echo} echo
 */
export function registerInventoryChannels(echo) {
    if (bfMetaContent('bf-staff-inventory') !== '1' && bfMetaContent('bf-staff-operations') !== '1') {
        return;
    }

    const dispatchStock = (payload) => {
        bfRealtimeStore.recordEvent('stock', payload);
        bfRealtimeLog('info', `Stock updated #${payload?.product_id ?? '?'}`);
        bfDispatchRealtimeEvent('bf:product-stock-updated', payload);
    };

    echo.private('operations.inventory').listen('.product.stock.updated', dispatchStock);

    if (bfMetaContent('bf-staff-operations') === '1') {
        echo.private('operations.dashboard').listen('.product.stock.updated', dispatchStock);
    }
}
