/* Date-first service times, supplied and validated by the server. */
(() => {
    document.querySelectorAll('input[data-booking-type][data-booking-time]').forEach(date => {
        const time = date.form?.elements.namedItem(date.dataset.bookingTime);
        if (!time || time.tagName !== 'SELECT') return;
        const status = document.createElement('small');
        status.className = 'ps-form-hint';
        status.id = `${time.id}-availability`;
        status.setAttribute('role', 'status');
        status.setAttribute('aria-live', 'polite');
        time.after(status);
        time.setAttribute('aria-describedby', status.id);
        let request = 0;
        let restored = '';
        try {
            const draft = JSON.parse(sessionStorage.getItem(`parishserve-draft-${date.dataset.bookingType}`) || '{}');
            restored = draft[time.name] || '';
        } catch (_) {}
        const reset = label => {
            time.replaceChildren(new Option(label, ''));
            time.disabled = true;
        };
        const retry = document.createElement('button');
        retry.type = 'button'; retry.className = 'ps-btn ps-btn-outline'; retry.textContent = 'Retry availability'; retry.hidden = true;
        status.after(retry);
        async function load() {
            const token = ++request;
            const previous = time.value || restored;
            restored = '';
            reset(date.value ? 'Loading times...' : 'Choose a date first');
            retry.hidden = true;
            status.textContent = date.value ? 'Checking the parish schedule...' : 'Select a date, then choose a listed time.';
            if (!date.value) return;
            try {
                const response = await fetch(`booking-availability.php?${new URLSearchParams({type:date.dataset.bookingType,date:date.value})}`, {cache:'no-store'});
                if (!response.ok) throw new Error('Unavailable');
                const data = await response.json();
                if (token !== request) return;
                if (!Array.isArray(data.slots)) throw new Error('Invalid response');
                time.replaceChildren(new Option('Select preferred time', ''));
                data.slots.forEach(slot => {
                    const option = new Option(`${slot.label}${slot.available ? '' : ' — ' + slot.reason}`, slot.value);
                    option.disabled = !slot.available;
                    time.add(option);
                });
                time.disabled = false;
                time.value = data.slots.some(slot => slot.value === previous && slot.available) ? previous : '';
                time.dispatchEvent(new Event('change', {bubbles:true}));
                const count = data.slots.filter(slot => slot.available).length;
                status.textContent = count ? 'Grey times cannot be selected. Listed times are preferences, subject to parish confirmation.' : 'No listed times remain. Please choose another date.';
            } catch (_) {
                if (token !== request) return;
                reset('Availability could not be loaded');
                status.textContent = 'Please retry before continuing.';
                retry.hidden = false;
            }
        }
        retry.addEventListener('click', load);
        date.addEventListener('change', load);
        date.form.addEventListener('submit', event => {
            if (time.disabled || !time.value) {
                event.preventDefault(); event.stopImmediatePropagation();
                status.textContent = 'Choose a date and an available listed time before continuing.';
                if (!date.value) date.focus(); else if (!time.disabled) time.focus(); else retry.focus();
            }
        }, true);
        load();
    });
})();
