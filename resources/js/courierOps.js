/**
 * Geolocalización del domiciliario y firma de entrega.
 */
document.addEventListener('DOMContentLoaded', () => {
    bootCourierLocation();
    bootCourierSignature();
});

function bootCourierLocation() {
    const root = document.querySelector('[data-courier-location]');
    if (!root) {
        return;
    }

    const url = root.dataset.locationUrl;
    if (!url || !navigator.geolocation) {
        return;
    }

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    const send = (coords) => {
        fetch(url, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf ?? '',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                latitude: coords.latitude,
                longitude: coords.longitude,
                accuracy: coords.accuracy,
            }),
        }).catch(() => {});
    };

    const tick = () => {
        navigator.geolocation.getCurrentPosition(
            (pos) => send(pos.coords),
            () => {},
            { enableHighAccuracy: true, maximumAge: 30000, timeout: 12000 },
        );
    };

    tick();
    window.setInterval(tick, 45000);
}

function bootCourierSignature() {
    const root = document.querySelector('[data-courier-delivery]');
    if (!root) {
        return;
    }

    const canvas = root.querySelector('[data-signature-canvas]');
    const form = root.querySelector('[data-delivery-form]');
    const signatureInput = root.querySelector('[data-signature-input]');
    const latInput = root.querySelector('[data-lat-input]');
    const lngInput = root.querySelector('[data-lng-input]');
    const clearBtn = root.querySelector('[data-signature-clear]');

    if (!canvas || !form || !signatureInput) {
        return;
    }

    const ctx = canvas.getContext('2d');
    let drawing = false;
    let hasStroke = false;

    const resize = () => {
        const rect = canvas.getBoundingClientRect();
        canvas.width = Math.floor(rect.width * window.devicePixelRatio);
        canvas.height = Math.floor(rect.height * window.devicePixelRatio);
        ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.strokeStyle = '#1c1917';
    };

    resize();
    window.addEventListener('resize', resize);

    const point = (event) => {
        const rect = canvas.getBoundingClientRect();
        const touch = event.touches?.[0];

        return {
            x: (touch?.clientX ?? event.clientX) - rect.left,
            y: (touch?.clientY ?? event.clientY) - rect.top,
        };
    };

    const start = (event) => {
        drawing = true;
        const p = point(event);
        ctx.beginPath();
        ctx.moveTo(p.x, p.y);
        event.preventDefault();
    };

    const move = (event) => {
        if (!drawing) {
            return;
        }
        const p = point(event);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
        hasStroke = true;
        event.preventDefault();
    };

    const end = () => {
        drawing = false;
    };

    canvas.addEventListener('mousedown', start);
    canvas.addEventListener('mousemove', move);
    canvas.addEventListener('mouseup', end);
    canvas.addEventListener('mouseleave', end);
    canvas.addEventListener('touchstart', start, { passive: false });
    canvas.addEventListener('touchmove', move, { passive: false });
    canvas.addEventListener('touchend', end);

    clearBtn?.addEventListener('click', () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        hasStroke = false;
    });

    form.addEventListener('submit', (event) => {
        if (!hasStroke) {
            event.preventDefault();
            window.alert('El cliente debe firmar en pantalla.');
            return;
        }

        signatureInput.value = canvas.toDataURL('image/png');

        if (navigator.geolocation && latInput && lngInput) {
            event.preventDefault();
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    latInput.value = String(pos.coords.latitude);
                    lngInput.value = String(pos.coords.longitude);
                    form.submit();
                },
                () => form.submit(),
                { enableHighAccuracy: true, timeout: 8000 },
            );
        }
    });
}
