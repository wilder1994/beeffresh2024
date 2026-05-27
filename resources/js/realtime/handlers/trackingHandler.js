import { bfMetaContent } from '../utils/dom.js';
import { bfRealtimeStore } from '../stores/realtimeStore.js';
import { bfPatchAdminOrderTracking, bfPatchTrackingPage } from '../utils/trackingUi.js';

/** @type {string|null} */
let lastTrackingSignature = null;

/**
 * @param {Record<string, unknown>} tracking
 */
function signature(tracking) {
    return JSON.stringify({
        status: tracking.status,
        updated_at: tracking.updated_at,
        timeline_len: Array.isArray(tracking.timeline) ? tracking.timeline.length : 0,
        courier: tracking.courier?.id ?? null,
        loc: tracking.courier_location?.updated_at ?? null,
    });
}

/**
 * @param {Record<string, unknown>} tracking
 */
export function bfHandleOrderTrackingUpdated(tracking) {
    if (!tracking?.order_id) {
        return;
    }

    const sig = signature(tracking);
    if (sig === lastTrackingSignature) {
        return;
    }

    lastTrackingSignature = sig;
    bfRealtimeStore.recordTrackingEvent();

    if (document.querySelector('[data-order-tracking]')) {
        bfPatchTrackingPage(tracking);
    }

    if (document.getElementById('admin-order-timeline')) {
        bfPatchAdminOrderTracking(tracking);
    }
}

/**
 * @param {Record<string, unknown>} location
 */
export function bfHandleTrackingCourierLocation(location) {
    if (!location?.lat || !location?.lng) {
        return;
    }

    window.dispatchEvent(new CustomEvent('bf:tracking-map-patch', {
        detail: { location },
        bubbles: true,
    }));
}

export function bfInitTrackingRealtimeHandler() {
    const hasTracking = document.querySelector('[data-order-tracking]')
        || document.getElementById('admin-order-timeline')
        || bfMetaContent('bf-order-id');

    if (!hasTracking) {
        return;
    }

    window.addEventListener('bf:order-tracking-updated', (event) => {
        const tracking = event.detail?.tracking ?? event.detail;
        bfHandleOrderTrackingUpdated(tracking);
    });

    window.addEventListener('bf:courier-location-updated', (event) => {
        const location = event.detail?.location ?? event.detail;
        const orderId = bfMetaContent('bf-order-id');
        if (orderId && String(location.order_id) !== orderId) {
            return;
        }

        bfHandleTrackingCourierLocation(location);
    });

    bfRealtimeStore.registerListener('tracking');
}
