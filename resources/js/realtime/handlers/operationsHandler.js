import { bfRealtimeStore } from '../stores/realtimeStore.js';
import { bfRealtimeLog } from '../utils/logger.js';
import {
    bfAcquireOrderInsertLock,
    bfMarkOrderRecentlyInserted,
    bfReleaseOrderInsertLock,
    bfShouldSkipOrderInsert,
    bfWasOrderRecentlyInserted,
} from '../utils/opsInsertGuards.js';
import {
    bfAnimateOrderCardInsert,
    bfAnimateOrderCardRemove,
    bfFindOrderCard,
    bfOrderMatchesTab,
    bfPatchOrderCard,
    bfSyncOpsEmptyState,
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
 * @param {{ allowInsert?: boolean }} [options]
 */
export async function bfHandleOrderUpdated(order, options = {}) {
    const allowInsert = options.allowInsert !== false;

    bfRealtimeLog('info', `Order updated #${order.id}`);
    bfRealtimeStore.recordBusinessEvent('order');

    const tab = opsRoot?.dataset.opsTab ?? 'all';
    const grid = document.getElementById('ops-order-grid');

    if (!grid) {
        bfRealtimeLog('warn', `Order #${order.id}: grid no disponible`);

        return;
    }

    const existing = bfFindOrderCard(order.id);
    const matches = bfOrderMatchesTab(tab, order.status);

    if (existing) {
        if (!matches) {
            await bfAnimateOrderCardRemove(existing);
            bfSyncOpsEmptyState();

            return;
        }

        bfPatchOrderCard(existing, order);
        bfSyncOpsEmptyState();

        return;
    }

    if (!matches || !allowInsert) {
        if (!matches && allowInsert) {
            bfMaybeToastNewOrder(order);
        }

        return;
    }

    if (!bfCanInsertOnCurrentPage()) {
        bfMaybeToastNewOrder(order);
        return;
    }

    if (bfShouldSkipOrderInsert(order.id)) {
        return;
    }

    if (!bfAcquireOrderInsertLock(order.id)) {
        return;
    }

    try {
        await bfInsertOrderCard(order);
        bfSyncOpsEmptyState();
    } finally {
        bfReleaseOrderInsertLock(order.id);
    }
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

    const fetchFragment = async (attempt = 0) => {
        const response = await fetch(url, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        return response.json();
    };

    try {
        let payload;
        try {
            payload = await fetchFragment(0);
        } catch (firstError) {
            await new Promise((resolve) => {
                window.setTimeout(resolve, 2000);
            });

            payload = await fetchFragment(1);
        }

        const grid = document.getElementById('ops-order-grid');
        if (!grid || !payload.html) {
            return;
        }

        if (bfFindOrderCard(order.id)) {
            bfPatchOrderCard(bfFindOrderCard(order.id), payload.order ?? order);
            return;
        }

        grid.insertAdjacentHTML('afterbegin', payload.html);
        const card = bfFindOrderCard(order.id);
        if (card) {
            bfPatchOrderCard(card, payload.order ?? order);
            bfAnimateOrderCardInsert(card);
            bfMarkOrderRecentlyInserted(order.id);
            bfSyncOpsEmptyState();
        }
    } catch {
        // fallback polling cubrirá
    }
}

/**
 * @param {object} order
 */
function bfMaybeToastNewOrder(order) {
    const label = order.status_label ?? `Pedido #${order.id}`;
    window.dispatchEvent(
        new CustomEvent('bf-toast', {
            detail: {
                type: 'info',
                message: `Nuevo pedido #${order.id} · ${label}`,
                duration: 5000,
            },
            bubbles: true,
        }),
    );
}

function bfCanInsertOnCurrentPage() {
    const page = Number(opsRoot?.dataset.opsPage ?? '1');

    return page === 1;
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

/**
 * Aplica feed operacional: parchea existentes e inserta pedidos nuevos (WS o polling).
 * @param {Array<object>} orders
 */
export async function bfSyncOrdersFromFeed(orders) {
    if (!Array.isArray(orders) || !document.getElementById('ops-order-grid')) {
        return;
    }

    for (const order of orders) {
        const existing = bfFindOrderCard(order.id);

        if (existing) {
            await bfHandleOrderUpdated(order, { allowInsert: false });
            continue;
        }

        if (bfShouldSkipOrderInsert(order.id) || bfWasOrderRecentlyInserted(order.id)) {
            continue;
        }

        await bfHandleOrderUpdated(order, { allowInsert: true });
    }

    bfPatchOrdersFromFeed(orders);
    bfSyncOpsEmptyState();
}

/**
 * @param {HTMLElement|null} root
 */
export function bfGetOpsPollingRoot() {
    return opsRoot;
}

export function bfDestroyOperationsGridHandler() {
    if (boundHandler) {
        window.removeEventListener('bf:order-updated', boundHandler);
        boundHandler = null;
        bfRealtimeStore.unregisterListener('order');
    }

    opsRoot = null;
}
