import { bfPatchCourierMarker } from '../utils/mapUi.js';
import { bfRealtimeStore } from '../stores/realtimeStore.js';

/**
 * @param {Record<string, unknown>} location
 */
export function bfHandleCourierLocationUpdated(location) {
    if (!location?.courier_id) {
        return;
    }

    bfRealtimeStore.recordCourierLocationEvent();
    bfPatchCourierMarker(location);
}

export function bfInitCourierLocationHandler() {
    if (!document.querySelector('[data-ops-map]')) {
        return;
    }

    window.addEventListener('bf:courier-location-updated', (event) => {
        const location = event.detail?.location ?? event.detail;
        bfHandleCourierLocationUpdated(location);
    });
}
