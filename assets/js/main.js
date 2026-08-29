/**
 * main.js
 * ---------------------------------------------------------------------
 * Shared vanilla JS, loaded on every page (see includes/footer.php).
 * No framework, no build step -- just plain DOM stuff. Add new
 * page-agnostic behavior here; anything that's ONLY relevant to one
 * page should go in that page instead so this file doesn't turn into
 * a junk drawer.
 *
 * Right now this only does one thing: highlights whichever entry in
 * "Today at Our Lady of the Gate" is currently happening, based on the
 * visitor's own clock. It's a nice touch that works with zero backend
 * since it's just comparing times already sitting in the HTML.
 * ---------------------------------------------------------------------
 */

(function highlightCurrentScheduleSlot() {
    const items = document.querySelectorAll('.db-timeline-item[data-time]');
    if (!items.length) return;

    const now = new Date();
    const nowMinutes = now.getHours() * 60 + now.getMinutes();

    let activeItem = null;
    let activeMinutes = -1;

    items.forEach((item) => {
        const [h, m] = item.dataset.time.split(':').map(Number);
        const slotMinutes = (h * 60) + m;

        // pick the LATEST slot that has already started (<= now),
        // so at 11:00 AM "Morning Mass" (8AM) stays highlighted until
        // "Baptism Ceremony" (10AM) takes over, etc.
        if (slotMinutes <= nowMinutes && slotMinutes > activeMinutes) {
            activeItem = item;
            activeMinutes = slotMinutes;
        }
    });

    items.forEach((item) => item.classList.remove('is-now'));
    if (activeItem) activeItem.classList.add('is-now');
})();


/**
 * Featured Announcement carousel (announcements.php). Plain
 * show/hide of pre-rendered .ann-slide elements -- all slides are
 * already in the HTML (PHP looped over $featuredSlides), JS just
 * toggles which one has .is-active. No AJAX needed since there's
 * nothing to fetch; this stops being true once slides come from a
 * paginated query instead of a small hardcoded array.
 */
(function initAnnouncementCarousel() {
    const carousel = document.querySelector('[data-carousel]');
    if (!carousel) return;

    const slides = Array.from(carousel.querySelectorAll('[data-slide]'));
    const dots = Array.from(carousel.querySelectorAll('[data-carousel-dot]'));
    const prevBtn = carousel.querySelector('[data-carousel-prev]');
    const nextBtn = carousel.querySelector('[data-carousel-next]');
    if (!slides.length) return;

    let current = slides.findIndex((s) => s.classList.contains('is-active'));
    if (current < 0) current = 0;

    function goTo(index) {
        current = (index + slides.length) % slides.length;
        slides.forEach((s, i) => s.classList.toggle('is-active', i === current));
        dots.forEach((d, i) => d.classList.toggle('is-active', i === current));
    }

    if (prevBtn) prevBtn.addEventListener('click', () => goTo(current - 1));
    if (nextBtn) nextBtn.addEventListener('click', () => goTo(current + 1));
    dots.forEach((dot, i) => dot.addEventListener('click', () => goTo(i)));
})();


/**
 * Announcements list: category tabs + search box, both filtering the
 * SAME rows that are already in the DOM (data-category attribute set
 * by PHP). Once this list comes from a real query, "changing tabs"
 * would probably become a real GET request instead -- but for a
 * hardcoded page-full of rows, refetching the page to filter 7 rows
 * would be silly, so this stays client-side even after the backend
 * exists.
 */
(function initAnnouncementFilters() {
    const tabsWrap = document.querySelector('[data-filter-tabs]');
    const searchInput = document.querySelector('[data-announcement-search]');
    const rows = Array.from(document.querySelectorAll('[data-announcement-row]'));
    const emptyMsg = document.querySelector('[data-announcement-empty]');
    if (!rows.length) return;

    let activeCategory = 'All Announcements';

    function applyFilters() {
        const query = (searchInput ? searchInput.value : '').trim().toLowerCase();
        let visibleCount = 0;

        rows.forEach((row) => {
            const matchesCategory = activeCategory === 'All Announcements' || row.dataset.category === activeCategory;
            const rowText = row.textContent.toLowerCase();
            const matchesSearch = !query || rowText.includes(query);
            const show = matchesCategory && matchesSearch;
            row.classList.toggle('is-hidden', !show);
            if (show) visibleCount += 1;
        });

        if (emptyMsg) emptyMsg.classList.toggle('is-visible', visibleCount === 0);

        // once someone's filtering/searching, "Load more" (which only
        // ever reveals rows 6-7 by index) doesn't make sense anymore
        const loadMoreBtn = document.querySelector('[data-load-more]');
        if (loadMoreBtn) {
            const isFiltering = activeCategory !== 'All Announcements' || query.length > 0;
            loadMoreBtn.style.display = isFiltering ? 'none' : '';
        }
    }

    if (tabsWrap) {
        tabsWrap.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-filter-tab]');
            if (!btn) return;
            activeCategory = btn.dataset.filterTab;
            tabsWrap.querySelectorAll('.ps-tab').forEach((t) => t.classList.toggle('active', t === btn));
            applyFilters();
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }
})();


/**
 * "Load more" on the All Announcements list -- reveals the rows PHP
 * already rendered with class="is-hidden" (index 5+), then disables
 * itself since there's nothing further to reveal from a hardcoded
 * array. A real paginated version would fetch + append instead.
 */
