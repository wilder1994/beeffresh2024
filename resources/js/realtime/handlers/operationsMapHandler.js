import { bfPatchCourierMarker, bfPatchOrderMarker } from '../utils/mapUi.js';
import { bfRealtimeStore } from '../stores/realtimeStore.js';

/**
 * @param {Record<string, unknown>} mapPayload
 */
export function bfHandleOperationsMapUpdated(mapPayload) {
    if (!mapPayload) {
        return;
    }

    bfRealtimeStore.recordMapEvent();

    if (mapPayload.order_id != null) {
        bfPatchOrderMarker(mapPayload);
    } else if (mapPayload.courier_id != null) {
        bfPatchCourierMarker(mapPayload);
    }
}

export function bfInitOperationsMapHandler() {
    if (!document.querySelector('[data-ops-map]')) {
        return;
    }

    window.addEventListener('bf:ops-map-updated', (event) => {
        const mapPayload = event.detail?.map ?? event.detail;
        bfHandleOperationsMapUpdated(mapPayload);
    });
}
