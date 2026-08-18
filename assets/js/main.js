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
    document.querySelectorAll('input[type="file"][data-max-size-mb]').forEach((input) => {
        input.addEventListener('change', () => {
            const maxMb = parseFloat(input.dataset.maxSizeMb);
            const errorEl = input.closest('.wr-req-upload')?.querySelector('[data-file-error]');
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
