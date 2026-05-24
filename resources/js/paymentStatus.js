document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-payment-status]');
    if (!root) return;

    const url = root.dataset.statusUrl;
    if (!url) return;

    const poll = async () => {
        try {
            const res = await fetch(url, { headers: { Accept: 'text/html' }, credentials: 'same-origin' });
            if (!res.ok) return;
            const html = await res.text();
            if (html.includes('Pago aprobado') || html.includes('payments/success') || html.includes('Entregado')) {
                window.location.reload();
            }
        } catch {}
    };

    setInterval(poll, 5000);
});
