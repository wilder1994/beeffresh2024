/**
 * Mapa en vivo del domiciliario (seguimiento cliente).
 */

const LIVE_STATUSES = new Set(['picked_up', 'in_transit']);
const CLOSED_STATUSES = new Set(['delivered']);

/** @type {import('google.maps').Map|null} */
let mapInstance = null;
/** @type {import('google.maps').Marker|null} */
let courierMarker = null;
/** @type {import('google.maps').Marker|null} */
let destinationMarker = null;
/** @type {import('google.maps').Polyline|null} */
let routePolyline = null;
/** @type {Array<{ lat: number, lng: number }>} */
const routePoints = [];
const MAX_ROUTE_POINTS = 120;

/**
 * Clave de Google Maps resuelta. Permite recuperar el mapa sin recargar cuando
 * el atributo `data-maps-api-key` llegó vacío en el render inicial (config en caché).
 * @type {string}
 */
let resolvedApiKey = '';

/**
 * @param {Record<string, unknown>} [tracking]
 * @returns {string}
 */
function resolveApiKey(tracking) {
    const root = document.querySelector('[data-order-tracking]');
    const fromDataset = root?.dataset.mapsApiKey ?? '';
    const fromFeed = typeof tracking?.maps_api_key === 'string' ? tracking.maps_api_key : '';
    const key = fromDataset || fromFeed || resolvedApiKey;

    if (key && key !== resolvedApiKey) {
        resolvedApiKey = key;
        if (root && !fromDataset) {
            root.dataset.mapsApiKey = key;
        }
    }

    return key;
}

/**
 * @param {string} apiKey
 */
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

/**
 * @param {'waiting'|'live'|'closed'} phase
 */
function resolvePhase(phase, status) {
    if (CLOSED_STATUSES.has(status)) {
        return 'closed';
    }

    if (LIVE_STATUSES.has(status)) {
        return 'live';
    }

    return phase === 'live' ? 'live' : 'waiting';
}

/**
 * @param {'waiting'|'live'|'closed'} phase
 */
function showMapPanel(phase) {
    const waiting = document.getElementById('tracking-map-waiting');
    const live = document.getElementById('tracking-map-live');
    const closed = document.getElementById('tracking-map-closed');
    const noKey = document.getElementById('tracking-map-no-key');

    waiting?.classList.toggle('hidden', phase !== 'waiting');
    live?.classList.toggle('hidden', phase !== 'live');
    closed?.classList.toggle('hidden', phase !== 'closed');
    noKey?.classList.add('hidden');

    if (phase === 'live' && mapInstance && window.google?.maps) {
        window.setTimeout(() => {
            google.maps.event.trigger(mapInstance, 'resize');
        }, 80);
    }
}

/**
 * @param {{ lat: number, lng: number }} destination
 * @param {string} apiKey
 */
async function ensureLiveMap(destination, apiKey) {
    const canvas = document.getElementById('tracking-map-canvas');
    if (!canvas || !apiKey) {
        document.getElementById('tracking-map-no-key')?.classList.remove('hidden');
        document.getElementById('tracking-map-live')?.classList.add('hidden');

        return;
    }

    await loadGoogleMaps(apiKey);

    const center = destination.lat != null && destination.lng != null
        ? { lat: destination.lat, lng: destination.lng }
        : { lat: 6.2442, lng: -75.5812 };

    if (!mapInstance) {
        mapInstance = new google.maps.Map(canvas, {
            center,
            zoom: 14,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: true,
        });

        if (destination.lat != null && destination.lng != null) {
            destinationMarker = new google.maps.Marker({
                map: mapInstance,
                position: center,
                title: 'Tu dirección de entrega',
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 9,
                    fillColor: '#22c55e',
                    fillOpacity: 1,
                    strokeWeight: 2,
                    strokeColor: '#fff',
                },
            });
        }

        routePolyline = new google.maps.Polyline({
            map: mapInstance,
            path: [],
            strokeColor: '#7b301b',
            strokeOpacity: 0.85,
            strokeWeight: 4,
        });
    } else if (destination.lat != null && destination.lng != null) {
        destinationMarker?.setPosition(center);
        mapInstance.setCenter(center);
    }
}

/**
 * @param {{ lat: number, lng: number }} position
 */
function updateCourierOnMap(position) {
    if (!mapInstance || position.lat == null || position.lng == null) {
        return;
    }

    const latLng = { lat: Number(position.lat), lng: Number(position.lng) };

    if (!courierMarker) {
        courierMarker = new google.maps.Marker({
            map: mapInstance,
            position: latLng,
            title: 'Domiciliario',
            icon: {
                url: '/images/tracking-courier-moto.svg',
                scaledSize: new google.maps.Size(44, 44),
                anchor: new google.maps.Point(22, 22),
            },
            zIndex: 3,
        });
    } else {
        courierMarker.setPosition(latLng);
    }

    const last = routePoints[routePoints.length - 1];
    if (!last || last.lat !== latLng.lat || last.lng !== latLng.lng) {
        routePoints.push(latLng);
        if (routePoints.length > MAX_ROUTE_POINTS) {
            routePoints.shift();
        }

        routePolyline?.setPath(routePoints);
    }

    const bounds = new google.maps.LatLngBounds();
    bounds.extend(latLng);
    if (destinationMarker?.getPosition()) {
        bounds.extend(destinationMarker.getPosition());
    }

    mapInstance.fitBounds(bounds, 48);
}

/**
 * @param {Record<string, unknown>} tracking
 */
export function bfUpdateCustomerTrackingMap(tracking) {
    const status = String(tracking.status ?? '');
    const phase = resolvePhase(String(tracking.map_phase ?? 'waiting'), status);
    const apiKey = resolveApiKey(tracking);
    const destination = tracking.destination ?? {};

    showMapPanel(phase);

    if (phase === 'closed') {
        return;
    }

    if (phase === 'waiting') {
        return;
    }

    const dest = {
        lat: destination.lat != null ? Number(destination.lat) : null,
        lng: destination.lng != null ? Number(destination.lng) : null,
    };

    ensureLiveMap(dest, apiKey).then(() => {
        const loc = tracking.courier_location;
        if (loc?.lat != null && loc?.lng != null) {
            updateCourierOnMap(loc);
        }
    }).catch(() => {
        document.getElementById('tracking-map-no-key')?.classList.remove('hidden');
        document.getElementById('tracking-map-live')?.classList.add('hidden');
    });
}

/**
 * @param {Record<string, unknown>|null} location
 */
export function bfPatchCustomerTrackingCourierLocation(location) {
    if (!location || location.lat == null || location.lng == null) {
        return;
    }

    const live = document.getElementById('tracking-map-live');
    if (live?.classList.contains('hidden')) {
        return;
    }

    updateCourierOnMap(location);
}

/**
 * @param {{ initialPhase: string, destination: { lat: number|null, lng: number|null }, courierLocation: object|null }} config
 */
export function bfInitCustomerTrackingMap(config) {
    const tracking = {
        status: document.querySelector('[data-order-tracking]')?.dataset.orderStatus ?? '',
        map_phase: config.initialPhase,
        destination: config.destination,
        courier_location: config.courierLocation,
    };

    bfUpdateCustomerTrackingMap(tracking);

    window.addEventListener('bf:tracking-map-patch', (event) => {
        bfPatchCustomerTrackingCourierLocation(event.detail?.location ?? null);
    });
}
