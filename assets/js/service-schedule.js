/**
 * service-schedule.js
 * ---------------------------------------------------------------------
 * The Schedule tab of each service (baptism-, wedding-, confirmation-,
 * funeral- and counseling-schedule.html). The month grid, the list view
 * and the "Upcoming" card show that service's bookings the parish office
 * has approved -- the same anonymous calendar-events.php feed as
 * calendar.html -- so they stay empty until a real request is approved.
 * Counseling appointments are private and never appear in that feed.
 *
 * Configured on #confSchedGrid:
 *   data-service-schedule="<request type>"   e.g. baptism (PS_REQUEST_TYPES)
 *   data-schedule-empty="..."                message when nothing is booked
 *   data-schedule-upcoming="<element id>"    the sidebar list, if any
 * ---------------------------------------------------------------------
 */
(function () {
    const grid = document.getElementById('confSchedGrid');
    if (!grid || !grid.dataset.serviceSchedule) return;

    const type = grid.dataset.serviceSchedule;
    const page = location.pathname.split('/').pop() || 'calendar.html';
    const emptyText = grid.dataset.scheduleEmpty || 'Nothing has been scheduled for this month yet.';
    const upcoming = grid.dataset.scheduleUpcoming ? document.getElementById(grid.dataset.scheduleUpcoming) : null;
    const listView = document.getElementById('confSchedList');
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    const params = new URLSearchParams(location.search);
    const now = new Date();
    let month = parseInt(params.get('month'), 10);
    let year = parseInt(params.get('year'), 10);
    if (!month || month < 1 || month > 12 || !year || year < 2000 || year > 2100) {
        month = now.getMonth() + 1;
        year = now.getFullYear();
    }
    const current = new Date(year, month - 1, 1);
    const iso = (date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;

    const monthLabel = document.querySelector('.conf-sched-month-label');
    if (monthLabel) monthLabel.textContent = monthNames[current.getMonth()] + ' ' + current.getFullYear();
    const link = (date) => `${page}?month=${date.getMonth() + 1}&year=${date.getFullYear()}`;
    const prevLink = document.querySelector('.conf-sched-nav-btn.is-prev');
    const nextLink = document.querySelector('.conf-sched-nav-btn.is-next');
    if (prevLink) prevLink.href = link(new Date(current.getFullYear(), current.getMonth() - 1, 1));
    if (nextLink) nextLink.href = link(new Date(current.getFullYear(), current.getMonth() + 1, 1));

    function el(tag, className, text) {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    }

    function eventItem(event) {
        const date = new Date(event.date + 'T00:00:00');
        const item = el('div', 'conf-sched-event-item is-highlighted');
        const badge = el('span', 'conf-sched-event-date cat-mass');
        badge.append(el('strong', '', String(date.getDate()).padStart(2, '0')), el('small', '', monthNames[date.getMonth()].slice(0, 3).toUpperCase()));
        const info = el('span', 'conf-sched-event-info');
        info.append(el('strong', '', event.label), el('span', '', event.time || 'Time to be confirmed'), el('span', '', event.statusLabel));
        item.append(badge, info);
        return item;
    }

    function render(events) {
        const byDay = new Map();
        events.forEach((event) => {
            const day = Number(event.date.slice(8, 10));
            byDay.set(day, [...(byDay.get(day) || []), event]);
        });

        grid.querySelectorAll('.conf-sched-day').forEach((cell) => cell.remove());
        for (let index = 0; index < 42; index += 1) {
            const cellDate = new Date(current.getFullYear(), current.getMonth(), 1 - current.getDay() + index);
            const cell = el('div', 'conf-sched-day');
            if (cellDate.getMonth() !== current.getMonth()) cell.classList.add('is-muted');
            if (cellDate.toDateString() === now.toDateString()) cell.classList.add('is-today');
            cell.append(el('span', 'conf-sched-day-num', String(cellDate.getDate())));
            const dayEvents = cellDate.getMonth() === current.getMonth() ? byDay.get(cellDate.getDate()) : null;
            if (dayEvents) {
                const dots = el('span', 'conf-sched-day-dots');
                dayEvents.forEach((event) => {
                    const dot = el('span', 'conf-sched-dot cat-mass');
                    dot.title = `${event.label} (${event.statusLabel})`;
                    dots.append(dot);
                });
                cell.append(dots);
            }
            grid.append(cell);
        }

        if (listView) listView.replaceChildren(...(events.length ? events.map(eventItem) : [el('p', 'conf-sched-list-empty', emptyText)]));
        if (upcoming) {
            const next = events.filter((event) => event.date >= iso(now)).slice(0, 4);
            upcoming.replaceChildren(...(next.length ? next.map(eventItem) : [el('p', 'conf-sched-list-empty', emptyText)]));
        }
    }

    render([]);
    if (location.protocol !== 'file:') {
        fetch(`calendar-events.php?month=${month}&year=${year}`, { cache: 'no-store' })
            .then((response) => (response.ok ? response.json() : null))
            .then((data) => {
                // The feed covers the 6-week grid, ordered by date and time.
                const prefix = `${year}-${String(month).padStart(2, '0')}-`;
                render(((data && data.events) || []).filter((event) => event.type === type && event.date.startsWith(prefix)));
            })
            .catch(() => { /* the empty month stays */ });
    }

    const calendarPanel = document.querySelector('.conf-sched-calendar-panel');
    const viewButtons = document.querySelectorAll('.conf-sched-view-btn');
    document.querySelector('.conf-sched-view-all')?.addEventListener('click', (event) => {
        event.preventDefault();
        document.querySelector('[data-view="list"]')?.click();
        listView?.focus();
    });
    viewButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const view = button.dataset.view;
            if (view === 'today') {
                window.location.href = page;
                return;
            }
            viewButtons.forEach((other) => {
                if (other.dataset.view !== 'today') {
                    other.classList.toggle('active', other === button);
                    other.setAttribute('aria-pressed', String(other === button));
                }
            });
            if (calendarPanel) calendarPanel.hidden = view !== 'month';
            if (listView) listView.hidden = view !== 'list';
        });
    });
})();