(function initLoadMore() {
    const btn = document.querySelector('[data-load-more]');
    if (!btn) return;

    btn.addEventListener('click', () => {
        const hiddenRows = document.querySelectorAll('[data-announcement-row].is-hidden');
        hiddenRows.forEach((row) => row.classList.remove('is-hidden'));
        btn.textContent = 'No more announcements';
        btn.disabled = true;
    });
})();


/**
 * Bookmark/save toggle on each announcement row. Purely visual --
 * there's no database yet to persist a "saved announcements" list to,
 * and it resets on page reload. Swap this for a real fetch() POST to
 * a save-announcement.php endpoint once accounts exist.
 */
(function initBookmarkToggles() {
    document.querySelectorAll('[data-bookmark-btn]').forEach((btn) => {
        btn.addEventListener('click', () => {
            btn.classList.toggle('is-saved');
        });
    });
})();


/**
 * calendar.php: "All Categories" dropdown hides/shows the .cal-event
 * entries already rendered in the month grid by matching each one's
 * data-category against the select's value. Same "filter what's
 * already in the DOM" approach as the announcements page's tabs --
 * there are only ever ~30 events on screen at once, no reason to
 * refetch anything for that.
 */
(function initCalendarCategoryFilter() {
    const select = document.querySelector('[data-category-filter]');
    const scope = document.querySelector('[data-category-scope]');
    if (!select || !scope) return;

    select.addEventListener('change', () => {
        const chosen = select.value; // '' = All Categories
        scope.querySelectorAll('.cal-event').forEach((ev) => {
            const show = !chosen || ev.dataset.category === chosen;
            ev.classList.toggle('is-filtered-out', !show);
        });
    });
})();


/**
 * calendar.php: Month/Week/Day segmented control. Only "Month" is
 * actually built right now (see calendar.php's comment on
 * data-view-switch) -- this just swaps which button LOOKS active so
 * the control doesn't feel dead. Wiring up real Week/Day rendering
 * later means adding an actual view-switch branch here instead of
 * just toggling .active.
 */
(function initCalendarViewSwitch() {
    const group = document.querySelector('[data-view-switch]');
    if (!group) return;

    group.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-view]');
        if (!btn) return;
        group.querySelectorAll('button').forEach((b) => b.classList.toggle('active', b === btn));
    });
})();


/**
 * Any multi-step request form whose NEXT step genuinely isn't built
 * yet -- currently wedding-request-step2.php's own "Save and
 * Continue" (Step 3, Review & Send, doesn't exist). Letting the form
 * actually submit would just 404, a bad look for what's visually the
 * page's main call-to-action. Instead we validate with the browser's
 * normal HTML5 validation (required/pattern/type all still work via
 * reportValidity()), and if that passes, show an inline notice
 * explaining the next step isn't built instead of navigating anywhere.
 * Once a step's "next" page is real (like Step 1 -> Step 2 now is),
 * drop the data-wizard-step-form attribute from that form so it goes
 * back to submitting normally -- see wedding-request.php's comment.
 */
(function initWizardStepForm() {
    const form = document.querySelector('[data-wizard-step-form]');
    if (!form) return;

    const notice = form.querySelector('[data-wizard-notice]');

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        if (!form.reportValidity()) return; // let the browser show its normal field errors
        if (notice) {
            notice.hidden = false;
            notice.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    });
})();


/**
 * wedding-request-step2.php's file uploads. Browsers can't enforce a
 * max file size purely through HTML attributes, so this checks
 * `file.size` against each input's data-max-size-mb on change and, if
 * it's too big, clears the input and shows an inline error right next
 * to that row instead of letting an oversized file silently sit there
 * until a server that doesn't exist yet would've rejected it. Generic
 * by design (matches on the data attribute, not a specific page) so
 * any future upload form -- baptism/funeral documents, etc. -- gets
 * the same behavior for free just by adding the attribute.
 */
(function initFileUploadValidation() {
    // id-based lookup first (an input id="foo" pairs with an error
    // element id="fooError", same convention setAuthFieldError uses),
    // falling back to the structural search wedding-request-step2.php's
    // rows still rely on (no matching id on those error spans) --
    // works for both without every existing file input needing an
    // id+"Error" pair retrofitted.
    function findFileErrorElement(input) {
        if (input.id) {
            const byId = document.getElementById(input.id + 'Error');
            if (byId) return byId;
        }
        return input.closest('.wr-req-upload, .ps-field')?.querySelector('[data-file-error]') || null;
    }

    document.querySelectorAll('input[type="file"][data-max-size-mb]').forEach((input) => {
        input.addEventListener('change', () => {
            const maxMb = parseFloat(input.dataset.maxSizeMb);
            const errorEl = findFileErrorElement(input);
            const file = input.files && input.files[0];
            if (!file) return;

            const tooBig = file.size > maxMb * 1024 * 1024;
            if (errorEl) {
                errorEl.hidden = !tooBig;
                errorEl.textContent = tooBig
                    ? `"${file.name}" is too large (max ${maxMb}MB). Please choose a smaller file.`
                    : '';
            }
            if (tooBig) input.value = '';
        });
    });
})();


/**
 * Drag-and-drop upload zones (.ps-dropzone), first used on baptism-
 * request-step2.php. The native file input already covers the whole
 * box (see style.css), so clicking anywhere just works natively --
 * this only adds what native <input type="file"> can't do on its own:
 * drag-enter/over/leave visual feedback, accepting a dropped file by
 * assigning it to the real input (so form submission and the file-
 * size validator above both still see it), and keeping the "No file
 * chosen" text in sync however a file arrives.
 */
