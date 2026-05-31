import { bfRealtimeStore } from '../stores/realtimeStore.js';
import { bfAnimateOrderCardInsert } from '../utils/orderOpsUi.js';

/** @type {(() => void)|null} */
let boundHandler = null;
/** @type {HTMLElement|null} */
let poolRoot = null;

/**
 * @param {HTMLElement} root
 */
export function bfInitCourierPoolHandler(root) {
    poolRoot = root;
    if (boundHandler) {
        return;
    }

    boundHandler = (event) => {
        const order = event.detail?.order;
        if (!order?.id) {
            return;
        }

        bfHandleCourierPoolOrderUpdated(order);
    };

    window.addEventListener('bf:courier-pool-order-updated', boundHandler);
    bfRealtimeStore.registerListener('courier-pool');
}

/**
 * @param {object} order
 */
export function bfHandleCourierPoolOrderUpdated(order) {
    const inPool = order.in_pool === true
        || (order.in_pool !== false
            && order.status === 'ready_for_delivery'
            && (order.courier_id === null || order.courier_id === undefined));

    if (inPool) {
        if (bfFindPoolCard(order.id)) {
            return;
        }

        bfInsertPoolCard(order);
        bfMaybeToastPoolOrder(order);

        return;
    }

    bfRemovePoolCard(order.id);
}

/**
 * @param {Array<object>} orders
 * @param {boolean} [canAccept]
 */
export async function bfSyncCourierPoolFromFeed(orders, canAccept) {
    const list = document.getElementById('courier-pool-list');
    if (!list || !Array.isArray(orders)) {
        return;
    }

    const feedIds = new Set(orders.map((order) => order.id));

    list.querySelectorAll('[data-courier-pool-order-id]').forEach((card) => {
        const id = Number(card.getAttribute('data-courier-pool-order-id'));
        if (!feedIds.has(id)) {
            card.remove();
        }
    });

    for (const order of orders) {
        if (!bfFindPoolCard(order.id)) {
            await bfInsertPoolCard(order, canAccept);
        }
    }

    if (typeof canAccept === 'boolean') {
        bfSyncPoolAcceptState(canAccept);
    }

    bfSyncPoolEmptyState();
    bfUpdatePoolCount();
}

/**
 * @param {number} orderId
 */
function bfFindPoolCard(orderId) {
    return document.querySelector(`[data-courier-pool-order-id="${orderId}"]`);
}

/**
 * @param {object} order
 * @param {boolean} [canAcceptOverride]
 */
async function bfInsertPoolCard(order, canAcceptOverride) {
    const template = poolRoot?.dataset.cardFragmentUrl;
    const list = document.getElementById('courier-pool-list');
    if (!template || !list) {
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
        if (!payload.html || bfFindPoolCard(order.id)) {
            return;
        }

        list.insertAdjacentHTML('afterbegin', payload.html);

        const card = bfFindPoolCard(order.id);
        if (card) {
            bfAnimateOrderCardInsert(card);
        }

        if (typeof canAcceptOverride === 'boolean') {
            bfSyncPoolAcceptState(canAcceptOverride);
        } else if (typeof poolRoot?.dataset.canAccept !== 'undefined') {
            bfSyncPoolAcceptState(poolRoot.dataset.canAccept === '1');
        }

        bfSyncPoolEmptyState();
        bfUpdatePoolCount();
    } catch {
        // polling cubrirá
    }
}

/**
 * @param {number} orderId
 */
function bfRemovePoolCard(orderId) {
    const card = bfFindPoolCard(orderId);
    if (card) {
        card.remove();
    }

    bfSyncPoolEmptyState();
    bfUpdatePoolCount();
}

/**
 * @param {boolean} canAccept
 */
function bfSyncPoolAcceptState(canAccept) {
    if (poolRoot) {
        poolRoot.dataset.canAccept = canAccept ? '1' : '0';
    }

    document.querySelectorAll('[data-courier-pool-actions]').forEach((actions) => {
        const busyMsg = actions.querySelector('[data-courier-pool-busy-msg]');
        const form = actions.querySelector('form');

        if (canAccept) {
            busyMsg?.remove();
            if (!form && poolRoot?.dataset.acceptUrlTemplate) {
                const card = actions.closest('[data-courier-pool-order-id]');
                const orderId = card?.getAttribute('data-courier-pool-order-id');
                if (orderId) {
                    const acceptUrl = poolRoot.dataset.acceptUrlTemplate.replace('__ORDER__', orderId);
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
                    actions.insertAdjacentHTML(
                        'beforeend',
                        `<form method="POST" action="${acceptUrl}"><input type="hidden" name="_token" value="${csrf}"><button type="submit" class="bf-btn-primary text-sm">Aceptar pedido</button></form>`,
                    );
                }
            }

            return;
        }

        form?.remove();
        if (!busyMsg) {
            actions.insertAdjacentHTML(
                'beforeend',
                '<p class="text-xs text-amber-700 self-center" data-courier-pool-busy-msg>Finaliza tu entrega actual para aceptar otro.</p>',
            );
        }
    });
}

function bfSyncPoolEmptyState() {
    const list = document.getElementById('courier-pool-list');
    if (!list) {
        return;
    }

    const hasCards = list.querySelector('[data-courier-pool-order-id]') !== null;
    let empty = list.querySelector('.bf-courier-pool-empty');

    if (hasCards) {
        empty?.remove();

        return;
    }

    if (!empty) {
        list.insertAdjacentHTML(
            'afterbegin',
            '<div class="bf-courier-pool-empty text-sm">No hay pedidos listos sin asignar.</div>',
        );
    }
}

function bfUpdatePoolCount() {
    const counter = document.getElementById('courier-pool-count');
    if (!counter) {
        return;
    }

    const count = document.querySelectorAll('[data-courier-pool-order-id]').length;
    counter.textContent = `${count} en cola`;
}

/**
 * @param {object} order
 */
function bfMaybeToastPoolOrder(order) {
    window.dispatchEvent(
        new CustomEvent('bf-toast', {
            detail: {
                type: 'info',
                message: `Pedido #${order.id} listo para recoger`,
                duration: 5000,
            },
            bubbles: true,
        }),
    );
}
