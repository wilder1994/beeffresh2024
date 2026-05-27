import { bfPatchCourierPresence } from '../utils/courierUi.js';
import { bfRealtimeStore } from '../stores/realtimeStore.js';

/**
 * @param {Record<string, unknown>} presence
 */
export function bfHandleCourierPresenceUpdated(presence) {
    if (!presence?.courier_id) {
        return;
    }

    bfRealtimeStore.recordCourierPresence(presence);
    bfPatchCourierPresence(presence);
}

export function bfInitCourierPresenceHandler() {
    window.addEventListener('bf:courier-presence-updated', (event) => {
        const presence = event.detail?.presence ?? event.detail;
        bfHandleCourierPresenceUpdated(presence);
    });
}