(function initDropzones() {
    document.querySelectorAll('[data-dropzone]').forEach((zone) => {
        const input = zone.querySelector('[data-dropzone-input]');
        const filenameEl = zone.querySelector('[data-dropzone-filename]');
        if (!input) return;

        function updateFilename() {
            if (!filenameEl) return;
            const file = input.files && input.files[0];
            filenameEl.textContent = file ? file.name : 'No file chosen';
        }
        input.addEventListener('change', updateFilename);

        ['dragenter', 'dragover'].forEach((evt) => {
            zone.addEventListener(evt, (e) => {
                e.preventDefault();
                zone.classList.add('is-dragover');
            });
        });
        ['dragleave', 'dragend'].forEach((evt) => {
            zone.addEventListener(evt, () => zone.classList.remove('is-dragover'));
        });
        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            zone.classList.remove('is-dragover');
            const files = e.dataTransfer && e.dataTransfer.files;
            if (files && files.length) {
                input.files = files;
                // let the file-size validator and the filename label
                // both react the same way a real picker selection would
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    });
})();


/**
 * Live "N / max" character counters for any <textarea> that has one.
 * Pairs by id convention (textarea id="foo" -> counter id="fooCount"),
 * same pattern used elsewhere in this file. Generic, so any future
 * textarea gets a working counter just by adding the matching span.
 */
(function initCharacterCounters() {
    document.querySelectorAll('textarea[maxlength]').forEach((textarea) => {
        const counter = document.getElementById(textarea.id + 'Count');
        if (!counter) return;
        const max = textarea.getAttribute('maxlength');

        function update() {
            counter.textContent = `${textarea.value.length} / ${max}`;
        }
        textarea.addEventListener('input', update);
        update();
    });
})();


/**
 * Custom popup-calendar date picker. First built for baptism-request.
 * php's "Preferred Regular Baptism Date" field, which needs future
 * Saturdays ONLY selectable -- a native <input type="date"> can't have
 * its own browser-drawn calendar popup styled to grey out arbitrary
 * weekdays (that popup lives in a closed shadow DOM), so this renders
 * a real month grid into a plain <div> instead.
 *
 * Markup contract, driven entirely by data attributes so any future
 * field can reuse this with different rules:
 *   <div class="ps-datepicker" data-datepicker data-weekday="6">
 *     <input data-datepicker-input id="foo" ...>
 *     <button data-datepicker-toggle>...</button>
 *     <div data-datepicker-panel hidden></div>
 *   </div>
 *   data-weekday: JS Date#getDay() value (0=Sun...6=Sat) that's the
 *     ONLY allowed weekday, e.g. "6" for Saturday-only. Omit the
 *     attribute entirely to allow any future date regardless of
 *     weekday. Read fresh on every render (not cached at init) so
 *     another script -- e.g. initBaptismTypeToggle() below -- can
 *     flip it live when a Regular/Special radio changes.
 * Past dates (today included) are always disabled; "future" always
 * means strictly after today, computed from the browser's actual
 * clock, never a hard-coded date.
 *
 * Typing a date manually is validated the same way clicking one is:
 * on blur/change the typed text is parsed strictly (no lenient
 * `new Date(str)` rollovers like "02/30" silently becoming March 2),
 * and an invalid or disallowed date sets the input's native
 * validationMessage via setCustomValidity() PLUS shows the matching
 * id+"Error" element (same id-based convention as the file-upload and
 * auth-field error helpers elsewhere in this file) -- so a bypass
 * attempt (typing straight into the field, or a fully JS-disabled
 * browser) still can't get an invalid value past the browser's own
 * form submission, and the real backend re-checks it again regardless
 * per baptism-request.php's PHP-side DateTime validation.
 */
(function initDatepickers() {
    const roots = document.querySelectorAll('[data-datepicker]');
    if (!roots.length) return;

    const MONTH_NAMES = ['January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'];
    const WEEKDAY_LABELS = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];

    // same markup ps_icon() echoes server-side for 'chevron-left'/
    // 'chevron-right' (includes/icons.php) -- kept as an exact copy
    // here (not the raw "‹ ›" text glyphs the panel used at first)
    // so the nav buttons render with the identical hand-drawn icon
    // style as every other icon on the page, not a font-dependent
    // punctuation character.
    const ICON_SVG_OPEN = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">';
    const CHEVRON_LEFT = ICON_SVG_OPEN + '<path d="M15 6l-6 6 6 6"/></svg>';
    const CHEVRON_RIGHT = ICON_SVG_OPEN + '<path d="M9 6l6 6-6 6"/></svg>';

    function startOfDay(d) {
        return new Date(d.getFullYear(), d.getMonth(), d.getDate());
    }

    function formatMDY(date) {
        const mm = String(date.getMonth() + 1).padStart(2, '0');
        const dd = String(date.getDate()).padStart(2, '0');
        return `${mm}/${dd}/${date.getFullYear()}`;
    }

    // strict mm/dd/yyyy parse, rejecting rollovers (e.g. "02/30/2026")
    // that `new Date("02/30/2026")` would silently accept as March 2
    function parseMDY(str) {
        const m = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec((str || '').trim());
        if (!m) return null;
        const month = parseInt(m[1], 10);
        const day = parseInt(m[2], 10);
        const year = parseInt(m[3], 10);
        const date = new Date(year, month - 1, day);
        if (date.getFullYear() !== year || date.getMonth() !== month - 1 || date.getDate() !== day) return null;
        return date;
    }

    roots.forEach((root) => {
        const input = root.querySelector('[data-datepicker-input]');
        const toggle = root.querySelector('[data-datepicker-toggle]');
        const panel = root.querySelector('[data-datepicker-panel]');
        if (!input || !panel) return;

        const errorEl = input.id ? document.getElementById(input.id + 'Error') : null;
        const today = startOfDay(new Date());
        let viewDate = new Date(today.getFullYear(), today.getMonth(), 1);
        let selected = null;

        function requiredWeekday() {
            return root.dataset.weekday !== undefined ? parseInt(root.dataset.weekday, 10) : null;
        }

        function isDisabled(date) {
            if (date <= today) return true; // must be strictly future
            const weekday = requiredWeekday();
            if (weekday !== null && date.getDay() !== weekday) return true;
            return false;
        }

        function setError(message) {
            input.setCustomValidity(message || '');
            if (errorEl) {
                errorEl.textContent = message || '';
                errorEl.hidden = !message;
            }
        }

        function validateCurrentValue() {
            const raw = input.value.trim();
            if (!raw) { setError(''); selected = null; return; }
            const parsed = parseMDY(raw);
            if (!parsed) {
                setError('Enter a valid date in mm/dd/yyyy format.');
                selected = null;
                return;
            }
            const date = startOfDay(parsed);
            if (isDisabled(date)) {
                setError(requiredWeekday() !== null
                    ? 'Please choose a future Saturday. That date is not available.'
                    : 'Please choose a future date.');
                selected = null;
                return;
            }
            setError('');
            selected = date;
            viewDate = new Date(date.getFullYear(), date.getMonth(), 1);
        }

        function closePanel() {
            panel.hidden = true;
            panel.classList.remove('is-flipped');
        }

        function openPanel() {
            render();
            panel.hidden = false;
            panel.classList.remove('is-flipped');
            // a field near the bottom of a long page would otherwise
            // open this panel further down than the page itself,
            // stretching the whole document taller than the sticky
            // sidebar -- flip it above the input instead whenever
            // there isn't enough room below (and there IS enough room
            // above), same as most date-picker widgets do
            const inputRect = input.getBoundingClientRect();
            const spaceBelow = window.innerHeight - inputRect.bottom;
            const spaceAbove = inputRect.top;
            if (spaceBelow < panel.offsetHeight + 16 && spaceAbove > spaceBelow) {
                panel.classList.add('is-flipped');
            }
        }

        function render() {
            const year = viewDate.getFullYear();
            const month = viewDate.getMonth();
            const firstWeekday = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const prevDisabled = year === today.getFullYear() && month === today.getMonth();

            let html = '';
            html += '<div class="ps-datepicker-head">'
                + `<button type="button" class="ps-datepicker-nav" data-dp-prev ${prevDisabled ? 'disabled' : ''} aria-label="Previous month">${CHEVRON_LEFT}</button>`
                + `<strong>${MONTH_NAMES[month]} ${year}</strong>`
                + `<button type="button" class="ps-datepicker-nav" data-dp-next aria-label="Next month">${CHEVRON_RIGHT}</button>`
                + '</div>';
            html += `<div class="ps-datepicker-weekdays">${WEEKDAY_LABELS.map((d) => `<span>${d}</span>`).join('')}</div>`;
            html += '<div class="ps-datepicker-days">';
            for (let i = 0; i < firstWeekday; i++) {
                html += '<span class="ps-datepicker-day is-muted"></span>';
            }
            for (let d = 1; d <= daysInMonth; d++) {
                const date = new Date(year, month, d);
                const disabled = isDisabled(date);
                const isSel = selected && date.getTime() === selected.getTime();
                const isToday = date.getTime() === today.getTime();
                const cls = ['ps-datepicker-day', disabled ? 'is-disabled' : 'is-selectable', isSel ? 'is-selected' : '', isToday ? 'is-today' : '']
                    .filter(Boolean).join(' ');
                html += `<button type="button" class="${cls}" ${disabled ? 'disabled' : ''} data-dp-day="${d}">${d}</button>`;
            }
            html += '</div>';
            panel.innerHTML = html;

            panel.querySelector('[data-dp-prev]')?.addEventListener('click', (e) => {
                // render() below replaces panel.innerHTML, which detaches
                // THIS button (the click's own target) from the DOM while
                // the click is still bubbling up toward the document-level
                // "click outside closes the panel" listener. Once detached,
                // root.contains(e.target) reads false there and the whole
                // panel would close itself right after switching months --
                // stopping propagation here keeps that outside-click
                // listener from ever seeing this click at all.
                e.stopPropagation();
                viewDate = new Date(year, month - 1, 1);
                render();
            });
            panel.querySelector('[data-dp-next]')?.addEventListener('click', (e) => {
                e.stopPropagation();
                viewDate = new Date(year, month + 1, 1);
                render();
            });
            panel.querySelectorAll('[data-dp-day]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    selected = new Date(year, month, parseInt(btn.dataset.dpDay, 10));
                    input.value = formatMDY(selected);
                    setError('');
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                    closePanel();
                });
            });
        }

        input.addEventListener('focus', openPanel);
        input.addEventListener('click', openPanel);
        toggle?.addEventListener('click', () => (panel.hidden ? openPanel() : closePanel()));
        input.addEventListener('blur', validateCurrentValue);
        input.addEventListener('change', validateCurrentValue);
        document.addEventListener('click', (e) => {
            if (!root.contains(e.target)) closePanel();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !panel.hidden) closePanel();
        });

        validateCurrentValue();
    });
})();


