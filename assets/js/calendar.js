(function () {
    const grid = document.querySelector('.cal-grid');
    if (!grid) return;
    const mini = document.querySelector('.cal-mini-grid');
    const dateKey = date => `${date.getFullYear()}-${date.getMonth()}-${date.getDate()}`;
    const isoDate = date => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    const params = new URLSearchParams(location.search);
    const today = new Date();
    const month = Number(params.get('month'));
    const year = Number(params.get('year'));
    const valid = Number.isInteger(month) && month >= 1 && month <= 12 && Number.isInteger(year) && year >= 100 && year <= 9998;
    const current = valid ? new Date(year, month - 1, 1) : new Date(today.getFullYear(), today.getMonth(), 1);
    const label = current.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
    document.querySelector('.cal-month-label').textContent = label;
    const miniLabel = document.querySelector('.cal-mini-head strong'); if (miniLabel) miniLabel.textContent = label;
    const link = date => `calendar.html?month=${date.getMonth() + 1}&year=${date.getFullYear()}`;
    document.querySelectorAll('[aria-label="Previous month"]').forEach(a => a.href = link(new Date(current.getFullYear(), current.getMonth() - 1, 1)));
    document.querySelectorAll('[aria-label="Next month"]').forEach(a => a.href = link(new Date(current.getFullYear(), current.getMonth() + 1, 1)));
    document.querySelector('.cal-toolbar .ps-filter-btn').href = link(today);
    grid.replaceChildren();
    mini?.replaceChildren();
    for (let index = 0; index < 42; index++) {
        const date = new Date(current.getFullYear(), current.getMonth(), 1 - current.getDay() + index);
        const muted = date.getMonth() !== current.getMonth() ? ' is-muted' : '';
        const active = dateKey(date) === dateKey(today) ? ' is-today' : '';
        const cell = document.createElement('div');
        cell.className = 'cal-cell' + muted;
        cell.dataset.date = isoDate(date);
        const day = document.createElement('span');
        day.className = 'cal-cell-day' + active;
        day.textContent = date.getDate();
        cell.append(day);
        grid.append(cell);
        const miniDay = document.createElement('span');
        miniDay.className = 'cal-mini-cell' + muted + active;
        miniDay.dataset.date = isoDate(date);
        miniDay.textContent = date.getDate();
        mini?.append(miniDay);
    }
    grid.classList.add('is-entering');
    loadBookings();

    // Parish bookings (approved / scheduled / completed requests) from
    // calendar-events.php -- the only entries on the calendar, so it never
    // shows anything staff haven't approved. Anonymous: type + time only. The category filter in main.js reads
    // .cal-event when it changes, so these filter like everything else.
    async function loadBookings() {
        if (location.protocol === 'file:') return;
        try {
            const response = await fetch(`calendar-events.php?month=${current.getMonth() + 1}&year=${current.getFullYear()}`, { cache: 'no-store' });
            if (!response.ok) return;
            const { events: bookings = [] } = await response.json();
            const chosen = document.querySelector('[data-category-filter]')?.value || '';
            bookings.forEach(booking => {
                const cell = grid.querySelector(`.cal-cell[data-date="${booking.date}"]`);
                if (!cell) return;
                const item = document.createElement('div');
                item.className = 'cal-event is-booked is-arriving';
                item.dataset.category = booking.category;
                item.title = `${booking.label} (${booking.statusLabel})`;
                item.classList.toggle('is-filtered-out', Boolean(chosen) && chosen !== booking.category);
                const dot = document.createElement('span');
                dot.className = 'ps-dot cat-' + booking.category;
                const text = document.createElement('span');
                text.className = 'cal-event-text';
                text.textContent = booking.time ? `${booking.time} ${booking.label}` : booking.label;
                item.append(dot, text);
                const more = cell.querySelector('.cal-event-more');
                if (more) more.before(item);
                else cell.append(item);
                mini?.querySelector(`[data-date="${booking.date}"]`)?.classList.add('has-booking');
            });
            const note = document.querySelector('.pc-note');
            const inMonth = bookings.some(booking => Number(booking.date.slice(5, 7)) === current.getMonth() + 1);
            if (note && !inMonth) note.textContent = 'No parish bookings have been approved for this month yet.';
        } catch (_) {
            // Without the feed the month grid still draws, just empty.
        }
    }
})();
