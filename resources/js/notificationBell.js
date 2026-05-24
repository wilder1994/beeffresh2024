/**
 * Campana de notificaciones (feed + contador unread).
 */
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-notification-bell]');
    if (!root) {
        return;
    }

    const feedUrl = root.dataset.feedUrl;
    const list = root.querySelector('[data-notification-list]');
    const badge = root.querySelector('[data-notification-count]');

    const renderList = (items) => {
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
                    <a href="${item.action_url || root.dataset.indexUrl}" class="block">
                        <p class="text-sm font-medium text-[var(--bf-ink)] line-clamp-1">${item.title ?? ''}</p>
                        <p class="text-xs text-[var(--bf-muted)] mt-0.5 line-clamp-2">${item.body ?? ''}</p>
                        <p class="text-[10px] text-stone-400 mt-1">${item.created_human ?? ''}</p>
                    </a>
                </li>`,
            )
            .join('');
    };

    const renderBadge = (count) => {
        if (!badge) {
            return;
        }

        if (!count || count <= 0) {
            badge.classList.add('hidden');
            badge.textContent = '';
            return;
        }

        badge.classList.remove('hidden');
        badge.textContent = count > 99 ? '99+' : String(count);
    };

    const refresh = async () => {
        try {
            const response = await fetch(feedUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            renderBadge(payload.unread_count ?? 0);
            renderList(payload.notifications ?? []);
        } catch {
            // ignore
        }
    };

    refresh();
    window.setInterval(refresh, 30000);
});
