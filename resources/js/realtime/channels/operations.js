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

    const dispatchMetrics = (payload) => {
        bfRealtimeStore.recordEvent('metrics', payload);
        bfDispatchRealtimeEvent('bf:ops-metrics-updated', payload);
    };

    echo.private('operations.orders').listen('.order.updated', dispatchOrder);
    echo.private('operations.orders').listen('.operations.metrics.updated', dispatchMetrics);
    echo.private('operations.dashboard').listen('.operations.metrics.updated', dispatchMetrics);
}
