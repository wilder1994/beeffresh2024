/**
 * Campana de notificaciones — solo no leídas + realtime + polling 30s.
 */
import {
    bfHandleNotificationsFromFeed,
    bfInitNotificationRealtimeHandler,
    bfRegisterNotificationBellRoots,
    bfSeedNotificationIds,
} from './realtime/handlers/notificationsHandler.js';
import {
    bfRemoveBellNotificationItem,
    bfRenderBellNotificationList,
    bfRenderNotificationBadge,
} from './realtime/utils/notificationUi.js';
import {
    bfInitNotificationSoundToggles,
    bfInitNotificationSoundUnlock,
} from './realtime/utils/notificationSound.js';
import { bfMarkNotificationRead } from './notificationCenter.js';

const UNREAD_STORAGE_KEY = 'bf:notifications:unread';

/**
 * @param {number} count
 */
function bfPersistUnreadCount(count) {
    try {
        localStorage.setItem(UNREAD_STORAGE_KEY, String(Math.max(0, count)));
    } catch {
        // storage bloqueado
    }
}

/**
 * @param {HTMLElement[]} roots
 * @param {number} count
 */
function bfApplyUnreadToRoots(roots, count) {
    roots.forEach((root) => {
        bfRenderNotificationBadge(root.querySelector('[data-notification-count]'), count);
    });
}

function bfBootNotificationBell() {
    bfInitNotificationSoundUnlock();
    bfInitNotificationSoundToggles();

    const roots = [...document.querySelectorAll('[data-notification-bell]')];
    if (roots.length === 0) {
        return;
    }

    bfRegisterNotificationBellRoots(roots);
    bfInitNotificationRealtimeHandler();

    const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    let feedInitialized = false;

    const refreshAll = async () => {
        const feedUrl = roots[0]?.dataset.feedUrl;
        if (!feedUrl) {
            return;
        }

        try {
            const response = await fetch(feedUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            const unreadCount = payload.unread_count ?? 0;
            const notifications = payload.notifications ?? [];

            if (feedInitialized) {
                bfHandleNotificationsFromFeed(notifications, unreadCount);
            } else {
                bfSeedNotificationIds(notifications);
                feedInitialized = true;
            }

            bfPersistUnreadCount(unreadCount);
            bfApplyUnreadToRoots(roots, unreadCount);

            roots.forEach((root) => {
                bfRenderBellNotificationList(
                    root.querySelector('[data-notification-list]'),
                    notifications,
                );
            });
        } catch {
            // fallback silencioso
        }
    };

    document.addEventListener('click', async (event) => {
        const link = event.target.closest('[data-notification-bell-link]');
        if (!link) {
            return;
        }

        const bell = link.closest('[data-notification-bell]');
        if (!bell) {
            return;
        }

        event.preventDefault();

        const destination = link.getAttribute('href') || '/';
        const readUrl = link.getAttribute('data-notification-read-url');
        const itemId = link.closest('[data-notification-item-id]')?.getAttribute('data-notification-item-id');
        const token = csrfToken();

        if (readUrl && token) {
            const result = await bfMarkNotificationRead(readUrl, token);
            if (result.ok && typeof result.unread_count === 'number') {
                bfPersistUnreadCount(result.unread_count);
                bfApplyUnreadToRoots(roots, result.unread_count);
            }

            if (itemId) {
                roots.forEach((root) => {
                    bfRemoveBellNotificationItem(root.querySelector('[data-notification-list]'), itemId);
                });
            }
        }

        window.location.assign(destination);
    });

    window.addEventListener('storage', (event) => {
        if (event.key !== UNREAD_STORAGE_KEY || event.newValue === null) {
            return;
        }

        const count = Number.parseInt(event.newValue, 10);
        if (Number.isNaN(count)) {
            return;
        }

        bfApplyUnreadToRoots(roots, count);
    });

    window.addEventListener('bf:notification-unread-sync', (event) => {
        const count = event.detail?.unread_count;
        if (typeof count !== 'number') {
            return;
        }

        bfApplyUnreadToRoots(roots, count);
    });

    window.addEventListener('bf:notification-bell-refresh', () => {
        feedInitialized = true;
        refreshAll();
    });

    document.querySelectorAll('[data-notification-mark-all]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            if (!form.closest('[data-notification-bell]')) {
                return;
            }

            event.preventDefault();

            const action = form.getAttribute('action');
            const token = form.querySelector('input[name="_token"]')?.value || csrfToken();

            if (!action || !token) {
                return;
            }

            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
            }

            try {
                const response = await fetch(action, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: new URLSearchParams({
                        _method: 'PATCH',
                        _token: token,
                    }),
                });

                if (!response.ok) {
                    return;
                }

                bfPersistUnreadCount(0);
                await refreshAll();

                const details = form.closest('details');
                details?.removeAttribute('open');
            } catch {
                // ignore
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                }
            }
        });
    });

    const cached = localStorage.getItem(UNREAD_STORAGE_KEY);
    if (cached !== null) {
        const parsed = Number.parseInt(cached, 10);
        if (!Number.isNaN(parsed)) {
            bfApplyUnreadToRoots(roots, parsed);
        }
    }

    refreshAll();
    window.setInterval(refreshAll, 30000);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bfBootNotificationBell);
} else {
    bfBootNotificationBell();
}
