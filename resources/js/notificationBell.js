/**
 * Campana de notificaciones — Fase 1 realtime + polling fallback 30s + sync multi-tab.
 */
import {
    bfInitNotificationRealtimeHandler,
    bfRegisterNotificationBellRoots,
    bfSeedNotificationIds,
} from './realtime/handlers/notificationsHandler.js';
import {
    bfRenderNotificationBadge,
    bfRenderNotificationList,
} from './realtime/utils/notificationUi.js';

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

document.addEventListener('DOMContentLoaded', () => {
    const roots = [...document.querySelectorAll('[data-notification-bell]')];
    if (roots.length === 0) {
        return;
    }

    bfRegisterNotificationBellRoots(roots);
    bfInitNotificationRealtimeHandler();

    const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

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

            bfSeedNotificationIds(notifications);
            bfPersistUnreadCount(unreadCount);
            bfApplyUnreadToRoots(roots, unreadCount);

            roots.forEach((root) => {
                bfRenderNotificationList(
                    root.querySelector('[data-notification-list]'),
                    notifications,
                    root.dataset.indexUrl ?? '/notificaciones',
                );
            });
        } catch {
            // fallback silencioso
        }
    };

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
});
