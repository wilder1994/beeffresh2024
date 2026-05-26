/**
 * Validación de stock en carrito (STAB-5) — sin websocket.
 */
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-cart-page]');
    const validateUrl = root?.dataset.cartValidateUrl;
    if (!root || !validateUrl) {
        return;
    }

    const banner = root.querySelector('[data-cart-validate-banner]');
    const checkout = root.querySelector('[data-cart-checkout]');

    const showBanner = (message, tone = 'warn') => {
        if (!banner) {
            return;
        }

        banner.textContent = message;
        banner.classList.remove('hidden');
        banner.classList.toggle('border-red-200', tone === 'error');
        banner.classList.toggle('bg-red-50', tone === 'error');
        banner.classList.toggle('text-red-900', tone === 'error');
        banner.classList.toggle('border-amber-200', tone === 'warn');
        banner.classList.toggle('bg-amber-50', tone === 'warn');
        banner.classList.toggle('text-amber-900', tone === 'warn');
    };

    const hideBanner = () => {
        banner?.classList.add('hidden');
    };

    const setCheckoutEnabled = (enabled) => {
        if (!(checkout instanceof HTMLAnchorElement)) {
            return;
        }

        if (enabled) {
            checkout.classList.remove('pointer-events-none', 'opacity-50');
            checkout.setAttribute('aria-disabled', 'false');
            checkout.removeAttribute('tabindex');
            return;
        }

        checkout.classList.add('pointer-events-none', 'opacity-50');
        checkout.setAttribute('aria-disabled', 'true');
        checkout.setAttribute('tabindex', '-1');
    };

    const applyLineState = (line, canPurchase, label) => {
        if (!line) {
            return;
        }

        const msg = line.querySelector('[data-cart-line-invalid-msg]');
        line.classList.toggle('opacity-60', !canPurchase);
        line.classList.toggle('ring-1', !canPurchase);
        line.classList.toggle('ring-red-200', !canPurchase);

        if (msg) {
            if (!canPurchase) {
                msg.textContent = label || 'No disponible';
                msg.classList.remove('hidden');
            } else {
                msg.textContent = '';
                msg.classList.add('hidden');
            }
        }

        line.querySelectorAll('input, button, select').forEach((el) => {
            if (el.closest('[data-cart-line-remove]')) {
                return;
            }

            if (el instanceof HTMLInputElement || el instanceof HTMLButtonElement || el instanceof HTMLSelectElement) {
                el.disabled = !canPurchase;
            }
        });
    };

    const run = async () => {
        try {
            const response = await fetch(validateUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const payload = await response.json();
            const lines = payload.lines ?? [];

            lines.forEach((row) => {
                const selector = row.product_id
                    ? `[data-cart-line][data-product-id="${row.product_id}"]`
                    : `[data-cart-line][data-line-key="${row.line_key}"]`;
                const lineEl = root.querySelector(selector);
                applyLineState(lineEl, Boolean(row.can_purchase), row.availability_label);
            });

            if (payload.has_invalid) {
                showBanner(
                    'Hay productos sin stock suficiente. Actualiza el carrito o elimina las líneas marcadas.',
                    'error',
                );
                setCheckoutEnabled(false);
                return;
            }

            hideBanner();
            setCheckoutEnabled(Boolean(payload.checkout_allowed));
        } catch {
            showBanner(
                'No pudimos validar stock; se verificará nuevamente al pagar.',
                'warn',
            );
            setCheckoutEnabled(true);
        }
    };

    run();
});
