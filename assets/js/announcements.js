/**
 * announcements.js
 * ---------------------------------------------------------------------
 * announcements.html, filled from the database -- nothing on the page is
 * sample content:
 *   - Featured carousel, All Announcements list and Important Notices
 *     (category "Notices"): announcements-data.php, i.e. what staff posted
 *     on admin-announcements.php
 *   - Upcoming Parish Events: the next approved bookings from
 *     calendar-events.php (anonymous, like the calendar)
 * Fires `ps:announcements-rendered` once the rows are in, which main.js's
 * carousel, category tabs, search and "Load more" listen for.
 * ---------------------------------------------------------------------
 */
(function () {
    const list = document.querySelector('.ann-table');
    if (!list || location.protocol === 'file:') return;

    const CATEGORY_CLASS = {
        'Parish News': 'cat-news', 'Events': 'cat-events', 'Mass & Liturgical': 'cat-liturgical',
        'Wedding Banns': 'cat-banns', 'Reminders': 'cat-reminders', 'Notices': 'cat-notices',
    };
    const FALLBACK_IMAGE = 'assets/images/mass-intention-hero.png';
    const INFO_ICON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5.5"/><path d="M12 7.5h.01"/></svg>';

    function el(tag, className, text) {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    }

    function getJson(url) {
        return fetch(url, { cache: 'no-store', headers: { Accept: 'application/json' } })
            .then((response) => (response.ok ? response.json() : Promise.reject(new Error(String(response.status)))));
    }

    function image(src, className) {
        const img = el('img', className);
        img.src = src;
        img.alt = '';
        img.loading = 'lazy';
        return img;
    }

    const excerpt = (text, length) => (text.length > length ? text.slice(0, length).trimEnd() + '…' : text);

    function renderFeatured(featured) {
        const card = document.querySelector('[data-announcements-featured]');
        const track = document.querySelector('[data-announcements-slides]');
        const dots = document.querySelector('[data-carousel-dots]');
        if (!card || !track) return;
        card.hidden = featured.length === 0;
        track.replaceChildren(...featured.map((item, index) => {
            const slide = el('div', 'ann-slide' + (index === 0 ? ' is-active' : ''));
            slide.dataset.slide = '';
            if (index > 0) slide.setAttribute('aria-hidden', 'true');
            const media = el('div', 'ann-slide-image');
            media.append(image(item.imageUrl || FALLBACK_IMAGE));
            const body = el('div', 'ann-slide-body');
            const actions = el('div', 'ann-slide-actions');
            const read = el('a', 'ps-btn ps-btn-primary', 'Read full announcement');
            read.href = '#announcement-' + item.id;
            actions.append(read, el('span', 'ann-slide-date', item.dateLabel));
            body.append(el('span', 'ann-featured-badge', 'FEATURED'), el('h3', '', item.title), el('p', '', excerpt(item.body, 220)), actions);
            slide.append(media, body);
            return slide;
        }));
        if (dots) {
            dots.replaceChildren(...featured.map((item, index) => {
                const dot = el('button', 'ann-dot' + (index === 0 ? ' is-active' : ''));
                dot.type = 'button';
                dot.dataset.carouselDot = String(index);
                dot.setAttribute('aria-label', `Go to slide ${index + 1}`);
                return dot;
            }));
        }
        const nav = document.querySelector('.ann-carousel-nav');
        if (nav) nav.hidden = featured.length < 2;
    }

    function renderRows(announcements) {
        const head = list.querySelector('.ann-table-head');
        list.querySelectorAll('[data-announcement-row]').forEach((row) => row.remove());
        const rows = announcements.map((item) => {
            const row = el('div', 'ann-row');
            row.id = 'announcement-' + item.id;
            row.dataset.announcementRow = '';
            row.dataset.category = item.category;
            const main = el('div', 'ann-row-main');
            if (item.imageUrl) main.append(image(item.imageUrl));
            const text = el('div', 'ann-row-text');
            text.append(el('strong', '', item.title), el('small', '', item.body));
            main.append(text);
            row.append(main, el('span', 'ps-tag ' + (CATEGORY_CLASS[item.category] || ''), item.category), el('span', 'ann-row-date', item.dateLabel), el('span'));
            return row;
        });
        (head || list).after(...rows);
        if (!head) list.append(...rows);
    }

    function renderNotices(notices) {
        const target = document.querySelector('[data-announcements-notices]');
        if (!target) return;
        if (!notices.length) {
            const empty = el('li');
            empty.append(el('small', '', 'No notices right now.'));
            target.replaceChildren(empty);
            return;
        }
        target.replaceChildren(...notices.slice(0, 3).map((item) => {
            const li = el('li');
            const icon = el('span', 'ann-notice-icon tint-blue');
            icon.innerHTML = INFO_ICON; // fixed markup
            const body = el('span', 'ann-notice-body');
            const link = el('a', '', item.title);
            link.href = '#announcement-' + item.id;
            const title = el('strong');
            title.append(link);
            body.append(title, el('small', '', excerpt(item.body, 80)));
            li.append(icon, body);
            return li;
        }));
    }

    function renderEvents(events) {
        const target = document.querySelector('[data-announcements-events]');
        if (!target) return;
        if (!events.length) {
            const empty = el('li');
            empty.append(el('small', '', 'No upcoming parish bookings yet.'));
            target.replaceChildren(empty);
            return;
        }
        target.replaceChildren(...events.slice(0, 4).map((event) => {
            const date = new Date(event.date + 'T00:00:00');
            const li = el('li');
            const day = el('span', 'ann-event-date');
            day.append(el('small', '', date.toLocaleDateString('en-US', { month: 'short' }).toUpperCase()), el('strong', '', String(date.getDate()).padStart(2, '0')));
            const body = el('span', 'ann-event-body');
            body.append(el('strong', '', event.label), el('small', '', [event.time, event.statusLabel].filter(Boolean).join(' · ')));
            li.append(day, body, el('span', 'ps-dot cat-' + event.category));
            return li;
        }));
    }

    getJson('announcements-data.php').then((data) => {
        const announcements = data.announcements || [];
        renderFeatured(announcements.filter((item) => item.featured));
        renderRows(announcements);
        renderNotices(announcements.filter((item) => item.category === 'Notices'));
        document.dispatchEvent(new CustomEvent('ps:announcements-rendered'));
        if (location.hash) document.querySelector(location.hash)?.scrollIntoView({ block: 'center' });
    }).catch(() => {
        const empty = document.querySelector('[data-announcement-empty]');
        if (empty) {
            empty.textContent = "Announcements couldn't be loaded right now. Please try again later.";
            empty.classList.add('is-visible');
        }
        renderNotices([]);
    });

    // The next few bookings from today, across this month and next.
    const now = new Date();
    const todayIso = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
    const next = new Date(now.getFullYear(), now.getMonth() + 1, 1);
    Promise.all([
        getJson(`calendar-events.php?month=${now.getMonth() + 1}&year=${now.getFullYear()}`),
        getJson(`calendar-events.php?month=${next.getMonth() + 1}&year=${next.getFullYear()}`),
    ]).then((months) => {
        const seen = new Set();
        const events = months.flatMap((month) => month.events || [])
            .filter((event) => event.date >= todayIso)
            .filter((event) => {
                const id = [event.date, event.time, event.type, event.label].join('|');
                if (seen.has(id)) return false; // the 6-week windows overlap
                seen.add(id);
                return true;
            })
            .sort((a, b) => (a.date + a.time).localeCompare(b.date + b.time));
        renderEvents(events);
    }).catch(() => renderEvents([]));
})();
