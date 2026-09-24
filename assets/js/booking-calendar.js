/**
 * booking-calendar.js
 * ---------------------------------------------------------------------
 * A bigger, clearer calendar dialog for [data-datepicker] fields, so a
 * parishioner can see at a glance which dates are already taken instead
 * of guessing against a plain date input. Replaces the old behavior of
 * just turning the field into a native <input type="date">.
 *
 * Rendered as a centered modal (fixed + dimmed backdrop), not a dropdown
 * anchored to the field -- these fields can sit near the bottom of a
 * long form, where an anchored panel this size would either run
 * off-screen or, flipped upward, float on top of unrelated sections
 * above it (visually, and for clicks -- the previous anchored version
 * had exactly that problem: its own "next month" button could end up
 * genuinely covered by page content that sat at the same screen spot).
 *
 * The underlying <input type="date"> stays the real form field (still
 * validated by the browser and the server the same as before) -- it's
 * just made read-only so this dialog is the only way to set it.
 *
 * data-booking-type="<type>": which PS_REQUEST_TYPES key this date is
 *   for (wedding/baptism/funeral/...). When set, each visible month is
 *   checked against calendar-events.php?month=&year= (the same public,
 *   anonymous feed the parish calendar itself uses) and any date that
 *   already has an approved/scheduled/completed booking of that type is
 *   greyed out alongside past/too-soon dates. Omit it (e.g. Mass
 *   Intention, which never conflicts) for a plain calendar with no
 *   greyed-out days and no legend.
 * data-min-months-ahead="N": earliest selectable date is N months from
 *   today (falls back to "tomorrow" when absent) -- mirrors the
 *   min_months_ahead field rule in includes/request-forms.php.
 * data-weekday + data-regular-weekday: baptism's "Regular Baptism is
 *   Saturdays only" rule -- only applied while [name="baptismType"] is
 *   checked to that value (or when there's no such radio at all). Since
 *   this is a true modal, that radio (outside the dialog) can't be
 *   reached while the calendar is open -- close it, flip Regular/
 *   Special, reopen to see the updated days.
 * ---------------------------------------------------------------------
 */
