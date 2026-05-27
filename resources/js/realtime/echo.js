import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { bfResyncOperationsAfterReconnect, bfResyncTrackingAndMapAfterReconnect } from './healthMonitor.js';
import { bfRealtimeStore } from './stores/realtimeStore.js';
import { bfRealtimeLog } from './utils/logger.js';

/** @type {Echo|null} */
let echoInstance = null;
/** @type {boolean} */
let hasConnectedOnce = false;

/**
 * @returns {Echo|null}
 */
export function createBfEcho() {
    const key = import.meta.env.VITE_REVERB_APP_KEY;

    if (!key) {
        bfRealtimeStore.setEchoEnabled(false);
        bfRealtimeLog('info', 'Reverb deshabilitado (falta VITE_REVERB_APP_KEY)');

        return null;
    }

    bfRealtimeStore.setEchoEnabled(true);
    window.Pusher = Pusher;

    const scheme = import.meta.env.VITE_REVERB_SCHEME ?? 'https';
    const port = import.meta.env.VITE_REVERB_PORT ?? (scheme === 'https' ? 443 : 80);

    const echo = new Echo({
        broadcaster: 'reverb',
        key,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: port,
        wssPort: port,
        forceTLS: scheme === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
    });

    const connection = echo.connector?.pusher?.connection;

    if (connection) {
        connection.bind('connected', () => {
            bfRealtimeStore.setConnected(true);
            bfRealtimeLog('info', 'Connected');

            if (hasConnectedOnce) {
                bfResyncOperationsAfterReconnect();
                bfResyncTrackingAndMapAfterReconnect();
            }

            hasConnectedOnce = true;
        });

        connection.bind('connecting', () => {
            if (!bfRealtimeStore.isConnected()) {
                bfRealtimeStore.setReconnecting(true);
            }
        });

        connection.bind('disconnected', () => {
            bfRealtimeStore.setDisconnected();
            bfRealtimeLog('warn', 'Disconnected (reconexión automática)');
        });

        connection.bind('error', (error) => {
            bfRealtimeStore.setDisconnected(error);
            bfRealtimeLog('error', 'WebSocket error', error);
        });

        connection.bind('unavailable', () => {
            bfRealtimeStore.setDisconnected();
            bfRealtimeLog('warn', 'Reverb unavailable');
        });
    }

    return echo;
}

/** @returns {Echo|null} */
export function getBfEcho() {
    return echoInstance;
}

/** @returns {Echo|null} */
export function initBfEcho() {
    if (echoInstance) {
        return echoInstance;
    }

    echoInstance = createBfEcho();

    if (echoInstance) {
        window.Echo = echoInstance;
    }

    return echoInstance;
}
