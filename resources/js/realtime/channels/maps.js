import { bfDispatchRealtimeEvent, bfMetaContent } from '../utils/dom.js';
import { bfRealtimeLog } from '../utils/logger.js';

/**
 * Mapa operaciones — Fase 0: preparado; operationsMap.js mantiene polling.
 * @param {import('laravel-echo').Echo} echo
 */
export function registerMapsChannels(echo) {
    if (bfMetaContent('bf-staff-operations-map') !== '1') {
        return;
    }

    echo.private('operations.orders').listen('.order.updated', (payload) => {
        bfRealtimeLog('debug', 'map order.updated', payload);
        bfDispatchRealtimeEvent('bf:map-order-updated', payload);
    });
}