/**
 * baptism-request.php's "1. Baptism Type" Regular/Special radio cards.
 * Toggles the .is-checked highlight, and (since only Regular Baptism
 * is Saturday-only) flips the shared date field between the two modes:
 * swapping its label/hint text and turning the datepicker's
 * Saturday-only restriction on or off via the same data-weekday
 * attribute initDatepickers() reads. Generic enough for any future
 * sacrament page with more than one request "type" to reuse.
 */
/**
 * Generic radio-card highlight: any group of label-wrapped radio
 * "cards" (baptism-request.php's Regular/Special Baptism type cards,
 * mass-intention-request.php's 5-way Intent Type cards) gets an
 * `.is-checked` class on whichever card's radio is currently selected.
 * Markup contract: a container with `data-radio-cards` holding one or
 * more `[data-radio-card]` elements, each wrapping exactly one
 * `input[type="radio"]`. Page-specific behavior beyond the highlight
 * itself (e.g. baptism's date-field toggling) stays in its own
 * separate handler rather than growing this one.
 */
(function initRadioCardGroups() {
    document.querySelectorAll('[data-radio-cards]').forEach((group) => {
        function sync() {
            group.querySelectorAll('[data-radio-card]').forEach((card) => {
                card.classList.toggle('is-checked', card.querySelector('input[type="radio"]').checked);
            });
        }
        group.querySelectorAll('input[type="radio"]').forEach((r) => r.addEventListener('change', sync));
        sync();
    });
})();


