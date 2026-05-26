import { bfRealtimeStore } from '../stores/realtimeStore.js';
import { bfRealtimeLog } from '../utils/logger.js';

/** @type {(() => void)|null} */
let boundHandler = null;
/** @type {((data: object) => void)|null} */
let paymentApplyFn = null;

/**
 * @param {(data: object) => void} applyFn
 */
export function bfInitPaymentRealtimeHandler(applyFn) {
    paymentApplyFn = applyFn;

    if (boundHandler) {
        return;
    }

    boundHandler = (event) => {
        const payment = event.detail?.payment;
        if (!payment?.uuid) {
            return;
        }

        bfRealtimeLog('info', payment.status === 'approved' ? 'Payment approved' : `Payment ${payment.status}`, payment);

        paymentApplyFn?.(bfNormalizePaymentRealtimePayload(payment));
    };

    window.addEventListener('bf:payment-status-updated', boundHandler);
    bfRealtimeStore.registerListener('payment');
}

/**
 * @param {object} payment
 * @returns {object}
 */
export function bfNormalizePaymentRealtimePayload(payment) {
    return {
        status: payment.status,
        status_label: payment.status_label,
        terminal: Boolean(payment.terminal),
        reference: payment.reference,
        order_id: payment.order_id,
        redirect_url: payment.redirect_url,
        message: payment.message,
        cart_count: payment.cart_count ?? (payment.status === 'approved' ? 0 : undefined),
    };
}

export function bfDestroyPaymentRealtimeHandler() {
    if (boundHandler) {
        window.removeEventListener('bf:payment-status-updated', boundHandler);
        boundHandler = null;
        bfRealtimeStore.unregisterListener('payment');
    }

    paymentApplyFn = null;
}
