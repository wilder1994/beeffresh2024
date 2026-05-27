import { bfMetaContent } from './utils/dom.js';
import { bfRealtimeStore } from './stores/realtimeStore.js';

const HEALTH_INTERVAL_MS = 60000;
/** @type {ReturnType<typeof setInterval>|null} */
let healthTimer = null;
/** @type {boolean} */
let resyncInFlight = false;

/**
 * @param {string} url
 */
async function fetchHealth(url) {
    const response = await fetch(url, {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
    });

    if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
    }

    return response.json();
}

export function bfStartRealtimeHealthMonitor() {
    const url = bfMetaContent('bf-realtime-health-url');
    if (!url || healthTimer !== null) {
        return;
    }

    const tick = async () => {
        try {
            const payload = await fetchHealth(url);
            bfRealtimeStore.applyHealthPayload(payload);
        } catch {
            bfRealtimeStore.applyHealthPayload({
                queue_healthy: false,
                mode: 'fallback',
                fallback_mode: true,
            });
        }
    };

    tick();
    healthTimer = window.setInterval(tick, HEALTH_INTERVAL_MS);
}

/**
 * Resync ops grid tras reconexión (una sola vez por ciclo).
 */
/**
 * Resync tracking y mapa visible tras reconexión (sin F5).
 */
export async function bfResyncTrackingAndMapAfterReconnect() {
    window.dispatchEvent(new CustomEvent('bf:realtime-resync', { bubbles: true }));
}

export async function bfResyncOperationsAfterReconnect() {
    if (resyncInFlight) {
        return;
    }

    const root = document.querySelector('[data-ops-polling]');
    const feedUrl = root?.dataset.feedUrl;
    if (!feedUrl) {
        return;
    }

    resyncInFlight = true;

    try {
        const response = await fetch(feedUrl, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            return;
        }

        const payload = await response.json();
        const { bfSyncOrdersFromFeed } = await import('./handlers/operationsHandler.js');
        await bfSyncOrdersFromFeed(payload.orders ?? []);
    } catch {
        // polling fallback
    } finally {
        window.setTimeout(() => {
            resyncInFlight = false;
        }, 5000);
    }
}
