/**
 * @param {number|null|undefined} count
 * @returns {string}
 */
export function bfFormatBadgeCount(count) {
    if (!count || count <= 0) {
        return '';
    }

    return count > 99 ? '99+' : String(count);
}

/**
 * @param {HTMLElement|null} badge
 * @param {number} count
 */
export function bfRenderNotificationBadge(badge, count) {
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
    badge.textContent = bfFormatBadgeCount(count);
}

/**
 * @param {object} item
 * @param {string} indexUrl
 * @returns {string}
 */
export function bfNotificationItemHtml(item, indexUrl) {
    const unreadClass = item.read ? '' : 'bg-amber-50/60 bf-notification-list-item--new';
    const url = item.action_url || indexUrl;

    return `
        <li class="px-4 py-3 ${unreadClass}" data-notification-item-id="${item.id}">
            <a href="${url}" class="block">
                <p class="text-sm font-medium text-[var(--bf-ink)] line-clamp-1">${item.title ?? ''}</p>
                <p class="text-xs text-[var(--bf-muted)] mt-0.5 line-clamp-2">${item.body ?? ''}</p>
                <p class="text-[10px] text-stone-400 mt-1">${item.created_human ?? ''}</p>
            </a>
        </li>`;
}

/**
 * @param {HTMLElement|null} list
 * @param {Array<object>} items
 * @param {string} indexUrl
 */
export function bfRenderNotificationList(list, items, indexUrl) {
    if (!list) {
        return;
    }

    if (!Array.isArray(items) || items.length === 0) {
        list.innerHTML = '<li class="px-4 py-6 text-sm text-[var(--bf-muted)] text-center">Sin notificaciones</li>';
        return;
    }

    list.innerHTML = items.map((item) => bfNotificationItemHtml(item, indexUrl)).join('');
}

/**
 * @param {HTMLElement|null} list
 * @param {object} item
 * @param {string} indexUrl
 * @param {Set<number|string>} seenIds
 * @returns {boolean} inserted
 */
export function bfPrependNotificationItem(list, item, indexUrl, seenIds) {
    if (!list || !item?.id || seenIds.has(item.id)) {
        return false;
    }

    seenIds.add(item.id);

    const emptyRow = list.querySelector('li:not([data-notification-item-id])');
    emptyRow?.remove();

    list.insertAdjacentHTML('afterbegin', bfNotificationItemHtml(item, indexUrl));

    const rows = list.querySelectorAll('[data-notification-item-id]');
    if (rows.length > 8) {
        rows[rows.length - 1]?.remove();
    }

    return true;
}

/**
 * @param {HTMLElement} bellRoot
 */
export function bfAnimateNotificationBell(bellRoot) {
    const summary = bellRoot.querySelector('.bf-notification-bell');
    if (!summary) {
        return;
    }

    summary.classList.remove('bf-notification-bell--ring');
    // reflow
    void summary.offsetWidth;
    summary.classList.add('bf-notification-bell--ring');
    window.setTimeout(() => summary.classList.remove('bf-notification-bell--ring'), 700);
}

/**
 * @param {string} title
 * @param {string} [body]
 */
export function bfShowNotificationToast(title, body) {
    window.dispatchEvent(
        new CustomEvent('bf-toast', {
            detail: {
                type: 'success',
                message: body ? `${title} — ${body}` : title,
                duration: 4500,
            },
            bubbles: true,
        }),
    );
}
