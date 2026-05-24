/** Polling y sincronización post-pago Wompi en /pago/procesar y /pago/pendiente. */
import { updateCartCount } from './cartBadge';

const TERMINAL_STATUSES = new Set(['approved', 'declined', 'failed', 'expired']);
const POLL_INTERVAL_MS = 2500;
const POLL_TIMEOUT_MS = 120000;
const REDIRECT_DELAY_MS = 1200;

function setPhase(root, phase) {
    root.dataset.phase = phase;
    root.querySelectorAll('[data-bf-payment-phase]').forEach((el) => {
        el.classList.toggle('hidden', el.dataset.bfPaymentPhase !== phase);
    });
}

function applyPayload(root, data) {
    if (typeof data.cart_count === 'number' && window.bfUpdateCartCount) {
        window.bfUpdateCartCount(data.cart_count);
    }

    const messageEl = root.querySelector('[data-bf-payment-message]');
    if (messageEl && data.message) {
        messageEl.textContent = data.message;
    }

    const referenceEl = root.querySelector('[data-bf-payment-reference]');
    if (referenceEl && data.reference) {
        referenceEl.textContent = data.reference;
    }

    if (data.status === 'approved') {
        setPhase(root, 'approved');
        const orderEl = root.querySelector('[data-bf-payment-order]');
        if (orderEl && data.order_id) {
            orderEl.textContent = `#${data.order_id}`;
        }
    } else if (data.status === 'declined' || data.status === 'failed' || data.status === 'expired') {
        setPhase(root, 'failed');
    } else {
        setPhase(root, 'syncing');
    }
}

function redirectTo(root, url) {
    if (!url) return;
    window.setTimeout(() => {
        window.location.href = url;
    }, REDIRECT_DELAY_MS);
}

async function pollOnce(root) {
    const pollUrl = root.dataset.pollUrl;
    if (!pollUrl) return null;

    const params = new URLSearchParams();
    const transactionId = root.dataset.transactionId;
    if (transactionId) {
        params.set('transaction_id', transactionId);
    }

    const url = params.toString() ? `${pollUrl}?${params.toString()}` : pollUrl;

    const response = await fetch(url, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
    });

    if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
    }

    return response.json();
}

function startPolling(root) {
    if (root.dataset.polling === '1') return;
    root.dataset.polling = '1';

    const startedAt = Date.now();

    const tick = async () => {
        try {
            const data = await pollOnce(root);
            if (!data) return;

            applyPayload(root, data);

            if (data.terminal && TERMINAL_STATUSES.has(data.status)) {
                root.dataset.polling = '0';
                if (data.redirect_url) {
                    redirectTo(root, data.redirect_url);
                }
                return;
            }

            if (Date.now() - startedAt >= POLL_TIMEOUT_MS) {
                root.dataset.polling = '0';
                setPhase(root, 'connection_error');
                return;
            }

            window.setTimeout(tick, POLL_INTERVAL_MS);
        } catch {
            if (Date.now() - startedAt >= POLL_TIMEOUT_MS) {
                root.dataset.polling = '0';
                setPhase(root, 'connection_error');
                return;
            }

            window.setTimeout(tick, POLL_INTERVAL_MS);
        }
    };

    tick();
}

function openWompiWidget(root, config) {
    if (typeof WidgetCheckout === 'undefined') {
        setPhase(root, 'connection_error');
        return;
    }

    setPhase(root, 'opening');

    const checkout = new WidgetCheckout(config);
    checkout.open((result) => {
        const transaction = result?.transaction;
        if (transaction?.id) {
            root.dataset.transactionId = transaction.id;
        }

        setPhase(root, 'syncing');
        startPolling(root);
    });
}

function bootstrapTransactionId(root) {
    const fromQuery = new URLSearchParams(window.location.search).get('id');
    if (fromQuery) {
        root.dataset.transactionId = fromQuery;
    }
}

function registerPaymentProcess() {
    document.addEventListener('DOMContentLoaded', () => {
        const root = document.querySelector('[data-bf-payment-process]');
        if (!root) return;

        bootstrapTransactionId(root);

        const widgetConfigRaw = root.dataset.widgetConfig;
        const autoOpen = root.dataset.autoOpenWidget === '1';
        const transactionFromUrl = Boolean(root.dataset.transactionId);

        if (widgetConfigRaw) {
            let config;
            try {
                config = JSON.parse(widgetConfigRaw);
            } catch {
                setPhase(root, 'connection_error');
                return;
            }

            const openBtn = root.querySelector('[data-bf-wompi-open]');
            const open = () => openWompiWidget(root, config);

            openBtn?.addEventListener('click', open);

            if (transactionFromUrl) {
                setPhase(root, 'syncing');
                startPolling(root);
            } else if (autoOpen) {
                window.setTimeout(open, 400);
            }
        } else {
            setPhase(root, 'syncing');
            startPolling(root);
        }
    });
}

registerPaymentProcess();

export { startPolling, applyPayload, setPhase, updateCartCount };
