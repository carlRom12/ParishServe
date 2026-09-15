/**
 * Booking hint under a request form's date field: input[data-booking-type="<type>"]
 * (wedding-request.html, baptism-request.html, funeral-request-step2.html).
 * Once a date is chosen, booking-availability.php lists the times the
 * parish has already booked that day in the same place. With
 * data-booking-time="<time input name>" the chosen time is tested too: an
 * overlap is shown as a problem and makes the time field invalid.
 * includes/request-forms.php refuses the same overlap on submit, so this
 * is only the early warning.
 */
(function initBookingHints() {
    if (location.protocol === 'file:') return;

    const minutesOf = (value) => {
        const match = /^(\d{2}):(\d{2})/.exec(value || '');
        return match ? Number(match[1]) * 60 + Number(match[2]) : null;
    };

    document.querySelectorAll('input[data-booking-type]').forEach((dateInput) => {
        const timeName = dateInput.dataset.bookingTime;
        const timeInput = timeName && dateInput.form ? dateInput.form.elements.namedItem(timeName) : null;
        const hint = document.createElement('small');
        hint.className = 'ps-booking-hint';
        hint.setAttribute('aria-live', 'polite');
        hint.hidden = true;
        (dateInput.closest('[data-datepicker], .ps-input-icon, .ps-field-icon') || dateInput).after(hint);

        let availability = null; // { date, minutes, blocks } for the date last looked up
        let lookup = 0;

        function render() {
            const blocks = availability && availability.date === dateInput.value ? availability.blocks : [];
            const start = timeInput ? minutesOf(timeInput.value) : null;
            const clashes = start === null ? [] : blocks.filter((block) => !block.shared && block.start !== null
                && block.start < start + availability.minutes && start < block.end);
            const describe = (list) => list.map((block) => `${block.label}, ${block.start === null ? 'time to be confirmed' : block.time}`).join('; ');

            if (timeInput) {
                timeInput.setCustomValidity(clashes.length ? 'That time overlaps a booking the parish already has. Please choose a different time or date.' : '');
            }
            if (!blocks.length) {
                hint.hidden = true;
                return;
            }
            hint.textContent = clashes.length
                ? `That time overlaps a booking the parish already has (${describe(clashes)}). Please choose a different time or date.`
                : `Already booked on this date: ${describe(blocks)}. ${timeInput ? "Please choose a time that doesn't overlap." : 'The parish office will confirm the time with you.'}`;
            hint.classList.toggle('is-conflict', clashes.length > 0);
            hint.hidden = false;
        }

        async function load() {
            const date = dateInput.value;
            const current = ++lookup;
            availability = null;
            render();
            if (!/^\d{4}-\d{2}-\d{2}$/.test(date)) return;
            try {
                const params = new URLSearchParams({ type: dateInput.dataset.bookingType, date });
                const response = await fetch(`booking-availability.php?${params}`, { cache: 'no-store' });
                if (!response.ok) return;
                const data = await response.json();
                if (current !== lookup) return;
                availability = { date, minutes: data.minutes, blocks: data.blocks || [] };
                render();
            } catch (_) {
                // Only a hint -- the server checks again on submit.
            }
        }

        dateInput.addEventListener('change', load);
        if (timeInput) {
            timeInput.addEventListener('input', render);
            timeInput.addEventListener('change', render);
        }
        if (dateInput.value) load();
    });
})();
