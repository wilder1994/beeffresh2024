/**
 * Modal centro de notificaciones (historial + preferencias).
 */
import {
    bfInitNotificationSoundToggles,
} from './realtime/utils/notificationSound.js';
import {
    bfNotificationCenterItemHtml,
    bfRemoveBellNotificationItem,
    bfRenderNotificationBadge,
} from './realtime/utils/notificationUi.js';

const MODAL_NAME = 'notification-center';

/**
 * @param {string} readUrl
 * @param {string} csrfToken
 * @returns {Promise<{ok: boolean, unread_count?: number}>}
 */
export async function bfMarkNotificationRead(readUrl, csrfToken) {
    if (!readUrl || !csrfToken) {
        return { ok: false };
    }

    try {
        const response = await fetch(readUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: new URLSearchParams({
                _method: 'PATCH',
                _token: csrfToken,
            }),
        });

        if (!response.ok) {
            return { ok: false };
        }

        return await response.json();
    } catch {
        return { ok: false };
    }
}

function bfOpenNotificationCenter() {
    document.querySelectorAll('[data-notification-bell] details[open]').forEach((details) => {
        details.removeAttribute('open');
    });

    window.dispatchEvent(
        new CustomEvent('open-modal', { detail: MODAL_NAME, bubbles: true }),
    );

    window.dispatchEvent(new CustomEvent('bf:notification-center-open', { bubbles: true }));
}

function bfBootNotificationCenter() {
    const root = document.querySelector('[data-notification-center]');
    if (!root) {
        return;
    }

    const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
    const historyUrl = root.dataset.historyUrl;
    const markAllUrl = root.dataset.markAllUrl;
    const listEl = root.querySelector('[data-notification-center-list]');
    const paginationWrap = root.querySelector('[data-notification-center-pagination]');
    const loadMoreBtn = root.querySelector('[data-notification-center-load-more]');
    const markAllBtn = root.querySelector('[data-notification-center-mark-all]');
    const prefsForm = root.querySelector('[data-notification-preferences]');

    let currentPage = 0;
    let lastPage = 1;
    let loading = false;

    const syncBellBadges = (count) => {
        document.querySelectorAll('[data-notification-bell]').forEach((bell) => {
            bfRenderNotificationBadge(bell.querySelector('[data-notification-count]'), count);
        });

        try {
            localStorage.setItem('bf:notifications:unread', String(Math.max(0, count)));
        } catch {
            // ignore
        }

        window.dispatchEvent(
            new CustomEvent('bf:notification-unread-sync', {
                detail: { unread_count: count },
                bubbles: true,
            }),
        );
    };

    const renderHistory = (items, append = false) => {
        if (!listEl) {
            return;
        }

        if (!append) {
            listEl.innerHTML = '';
        }

        if (!Array.isArray(items) || items.length === 0) {
            if (!append) {
                listEl.innerHTML = '<div class="bf-store-empty text-sm">No tienes notificaciones todavía.</div>';
            }

            return;
        }

        const html = items.map((item) => bfNotificationCenterItemHtml(item)).join('');

        if (append) {
            listEl.insertAdjacentHTML('beforeend', html);
        } else {
            listEl.innerHTML = html;
        }
    };

    const loadHistory = async (page = 1, append = false) => {
        if (!historyUrl || loading) {
            return;
        }

        if (page > lastPage && page !== 1) {
            return;
        }

        loading = true;
        if (loadMoreBtn) {
            loadMoreBtn.disabled = true;
        }

        try {
            const url = new URL(historyUrl, window.location.origin);
            url.searchParams.set('page', String(page));

            const response = await fetch(url, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            renderHistory(payload.notifications ?? [], append);

            if (typeof payload.unread_count === 'number') {
                syncBellBadges(payload.unread_count);
            }

            const meta = payload.meta ?? {};
            currentPage = meta.current_page ?? page;
            lastPage = meta.last_page ?? 1;

            if (paginationWrap && loadMoreBtn) {
                const hasMore = meta.has_more ?? currentPage < lastPage;
                paginationWrap.hidden = !hasMore;
                loadMoreBtn.disabled = false;
            }
        } catch {
            if (listEl && !append) {
                listEl.innerHTML = '<div class="bf-store-empty text-sm text-red-700">No se pudo cargar el historial.</div>';
            }
        } finally {
            loading = false;
            if (loadMoreBtn && !(paginationWrap?.hidden)) {
                loadMoreBtn.disabled = false;
            }
        }
    };

    const reloadCenter = () => loadHistory(1, false);

    document.querySelectorAll('[data-open-notification-center]').forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            bfOpenNotificationCenter();
        });
    });

    window.addEventListener('bf:notification-center-open', () => {
        bfInitNotificationSoundToggles(root);
        currentPage = 0;
        lastPage = 1;
        reloadCenter();
    });

    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', () => {
            loadHistory(currentPage + 1, true);
        });
    }

    if (markAllBtn && markAllUrl) {
        markAllBtn.addEventListener('click', async () => {
            const token = csrfToken();
            if (!token) {
                return;
            }

            markAllBtn.disabled = true;

            try {
                const response = await fetch(markAllUrl, {
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

                const payload = await response.json();
                syncBellBadges(payload.unread_count ?? 0);
                window.dispatchEvent(new CustomEvent('bf:notification-bell-refresh', { bubbles: true }));
                await reloadCenter();
            } finally {
                markAllBtn.disabled = false;
            }
        });
    }

    if (prefsForm) {
        prefsForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const token = csrfToken();
            const action = prefsForm.getAttribute('action');
            if (!action || !token) {
                return;
            }

            const submitButton = prefsForm.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
            }

            try {
                const body = new FormData(prefsForm);
                body.append('_method', 'PATCH');

                const response = await fetch(action, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body,
                });

                if (response.ok) {
                    window.dispatchEvent(
                        new CustomEvent('bf-toast', {
                            detail: { type: 'success', message: 'Preferencias guardadas.' },
                            bubbles: true,
                        }),
                    );
                }
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                }
            }
        });
    }

    root.addEventListener('click', async (event) => {
        const markBtn = event.target.closest('[data-notification-center-mark-read]');
        if (!markBtn) {
            return;
        }

        event.preventDefault();
        const readUrl = markBtn.getAttribute('data-notification-read-url');
        const itemId = markBtn.getAttribute('data-notification-item-id');
        const result = await bfMarkNotificationRead(readUrl, csrfToken());

        if (!result.ok) {
            return;
        }

        const article = markBtn.closest('[data-notification-item-id]');
        article?.classList.remove('bf-notification-item--unread');
        markBtn.remove();

        if (typeof result.unread_count === 'number') {
            syncBellBadges(result.unread_count);
            window.dispatchEvent(new CustomEvent('bf:notification-bell-refresh', { bubbles: true }));
        }

        if (itemId) {
            document.querySelectorAll('[data-notification-bell]').forEach((bell) => {
                bfRemoveBellNotificationItem(bell.querySelector('[data-notification-list]'), itemId);
            });
        }
    });

    const params = new URLSearchParams(window.location.search);
    if (params.get('open') === 'notifications') {
        bfOpenNotificationCenter();
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bfBootNotificationCenter);
} else {
    bfBootNotificationCenter();
}
