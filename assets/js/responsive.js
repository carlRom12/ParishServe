(function () {
    const sidebar = document.querySelector('.ps-sidebar');
    const main = document.querySelector('.ps-main');
    if (sidebar && main) {
        const mobile = window.matchMedia('(max-width: 760px)');
        const header = document.createElement('header');
        header.className = 'ps-mobile-header';
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'ps-menu-toggle';
        button.setAttribute('aria-label', 'Open navigation');
        button.setAttribute('aria-expanded', 'false');
        button.title = 'Open navigation';
        const symbol = document.createElement('span');
        symbol.className = 'ps-menu-symbol';
        symbol.setAttribute('aria-hidden', 'true');
        button.append(symbol);
        const brand = document.createElement('a');
        brand.href = 'dashboard.html';
        brand.textContent = 'ParishServe';
        header.append(button, brand);
        document.body.prepend(header);
        const backdrop = document.createElement('div');
        backdrop.className = 'ps-nav-backdrop';
        backdrop.hidden = true;
        document.body.append(backdrop);
        sidebar.id = 'parish-navigation';
        sidebar.tabIndex = -1;
        button.setAttribute('aria-controls', sidebar.id);
        sidebar.querySelectorAll('a').forEach(link => {
            const label = link.textContent.trim();
            if (label) { link.setAttribute('aria-label', label); link.title = label; }
        });
        let opened = false;
        const setOpen = (next, restoreFocus = true) => {
            opened = next && mobile.matches;
            document.body.classList.toggle('ps-nav-open', opened);
            button.setAttribute('aria-expanded', String(opened));
            button.setAttribute('aria-label', opened ? 'Close navigation' : 'Open navigation');
            button.title = opened ? 'Close navigation' : 'Open navigation';
            backdrop.hidden = !opened;
            sidebar.inert = mobile.matches && !opened;
            main.inert = opened;
            brand.inert = opened;
            if (opened) sidebar.querySelector('a')?.focus();
            else if (restoreFocus) button.focus();
        };
        button.addEventListener('click', () => setOpen(!opened));
        backdrop.addEventListener('click', () => setOpen(false));
        sidebar.addEventListener('click', event => {
            if (opened && event.target.closest('a')) setOpen(false);
        });
        document.addEventListener('keydown', event => {
            if (!opened) return;
            if (event.key === 'Escape') { event.preventDefault(); setOpen(false); }
            if (event.key === 'Tab') {
                const links = Array.from(sidebar.querySelectorAll('a[href], button:not([disabled])')).filter(item => item.getClientRects().length);
                const items = [button, ...links];
                const index = items.indexOf(document.activeElement);
                const next = event.shiftKey ? (index - 1 + items.length) % items.length : (index + 1) % items.length;
                event.preventDefault();
                items[next].focus();
            }
        });
        mobile.addEventListener('change', () => setOpen(false, false));
        document.body.classList.add('ps-has-mobile-nav');
        setOpen(false, false);
    }

    document.querySelectorAll('table').forEach(table => {
        const wrapper = document.createElement('div');
        wrapper.className = 'ps-table-scroll';
        wrapper.tabIndex = 0;
        wrapper.setAttribute('role', 'region');
        wrapper.setAttribute('aria-label', table.caption?.textContent || 'Table');
        table.before(wrapper);
        wrapper.append(table);
    });
    const calendar = document.querySelector('.cal-grid');
    const weekdays = document.querySelector('.cal-weekday-row');
    if (calendar && weekdays) {
        const wrapper = document.createElement('div');
        wrapper.className = 'ps-calendar-scroll';
        wrapper.tabIndex = 0;
        wrapper.setAttribute('role', 'region');
        wrapper.setAttribute('aria-label', 'Monthly parish events');
        weekdays.before(wrapper);
        wrapper.append(weekdays, calendar);
    }
})();
