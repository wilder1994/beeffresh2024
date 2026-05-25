/**
 * Campana de notificaciones (feed + contador unread).
 * Inicializa todas las campanas visibles (sidebar + header móvil).
 */
document.addEventListener('DOMContentLoaded', () => {
    const roots = document.querySelectorAll('[data-notification-bell]');
    if (roots.length === 0) {
        return;
    }

    const renderList = (list, items, indexUrl) => {
        if (!list) {
            return;
        }

        if (!Array.isArray(items) || items.length === 0) {
            list.innerHTML = '<li class="px-4 py-6 text-sm text-[var(--bf-muted)] text-center">Sin notificaciones</li>';
            return;
        }

        list.innerHTML = items
            .map(
                (item) => `
                <li class="px-4 py-3 ${item.read ? '' : 'bg-amber-50/60'}">
                    <a href="${item.action_url || indexUrl}" class="block">
                        <p class="text-sm font-medium text-[var(--bf-ink)] line-clamp-1">${item.title ?? ''}</p>
                        <p class="text-xs text-[var(--bf-muted)] mt-0.5 line-clamp-2">${item.body ?? ''}</p>
                        <p class="text-[10px] text-stone-400 mt-1">${item.created_human ?? ''}</p>
                    </a>
                </li>`,
            )
            .join('');
    };

    const renderBadge = (badge, count) => {
        if (!badge) {
            return;
        }

        if (!count || count <= 0) {
            badge.classList.remove('inline-flex');
            badge.classList.add('hidden');
            badge.textContent = '';
            return;
        }

        badge.classList.remove('hidden');
        badge.classList.add('inline-flex');
        badge.textContent = count > 99 ? '99+' : String(count);
    };

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

            roots.forEach((root) => {
                renderBadge(root.querySelector('[data-notification-count]'), unreadCount);
                renderList(
                    root.querySelector('[data-notification-list]'),
                    notifications,
                    root.dataset.indexUrl,
                );
            });
        } catch {
            // ignore
        }
    };

    const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

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
                if (details) {
                    details.removeAttribute('open');
                }
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
