/** Guardas compartidas insert/poll ops (Fase 1.5-STAB). */

/** @type {Set<number>} */
const orderInsertLocks = new Set();

/** @type {Map<number, number>} */
const recentlyInserted = new Map();

const RECENTLY_INSERTED_TTL_MS = 5000;

/**
 * @param {number} orderId
 */
export function bfAcquireOrderInsertLock(orderId) {
    if (orderInsertLocks.has(orderId)) {
        return false;
    }

    orderInsertLocks.add(orderId);

    return true;
}

/**
 * @param {number} orderId
 */
export function bfReleaseOrderInsertLock(orderId) {
    orderInsertLocks.delete(orderId);
}

/**
 * @param {number} orderId
 */
export function bfMarkOrderRecentlyInserted(orderId) {
    recentlyInserted.set(orderId, Date.now() + RECENTLY_INSERTED_TTL_MS);
}

/**
 * @param {number} orderId
 */
export function bfWasOrderRecentlyInserted(orderId) {
    const expires = recentlyInserted.get(orderId);
    if (!expires) {
        return false;
    }

    if (Date.now() > expires) {
        recentlyInserted.delete(orderId);

        return false;
    }

    return true;
}

/**
 * @param {number} orderId
 */
export function bfShouldSkipOrderInsert(orderId) {
    return orderInsertLocks.has(orderId) || bfWasOrderRecentlyInserted(orderId);
}
