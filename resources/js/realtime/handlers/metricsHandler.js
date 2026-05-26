import { bfRealtimeStore } from '../stores/realtimeStore.js';
import { bfPatchOperationsMetrics } from '../utils/metricsUi.js';

/** @type {(() => void)|null} */
let boundHandler = null;
/** @type {ReturnType<typeof setTimeout>|null} */
let debounceTimer = null;

const DEBOUNCE_MS = 300;

/**
 * @param {object} payload
 */
export function bfHandleOperationsMetricsUpdated(payload) {
    const metrics = payload?.metrics ?? payload;
    bfPatchOperationsMetrics(metrics);
    bfRealtimeStore.recordBusinessEvent('metrics');
}

export function bfInitMetricsRealtimeHandler() {
    if (boundHandler) {
        return;
    }

    boundHandler = (event) => {
        if (debounceTimer !== null) {
            window.clearTimeout(debounceTimer);
        }

        debounceTimer = window.setTimeout(() => {
            debounceTimer = null;
            bfHandleOperationsMetricsUpdated(event.detail ?? {});
        }, DEBOUNCE_MS);
    };

    window.addEventListener('bf:ops-metrics-updated', boundHandler);
    bfRealtimeStore.registerListener('metrics');
}

export function bfDestroyMetricsRealtimeHandler() {
    if (boundHandler) {
        window.removeEventListener('bf:ops-metrics-updated', boundHandler);
        boundHandler = null;
        bfRealtimeStore.unregisterListener('metrics');
    }

    if (debounceTimer !== null) {
        window.clearTimeout(debounceTimer);
        debounceTimer = null;
    }
}
