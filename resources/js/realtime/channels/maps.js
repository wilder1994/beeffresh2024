import { bfDispatchRealtimeEvent, bfMetaContent } from '../utils/dom.js';
import { bfRealtimeStore } from '../stores/realtimeStore.js';

/**
 * Mapa operativo — Fase 2 (operationsMap.js mantiene polling 15s).
 *
 * @param {import('laravel-echo').Echo} echo
 */
export function registerMapsChannels(echo) {
    if (bfMetaContent('bf-staff-operations-map') !== '1') {
        return;
    }

    const dispatchMap = (payload) => {
        bfRealtimeStore.recordEvent('map', payload);
        bfDispatchRealtimeEvent('bf:ops-map-updated', { map: payload?.map ?? payload });
    };

    const dispatchLocation = (payload) => {
        bfRealtimeStore.recordEvent('map', payload);
        bfDispatchRealtimeEvent('bf:courier-location-updated', { location: payload?.location ?? payload });
    };

    const dispatchPresence = (payload) => {
        bfRealtimeStore.recordEvent('tracking', payload);
        bfDispatchRealtimeEvent('bf:courier-presence-updated', { presence: payload?.presence ?? payload });
    };

    echo.private('operations.map')
        .listen('.operations.map.updated', (event) => dispatchMap(event.map ?? event))
        .listen('.courier.location.updated', (event) => dispatchLocation(event.location ?? event))
        .listen('.courier.presence.updated', (event) => dispatchPresence(event.presence ?? event));

    echo.private('operations.couriers').listen('.courier.presence.updated', (event) => {
        dispatchPresence(event.presence ?? event);
    });
}
