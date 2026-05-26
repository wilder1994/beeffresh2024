import { bfRealtimeStore } from '../stores/realtimeStore.js';
import { bfPatchDashboardLowStockRow, bfPatchInventoryRow } from '../utils/stockUi.js';

/** @type {(() => void)|null} */
let boundHandler = null;

/**
 * @param {object} payload
 */
export function bfHandleProductStockUpdated(payload) {
    if (!payload?.product_id) {
        return;
    }

    bfPatchInventoryRow(payload);
    bfPatchDashboardLowStockRow(payload);
}

export function bfInitStockRealtimeHandler() {
    if (boundHandler) {
        return;
    }

    boundHandler = (event) => bfHandleProductStockUpdated(event.detail ?? {});
    window.addEventListener('bf:product-stock-updated', boundHandler);
    bfRealtimeStore.registerListener('stock');
}

export function bfDestroyStockRealtimeHandler() {
    if (boundHandler) {
        window.removeEventListener('bf:product-stock-updated', boundHandler);
        boundHandler = null;
        bfRealtimeStore.unregisterListener('stock');
    }
}
