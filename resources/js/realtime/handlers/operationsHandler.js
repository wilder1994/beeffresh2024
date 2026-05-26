import { bfRealtimeStore } from '../stores/realtimeStore.js';
import { bfRealtimeLog } from '../utils/logger.js';
import {
    bfAnimateOrderCardInsert,
    bfAnimateOrderCardRemove,
    bfFindOrderCard,
    bfOrderMatchesTab,
    bfPatchOrderCard,
} from '../utils/orderOpsUi.js';

/** @type {(() => void)|null} */
let boundHandler = null;
/** @type {HTMLElement|null} */
let opsRoot = null;

/**
 * @param {HTMLElement} root
 */
export function bfInitOperationsGridHandler(root) {
    opsRoot = root;
    if (boundHandler) {
        return;
    }

    boundHandler = (event) => {
        const order = event.detail?.order;
        if (!order?.id) {
            return;
        }

        bfHandleOrderUpdated(order);
    };

    window.addEventListener('bf:order-updated', boundHandler);
    bfRealtimeStore.registerListener('order');
}

/**
 * @param {object} order
 */
export async function bfHandleOrderUpdated(order) {
    bfRealtimeLog('info', `Order updated #${order.id}`);

    const tab = opsRoot?.dataset.opsTab ?? 'all';
    const grid = document.getElementById('ops-order-grid');

    if (!grid) {
        return;
    }

    const existing = bfFindOrderCard(order.id);
    const matches = bfOrderMatchesTab(tab, order.status);

    if (existing) {
        if (!matches) {
            await bfAnimateOrderCardRemove(existing);
            return;
        }

        bfPatchOrderCard(existing, order);
        return;
    }

    if (!matches) {
        return;
    }

    await bfInsertOrderCard(order);
}

/**
 * @param {object} order
 */
async function bfInsertOrderCard(order) {
    const template = opsRoot?.dataset.cardFragmentUrl;
    if (!template) {
        return;
    }

    const url = template.replace('__ORDER__', String(order.id));

    try {
        const response = await fetch(url, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            return;
        }

        const payload = await response.json();
        const grid = document.getElementById('ops-order-grid');
        if (!grid || !payload.html) {
            return;
        }

        grid.insertAdjacentHTML('afterbegin', payload.html);
        const card = bfFindOrderCard(order.id);
        if (card) {
            bfPatchOrderCard(card, payload.order ?? order);
            bfAnimateOrderCardInsert(card);
        }
    } catch {
        // fallback polling cubrirá
    }
}

/**
 * @param {Array<object>} orders
 */
export function bfPatchOrdersFromFeed(orders) {
    if (!Array.isArray(orders)) {
        return;
    }

    orders.forEach((order) => {
        const card = bfFindOrderCard(order.id);
        if (card) {
            bfPatchOrderCard(card, order);
        }
    });
}

export function bfDestroyOperationsGridHandler() {
    if (boundHandler) {
        window.removeEventListener('bf:order-updated', boundHandler);
        boundHandler = null;
        bfRealtimeStore.unregisterListener('order');
    }

    opsRoot = null;
}
