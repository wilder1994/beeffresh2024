/** Actualiza los contadores del carrito en el nav de la tienda. */
export function updateCartCount(count) {
    const total = Math.max(0, Number(count) || 0);

    document.querySelectorAll('[data-bf-cart-count]').forEach((el) => {
        el.textContent = String(total);
        el.classList.toggle('hidden', total < 1);
    });

    document.querySelectorAll('[data-bf-cart-menu-count]').forEach((el) => {
        el.textContent = total > 0 ? ` (${total})` : '';
    });

    document.querySelectorAll('[data-bf-cart-link]').forEach((el) => {
        el.setAttribute('aria-label', total > 0 ? `Carrito, ${total} productos` : 'Carrito');
    });
}

export function registerCartBadge(windowRef) {
    windowRef.bfUpdateCartCount = updateCartCount;
}