(function initTypeCardToggle() {
    const radios = document.querySelectorAll('[data-type-toggle] input[type="radio"]');
    if (!radios.length) return;

    const group = document.querySelector('[data-type-toggle]');
    const dateRoot = document.querySelector('[data-datepicker][data-regular-weekday]');
    const dateTypeWord = document.getElementById('baptismDateTypeWord');
    const dateLabel = document.getElementById('baptismDateLabel');
    const dateHint = document.getElementById('baptismDateHint');
    const dateInput = document.getElementById('baptismDate');
    const banner = document.getElementById('baptismTypeBanner');
    const dateTipBanner = document.getElementById('baptismDateTipBanner');

    // card highlighting itself is handled by initRadioCardGroups() above
    // (this container also carries data-radio-cards/data-radio-card) --
    // this function only owns the date-field-specific side effects
    function apply(resetValue) {
        const checked = group.querySelector('input[type="radio"]:checked');
        const isRegular = !checked || checked.value === 'regular';

        if (dateRoot) {
            if (isRegular) {
                dateRoot.dataset.weekday = dateRoot.dataset.regularWeekday;
            } else {
                delete dateRoot.dataset.weekday;
            }
        }
        if (dateTypeWord) dateTypeWord.textContent = isRegular ? 'Regular' : 'Special';
        if (dateLabel) dateLabel.textContent = isRegular ? 'Select a future Saturday' : 'Select a preferred date';
        if (dateHint) {
            dateHint.textContent = isRegular
                ? 'Regular Baptism is scheduled every Saturday.'
                : 'Choose your preferred date. Final confirmation is subject to parish approval and availability.';
        }
        if (banner) banner.hidden = !isRegular;
        // Special has only the one simple "must be a future date" rule,
        // so a short standing note is proportionate there -- Regular's
        // Saturday-only rule is already visible in the greyed-out
        // calendar itself, so it stays without a persistent banner
        if (dateTipBanner) dateTipBanner.hidden = isRegular;

        // a date picked under one mode's rule (e.g. a Saturday) can be
        // stale once the rule changes, so clear it rather than leaving
        // a value on screen the current mode wouldn't have allowed
        if (resetValue && dateInput) {
            dateInput.value = '';
            dateInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    radios.forEach((r) => r.addEventListener('change', () => apply(true)));
    apply(false); // reflect whichever radio is checked on load (incl. PHP-repopulated state) without wiping it
})();


/**
 * donations.php's "Donation Purpose" fund picker -- a custom dropdown
 * rather than a plain <select>, since its closed state needs to show
 * an icon + title + description together (a native select's own
 * closed-state rendering can't do that). Same open/close/outside-
 * click/Escape mechanics as initDatepickers() above, just a plain
 * option list instead of a calendar grid.
 *
 * Markup contract:
 *   <div class="ps-fund-picker" data-fund-picker>
 *     <button data-fund-trigger>
 *       <span data-fund-icon>...</span>
 *       <strong data-fund-label>...</strong>
 *       <small data-fund-desc>...</small>
 *     </button>
 *     <input type="hidden" data-fund-input>
 *     <div data-fund-panel hidden>
 *       <button data-fund-option data-value="..." data-icon="<svg>...</svg>">...</button>
 *       ...
 *     </div>
 *   </div>
 * Each option's data-icon holds its own pre-rendered <svg> markup
 * (echoed server-side by ps_icon() into the option button already, and
 * just read back out of it here) so the trigger can swap icons without
 * this file needing its own duplicate copy of every icon's path data.
 */
(function initFundPickers() {
    document.querySelectorAll('[data-fund-picker]').forEach((root) => {
        const trigger = root.querySelector('[data-fund-trigger]');
        const panel = root.querySelector('[data-fund-panel]');
        const input = root.querySelector('[data-fund-input]');
        const iconEl = root.querySelector('[data-fund-icon]');
        const labelEl = root.querySelector('[data-fund-label]');
        const descEl = root.querySelector('[data-fund-desc]');
        if (!trigger || !panel || !input) return;

        function closePanel() { panel.hidden = true; }
        function openPanel() { panel.hidden = false; }

        function selectOption(option) {
            input.value = option.dataset.value;
            if (labelEl) labelEl.textContent = option.querySelector('strong')?.textContent || '';
            if (descEl) descEl.textContent = option.dataset.desc || '';
            if (iconEl) {
                const optionIcon = option.querySelector('svg');
                if (optionIcon) iconEl.innerHTML = optionIcon.outerHTML;
            }
            root.querySelectorAll('[data-fund-option]').forEach((o) => o.classList.toggle('is-selected', o === option));
            input.dispatchEvent(new Event('change', { bubbles: true }));
            closePanel();
        }

        trigger.addEventListener('click', () => (panel.hidden ? openPanel() : closePanel()));
        root.querySelectorAll('[data-fund-option]').forEach((option) => {
            option.addEventListener('click', () => selectOption(option));
        });
        document.addEventListener('click', (e) => {
            if (!root.contains(e.target)) closePanel();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !panel.hidden) closePanel();
        });
    });
})();


/**
 * donations.php's "I would like to remain anonymous" checkbox --
 * disables (and clears) the Full Name field while checked, since a
 * name typed there wouldn't make sense to keep once anonymity is
 * requested. Not shown in the reference image explicitly, but a
 * natural, low-risk expectation for this exact combination of fields.
 */
(function initAnonymousDonationToggle() {
    const checkbox = document.getElementById('isAnonymous');
    const nameField = document.getElementById('donorName');
    if (!checkbox || !nameField) return;

    function sync() {
        nameField.disabled = checkbox.checked;
        if (checkbox.checked) nameField.value = '';
    }
    checkbox.addEventListener('change', sync);
    sync();
})();


/**
 * wedding-request-step3.php's "I confirm" toggle. Starts unchecked
 * (see that page's file header for why we deviated from the reference
 * image showing it pre-switched-on), so the Submit button starts
 * disabled and only becomes clickable once the user actually flips
 * the toggle themselves. Generic on the data attributes, not the
 * page, so any future "you must agree before submitting" form gets
 * the same behavior for free.
 */
(function initConfirmToggle() {
    const toggle = document.querySelector('[data-confirm-toggle]');
    const submitBtn = document.querySelector('[data-confirm-submit]');
    if (!toggle || !submitBtn) return;

    const sync = () => { submitBtn.disabled = !toggle.checked; };
    toggle.addEventListener('change', sync);
    sync();
})();


/**
 * index.php's public navbar mobile menu. Toggle button flips the
 * panel's `hidden` attribute and keeps aria-expanded in sync (screen
 * readers rely on that, not just the visual state) -- landing.css
 * uses that same aria-expanded value to swap which of the two icons
 * (menu/close) is visible inside the button, so this never touches
 * icon markup directly and can't drift out of sync with icons.php.
 * Also closes on Escape and whenever a link inside the menu is
 * actually clicked -- without that second bit, tapping "Services"
 * would leave the mobile menu open, hovering over the newly-scrolled-
 * to content underneath.
 */
(function initMobileMenu() {
    const toggle = document.querySelector('[data-mobile-menu-toggle]');
    const menu = document.querySelector('[data-mobile-menu]');
    if (!toggle || !menu) return;

    function setOpen(isOpen) {
        menu.hidden = !isOpen;
        toggle.setAttribute('aria-expanded', String(isOpen));
    }

    toggle.addEventListener('click', () => setOpen(menu.hidden));
    menu.addEventListener('click', (e) => {
        if (e.target.closest('a')) setOpen(false);
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !menu.hidden) setOpen(false);
    });
})();


