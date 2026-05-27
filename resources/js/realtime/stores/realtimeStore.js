/**
 * Store central BF-Realtime (Fase 1.5).
 * @typedef {'notification'|'order'|'payment'|'metrics'|'stock'|'availability'|'tracking'|'map'|'connection'} RealtimeEventKind
 */

/** @type {boolean} */
let connected = false;
/** @type {boolean} */
let reconnecting = false;
/** @type {boolean} */
let echoEnabled = false;
/** @type {boolean} */
let queueHealthy = true;
/** @type {string} */
let serverMode = 'live';
/** @type {unknown} */
let lastError = null;
/** @type {string|null} */
let lastBusinessEventAt = null;
/** @type {string|null} */
let lastCourierLocationAt = null;
/** @type {string|null} */
let lastTrackingEventAt = null;
/** @type {boolean} */
let trackingConnected = false;
/** @type {Record<string, Record<string, unknown>>} */
const courierPresence = {};
/** @type {Record<string, string|null>} */
const lastEventAt = {
    notification: null,
    order: null,
    payment: null,
    metrics: null,
    stock: null,
    availability: null,
    tracking: null,
    map: null,
    connection: null,
};
/** @type {Record<string, number>} */
const listenerMetrics = {
    notification: 0,
    order: 0,
    payment: 0,
    metrics: 0,
    stock: 0,
    availability: 0,
    tracking: 0,
    map: 0,
};

function stamp(kind) {
    lastEventAt[kind] = new Date().toISOString();
}

function dispatch(name, detail = {}) {
    window.dispatchEvent(new CustomEvent(name, { detail, bubbles: true }));
}

function computeClientMode() {
    if (!echoEnabled || !connected) {
        return 'fallback';
    }

    if (!queueHealthy || serverMode === 'fallback') {
        return 'fallback';
    }

    if (serverMode === 'degraded') {
        return 'degraded';
    }

    return 'live';
}

export const bfRealtimeStore = {
    /** @param {boolean} enabled */
    setEchoEnabled(enabled) {
        echoEnabled = enabled;
        this.emitStatus();
    },

    isEchoEnabled() {
        return echoEnabled;
    },

    isConnected() {
        return connected;
    },

    isReconnecting() {
        return reconnecting;
    },

    isQueueHealthy() {
        return queueHealthy;
    },

    getMode() {
        return computeClientMode();
    },

    isFallbackMode() {
        return computeClientMode() === 'fallback';
    },

    isLiveMode() {
        return computeClientMode() === 'live';
    },

    getLastError() {
        return lastError;
    },

    getLastBusinessEventAt() {
        return lastBusinessEventAt;
    },

    getLastCourierLocationAt() {
        return lastCourierLocationAt;
    },

    getLastTrackingEventAt() {
        return lastTrackingEventAt;
    },

    isTrackingConnected() {
        return trackingConnected;
    },

    getCourierPresence() {
        return { ...courierPresence };
    },

    /** @param {RealtimeEventKind} kind */
    getLastEventAt(kind) {
        return lastEventAt[kind] ?? null;
    },

    getListenerMetrics() {
        return { ...listenerMetrics };
    },

    /**
     * @param {{ queue_healthy?: boolean, mode?: string, fallback_mode?: boolean }} payload
     */
    applyHealthPayload(payload) {
        if (typeof payload.queue_healthy === 'boolean') {
            queueHealthy = payload.queue_healthy;
        }

        if (typeof payload.mode === 'string') {
            serverMode = payload.mode;
        } else if (payload.fallback_mode === true) {
            serverMode = 'fallback';
        }

        this.emitStatus();
    },

    /** @param {RealtimeEventKind} kind */
    registerListener(kind) {
        if (listenerMetrics[kind] !== undefined) {
            listenerMetrics[kind] += 1;
        }
    },

    /** @param {RealtimeEventKind} kind */
    unregisterListener(kind) {
        if (listenerMetrics[kind] !== undefined && listenerMetrics[kind] > 0) {
            listenerMetrics[kind] -= 1;
        }
    },

    setConnected(value) {
        connected = value;
        if (value) {
            reconnecting = false;
            lastError = null;
        }
        stamp('connection');
        dispatch('bf:realtime-connected', this.getStatus());
        this.emitStatus();
    },

    setReconnecting(value) {
        reconnecting = value;
        stamp('connection');
        if (value) {
            dispatch('bf:realtime-reconnecting', this.getStatus());
        }
        this.emitStatus();
    },

    setDisconnected(error = null) {
        connected = false;
        lastError = error;
        stamp('connection');
        dispatch('bf:realtime-disconnected', this.getStatus());
        this.emitStatus();
    },

    /** @param {RealtimeEventKind} kind @param {unknown} [payload] */
    recordEvent(kind, payload) {
        stamp(kind);
        bfRealtimeLogEvent(kind, payload);
    },

    recordBusinessEvent(kind) {
        lastBusinessEventAt = new Date().toISOString();
        this.recordEvent(kind);
    },

    recordTrackingEvent() {
        lastTrackingEventAt = new Date().toISOString();
        trackingConnected = this.isLiveMode() || this.getMode() === 'degraded';
        this.recordEvent('tracking');
    },

    recordCourierLocationEvent() {
        lastCourierLocationAt = new Date().toISOString();
        this.recordEvent('map');
    },

    recordMapEvent() {
        this.recordEvent('map');
    },

    /** @param {Record<string, unknown>} presence */
    recordCourierPresence(presence) {
        const id = String(presence.courier_id ?? '');
        if (id) {
            courierPresence[id] = { ...presence };
        }

        this.recordEvent('tracking');
    },

    getStatus() {
        return {
            connected,
            reconnecting,
            echoEnabled,
            queueHealthy,
            mode: computeClientMode(),
            serverMode,
            fallbackMode: this.isFallbackMode(),
            lastError,
            lastBusinessEventAt,
            lastCourierLocationAt,
            lastTrackingEventAt,
            trackingConnected,
            courierPresence: { ...courierPresence },
            lastEventAt: { ...lastEventAt },
            listeners: { ...listenerMetrics },
        };
    },

    emitStatus() {
        dispatch('bf:realtime-status', this.getStatus());
    },
};

/** @param {RealtimeEventKind} kind @param {unknown} payload */
function bfRealtimeLogEvent(kind, payload) {
    if (!import.meta.env.DEV && import.meta.env.VITE_BF_REALTIME_DEBUG !== 'true') {
        return;
    }

    const prefix = '[BF-Realtime]';
    switch (kind) {
        case 'notification':
            console.info(prefix, 'Notification received', payload);
            break;
        case 'order':
            console.info(prefix, 'Order updated', payload?.order?.id ?? payload);
            break;
        case 'payment':
            console.info(prefix, 'Payment status', payload?.payment?.status ?? payload);
            break;
        default:
            console.info(prefix, kind, payload);
    }
}

// Compat Fase 0
export const bfConnectionStore = {
    isConnected: () => bfRealtimeStore.isConnected(),
    getLastError: () => bfRealtimeStore.getLastError(),
    setConnected: (value) => {
        if (value) {
            bfRealtimeStore.setConnected(true);
        } else {
            bfRealtimeStore.setDisconnected();
        }
    },
    setError: (error) => bfRealtimeStore.setDisconnected(error),
};
