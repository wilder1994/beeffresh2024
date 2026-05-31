/**
 * Catálogo › Productos — respaldo por polling del stock cuando el WebSocket
 * no está disponible (p. ej. navegando por un túnel HTTPS sin wss).
 * Reutiliza el mismo parche DOM que usa el handler realtime.
 */
import { bfPatchCatalogStockRow } from './realtime/utils/stockUi.js';

const INTERVAL_MS = 15000;

document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-catalog-stock-feed]');
    const feedUrl = root?.dataset.catalogStockFeed;

    if (!feedUrl) {
        return;
    }

    let lastSignature = '';

    const poll = async () => {
        try {
            const response = await fetch(feedUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            const products = payload.products ?? [];
            const signature = products
                .map((p) => `${p.product_id}:${p.stock}:${p.is_out_of_stock ? 1 : 0}:${p.is_low_stock ? 1 : 0}`)
                .join('|');

            if (signature === lastSignature) {
                return;
            }

            products.forEach((product) => bfPatchCatalogStockRow(product));
            lastSignature = signature;
        } catch {
            // fallback silencioso
        }
    };

    poll();
    window.setInterval(poll, INTERVAL_MS);
});