(function () {
    const isoDate = date => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    const parseIso = value => /^\d{4}-\d{2}-\d{2}$/.test(value || '') ? new Date(value + 'T00:00:00') : null;
    const sameDate = (a, b) => a && b && a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate();
    const CAL_ICON_PREV = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>';
    const CAL_ICON_NEXT = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>';
    const WEEKDAY_NAMES = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    document.querySelectorAll('[data-datepicker]').forEach(picker => {
        const input = picker.querySelector('input');
        const toggle = picker.querySelector('[data-datepicker-toggle]');
        const panel = picker.querySelector('[data-datepicker-panel]');
        if (!input || !toggle || !panel) return;

        input.type = 'date';
        input.readOnly = true;
        input.removeAttribute('pattern');

        const card = document.createElement('div');
        card.className = 'ps-datepicker-panel-card';
        panel.append(card);

        const bookingType = picker.dataset.bookingType || '';
        const minMonths = parseInt(picker.dataset.minMonthsAhead, 10) || 0;
        const regularWeekday = 'regularWeekday' in picker.dataset ? parseInt(picker.dataset.regularWeekday, 10) : null;

        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const minDate = new Date(today);
        if (minMonths > 0) minDate.setMonth(minDate.getMonth() + minMonths);
        else minDate.setDate(minDate.getDate() + 1);
        input.min = isoDate(minDate);

        let view = new Date(minDate.getFullYear(), minDate.getMonth(), 1);
        let selected = parseIso(input.value);
        const bookedCache = new Map(); // 'YYYY-M' -> Set of 'YYYY-MM-DD'

        async function fetchBooked(year, month) {
            if (!bookingType) return new Set();
            const key = `${year}-${month}`;
            if (bookedCache.has(key)) return bookedCache.get(key);
            const dates = new Set();
            try {
                const response = await fetch(`calendar-events.php?month=${month}&year=${year}`, { cache: 'no-store' });
                if (response.ok) {
                    const { events = [] } = await response.json();
                    events.forEach(event => { if (event.type === bookingType) dates.add(event.date); });
                }
            } catch (_) {
                // Without the feed the calendar still renders, just without greyed-out booked days.
            }
            bookedCache.set(key, dates);
            return dates;
        }

        function regularOnlyActive() {
            if (regularWeekday === null) return false;
            const checked = document.querySelector('[name="baptismType"]:checked');
            return !checked || checked.value === 'regular';
        }

        async function render(requestToken) {
            const year = view.getFullYear();
            const month = view.getMonth();

            card.replaceChildren();
            const loading = document.createElement('p');
            loading.className = 'ps-datepicker-loading';
            loading.textContent = 'Loading availability…';
            if (bookingType) card.append(loading);

            const booked = await fetchBooked(year, month + 1);
            if (requestToken !== renderToken) return; // a newer render started meanwhile

            card.replaceChildren();

            const head = document.createElement('div');
            head.className = 'ps-datepicker-head';
            const prev = document.createElement('button');
            prev.type = 'button';
            prev.className = 'ps-datepicker-nav';
            prev.setAttribute('aria-label', 'Previous month');
            prev.innerHTML = CAL_ICON_PREV;
            const label = document.createElement('strong');
            label.textContent = view.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
            const next = document.createElement('button');
            next.type = 'button';
            next.className = 'ps-datepicker-nav';
            next.setAttribute('aria-label', 'Next month');
            next.innerHTML = CAL_ICON_NEXT;
            prev.disabled = year === minDate.getFullYear() && month === minDate.getMonth();
            prev.addEventListener('click', () => { view = new Date(year, month - 1, 1); rerender(); });
            next.addEventListener('click', () => { view = new Date(year, month + 1, 1); rerender(); });
            head.append(prev, label, next);

            const weekdays = document.createElement('div');
            weekdays.className = 'ps-datepicker-weekdays';
            ['S', 'M', 'T', 'W', 'T', 'F', 'S'].forEach(letter => {
                const span = document.createElement('span');
                span.textContent = letter;
                weekdays.append(span);
            });

            const days = document.createElement('div');
            days.className = 'ps-datepicker-days';
            const firstOfMonth = new Date(year, month, 1);
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const regularOnly = regularOnlyActive();

            for (let i = 0; i < firstOfMonth.getDay(); i++) {
                const blank = document.createElement('span');
                blank.className = 'ps-datepicker-day is-muted';
                days.append(blank);
            }
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(year, month, day);
                const iso = isoDate(date);
                const cell = document.createElement('button');
                cell.type = 'button';
                cell.className = 'ps-datepicker-day';
                cell.textContent = String(day);

                const tooSoon = date < minDate;
                const wrongWeekday = regularOnly && date.getDay() !== regularWeekday;
                const isBooked = booked.has(iso);

                if (tooSoon || wrongWeekday) {
                    cell.classList.add('is-disabled');
                    cell.disabled = true;
                    // Tells a hovering mouse *why* -- the grey look alone
                    // doesn't distinguish "too early to book" from "someone
                    // already has this date", which read as a bug otherwise.
                    if (isBooked) cell.title = 'Already booked';
                    else if (wrongWeekday) cell.title = `Regular Baptism is only available on ${WEEKDAY_NAMES[regularWeekday]}s`;
                    else if (tooSoon) cell.title = minMonths > 0
                        ? `Must be booked at least ${minMonths} month${minMonths > 1 ? 's' : ''} in advance`
                        : 'This date has already passed';
                } else {
                    cell.classList.add('is-selectable'); cell.title = isBooked ? 'Existing ceremony — select to check remaining times' : 'Select date to check times'; cell.setAttribute('aria-label', date.toLocaleDateString() + '. ' + cell.title);
                    cell.addEventListener('click', () => {
                        input.value = iso;
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                        selected = date;
                        close();
                        toggle.focus();
                    });
                }
                if (sameDate(date, today)) cell.classList.add('is-today');
                if (sameDate(date, selected)) cell.classList.add('is-selected');
                days.append(cell);
            }

            card.append(head, weekdays, days);

            if (bookingType) {
                const legend = document.createElement('div');
                legend.className = 'ps-datepicker-legend';
                legend.innerHTML =
                    '<span class="ps-datepicker-legend-item"><i class="ps-datepicker-swatch"></i>Choose date to check times</span>' +
                    '<span class="ps-datepicker-legend-item"><i class="ps-datepicker-swatch is-disabled"></i>Not available</span>';
                card.append(legend);
            }

            // Only now (real content in place, not the "Loading…" placeholder)
            // does the panel have its true size to position against.
            positionPanel();
        }

        let renderToken = 0;
        function rerender() { renderToken += 1; render(renderToken); }

        function open() {
            view = selected ? new Date(selected.getFullYear(), selected.getMonth(), 1) : new Date(minDate.getFullYear(), minDate.getMonth(), 1);
            panel.hidden = false;
            rerender();
        }
        function close() {
            panel.hidden = true;
        }

        // Anchors the popup near the field using viewport (not page)
        // coordinates, clamped so it always stays fully on-screen and
        // never has to flip up over unrelated content further up a long
        // form -- position:fixed means this is independent of where the
        // field happens to sit on the page or how far it's scrolled.
        function positionPanel() {
            const margin = 8;
            const fieldRect = picker.getBoundingClientRect();
            const panelRect = panel.getBoundingClientRect();

            let top = fieldRect.bottom + margin;
            if (top + panelRect.height > window.innerHeight - margin) {
                const above = fieldRect.top - margin - panelRect.height;
                top = above > margin ? above : Math.max(margin, window.innerHeight - panelRect.height - margin);
            }

            let left = fieldRect.left;
            if (left + panelRect.width > window.innerWidth - margin) {
                left = window.innerWidth - panelRect.width - margin;
            }
            left = Math.max(margin, left);

            panel.style.top = `${top}px`;
            panel.style.left = `${left}px`;
        }

        toggle.addEventListener('click', () => { panel.hidden ? open() : close(); });
        input.addEventListener('click', () => { if (panel.hidden) open(); });
        document.addEventListener('click', event => {
            if (panel.hidden) return;
            // composedPath(), not contains(event.target): a click on the
            // month-nav buttons rebuilds the day grid (replacing those
            // buttons) while this same click is still bubbling, so by the
            // time it reaches here event.target may already be detached
            // and .contains() would wrongly read as "outside".
            const path = event.composedPath();
            if (!path.includes(picker) && !path.includes(panel)) close();
        });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && !panel.hidden) { close(); toggle.focus(); }
        });
        window.addEventListener('resize', () => { if (!panel.hidden) positionPanel(); });
        window.addEventListener('scroll', () => { if (!panel.hidden) positionPanel(); });

        // Switching Special -> Regular can leave a previously-picked
        // non-Saturday sitting in the field, looking chosen but no longer
        // valid for the new type -- clear it rather than silently carry
        // it over (Regular -> Special never needs this: every Saturday is
        // still a fine day for Special too).
        if (regularWeekday !== null) {
            document.querySelectorAll('[name="baptismType"]').forEach(radio => {
                radio.addEventListener('change', () => {
                    if (selected && regularOnlyActive() && selected.getDay() !== regularWeekday) {
                        input.value = '';
                        selected = null;
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });
            });
        }
    });
})();
