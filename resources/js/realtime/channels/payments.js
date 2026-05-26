import { bfDispatchRealtimeEvent, bfMetaContent } from '../utils/dom.js';
import { bfRealtimeLog } from '../utils/logger.js';
import { bfRealtimeStore } from '../stores/realtimeStore.js';

/**
 * @param {import('laravel-echo').Echo} echo
 */
export function registerPaymentChannels(echo) {
    const paymentUuid = bfMetaContent('bf-payment-uuid');

    if (!paymentUuid) {
        return;
    }

    echo.private(`payments.${paymentUuid}`).listen('.payment.status.updated', (payload) => {
        bfRealtimeStore.recordEvent('payment', payload);
        const status = payload?.payment?.status ?? 'unknown';
        bfRealtimeLog('info', status === 'approved' ? 'Payment approved' : `Payment ${status}`, payload);
        bfDispatchRealtimeEvent('bf:payment-status-updated', payload);
    });
}
