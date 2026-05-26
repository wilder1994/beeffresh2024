import { bfRealtimeStore } from '../stores/realtimeStore.js';
import {
    bfAnimateNotificationBell,
    bfPrependNotificationItem,
    bfRenderNotificationBadge,
    bfShowNotificationToast,
} from '../utils/notificationUi.js';
import {
    bfNormalizeNotificationId,
    bfPlayNotificationSound,
} from '../utils/notificationSound.js';

/** @type {Set<number|string>} */
const seenNotificationIds = new Set();
/** @type {HTMLElement[]} */
let bellRoots = [];
/** @type {(() => void)|null} */
let boundHandler = null;

function bfRefreshBellRoots() {
    bellRoots = [...document.querySelectorAll('[data-notification-bell]')];
}

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

    const notificationId = bfNormalizeNotificationId(notification.id);

    if (seenNotificationIds.has(notificationId)) {
        return;
    }

    if (bellRoots.length === 0) {
        bfRefreshBellRoots();
    }

    const unreadCount =
        typeof payload.unread_count === 'number'
            ? payload.unread_count
            : null;

    if (unreadCount !== null) {
        try {
            localStorage.setItem('bf:notifications:unread', String(Math.max(0, unreadCount)));
        } catch {
            // ignore
        }

        window.dispatchEvent(
            new CustomEvent('bf:notification-unread-sync', {
                detail: { unread_count: unreadCount },
                bubbles: true,
            }),
        );
    }

    bellRoots.forEach((root) => {
        if (unreadCount !== null) {
            bfRenderNotificationBadge(root.querySelector('[data-notification-count]'), unreadCount);
        }

        bfPrependNotificationItem(
            root.querySelector('[data-notification-list]'),
            { ...notification, id: notificationId, read: false },
            root.dataset.indexUrl ?? '/notificaciones',
            seenNotificationIds,
        );

        bfAnimateNotificationBell(root);
    });

    if (!seenNotificationIds.has(notificationId)) {
        seenNotificationIds.add(notificationId);
    }

    void bfPlayNotificationSound();

    if (!payload?.suppressToast) {
        bfShowNotificationToast(notification.title ?? 'Nueva notificación', notification.body ?? '');
    }
}

/**
 * Notificaciones nuevas detectadas por polling (sin sonar en la carga inicial).
 *
 * @param {Array<object>} notifications
 * @param {number|null} unreadCount
 */
export function bfHandleNotificationsFromFeed(notifications, unreadCount = null) {
    if (!Array.isArray(notifications)) {
        return;
    }

    notifications.forEach((item) => {
        if (!item?.id) {
            return;
        }

        const id = bfNormalizeNotificationId(item.id);
        if (seenNotificationIds.has(id)) {
            return;
        }

        bfHandleNotificationCreated({
            notification: item,
            unread_count: unreadCount,
            suppressToast: true,
        });
    });
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
            seenNotificationIds.add(bfNormalizeNotificationId(item.id));
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
