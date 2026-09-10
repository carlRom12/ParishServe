(function () {
    const grid = document.querySelector('.cal-grid');
    if (!grid) return;
    const mini = document.querySelector('.cal-mini-grid');
    const events = new Map();
    const dateKey = date => `${date.getFullYear()}-${date.getMonth()}-${date.getDate()}`;
    // The initial HTML contains the existing May 2026 sample schedule.
    Array.from(grid.children).forEach((cell, index) => {
        events.set(dateKey(new Date(2026, 3, 26 + index)), Array.from(cell.querySelectorAll('.cal-event, .cal-event-more')).map(event => event.cloneNode(true)));
    });
    const params = new URLSearchParams(location.search);
    const month = Number(params.get('month') || 5);
    const year = Number(params.get('year') || 2026);
    const valid = Number.isInteger(month) && Math.abs(month) <= 1200 && Number.isInteger(year) && year >= 100 && year <= 9998;
    const current = valid ? new Date(year, month - 1, 1) : new Date(2026, 4, 1);
    const today = new Date();
    const label = current.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
    document.querySelector('.cal-month-label').textContent = label;
    document.querySelector('.cal-mini-head strong').textContent = label;
    const link = date => `calendar.html?month=${date.getMonth() + 1}&year=${date.getFullYear()}`;
    document.querySelectorAll('[aria-label="Previous month"]').forEach(a => a.href = link(new Date(current.getFullYear(), current.getMonth() - 1, 1)));
    document.querySelectorAll('[aria-label="Next month"]').forEach(a => a.href = link(new Date(current.getFullYear(), current.getMonth() + 1, 1)));
    document.querySelector('.cal-toolbar .ps-filter-btn').href = link(today);
    grid.replaceChildren();
    mini.replaceChildren();
    for (let index = 0; index < 42; index++) {
        const date = new Date(current.getFullYear(), current.getMonth(), 1 - current.getDay() + index);
        const muted = date.getMonth() !== current.getMonth() ? ' is-muted' : '';
        const active = dateKey(date) === dateKey(today) ? ' is-today' : '';
        const cell = document.createElement('div');
        cell.className = 'cal-cell' + muted;
        const day = document.createElement('span');
        day.className = 'cal-cell-day' + active;
        day.textContent = date.getDate();
        cell.append(day, ...(events.get(dateKey(date)) || []));
        grid.append(cell);
        const miniDay = document.createElement('span');
        miniDay.className = 'cal-mini-cell' + muted + active;
        miniDay.textContent = date.getDate();
        mini.append(miniDay);
    }
})();
