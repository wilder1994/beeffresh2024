import { updateCartCount } from './cartBadge';

function cartAddUrl() {
    return document.querySelector('meta[name="bf-cart-add-url"]')?.content ?? '/carrito/agregar';
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

function dispatchCartToast(type, message) {
    window.dispatchEvent(
        new CustomEvent('bf-toast', { detail: { type, message } })
    );
}

function readPurchaseOptions(button) {
    const wrap = button.closest('[data-product-purchase]');
    const saleUnit = wrap?.dataset.saleUnit === 'lb' ? 'lb' : 'kg';
    const qtyInput = wrap?.querySelector('[data-cart-qty-input]');
    const cantidad = Math.max(1, Number(qtyInput?.value ?? wrap?.dataset.cartQty ?? 1));

    return { saleUnit, cantidad };
}

async function addProductToCart(productId, trigger) {
    if (trigger instanceof HTMLButtonElement) {
        trigger.disabled = true;
    }

    const { saleUnit, cantidad } = readPurchaseOptions(trigger);

    try {
        const response = await fetch(cartAddUrl(), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                product_id: Number(productId),
                sale_unit: saleUnit,
                cantidad,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.mensaje ?? 'No se pudo agregar al carrito.');
        }

        updateCartCount(Number(data.totalProductos ?? 0));
        dispatchCartToast('success', data.mensaje ?? 'Producto agregado al carrito');
    } catch (error) {
        const message = error instanceof Error ? error.message : 'No se pudo agregar al carrito.';
        dispatchCartToast('error', message);
    } finally {
        if (trigger instanceof HTMLButtonElement) {
            trigger.disabled = false;
        }
    }
}

export function registerStoreCart() {
    document.addEventListener('click', (event) => {
        const button = event.target.closest('.agregar-carrito');

        if (!button || !(button instanceof HTMLButtonElement)) {
            return;
        }

        event.preventDefault();
        addProductToCart(button.dataset.id, button);
    });
}
