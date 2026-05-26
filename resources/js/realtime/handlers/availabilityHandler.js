import { bfRealtimeStore } from '../stores/realtimeStore.js';
import { bfPatchStoreProductAvailability } from '../utils/stockUi.js';

/** @type {(() => void)|null} */
let boundHandler = null;

/**
 * @param {object} payload
 */
export function bfHandleProductAvailabilityUpdated(payload) {
    if (!payload?.product_id) {
        return;
    }

    bfPatchStoreProductAvailability(payload);
}

export function bfInitAvailabilityRealtimeHandler() {
    if (boundHandler) {
        return;
    }

    boundHandler = (event) => bfHandleProductAvailabilityUpdated(event.detail ?? {});
    window.addEventListener('bf:product-availability-updated', boundHandler);
    bfRealtimeStore.registerListener('availability');
}

export function bfDestroyAvailabilityRealtimeHandler() {
    if (boundHandler) {
        window.removeEventListener('bf:product-availability-updated', boundHandler);
        boundHandler = null;
        bfRealtimeStore.unregisterListener('availability');
    }
}
