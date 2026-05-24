/**
 * Mapa operativo: tienda, pedidos activos y domiciliarios.
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

const statusColors = {
    pending: '#eab308',
    preparing: '#3b82f6',
    ready_for_delivery: '#6366f1',
    picked_up: '#a855f7',
    in_transit: '#06b6d4',
    delivery_failed: '#ef4444',
};

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
    const markers = [];

    const clearMarkers = () => {
        markers.forEach((m) => m.setMap(null));
        markers.length = 0;
    };

    const render = async () => {
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
            }

            clearMarkers();

            if (payload.store?.latitude && payload.store?.longitude) {
                markers.push(new google.maps.Marker({
                    map,
                    position: { lat: payload.store.latitude, lng: payload.store.longitude },
                    title: 'Tienda',
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 10,
                        fillColor: '#7b301b',
                        fillOpacity: 1,
                        strokeWeight: 2,
                        strokeColor: '#fff',
                    },
                }));
            }

            (payload.orders ?? []).forEach((order) => {
                markers.push(new google.maps.Marker({
                    map,
                    position: { lat: order.latitude, lng: order.longitude },
                    title: `Pedido #${order.id}`,
                    icon: {
                        path: google.maps.SymbolPath.BACKWARD_CLOSED_ARROW,
                        scale: 5,
                        fillColor: statusColors[order.status] ?? '#64748b',
                        fillOpacity: 1,
                        strokeWeight: 1,
                        strokeColor: '#fff',
                    },
                }));
            });

            (payload.couriers ?? []).forEach((courier) => {
                if (courier.latitude == null || courier.longitude == null) {
                    return;
                }

                markers.push(new google.maps.Marker({
                    map,
                    position: { lat: courier.latitude, lng: courier.longitude },
                    title: `${courier.name} (${courier.available ? 'Libre' : 'Ocupado'})`,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 8,
                        fillColor: courier.available ? '#22c55e' : '#f59e0b',
                        fillOpacity: 1,
                        strokeWeight: 2,
                        strokeColor: '#fff',
                    },
                }));
            });
        } catch {
            // ignore
        }
    };

    render();
    window.setInterval(render, 15000);
});
