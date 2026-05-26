/**
 * Campana de notificaciones — Fase 1 realtime + polling fallback 30s.
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

            roots.forEach((root) => {
                bfRenderNotificationBadge(root.querySelector('[data-notification-count]'), unreadCount);
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

    refreshAll();
    window.setInterval(refreshAll, 30000);
});