/**
 * Password show/hide toggle. Generic on [data-password-toggle] sitting
 * next to a <input type="password">, so login.php today and
 * register.php later both get this for free. Icon swap is CSS-driven
 * off the button's own aria-pressed state (see login.css) -- same
 * "don't rebuild icon markup in JS" approach as the mobile menu toggle.
 */
(function initPasswordToggles() {
    document.querySelectorAll('[data-password-toggle]').forEach((btn) => {
        const input = btn.closest('.ps-field-icon')?.querySelector('input[type="password"], input[type="text"]');
        if (!input) return;

        btn.addEventListener('click', () => {
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            btn.setAttribute('aria-pressed', String(!showing));
            btn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
        });
    });
})();


/**
 * Shared by login.php AND register.php's validation (below). Finds
 * each field's error <small> by ID convention -- an input with
 * id="foo" pairs with an error element id="fooError" (every auth field
 * in both forms follows this, including the register page's checkbox)
 * -- so this doesn't care whether the input sits inside a .auth-field
 * wrapper or not, unlike an earlier version that used .closest() and
 * would've silently done nothing for the checkbox row.
 */
function setAuthFieldError(input, message) {
    const errorEl = document.getElementById(input.id + 'Error');
    const wrap = input.closest('.auth-field');

    if (errorEl) {
        errorEl.textContent = message || '';
        errorEl.hidden = !message;
    }
    if (wrap) wrap.classList.toggle('has-error', Boolean(message));

    if (message) input.setAttribute('aria-invalid', 'true');
    else input.removeAttribute('aria-invalid');
}

/**
 * login.php's form. There's no backend to authenticate against (see
 * that file's header comment) so this validates for real -- exact
 * wording the group specified, shown next to the relevant field, focus
 * moved to the first invalid field -- and only once both fields pass
 * does it reveal the "not wired up yet" notice, rather than faking a
 * success or failure. Written generically enough (matching on
 * data-login-form / data-field-error="email|password") that swapping
 * in a real fetch()-based submit later is a contained change inside
 * this one function, not a rewrite of the validation itself.
 */
