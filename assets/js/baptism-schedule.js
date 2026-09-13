(function () {
    const params = new URLSearchParams(location.search);
    const now = new Date();

    let month = parseInt(params.get('month'), 10);
    let year = parseInt(params.get('year'), 10);
    if (!month || month < 1 || month > 12 || !year || year < 2000 || year > 2100) {
        month = now.getMonth() + 1;
        year = now.getFullYear();
    }
    const current = new Date(year, month - 1, 1);

    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    const monthLabel = document.querySelector('.conf-sched-month-label');
    if (monthLabel) monthLabel.textContent = monthNames[current.getMonth()] + ' ' + current.getFullYear();

    const prevMonth = new Date(current.getFullYear(), current.getMonth() - 1, 1);
    const nextMonth = new Date(current.getFullYear(), current.getMonth() + 1, 1);
    const prevLink = document.querySelector('.conf-sched-nav-btn.is-prev');
    const nextLink = document.querySelector('.conf-sched-nav-btn.is-next');
    if (prevLink) prevLink.href = 'baptism-schedule.html?month=' + (prevMonth.getMonth() + 1) + '&year=' + prevMonth.getFullYear();
    if (nextLink) nextLink.href = 'baptism-schedule.html?month=' + (nextMonth.getMonth() + 1) + '&year=' + nextMonth.getFullYear();

    // Preview only: sample Saturday baptisms, not live parish availability.
    const sampleEvents = [];
    for (let day = 1; day <= 31; day += 1) {
        const date = new Date(current.getFullYear(), current.getMonth(), day);
        if (date.getMonth() === current.getMonth() && date.getDay() === 6) {
            sampleEvents.push({ day, category: 'mass', title: 'Regular Baptism', time: 'Time to be confirmed', location: 'Parish Church' });
        }
    }
    const daysInMonth = new Date(current.getFullYear(), current.getMonth() + 1, 0).getDate();
    const monthEvents = sampleEvents.filter(event => event.day <= daysInMonth);
    const eventsByDay = new Map(monthEvents.map(event => [event.day, event]));

    const grid = document.getElementById('confSchedGrid');
    if (grid) {
        grid.querySelectorAll('.conf-sched-day').forEach(cell => cell.remove());
        for (let index = 0; index < 42; index += 1) {
            const cellDate = new Date(current.getFullYear(), current.getMonth(), 1 - current.getDay() + index);
            const cell = document.createElement('div');
            cell.className = 'conf-sched-day';
            if (cellDate.getMonth() !== current.getMonth()) cell.classList.add('is-muted');
            if (cellDate.toDateString() === now.toDateString()) cell.classList.add('is-today');

            const num = document.createElement('span');
            num.className = 'conf-sched-day-num';
            num.textContent = String(cellDate.getDate());
            cell.append(num);

            const event = cellDate.getMonth() === current.getMonth() ? eventsByDay.get(cellDate.getDate()) : null;
            if (event) {
                const dots = document.createElement('span');
                dots.className = 'conf-sched-day-dots';
                const dot = document.createElement('span');
                dot.className = 'conf-sched-dot cat-' + event.category;
                dot.title = event.title;
                dots.append(dot);
                cell.append(dots);
            }
            grid.append(cell);
        }
    }

    const listView = document.getElementById('confSchedList');
    if (listView) {
        listView.replaceChildren();
        if (monthEvents.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'conf-sched-list-empty';
            empty.textContent = 'No scheduled baptisms for this month yet.';
            listView.append(empty);
        } else {
            monthEvents.slice().sort((a, b) => a.day - b.day).forEach(event => {
                const item = document.createElement('div');
                item.className = 'conf-sched-event-item' + (event.category === 'mass' ? ' is-highlighted' : '');

                const date = document.createElement('span');
                date.className = 'conf-sched-event-date cat-' + event.category;
                const day = document.createElement('strong');
                day.textContent = String(event.day).padStart(2, '0');
                const abbr = document.createElement('small');
                abbr.textContent = monthNames[current.getMonth()].slice(0, 3).toUpperCase();
                date.append(day, abbr);

                const info = document.createElement('span');
                info.className = 'conf-sched-event-info';
                const title = document.createElement('strong');
                title.textContent = event.title;
                const time = document.createElement('span');
                time.textContent = event.time;
                const location = document.createElement('span');
                location.textContent = event.location;
                info.append(title, time, location);

                item.append(date, info);
                listView.append(item);
            });
        }
    }

    // Keep the sidebar and list synchronized with the displayed month.
    const upcoming = document.getElementById('baptismUpcoming');
    if (upcoming && listView) upcoming.replaceChildren(...Array.from(listView.children, item => item.cloneNode(true)));

    const calendarPanel = document.querySelector('.conf-sched-calendar-panel');
    const viewButtons = document.querySelectorAll('.conf-sched-view-btn');
    document.querySelector('.conf-sched-view-all')?.addEventListener('click', event => {
        event.preventDefault();
        document.querySelector('[data-view="list"]')?.click();
        listView?.focus();
    });
    viewButtons.forEach(button => {
        button.addEventListener('click', () => {
            const view = button.dataset.view;
            if (view === 'today') {
                window.location.href = 'baptism-schedule.html';
                return;
            }
            viewButtons.forEach(other => {
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
