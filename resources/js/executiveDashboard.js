document.addEventListener('DOMContentLoaded', () => {
    bootExecutiveDashboardHeatmap();
    bootExecutiveDashboardPolling();
});

function bootExecutiveDashboardHeatmap() {
    const root = document.querySelector('[data-ops-dashboard-map]');
    const canvas = document.getElementById('ops-dashboard-heatmap');
    if (!root || !canvas) {
        return;
    }

    const apiKey = root.dataset.apiKey;
    const points = JSON.parse(root.dataset.mapPoints || '[]');

    if (!apiKey || points.length === 0) {
        canvas.innerHTML = '<p class="p-4 text-sm text-stone-500">Sin coordenadas o falta API de mapas.</p>';

        return;
    }

    const scriptId = 'bf-google-maps-dashboard';
    const render = () => {
        const center = points[0] ?? { lat: 3.4516, lng: -76.532 };
        const map = new google.maps.Map(canvas, {
            center: { lat: center.lat, lng: center.lng },
            zoom: 12,
            disableDefaultUI: true,
            gestureHandling: 'cooperative',
        });

        points.forEach((point) => {
            new google.maps.Circle({
                map,
                center: { lat: point.lat, lng: point.lng },
                radius: 420,
                strokeOpacity: 0,
                fillColor: '#b91c1c',
                fillOpacity: 0.25,
            });
        });
    };

    if (window.google?.maps) {
        render();

        return;
    }

    if (document.getElementById(scriptId)) {
        return;
    }

    const script = document.createElement('script');
    script.id = scriptId;
    script.async = true;
    script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(apiKey)}`;
    script.onload = render;
    document.head.appendChild(script);
}

function bootExecutiveDashboardPolling() {
    const root = document.querySelector('[data-executive-dashboard]');
    const feedUrl = root?.dataset.feedUrl;
    if (!feedUrl) {
        return;
    }

    window.setInterval(async () => {
        try {
            await fetch(feedUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
        } catch {
            // WS / recarga manual
        }
    }, 60000);
}