(function initLoginForm() {
    const form = document.querySelector('[data-login-form]');
    if (!form) return;

    const emailInput = document.getElementById('loginEmail');
    const passwordInput = document.getElementById('loginPassword');
    const alertBox = document.querySelector('[data-auth-alert]');
    const submitBtn = document.querySelector('[data-login-submit]');
    const submitLabel = submitBtn ? submitBtn.querySelector('[data-submit-label]') : null;
    if (!emailInput || !passwordInput) return;

    function validateEmail() {
        const value = emailInput.value.trim();
        if (!value) { setAuthFieldError(emailInput, 'Please enter your email address.'); return false; }
        // permissive shape check matching what type="email" already
        // constrains natively -- not exhaustive RFC 5322, doesn't need
        // to be for a client-side "did you typo this" hint
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            setAuthFieldError(emailInput, 'Please enter a valid email address.');
            return false;
        }
        setAuthFieldError(emailInput, null);
        return true;
    }

    function validatePassword() {
        if (!passwordInput.value) { setAuthFieldError(passwordInput, 'Please enter your password.'); return false; }
        setAuthFieldError(passwordInput, null);
        return true;
    }

    // re-validate as they type, but only once a field has already
    // shown an error -- don't nag someone who hasn't finished typing
    // their first pass through the form
    emailInput.addEventListener('blur', validateEmail);
    passwordInput.addEventListener('blur', validatePassword);
    emailInput.addEventListener('input', () => {
        if (emailInput.closest('.auth-field').classList.contains('has-error')) validateEmail();
    });
    passwordInput.addEventListener('input', () => {
        if (passwordInput.closest('.auth-field').classList.contains('has-error')) validatePassword();
    });

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        if (alertBox) alertBox.hidden = true;

        const emailOk = validateEmail();
        const passwordOk = validatePassword();

        if (!emailOk) { emailInput.focus(); return; }
        if (!passwordOk) { passwordInput.focus(); return; }

        // both fields pass -- disable + show a loading state so a
        // double-click can't submit twice, same as a real network
        // request would need
        if (submitBtn) { submitBtn.disabled = true; submitBtn.classList.add('is-loading'); }
        if (submitLabel) submitLabel.textContent = 'Logging in…';

        window.setTimeout(() => {
            if (submitBtn) { submitBtn.disabled = false; submitBtn.classList.remove('is-loading'); }
            if (submitLabel) submitLabel.textContent = 'Log In';

            // never re-populate a password field after an attempt;
            // email is left alone on purpose so the user doesn't have
            // to retype it
            passwordInput.value = '';

            // focus goes to the alert (not back into the password
            // field) so screen readers land on the notice that just
            // appeared, per "return focus appropriately"
            if (alertBox) {
                alertBox.hidden = false;
                alertBox.focus();
            }
        }, 650);
    });
})();


/**
 * register.php's form. Same "real validation, honest not-wired-up
 * notice" approach as login (see initLoginForm above, and
 * setAuthFieldError). A few differences specific to registration:
 * Confirm Password re-validates whenever Password changes (so a
 * mismatch error updates live instead of going stale), and the "I
 * confirm..." checkbox gets the exact same field-error treatment as
 * every text input via setAuthFieldError's id+'Error' lookup -- no
 * special-casing needed since it already has agreeTruthfulError in
 * the markup.
 */
