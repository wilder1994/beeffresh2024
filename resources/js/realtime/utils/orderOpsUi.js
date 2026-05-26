/** Mapeo tab operaciones → estados (Fase 1). */
export const BF_OPS_TAB_STATUSES = {
    all: null,
    pending: ['pending'],
    preparing: ['preparing'],
    ready: ['ready_for_delivery'],
    in_delivery: ['picked_up', 'in_transit'],
    delivered: ['delivered'],
    failed: ['delivery_failed'],
    returned: ['returned_to_store'],
    cancelled: ['cancelled'],
};

/**
 * @param {string} tab
 * @param {string} status
 */
export function bfOrderMatchesTab(tab, status) {
    const allowed = BF_OPS_TAB_STATUSES[tab] ?? null;

    if (allowed === null) {
        return true;
    }

    return allowed.includes(status);
}

/**
 * @param {HTMLElement|null} badge
 * @param {object} order
 */
export function bfPatchOrderBadge(badge, order) {
    if (!badge || !order) {
        return;
    }

    badge.textContent = order.status_label ?? order.status ?? '';
    badge.className = `inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold ${order.status_badge_class ?? 'bg-gray-100 text-gray-800'}`;
}

/**
 * @param {HTMLElement} card
 * @param {object} order
 */
export function bfPatchOrderCard(card, order) {
    card.dataset.opsOrderStatus = order.status;
    card.dataset.opsOrderUpdatedAt = order.updated_at ?? '';

    const badge = card.querySelector('[data-ops-order-badge]');
    bfPatchOrderBadge(badge, order);

    const courierRow = card.querySelector('[data-ops-order-courier-row]');
    const courierValue = card.querySelector('[data-ops-order-courier]');

    if (order.courier_name) {
        if (courierRow) {
            courierRow.classList.remove('hidden');
        }
        if (courierValue) {
            courierValue.textContent = order.courier_name;
        }
    } else if (courierRow) {
        courierRow.classList.add('hidden');
    }

    const timeline = card.querySelector('[data-ops-order-timeline]');
    if (timeline && order.updated_human) {
        timeline.textContent = `Actualizado ${order.updated_human}`;
    }

    card.classList.remove('bf-ops-order-card--updated');
    void card.offsetWidth;
    card.classList.add('bf-ops-order-card--updated');
}

/**
 * @param {HTMLElement} card
 */
export function bfAnimateOrderCardInsert(card) {
    card.classList.add('bf-ops-order-card--enter');
    window.setTimeout(() => card.classList.remove('bf-ops-order-card--enter'), 420);
}

/**
 * @param {HTMLElement} card
 * @returns {Promise<void>}
 */
export function bfAnimateOrderCardRemove(card) {
    return new Promise((resolve) => {
        card.classList.add('bf-ops-order-card--leave');
        window.setTimeout(() => {
            card.remove();
            resolve();
        }, 260);
    });
}

/**
 * @param {number} orderId
 * @returns {HTMLElement|null}
 */
export function bfFindOrderCard(orderId) {
    return document.querySelector(`[data-ops-order-id="${orderId}"]`);
}

/** Muestra u oculta el mensaje vacío según tarjetas en el grid. */
export function bfSyncOpsEmptyState() {
    const grid = document.getElementById('ops-order-grid');
    const empty = document.querySelector('[data-ops-empty-state]');

    if (!grid || !empty) {
        return;
    }

    const hasCards = grid.querySelector('[data-ops-order-id]') !== null;
    empty.classList.toggle('hidden', hasCards);
}
