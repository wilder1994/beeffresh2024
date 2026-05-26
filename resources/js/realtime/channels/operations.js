import { bfDispatchRealtimeEvent, bfMetaContent } from '../utils/dom.js';
import { bfRealtimeLog } from '../utils/logger.js';
import { bfRealtimeStore } from '../stores/realtimeStore.js';

/**
 * @param {import('laravel-echo').Echo} echo
 */
export function registerOperationsChannels(echo) {
    if (bfMetaContent('bf-staff-operations') !== '1') {
        return;
    }

    const dispatchOrder = (payload) => {
        bfRealtimeStore.recordEvent('order', payload);
        bfRealtimeLog('info', `Order updated #${payload?.order?.id ?? '?'}`);
        bfDispatchRealtimeEvent('bf:order-updated', payload);
    };

    echo.private('operations.orders').listen('.order.updated', dispatchOrder);
    echo.private('operations.dashboard').listen('.order.updated', (payload) => {
        bfDispatchRealtimeEvent('bf:dashboard-order-updated', payload);
        dispatchOrder(payload);
    });
}