(function initRegisterForm() {
    const form = document.querySelector('[data-register-form]');
    if (!form) return;

    const fields = {
        firstName: document.getElementById('firstName'),
        lastName: document.getElementById('lastName'),
        dateOfBirth: document.getElementById('dateOfBirth'),
        gender: document.getElementById('gender'),
        email: document.getElementById('registerEmail'),
        mobileNumber: document.getElementById('mobileNumber'),
        password: document.getElementById('registerPassword'),
        confirmPassword: document.getElementById('confirmPassword'),
        agreeTruthful: document.getElementById('agreeTruthful'),
    };
    if (Object.values(fields).some((el) => !el)) return;

    const alertBox = document.querySelector('[data-auth-alert]');
    const submitBtn = document.querySelector('[data-register-submit]');
    const submitLabel = submitBtn ? submitBtn.querySelector('[data-submit-label]') : null;

    const validators = {
        firstName: () => {
            if (!fields.firstName.value.trim()) { setAuthFieldError(fields.firstName, 'Please enter your first name.'); return false; }
            setAuthFieldError(fields.firstName, null); return true;
        },
        lastName: () => {
            if (!fields.lastName.value.trim()) { setAuthFieldError(fields.lastName, 'Please enter your last name.'); return false; }
            setAuthFieldError(fields.lastName, null); return true;
        },
        dateOfBirth: () => {
            if (!fields.dateOfBirth.value) { setAuthFieldError(fields.dateOfBirth, 'Please enter your date of birth.'); return false; }
            setAuthFieldError(fields.dateOfBirth, null); return true;
        },
        gender: () => {
            if (!fields.gender.value) { setAuthFieldError(fields.gender, 'Please select your gender.'); return false; }
            setAuthFieldError(fields.gender, null); return true;
        },
        email: () => {
            const value = fields.email.value.trim();
            if (!value) { setAuthFieldError(fields.email, 'Please enter your email address.'); return false; }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) { setAuthFieldError(fields.email, 'Please enter a valid email address.'); return false; }
            setAuthFieldError(fields.email, null); return true;
        },
        mobileNumber: () => {
            const value = fields.mobileNumber.value.trim();
            if (!value) { setAuthFieldError(fields.mobileNumber, 'Please enter your mobile number.'); return false; }
            if (!/^09\d{9}$/.test(value)) { setAuthFieldError(fields.mobileNumber, 'Please enter a valid 11-digit mobile number (e.g. 09XXXXXXXXX).'); return false; }
            setAuthFieldError(fields.mobileNumber, null); return true;
        },
        password: () => {
            if (!fields.password.value) { setAuthFieldError(fields.password, 'Please create a password.'); return false; }
            if (fields.password.value.length < 8) { setAuthFieldError(fields.password, 'Password must be at least 8 characters.'); return false; }
            setAuthFieldError(fields.password, null); return true;
        },
        confirmPassword: () => {
            if (!fields.confirmPassword.value) { setAuthFieldError(fields.confirmPassword, 'Please confirm your password.'); return false; }
            if (fields.confirmPassword.value !== fields.password.value) { setAuthFieldError(fields.confirmPassword, 'Passwords do not match.'); return false; }
            setAuthFieldError(fields.confirmPassword, null); return true;
        },
        agreeTruthful: () => {
            if (!fields.agreeTruthful.checked) { setAuthFieldError(fields.agreeTruthful, 'Please confirm that the information provided is true and correct.'); return false; }
            setAuthFieldError(fields.agreeTruthful, null); return true;
        },
    };

    const order = ['firstName', 'lastName', 'dateOfBirth', 'gender', 'email', 'mobileNumber', 'password', 'confirmPassword', 'agreeTruthful'];

    order.forEach((key) => {
        const el = fields[key];
        const evt = el.type === 'checkbox' ? 'change' : (el.tagName === 'SELECT' ? 'change' : 'blur');
        el.addEventListener(evt, validators[key]);

        if (el.tagName !== 'SELECT' && el.type !== 'checkbox') {
            el.addEventListener('input', () => {
                if (el.closest('.auth-field')?.classList.contains('has-error')) validators[key]();
            });
        }
    });

    // Confirm Password depends on Password -- keep it live once it's
    // been filled in, so fixing the password also clears a stale
    // "Passwords do not match" instead of leaving it hanging
    fields.password.addEventListener('input', () => {
        if (fields.confirmPassword.value) validators.confirmPassword();
    });

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        if (alertBox) alertBox.hidden = true;

        let firstInvalid = null;
        order.forEach((key) => {
            const ok = validators[key]();
            if (!ok && !firstInvalid) firstInvalid = fields[key];
        });

        if (firstInvalid) { firstInvalid.focus(); return; }

        if (submitBtn) { submitBtn.disabled = true; submitBtn.classList.add('is-loading'); }
        if (submitLabel) submitLabel.textContent = 'Creating account…';

        window.setTimeout(() => {
            if (submitBtn) { submitBtn.disabled = false; submitBtn.classList.remove('is-loading'); }
            if (submitLabel) submitLabel.textContent = 'Create Account';

            // clear both password fields after the attempt, same
            // reasoning as login: never leave sensitive input sitting
            // in the DOM after a submit that didn't go anywhere real
            fields.password.value = '';
            fields.confirmPassword.value = '';

            if (alertBox) {
                alertBox.hidden = false;
                alertBox.focus();
            }
        }, 650);
    });
})();


/**
 * A <select> has no native "placeholder" concept the way a text input
 * does -- its hint option ("Select suffix", "Select gender") renders
 * in the exact same color a real chosen answer would, which reads as
 * already-filled-in and looks inconsistent next to an actual empty
 * text field's lighter placeholder text right beside it. This just
 * toggles .is-placeholder while the current value is the empty hint
 * option; style.css dims the text for exactly that state (see
 * ".ps-field select.is-placeholder"). Runs on every <select> on the
 * page, not just form ones -- harmless where no matching CSS rule
 * exists (e.g. calendar.php's toolbar filter), so it doesn't need to
 * know which selects "count".
 */
(function initSelectPlaceholderStyling() {
    function sync(select) {
        select.classList.toggle('is-placeholder', select.value === '');
    }
    document.querySelectorAll('select').forEach((select) => {
        sync(select);
        select.addEventListener('change', () => sync(select));
    });
})();


/**
 * Section tab bar scroll-spy, first used on baptism-guidelines.php's
 * Guidelines/Required Document/Types & Fees/FAQ tabs. Clicking a tab
 * already jumps to its anchor for free (plain <a href="#id">, no JS
 * needed for that part) -- this only handles the OTHER direction:
 * keeping the correct tab marked .active as someone scrolls past each
 * section on their own, using IntersectionObserver instead of a
 * scroll-position math (simpler, and the browser does the work).
 * rootMargin shrinks the "counts as visible" zone to roughly the top
 * 40% of the viewport, so the active tab doesn't flicker between two
 * sections while their shared boundary is mid-screen.
 */
(function initSectionTabScrollSpy() {
    const tabsWrap = document.querySelector('[data-scroll-spy-tabs]');
    const sections = document.querySelectorAll('[data-scroll-spy-section]');
    if (!tabsWrap || !sections.length || typeof IntersectionObserver === 'undefined') return;

    const tabs = tabsWrap.querySelectorAll('[data-scroll-spy-tab]');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            const id = entry.target.dataset.scrollSpySection;
            tabs.forEach((tab) => tab.classList.toggle('active', tab.dataset.scrollSpyTab === id));
        });
    }, {
        rootMargin: '-96px 0px -60% 0px',
        threshold: 0,
    });

    sections.forEach((section) => observer.observe(section));
})();
