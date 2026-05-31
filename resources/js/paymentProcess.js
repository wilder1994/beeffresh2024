/** Polling y sincronización post-pago Wompi — Fase 1 realtime + fallback. */
import { updateCartCount } from './cartBadge';
import { bfInitPaymentRealtimeHandler } from './realtime/handlers/paymentHandler.js';

const TERMINAL_STATUSES = new Set(['approved', 'declined', 'failed', 'expired']);
const POLL_INTERVAL_MS = 2500;
const POLL_TIMEOUT_MS = 120000;
const REDIRECT_DELAY_MS = 1200;
const WOMPI_READY_TIMEOUT_MS = 15000;
const AUTO_OPEN_DELAY_MS = 300;
const OPEN_HINT_DELAY_MS = 5000;

function setPhase(root, phase) {
    root.dataset.phase = phase;
    root.querySelectorAll('[data-bf-payment-phase]').forEach((el) => {
        el.classList.toggle('hidden', el.dataset.bfPaymentPhase !== phase);
    });
}

function setOpeningHint(root, message) {
    const hint = root.querySelector('[data-bf-payment-opening-hint]');
    if (hint) {
        hint.textContent = message;
        hint.classList.remove('hidden');
    }
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

function handleTerminal(root, data) {
    if (!data.terminal || !TERMINAL_STATUSES.has(data.status)) {
        return false;
    }

    root.dataset.polling = '0';
    if (data.redirect_url) {
        redirectTo(root, data.redirect_url);
    }

    return true;
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

            if (handleTerminal(root, data)) {
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

function bindRealtimePayment(root) {
    bfInitPaymentRealtimeHandler((data) => {
        applyPayload(root, data);

        if (data.status === 'approved' && typeof data.cart_count === 'number') {
            updateCartCount(data.cart_count);
        }

        handleTerminal(root, data);
    });
}

/**
 * Espera a que widget.js de Wompi defina WidgetCheckout (carga async/defer).
 * @returns {Promise<void>}
 */
function waitForWompiWidget() {
    if (typeof WidgetCheckout !== 'undefined') {
        return Promise.resolve();
    }

    return new Promise((resolve, reject) => {
        const script = document.getElementById('bf-wompi-widget-script');

        const finish = () => {
            if (typeof WidgetCheckout !== 'undefined') {
                resolve();
                return true;
            }
            return false;
        };

        if (finish()) {
            return;
        }

        const onLoad = () => {
            if (finish()) {
                cleanup();
            }
        };

        const onError = () => {
            cleanup();
            reject(new Error('No se pudo cargar el script de Wompi.'));
        };

        const timeout = window.setTimeout(() => {
            cleanup();
            reject(new Error('Tiempo de espera agotado al cargar la pasarela Wompi.'));
        }, WOMPI_READY_TIMEOUT_MS);

        const poll = window.setInterval(() => {
            if (finish()) {
                cleanup();
            }
        }, 150);

        const cleanup = () => {
            window.clearTimeout(timeout);
            window.clearInterval(poll);
            script?.removeEventListener('load', onLoad);
            script?.removeEventListener('error', onError);
        };

        script?.addEventListener('load', onLoad);
        script?.addEventListener('error', onError);
    });
}

function openWompiWidget(root, config) {
    if (typeof WidgetCheckout === 'undefined') {
        setPhase(root, 'connection_error');
        return;
    }

    setPhase(root, 'opening');
    root.dataset.widgetOpened = '0';

    try {
        const checkout = new WidgetCheckout(config);
        checkout.open((result) => {
            root.dataset.widgetOpened = '1';
            const transaction = result?.transaction;
            if (transaction?.id) {
                root.dataset.transactionId = transaction.id;
            }

            setPhase(root, 'syncing');
            startPolling(root);
        });
        root.dataset.widgetOpened = '1';
    } catch (error) {
        console.error('[bf-payment] WidgetCheckout error', error);
        setPhase(root, 'connection_error');
        setOpeningHint(
            root,
            'No se pudo abrir la pasarela. Revisa tu conexión o pulsa «Pagar ahora» de nuevo.',
        );
    }
}

function scheduleOpenHint(root) {
    window.setTimeout(() => {
        if (root.dataset.phase !== 'opening') {
            return;
        }

        setOpeningHint(
            root,
            'Si la ventana de pago no apareció, pulsa «Pagar ahora» (algunos navegadores bloquean la apertura automática).',
        );
    }, OPEN_HINT_DELAY_MS);
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
        bindRealtimePayment(root);

        const widgetConfigRaw = root.dataset.widgetConfig;
        const autoOpen = root.dataset.autoOpenWidget === '1';
        const transactionFromUrl = Boolean(root.dataset.transactionId);

        if (!widgetConfigRaw) {
            setPhase(root, 'syncing');
            startPolling(root);
            return;
        }

        let config;
        try {
            config = JSON.parse(widgetConfigRaw);
        } catch {
            setPhase(root, 'connection_error');
            return;
        }

        const openBtn = root.querySelector('[data-bf-wompi-open]');
        let opening = false;

        const open = async () => {
            if (opening) {
                return;
            }
            opening = true;

            try {
                await waitForWompiWidget();
                openWompiWidget(root, config);
                scheduleOpenHint(root);
            } catch (error) {
                console.error('[bf-payment] Wompi load failed', error);
                setPhase(root, 'connection_error');
                setOpeningHint(
                    root,
                    error instanceof Error
                        ? error.message
                        : 'No se pudo cargar la pasarela. Comprueba tu conexión e intenta de nuevo.',
                );
            } finally {
                opening = false;
            }
        };

        openBtn?.addEventListener('click', () => {
            void open();
        });

        if (transactionFromUrl) {
            setPhase(root, 'syncing');
            startPolling(root);
            return;
        }

        if (autoOpen) {
            window.setTimeout(() => {
                void open();
            }, AUTO_OPEN_DELAY_MS);
        }
    });
}

registerPaymentProcess();

export { startPolling, applyPayload, setPhase, updateCartCount };
