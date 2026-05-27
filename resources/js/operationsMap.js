/**
 * Mapa operativo: patch granular + polling fallback 15s.
 */
import { bfRealtimeStore } from './realtime/stores/realtimeStore.js';
import {
    bfRegisterOpsMap,
    bfSyncOpsMapFromFeed,
} from './realtime/utils/mapUi.js';

function loadGoogleMaps(apiKey) {
    if (window.google?.maps) {
        return Promise.resolve();
    }

    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(apiKey)}&language=es&region=CO`;
        script.async = true;
        script.defer = true;
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('No se pudo cargar Google Maps'));
        document.head.appendChild(script);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-ops-map]');
    if (!root) {
        return;
    }

    const dataUrl = root.dataset.mapDataUrl;
    const apiKey = root.dataset.apiKey;
    const canvas = document.getElementById('ops-map-canvas');

    if (!dataUrl || !apiKey || !canvas) {
        return;
    }

    let map = null;
    let renderInFlight = false;

    const pollIntervalMs = () => (bfRealtimeStore.isLiveMode() ? 30000 : 15000);

    let pollTimer = null;

    const schedulePoll = () => {
        if (pollTimer !== null) {
            window.clearInterval(pollTimer);
        }

        pollTimer = window.setInterval(render, pollIntervalMs());
    };

    const render = async () => {
        if (renderInFlight) {
            return;
        }

        renderInFlight = true;

        try {
            const response = await fetch(dataUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            await loadGoogleMaps(apiKey);

            const center = payload.store?.latitude && payload.store?.longitude
                ? { lat: payload.store.latitude, lng: payload.store.longitude }
                : { lat: 6.2442, lng: -75.5812 };

            if (!map) {
                map = new google.maps.Map(canvas, {
                    center,
                    zoom: 12,
                    mapTypeControl: false,
                    streetViewControl: false,
                    fullscreenControl: true,
                });
                bfRegisterOpsMap(map);
                window.setTimeout(() => {
                    google.maps.event.trigger(map, 'resize');
                }, 100);
            }

            bfSyncOpsMapFromFeed(payload);

            if (map) {
                google.maps.event.trigger(map, 'resize');
            }
        } catch {
            // ignore
        } finally {
            renderInFlight = false;
        }
    };

    render();
    schedulePoll();

    window.addEventListener('bf:realtime-resync', () => {
        render();
    });

    window.addEventListener('bf:realtime-status', () => {
        schedulePoll();
    });

    window.addEventListener('resize', () => {
        if (map) {
            google.maps.event.trigger(map, 'resize');
        }
    });
});
