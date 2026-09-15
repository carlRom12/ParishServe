/**
 * dashboard-revamp.js
 * ---------------------------------------------------------------------
 * dashboard.html. Nothing personal or dated is baked into the page:
 *   - greeting, My Requests, Request Progress: dashboard-data.php -- a
 *     visitor who isn't signed in keeps the "Log in to see your
 *     requests" prompt
 *   - Today at the Parish: today's approved bookings (calendar-events.php)
 *   - Parish Updates: the newest announcement (announcements-data.php),
 *     featured ones first
 * ---------------------------------------------------------------------
 */
(function () {
    const now = new Date();
    const current = new Date(now.getFullYear(), now.getMonth(), 1);
    const online = location.protocol !== 'file:';
    const iso = (date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    const partOfDay = now.getHours() < 12 ? 'morning' : now.getHours() < 18 ? 'afternoon' : 'evening';

    function el(tag, className, text) {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    }

    function getJson(url) {
        return fetch(url, { cache: 'no-store', credentials: 'same-origin', headers: { Accept: 'application/json' } })
            .then((response) => (response.ok ? response.json() : null));
    }

    // ---- Month calendar ----------------------------------------------
    function renderCalendar() {
        document.getElementById('dvMonth').textContent = current.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        const grid = document.getElementById('dvCalendar');
        grid.replaceChildren();
        for (const day of ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']) grid.append(el('strong', '', day));
        for (let i = 0; i < 42; i++) {
            const date = new Date(current.getFullYear(), current.getMonth(), 1 - current.getDay() + i);
            const className = date.getMonth() !== current.getMonth() ? 'muted' : date.toDateString() === now.toDateString() ? 'today' : '';
            grid.append(el('span', className, String(date.getDate())));
        }
    }
    document.getElementById('dvPrev').onclick = () => { current.setMonth(current.getMonth() - 1); renderCalendar(); };
    document.getElementById('dvNext').onclick = () => { current.setMonth(current.getMonth() + 1); renderCalendar(); };
    renderCalendar();

    if (!online) return;

    // ---- Greeting, My Requests, Request Progress -----------------------
    const pipeline = ['submitted', 'under_review', 'approved', 'scheduled', 'completed'];

    function requestItem(request) {
        const item = el('li', 'db-request-item');
        const icon = el('span', 'db-request-icon');
        icon.innerHTML = request.iconSvg; // fixed SVG markup from includes/icons.php
        const body = el('span', 'db-request-body');
        body.append(el('strong', '', request.title), el('small', '', `Submitted · ${request.submitted}`));
        if (request.schedule) body.append(el('small', 'db-request-extra', request.schedule));
        item.append(icon, body, el('span', 'ps-status is-' + request.status, request.statusLabel));
        return item;
    }

    function requestMessage(text) {
        const item = el('li', 'db-request-item');
        const body = el('span', 'db-request-body');
        body.append(el('small', '', text));
        item.append(body);
        return item;
    }

    getJson('dashboard-data.php').then((data) => {
        if (!data || !data.loggedIn) return;
        document.getElementById('dvGreeting').textContent = `Good ${partOfDay}, ${data.firstName}!`;
        const welcome = document.querySelector('[data-dash-welcome]');
        if (welcome) welcome.textContent = `Welcome back, ${data.firstName}`;

        const requests = data.requests || [];
        document.querySelector('[data-dash-requests]')?.replaceChildren(
            ...(requests.length ? requests.map(requestItem) : [requestMessage('No requests yet. Your sacrament and service requests will show up here.')])
        );
        const latest = requests[0] ? requests[0].status : null;
        document.querySelectorAll('[data-dash-progress] .db-progress-step').forEach((step, index) => {
            step.classList.toggle('is-current', pipeline[index] === latest);
        });
    }).catch(() => { /* keeps the log-in prompt */ });

    // ---- Today at the Parish ------------------------------------------
    const today = document.querySelector('[data-dash-today]');
    if (today) {
        const message = (text) => {
            const item = el('li');
            item.append(el('p', 'dv-muted', text));
            return item;
        };
        const dotFor = { mass: 'dot-gold', sacrament: 'dot-red' };
        getJson(`calendar-events.php?month=${now.getMonth() + 1}&year=${now.getFullYear()}`).then((data) => {
            const events = ((data && data.events) || []).filter((event) => event.date === iso(now));
            today.replaceChildren(...(events.length ? events.map((event) => {
                const item = el('li', 'db-timeline-item');
                const body = el('span', 'db-timeline-body');
                body.append(el('strong', '', event.label), el('small', '', event.statusLabel));
                item.append(el('span', 'db-timeline-time', event.time || 'Time to be confirmed'), el('span', 'db-timeline-dot ' + (dotFor[event.category] || 'dot-blue')), body);
                return item;
            }) : [message('No parish bookings today.')]));
        }).catch(() => today.replaceChildren(message("Today's schedule couldn't be loaded.")));
    }

    // ---- Parish Updates -----------------------------------------------
    const update = document.querySelector('[data-dash-update]');
    if (update) {
        getJson('announcements-data.php').then((data) => {
            const announcements = (data && data.announcements) || [];
            const latest = announcements.find((item) => item.featured) || announcements[0];
            if (!latest) return;
            const feature = el('div', 'db-update-feature');
            if (latest.imageUrl) {
                const image = el('img');
                image.src = latest.imageUrl;
                image.alt = '';
                feature.append(image);
            }
            const text = el('div', 'db-update-feature-text');
            const excerpt = latest.body.length > 180 ? latest.body.slice(0, 180).trimEnd() + '…' : latest.body;
            const more = el('a', 'ps-link-more', 'Read more');
            more.href = 'announcements.html#announcement-' + latest.id;
            text.append(el('strong', '', latest.title), el('p', '', excerpt), more);
            feature.append(text);
            update.replaceChildren(feature);
        }).catch(() => { /* keeps "No announcements have been posted yet." */ });
    }
})();
