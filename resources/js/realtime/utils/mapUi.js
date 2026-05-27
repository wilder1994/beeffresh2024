/**
 * Registro de markers del mapa operativo (patch granular, sin redraw).
 */

/** @type {import('google.maps').Map|null} */
let opsMapInstance = null;
/** @type {import('google.maps').Marker|null} */
let storeMarker = null;
/** @type {Map<string, import('google.maps').Marker>} */
const orderMarkers = new Map();
/** @type {Map<string, import('google.maps').Marker>} */
const courierMarkers = new Map();

/** @type {Map<string, number>} */
const courierMarkerAnimations = new Map();

/**
 * @param {import('google.maps').Marker} marker
 * @param {{ lat: number, lng: number }} target
 * @param {number} durationMs
 */
function bfAnimateMarkerTo(marker, target, durationMs = 900, animKey = 'marker') {
    const key = String(animKey);
    if (courierMarkerAnimations.has(key)) {
        window.cancelAnimationFrame(courierMarkerAnimations.get(key));
    }

    const start = marker.getPosition();
    if (!start) {
        marker.setPosition(target);

        return;
    }

    const startLat = start.lat();
    const startLng = start.lng();
    const deltaLat = target.lat - startLat;
    const deltaLng = target.lng - startLng;
    const t0 = performance.now();

    const step = (now) => {
        const t = Math.min(1, (now - t0) / durationMs);
        const eased = t * (2 - t);
        marker.setPosition({
            lat: startLat + deltaLat * eased,
            lng: startLng + deltaLng * eased,
        });

        if (t < 1) {
            const id = window.requestAnimationFrame(step);
            courierMarkerAnimations.set(key, id);
        } else {
            courierMarkerAnimations.delete(key);
        }
    };

    const id = window.requestAnimationFrame(step);
    courierMarkerAnimations.set(key, id);
}

const statusColors = {
    pending: '#eab308',
    preparing: '#3b82f6',
    ready_for_delivery: '#6366f1',
    picked_up: '#a855f7',
    in_transit: '#06b6d4',
    delivery_failed: '#ef4444',
};

/**
 * @param {import('google.maps').Map} map
 */
export function bfRegisterOpsMap(map) {
    opsMapInstance = map;
}

/**
 * @param {import('google.maps').Map} map
 * @param {{ latitude: number, longitude: number }} store
 */
export function bfEnsureStoreMarker(map, store) {
    const pos = { lat: store.latitude, lng: store.longitude };

    if (storeMarker) {
        storeMarker.setPosition(pos);

        return;
    }

    storeMarker = new google.maps.Marker({
        map,
        position: pos,
        title: 'Tienda',
        icon: {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 10,
            fillColor: '#7b301b',
            fillOpacity: 1,
            strokeWeight: 2,
            strokeColor: '#fff',
        },
    });
}

/**
 * @param {Record<string, unknown>} payload
 */
export function bfPatchOrderMarker(payload) {
    const map = opsMapInstance;
    const orderId = payload.order_id;

    if (!map || orderId == null || payload.lat == null || payload.lng == null) {
        return;
    }

    const key = `order-${orderId}`;
    const position = { lat: Number(payload.lat), lng: Number(payload.lng) };
    const status = String(payload.status ?? '');
    const existing = orderMarkers.get(key);

    if (existing) {
        existing.setPosition(position);
        existing.setIcon({
            path: google.maps.SymbolPath.BACKWARD_CLOSED_ARROW,
            scale: 5,
            fillColor: statusColors[status] ?? '#64748b',
            fillOpacity: 1,
            strokeWeight: 1,
            strokeColor: '#fff',
        });

        return;
    }

    orderMarkers.set(key, new google.maps.Marker({
        map,
        position,
        title: `Pedido #${orderId}`,
        icon: {
            path: google.maps.SymbolPath.BACKWARD_CLOSED_ARROW,
            scale: 5,
            fillColor: statusColors[status] ?? '#64748b',
            fillOpacity: 1,
            strokeWeight: 1,
            strokeColor: '#fff',
        },
    }));
}

/**
 * @param {Record<string, unknown>} payload
 */
export function bfPatchCourierMarker(payload) {
    const map = opsMapInstance;
    const courierId = payload.courier_id;

    if (!map || courierId == null || payload.lat == null || payload.lng == null) {
        return;
    }

    const key = `courier-${courierId}`;
    const position = { lat: Number(payload.lat), lng: Number(payload.lng) };
    const available = payload.available !== false;
    const name = payload.courier_name ?? `Domiciliario #${courierId}`;
    const existing = courierMarkers.get(key);

    if (existing) {
        bfAnimateMarkerTo(existing, position, 900, key);
        existing.setTitle(`${name} (${available ? 'Libre' : 'Ocupado'})`);
        existing.setIcon({
            path: google.maps.SymbolPath.CIRCLE,
            scale: 8,
            fillColor: available ? '#22c55e' : '#f59e0b',
            fillOpacity: 1,
            strokeWeight: 2,
            strokeColor: '#fff',
        });

        return;
    }

    courierMarkers.set(key, new google.maps.Marker({
        map,
        position,
        title: `${name} (${available ? 'Libre' : 'Ocupado'})`,
        icon: {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 8,
            fillColor: available ? '#22c55e' : '#f59e0b',
            fillOpacity: 1,
            strokeWeight: 2,
            strokeColor: '#fff',
        },
    }));
}

/**
 * Sincroniza markers desde payload completo del feed (polling / resync).
 *
 * @param {{ store?: { latitude?: number, longitude?: number }, orders?: Array<Record<string, unknown>>, couriers?: Array<Record<string, unknown>> }} payload
 */
export function bfSyncOpsMapFromFeed(payload) {
    const map = opsMapInstance;
    if (!map) {
        return;
    }

    if (payload.store?.latitude != null && payload.store?.longitude != null) {
        bfEnsureStoreMarker(map, {
            latitude: payload.store.latitude,
            longitude: payload.store.longitude,
        });
    }

    const seenOrders = new Set();
    (payload.orders ?? []).forEach((order) => {
        seenOrders.add(`order-${order.id}`);
        bfPatchOrderMarker({
            order_id: order.id,
            lat: order.latitude,
            lng: order.longitude,
            status: order.status,
        });
    });

    orderMarkers.forEach((marker, key) => {
        if (!seenOrders.has(key)) {
            marker.setMap(null);
            orderMarkers.delete(key);
        }
    });

    const seenCouriers = new Set();
    (payload.couriers ?? []).forEach((courier) => {
        if (courier.latitude == null || courier.longitude == null) {
            return;
        }

        const key = `courier-${courier.id}`;
        seenCouriers.add(key);
        bfPatchCourierMarker({
            courier_id: courier.id,
            lat: courier.latitude,
            lng: courier.longitude,
            available: courier.available,
            courier_name: courier.name,
        });
    });

    courierMarkers.forEach((marker, key) => {
        if (!seenCouriers.has(key)) {
            marker.setMap(null);
            courierMarkers.delete(key);
        }
    });
}
