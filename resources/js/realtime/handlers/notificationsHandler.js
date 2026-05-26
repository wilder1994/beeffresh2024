import { bfRealtimeStore } from '../stores/realtimeStore.js';
import {
    bfAnimateNotificationBell,
    bfPrependNotificationItem,
    bfRenderNotificationBadge,
    bfShowNotificationToast,
} from '../utils/notificationUi.js';

/** @type {Set<number|string>} */
const seenNotificationIds = new Set();
/** @type {HTMLElement[]} */
let bellRoots = [];
/** @type {(() => void)|null} */
let boundHandler = null;

/**
 * @param {HTMLElement[]} roots
 */
export function bfRegisterNotificationBellRoots(roots) {
    bellRoots = roots;
}

/**
 * @param {object} payload
 */
export function bfHandleNotificationCreated(payload) {
    const notification = payload?.notification;
    if (!notification?.id) {
        return;
    }

    if (seenNotificationIds.has(notification.id)) {
        return;
    }

    const unreadCount =
        typeof payload.unread_count === 'number'
            ? payload.unread_count
            : null;

    bellRoots.forEach((root) => {
        if (unreadCount !== null) {
            bfRenderNotificationBadge(root.querySelector('[data-notification-count]'), unreadCount);
        }

        bfPrependNotificationItem(
            root.querySelector('[data-notification-list]'),
            { ...notification, read: false },
            root.dataset.indexUrl ?? '/notificaciones',
            seenNotificationIds,
        );

        bfAnimateNotificationBell(root);
    });

    if (!seenNotificationIds.has(notification.id)) {
        return;
    }

    bfShowNotificationToast(notification.title ?? 'Nueva notificación', notification.body ?? '');
}

/**
 * @param {Array<object>} items
 */
export function bfSeedNotificationIds(items) {
    if (!Array.isArray(items)) {
        return;
    }

    items.forEach((item) => {
        if (item?.id) {
            seenNotificationIds.add(item.id);
        }
    });
}

export function bfInitNotificationRealtimeHandler() {
    if (boundHandler) {
        return;
    }

    boundHandler = (event) => bfHandleNotificationCreated(event.detail ?? {});
    window.addEventListener('bf:notification-created', boundHandler);
    bfRealtimeStore.registerListener('notification');
}

export function bfDestroyNotificationRealtimeHandler() {
    if (boundHandler) {
        window.removeEventListener('bf:notification-created', boundHandler);
        boundHandler = null;
        bfRealtimeStore.unregisterListener('notification');
    }
}
