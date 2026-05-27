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
 * Convierte action_url absoluta (p. ej. ngrok viejo) a ruta relativa del host actual.
 *
 * @param {string|null|undefined} url
 * @returns {string|null}
 */
export function bfNotificationActionHref(url) {
    if (!url) {
        return null;
    }

    if (url.startsWith('/')) {
        return url;
    }

    try {
        const parsed = new URL(url, window.location.origin);

        return parsed.pathname + parsed.search + parsed.hash;
    } catch {
        return url;
    }
}

/**
 * @param {object} item
 * @returns {string}
 */
export function bfNotificationBellItemHtml(item) {
    const unreadClass = 'bg-amber-50/60 bf-notification-list-item--new';
    const url = bfNotificationActionHref(item.action_url) || '#';
    const readUrl = item.read_url ?? '';

    return `
        <li class="px-4 py-3 ${unreadClass}" data-notification-item-id="${item.id}">
            <a
                href="${url}"
                class="block"
                data-notification-bell-link
                data-notification-read-url="${readUrl}"
            >
                <p class="text-sm font-medium text-[var(--bf-ink)] line-clamp-1">${item.title ?? ''}</p>
                <p class="text-xs text-[var(--bf-muted)] mt-0.5 line-clamp-2">${item.body ?? ''}</p>
                <p class="text-[10px] text-stone-400 mt-1">${item.created_human ?? ''}</p>
            </a>
        </li>`;
}

/**
 * @param {object} item
 * @returns {string}
 */
export function bfNotificationCenterItemHtml(item) {
    const unreadClass = item.read ? '' : 'bf-notification-item--unread';
    const actionHref = bfNotificationActionHref(item.action_url);
    const openLink = actionHref
        ? `<a href="${actionHref}" class="text-xs font-medium text-[var(--bf-brand)] hover:underline">Abrir</a>`
        : '';
    const markRead = item.read
        ? ''
        : `<button type="button" class="text-xs text-stone-500 hover:text-stone-800" data-notification-center-mark-read data-notification-read-url="${item.read_url ?? ''}" data-notification-item-id="${item.id}">Marcar leída</button>`;

    return `
        <article class="bf-notification-item ${unreadClass}" data-notification-item-id="${item.id}">
            <div class="bf-notification-item__body">
                <p class="bf-notification-item__title">${item.title ?? ''}</p>
                <p class="bf-notification-item__text">${item.body ?? ''}</p>
                <p class="bf-notification-item__meta">${item.created_human ?? ''}</p>
            </div>
            <div class="bf-notification-item__actions">
                ${openLink}
                ${markRead}
            </div>
        </article>`;
}

/**
 * Lista de campana: solo no leídas.
 *
 * @param {HTMLElement|null} list
 * @param {Array<object>} items
 */
export function bfRenderBellNotificationList(list, items) {
    if (!list) {
        return;
    }

    const unread = Array.isArray(items) ? items.filter((item) => !item.read) : [];

    if (unread.length === 0) {
        list.innerHTML =
            '<li class="px-4 py-6 text-sm text-[var(--bf-muted)] text-center">No tienes notificaciones nuevas</li>';

        return;
    }

    list.innerHTML = unread.map((item) => bfNotificationBellItemHtml(item)).join('');
}

/**
 * @param {HTMLElement|null} list
 * @param {Array<object>} items
 * @param {string} indexUrl
 * @deprecated Use bfRenderBellNotificationList for the bell dropdown.
 */
export function bfRenderNotificationList(list, items, indexUrl) {
    bfRenderBellNotificationList(list, items);
}

/**
 * @param {HTMLElement|null} list
 * @param {object} item
 * @param {Set<number|string>} seenIds
 * @returns {boolean} inserted
 */
export function bfPrependNotificationItem(list, item, seenIds) {
    if (!list || !item?.id || item.read || seenIds.has(item.id)) {
        return false;
    }

    seenIds.add(item.id);

    const emptyRow = list.querySelector('li:not([data-notification-item-id])');
    emptyRow?.remove();

    list.insertAdjacentHTML('afterbegin', bfNotificationBellItemHtml(item));

    return true;
}

/**
 * @param {HTMLElement|null} list
 * @param {number|string} id
 */
export function bfRemoveBellNotificationItem(list, id) {
    if (!list) {
        return;
    }

    list.querySelector(`[data-notification-item-id="${id}"]`)?.remove();

    if (!list.querySelector('[data-notification-item-id]')) {
        list.innerHTML =
            '<li class="px-4 py-6 text-sm text-[var(--bf-muted)] text-center">No tienes notificaciones nuevas</li>';
    }
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
