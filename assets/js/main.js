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
 * Featured Announcement carousel (announcements.html). Shows/hides the
 * .ann-slide elements announcements.js renders from the database, so it
 * sets itself up again on 'ps:announcements-rendered'.
 */
(function initAnnouncementCarousel() {
    setup();
    document.addEventListener('ps:announcements-rendered', setup);

    function setup() {
    const carousel = document.querySelector('[data-carousel]');
    if (!carousel || carousel.dataset.carouselReady) return;

    const slides = Array.from(carousel.querySelectorAll('[data-slide]'));
    const dots = Array.from(carousel.querySelectorAll('[data-carousel-dot]'));
    const prevBtn = carousel.querySelector('[data-carousel-prev]');
    const nextBtn = carousel.querySelector('[data-carousel-next]');
    if (!slides.length) return;
    carousel.dataset.carouselReady = 'true';

    let current = slides.findIndex((s) => s.classList.contains('is-active'));
    if (current < 0) current = 0;

    function goTo(index) {
        current = (index + slides.length) % slides.length;
        slides.forEach((s, i) => {
            s.classList.toggle('is-active', i === current);
            s.setAttribute('aria-hidden', i === current ? 'false' : 'true');
        });
        dots.forEach((d, i) => d.classList.toggle('is-active', i === current));
    }

    if (prevBtn) prevBtn.addEventListener('click', () => goTo(current - 1));
    if (nextBtn) nextBtn.addEventListener('click', () => goTo(current + 1));
    dots.forEach((dot, i) => dot.addEventListener('click', () => goTo(i)));

    // slow auto-advance so the featured card feels alive; pause while
    // the visitor is actually looking at / using the controls
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches && slides.length > 1) {
        let timer = null;
        const start = () => { if (timer) clearInterval(timer); timer = setInterval(() => goTo(current + 1), 6500); };
        const stop = () => { if (timer) { clearInterval(timer); timer = null; } };
        carousel.addEventListener('mouseenter', stop);
        carousel.addEventListener('mouseleave', start);
        carousel.addEventListener('focusin', stop);
        carousel.addEventListener('focusout', start);
        start();
    }
    }
})();


/**
 * Announcements list: category tabs + search box filter the rows
 * announcements.js rendered (data-category). Unfiltered, only the first
 * 5 show until "Load more"; re-runs on 'ps:announcements-rendered'.
 */
(function initAnnouncementFilters() {
    const tabsWrap = document.querySelector('[data-filter-tabs]');
    const searchInput = document.querySelector('[data-announcement-search]');
    const emptyMsg = document.querySelector('[data-announcement-empty]');
    if (!tabsWrap && !searchInput) return;

    const PAGE_SIZE = 5;
    let activeCategory = 'All Announcements';

    function applyFilters() {
        const rows = Array.from(document.querySelectorAll('[data-announcement-row]'));
        const query = (searchInput ? searchInput.value : '').trim().toLowerCase();
        const loadMoreBtn = document.querySelector('[data-load-more]');
        const isFiltering = activeCategory !== 'All Announcements' || query.length > 0;
        const expanded = !loadMoreBtn || loadMoreBtn.disabled;
        let matching = 0;
        let visibleCount = 0;

        rows.forEach((row) => {
            const matchesCategory = activeCategory === 'All Announcements' || row.dataset.category === activeCategory;
            const matchesSearch = !query || row.textContent.toLowerCase().includes(query);
            if (matchesCategory && matchesSearch) matching += 1;
            const show = matchesCategory && matchesSearch && (isFiltering || expanded || matching <= PAGE_SIZE);
            row.classList.toggle('is-hidden', !show);
            if (show) visibleCount += 1;
        });

        if (emptyMsg) {
            emptyMsg.textContent = rows.length ? 'No announcements match this category or search.' : 'No announcements have been posted yet.';
            emptyMsg.classList.toggle('is-visible', visibleCount === 0);
        }
        if (loadMoreBtn) loadMoreBtn.hidden = isFiltering || expanded || matching <= PAGE_SIZE;
    }
    document.addEventListener('ps:announcements-rendered', applyFilters);

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
 * "Load more" on the All Announcements list -- reveals the rows past the
 * first page (initAnnouncementFilters() above hides them), then hides
 * itself: every published announcement is already on the page.
 */
(function initLoadMore() {
    const btn = document.querySelector('[data-load-more]');
    if (!btn) return;

    btn.addEventListener('click', () => {
        btn.disabled = true;
        document.querySelectorAll('[data-announcement-row].is-hidden').forEach((row) => row.classList.remove('is-hidden'));
        btn.hidden = true;
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
 * The final step of a public request form ([data-wizard-step-form]: the
 * last wizard step, or donation-request.php). It really posts now, to its own
 * .php page -- see ps_handle_request_form() in includes/request-forms.php.
 * The earlier steps' answers only live in the sessionStorage draft
 * (frontend.js) and the review above shows them as plain text, so right
 * before the native submit this copies the whole draft (data-draft-key)
 * into one hidden ps_draft field. Documents are already on the server
 * (request-uploads.js). When the server sends the visitor back with
 * problems, its [data-form-errors] list gets focus.
 * (funeral-request.js does the same for the funeral form.)
 */
(function initWizardStepForm() {
    const form = document.querySelector('[data-wizard-step-form]');
    if (!form) return;

    form.querySelector('[data-form-errors]')?.focus();

    form.addEventListener('submit', (e) => {
        if (!form.reportValidity() || form.dataset.submitting) {
            e.preventDefault();
            return;
        }
        let draft = {};
        try {
            draft = JSON.parse(sessionStorage.getItem(form.dataset.draftKey) || '{}') || {};
        } catch (_) {
            // No storage: the server still gets this page's own fields.
        }
        let field = form.querySelector('input[name="ps_draft"]');
        if (!field) {
            field = document.createElement('input');
            field.type = 'hidden';
            field.name = 'ps_draft';
            form.append(field);
        }
        field.value = JSON.stringify(draft);
        form.dataset.submitting = 'true';
        psSetLoading(e.submitter || form.querySelector('[type="submit"]'), true, 'Submitting…');
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
 * Shared helpers for the auth forms and the admin portal below.
 *
 * psSetLoading(button, loading, label) -- busy state for a submit button
 * while its request is in flight: spinner via .is-loading (style.css),
 * aria-busy, and an optional temporary [data-submit-label] text (or the
 * button's data-loading-label). It deliberately doesn't disable the
 * button: a native form post needs the clicked button's name (verify vs
 * resend), so forms guard double submits with data-submitting instead.
 *
 * psReveal(el, cls) / psConceal(el, cls) -- show/hide a [hidden] element
 * that still wants a CSS transition (modals, toasts): unhide, then add
 * the open class; remove it, then re-hide once the transition is over.
 *
 * psShowToast(message, variant) -- the admin toast ([data-toast]), built
 * on the fly on pages that don't carry one. variant 'error' turns it red.
 *
 * psCountUp(el, target) -- stat numbers count up from 0 (skipped for
 * reduced motion).
 */
const psHideTimers = new WeakMap();
let psToastTimer = null;

function psSetLoading(button, loading, label) {
    if (!button) return;
    const labelEl = button.querySelector('[data-submit-label]');
    button.classList.toggle('is-loading', loading);
    if (loading) {
        button.setAttribute('aria-busy', 'true');
        const busyLabel = label || button.dataset.loadingLabel;
        if (labelEl && busyLabel) {
            if (!('idleLabel' in button.dataset)) button.dataset.idleLabel = labelEl.textContent;
            labelEl.textContent = busyLabel;
        }
    } else {
        button.removeAttribute('aria-busy');
        if (labelEl && 'idleLabel' in button.dataset) {
            labelEl.textContent = button.dataset.idleLabel;
            delete button.dataset.idleLabel;
        }
    }
}

function psReveal(el, openClass) {
    clearTimeout(psHideTimers.get(el));
    el.hidden = false;
    void el.offsetWidth; // commit the unhidden state so the transition runs
    el.classList.add(openClass);
}

function psConceal(el, openClass, duration = 250) {
    el.classList.remove(openClass);
    clearTimeout(psHideTimers.get(el));
    psHideTimers.set(el, setTimeout(() => { el.hidden = true; }, duration));
}

function psShowToast(message, variant) {
    if (!message) return;
    let toast = document.querySelector('[data-toast]');
    if (!toast) {
        toast = document.createElement('div');
        toast.className = 'ps-toast';
        toast.setAttribute('data-toast', '');
        toast.setAttribute('role', 'status');
        toast.setAttribute('aria-live', 'polite');
        toast.hidden = true;
        toast.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.5 2.5L16 9.5"/></svg> <span data-toast-text></span>';
        document.body.append(toast);
    }
    toast.classList.toggle('is-error', variant === 'error');
    toast.querySelector('[data-toast-text]').textContent = message;
    psReveal(toast, 'is-visible');
    clearTimeout(psToastTimer);
    psToastTimer = setTimeout(() => psConceal(toast, 'is-visible'), variant === 'error' ? 6000 : 3500);
}

function psCountUp(el, target) {
    const end = Math.max(0, Number(target) || 0);
    if (end === 0 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        el.textContent = end;
        return;
    }
    const duration = 700;
    const start = performance.now();
    const step = (now) => {
        const progress = Math.min(1, (now - start) / duration);
        el.textContent = Math.round(end * (1 - Math.pow(1 - progress, 3)));
        if (progress < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
}

// Back/forward cache restores a page exactly as it was left -- mid-submit.
window.addEventListener('pageshow', (e) => {
    if (!e.persisted) return;
    document.querySelectorAll('.is-loading').forEach((button) => psSetLoading(button, false));
    document.querySelectorAll('form[data-submitting]').forEach((form) => { delete form.dataset.submitting; });
    document.querySelectorAll('[data-register-submit]').forEach((button) => { button.disabled = false; });
});

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

(function initLoginForm() {
    const form = document.querySelector('[data-login-form]');
    if (!form) return;

    const emailInput = document.getElementById('loginEmail');
    const passwordInput = document.getElementById('loginPassword');
    const alertBox = document.querySelector('[data-auth-alert]');
    const submitBtn = document.querySelector('[data-login-submit]');
    if (!emailInput || !passwordInput) return;

    function validateEmail() {
        const value = emailInput.value.trim();
        if (!value) { setAuthFieldError(emailInput, 'Please enter your email address.'); return false; }
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
    emailInput.addEventListener('blur', validateEmail);
    passwordInput.addEventListener('blur', validatePassword);
    emailInput.addEventListener('input', () => {
        if (emailInput.closest('.auth-field').classList.contains('has-error')) validateEmail();
    });
    passwordInput.addEventListener('input', () => {
        if (passwordInput.closest('.auth-field').classList.contains('has-error')) validatePassword();
    });

    // Client-side checks first; when they pass, the form posts to
    // login.php normally and any error comes back through auth-state.php.
    form.addEventListener('submit', (e) => {
        if (alertBox) alertBox.hidden = true;

        const emailOk = validateEmail();
        const passwordOk = validatePassword();

        if (!emailOk || !passwordOk) {
            e.preventDefault();
            (emailOk ? passwordInput : emailInput).focus();
            return;
        }
        if (form.dataset.submitting) { e.preventDefault(); return; }
        form.dataset.submitting = 'true';
        psSetLoading(submitBtn, true, 'Logging in…');
    });
})();

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

    const validators = {
        firstName: () => {
            if (!fields.firstName.value.trim()) { setAuthFieldError(fields.firstName, 'Please enter your first name.'); return false; }
            setAuthFieldError(fields.firstName, null); return true
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
        if (!form.reportValidity()) return;

        if (form.dataset.submitting) return;
        form.dataset.submitting = 'true';
        if (submitBtn) submitBtn.disabled = true;
        psSetLoading(submitBtn, true, 'Creating account…');

        form.submit();
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
 * Admin tables (the per-type request pages, admin-donations.php,
 * admin-announcements.php, admin-accounts.php): type tabs, status select
 * and search box all filter the rows PHP already rendered -- same "filter
 * what's already in the DOM" approach as the announcements list above.
 * A search the page pre-filled (?ref= on the request pages) applies on
 * load. Re-runs on 'ps:admin-rows-changed' so a saved status update or a
 * delete (see initAdminModals below) still respects the current filters.
 */
(function initAdminTableFilters() {
    const rows = Array.from(document.querySelectorAll('[data-admin-row]'));
    if (!rows.length) return;

    const typeTabs = document.querySelector('[data-admin-type-tabs]');
    const searchInput = document.querySelector('[data-admin-search]');
    const statusSelect = document.querySelector('[data-admin-status-select]');
    const emptyMsg = document.querySelector('[data-admin-empty]');
    let activeType = 'all';

    function applyFilters() {
        const query = (searchInput ? searchInput.value : '').trim().toLowerCase();
        const status = statusSelect ? statusSelect.value : '';
        let visibleCount = 0;

        rows.forEach((row) => {
            if (!row.isConnected) return; // deleted
            const show = (activeType === 'all' || row.dataset.type === activeType)
                && (!status || row.dataset.status === status)
                && (!query || (row.dataset.search || '').includes(query));
            row.classList.toggle('is-hidden', !show);
            if (show) visibleCount += 1;
        });

        if (emptyMsg) emptyMsg.hidden = visibleCount > 0;
    }

    if (typeTabs) {
        typeTabs.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-admin-type-tab]');
            if (!btn) return;
            activeType = btn.dataset.adminTypeTab;
            typeTabs.querySelectorAll('[data-admin-type-tab]').forEach((t) => t.classList.toggle('active', t === btn));
            applyFilters();
        });
    }
    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (statusSelect) statusSelect.addEventListener('change', applyFilters);
    document.addEventListener('ps:admin-rows-changed', applyFilters);
    if (searchInput && searchInput.value) applyFilters();
})();


/**
 * Admin modals:
 *   - [data-modal-trigger="<id>"] opens that [data-modal] overlay and
 *     copies the trigger's data-* values into the modal's matching
 *     [data-modal-field] elements (data-reference -> "reference", ...).
 *     A data-docs JSON list renders the document checklist,
 *     data-allowed-statuses limits the status select to the current
 *     status + the next pipeline step + Rejected, and data-proof
 *     previews an uploaded proof of payment.
 *   - A [data-modal-schedule="<endpoint>"] fieldset (the request pages'
 *     date/time) is read-only on a final record, required for Approved
 *     and Scheduled, and lists that day's other bookings -- flagging any
 *     the chosen time overlaps -- from the endpoint as staff pick.
 *   - A [data-admin-form="<endpoint>"] POSTs (with the CSRF token and
 *     the checklist as JSON) and only updates the row and toasts once
 *     the server confirms -- see applyRowUpdate() for the reply shape.
 *   - A reply with reload: true (admin-save-announcement.php) reloads the
 *     page, which then shows the reply's message.
 */
(function initAdminModals() {
    const modals = document.querySelectorAll('[data-modal]');
    if (!modals.length) return;

    const showToast = psShowToast;
    let openModal = null;
    let activeTrigger = null;

    function renderDocs(modal) {
        const wrap = modal.querySelector('[data-modal-docs-wrap]');
        const list = modal.querySelector('[data-modal-docs]');
        if (!wrap || !list) return;

        let docs = [];
        try { docs = JSON.parse(activeTrigger.dataset.docs || '[]'); } catch (_) { /* no checklist */ }

        list.replaceChildren(...docs.map((doc) => {
            const item = document.createElement('div');
            item.className = 'admin-doc-item';

            const label = document.createElement('label');
            label.className = 'admin-doc-check';
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.checked = Boolean(doc.checked);
            const text = document.createElement('span');
            text.textContent = doc.label;
            label.append(checkbox, text);
            item.append(label);

            if (doc.file) {
                const link = document.createElement('a');
                link.className = 'admin-doc-thumb-link';
                link.href = doc.file;
                link.target = '_blank';
                link.rel = 'noopener';
                link.title = doc.fileName || 'Open the uploaded file';
                if (doc.isImage) {
                    const img = document.createElement('img');
                    img.className = 'admin-thumb';
                    img.src = doc.file;
                    img.alt = 'Uploaded ' + doc.label;
                    link.append(img);
                } else {
                    link.classList.add('admin-doc-file-link');
                    link.textContent = 'View PDF';
                }
                item.append(link);
            }
            return item;
        }));
        wrap.hidden = docs.length === 0;
        syncSeminarNote(modal);
    }

    // "Submitted Details": the [label, value] pairs a public form stored in
    // the row's details JSON (data-details, see ps_request_details()).
    function renderDetails(modal) {
        const wrap = modal.querySelector('[data-modal-details-wrap]');
        const list = modal.querySelector('[data-modal-details]');
        if (!wrap || !list || !activeTrigger) return;

        let pairs = [];
        try { pairs = JSON.parse(activeTrigger.dataset.details || '[]'); } catch (_) { /* no details */ }
        list.replaceChildren(...pairs.map(([label, value]) => {
            const row = document.createElement('div');
            const term = document.createElement('dt');
            term.textContent = label;
            const detail = document.createElement('dd');
            detail.textContent = value;
            row.append(term, detail);
            return row;
        }));
        wrap.hidden = pairs.length === 0;
    }

    // The Pre-Cana note only applies to weddings, once every required
    // document is checked off.
    function syncSeminarNote(modal) {
        const note = modal.querySelector('[data-modal-seminar-wrap]');
        if (!note || !activeTrigger) return;
        const isWedding = activeTrigger.closest('[data-admin-row]')?.dataset.type === 'wedding';
        const boxes = Array.from(modal.querySelectorAll('[data-modal-docs] input[type="checkbox"]'));
        note.hidden = !(isWedding && boxes.length > 0 && boxes.every((box) => box.checked));
    }

    function open(modal, trigger) {
        activeTrigger = trigger;
        openModal = modal;
        const form = modal.querySelector('form');
        if (form) form.reset();

        modal.querySelectorAll('[data-modal-field]').forEach((field) => {
            const value = trigger.dataset[field.dataset.modalField] || '';
            if (field.type === 'checkbox') field.checked = Boolean(value);
            else if (field.matches('input, select, textarea')) field.value = value;
            else field.textContent = value;
        });
        renderDocs(modal);
        renderDetails(modal);
        syncStatusOptions(modal);
        syncSchedule(modal);
        syncProofPreview(modal);
        modal.querySelectorAll('[data-dropzone-filename]').forEach((name) => { name.textContent = 'No file chosen'; });
        const errorBox = modal.querySelector('[data-modal-error]');
        if (errorBox) errorBox.hidden = true;

        psReveal(modal, 'is-open');
        document.body.classList.add('ps-modal-open');
        const firstField = modal.querySelector('input:not([type="file"]):not([type="hidden"]), select, textarea') || modal.querySelector('[data-modal-close]');
        if (firstField) firstField.focus();
    }

    function close() {
        if (!openModal) return;
        psConceal(openModal, 'is-open');
        openModal = null;
        document.body.classList.remove('ps-modal-open');
        if (activeTrigger && activeTrigger.isConnected) activeTrigger.focus();
        activeTrigger = null;
    }

    // Strict pipeline: only the current status, the next step and
    // Rejected stay selectable. admin-update-request.php enforces the same.
    function syncStatusOptions(modal) {
        const select = modal.querySelector('select[data-modal-field="status"]');
        const hint = modal.querySelector('[data-modal-status-hint]');
        if (!select || !activeTrigger || !('allowedStatuses' in activeTrigger.dataset)) return;

        const current = activeTrigger.dataset.status;
        const allowed = new Set([current, ...activeTrigger.dataset.allowedStatuses.split(',').filter(Boolean)]);
        Array.from(select.options).forEach((option) => { option.disabled = !allowed.has(option.value); });
        select.value = current;
        if (hint) {
            const next = Array.from(select.options)
                .filter((option) => !option.disabled && option.value !== current)
                .map((option) => option.textContent.trim());
            hint.textContent = next.length
                ? `Status moves one step at a time. Next: ${next.join(' or ')}.`
                : 'This is a final status. You can still update the remarks.';
        }
    }

    // Schedule fields: a final record keeps its schedule (disabled fields
    // aren't sent), and Approved/Scheduled need a date and time --
    // admin-update-request.php enforces both too.
    function syncSchedule(modal) {
        const box = modal.querySelector('[data-modal-schedule]');
        if (!box || !activeTrigger) return;
        const final = !activeTrigger.dataset.allowedStatuses;
        const status = modal.querySelector('select[data-modal-field="status"]')?.value;
        box.querySelectorAll('input').forEach((input) => {
            input.disabled = final;
            if (input.name !== 'event_end') input.required = !final && ['approved', 'scheduled'].includes(status);
        });
        checkSchedule(modal);
    }

    // What else is booked that day, and which of those the chosen time
    // overlaps. Only a preview: saving runs the same check on the server.
    let scheduleCheck = 0;
    async function checkSchedule(modal) {
        const box = modal.querySelector('[data-modal-schedule]');
        const result = box?.querySelector('[data-schedule-result]');
        if (!result || !activeTrigger) return;
        const value = (name) => box.querySelector(`[name="${name}"]`)?.value || '';
        const check = ++scheduleCheck;
        const date = value('event_date');
        result.hidden = true;
        if (!box.dataset.modalSchedule || !date || location.protocol === 'file:') return;

        let data = null;
        try {
            const params = new URLSearchParams({
                type: activeTrigger.dataset.type, id: activeTrigger.dataset.id, date, time: value('event_time'), end: value('event_end'),
            });
            const response = await fetch(`${box.dataset.modalSchedule}?${params}`, {
                cache: 'no-store', credentials: 'same-origin', headers: { Accept: 'application/json' },
            });
            if (response.ok) data = await response.json();
        } catch (_) {
            // No preview then.
        }
        if (check !== scheduleCheck || !data || !data.ok) return;

        const clashing = new Set(data.conflicts.map((booking) => booking.reference));
        const heading = document.createElement('strong');
        heading.textContent = clashing.size
            ? `This time overlaps ${clashing.size === 1 ? 'another booking' : clashing.size + ' other bookings'}:`
            : data.bookings.length ? 'Also booked that day:' : 'Nothing else is booked that day.';
        result.replaceChildren(heading);
        if (data.bookings.length) {
            const list = document.createElement('ul');
            data.bookings.forEach((booking) => {
                const item = document.createElement('li');
                item.classList.toggle('is-conflict', clashing.has(booking.reference));
                const link = document.createElement('a');
                link.href = booking.url;
                link.target = '_blank';
                link.rel = 'noopener';
                link.textContent = booking.reference;
                item.append(link, ` · ${booking.type} · ${booking.name} · ${booking.time} (${booking.status})`);
                list.append(item);
            });
            result.append(list);
        }
        result.classList.toggle('is-conflict', clashing.size > 0);
        result.hidden = false;
    }

    function syncProofPreview(modal) {
        const wrap = modal.querySelector('[data-modal-proof-wrap]');
        if (!wrap || !activeTrigger) return;
        const file = activeTrigger.dataset.proof || '';
        wrap.hidden = !file;
        const empty = modal.querySelector('[data-modal-proof-empty]');
        if (empty) empty.hidden = Boolean(file);
        if (file) {
            wrap.querySelector('[data-modal-proof-link]').href = file;
            wrap.querySelector('[data-modal-proof-img]').src = file;
        }
    }

    // Applies a saved row as described by the server's reply:
    //   { status, statusLabel, statusClass, type, fields: {name: text},
    //     data: {triggerDataKey: value}, docsComplete }
    function applyRowUpdate(row, trigger, update) {
        if (update.status) {
            row.dataset.status = update.status;
            const pill = row.querySelector('[data-row-status]');
            if (pill) {
                pill.className = 'ps-status ' + (update.statusClass || 'is-' + update.status);
                pill.textContent = update.statusLabel || update.status;
            }
        }
        if (update.type) row.dataset.type = update.type;
        Object.entries(update.fields || {}).forEach(([name, value]) => {
            row.querySelectorAll(`[data-row-field="${name}"]`).forEach((cell) => { cell.textContent = value; });
        });
        Object.entries(update.data || {}).forEach(([name, value]) => { trigger.dataset[name] = value; });
        if ('docsComplete' in update) {
            row.querySelector('[data-row-docs]')?.classList.toggle('is-complete', Boolean(update.docsComplete));
        }
        row.classList.remove('is-updated');
        void row.offsetWidth; // restart the "just saved" highlight
        row.classList.add('is-updated');
        document.dispatchEvent(new CustomEvent('ps:admin-rows-changed'));
    }

    async function saveToServer(modal, form) {
        if (form.dataset.submitting) return;
        const trigger = activeTrigger;
        const row = trigger ? trigger.closest('[data-admin-row]') : null;
        const submitBtn = form.querySelector('[type="submit"]');
        const errorBox = form.querySelector('[data-modal-error]');

        const body = new FormData(form);
        const docBoxes = form.querySelectorAll('[data-modal-docs] input[type="checkbox"]');
        if (docBoxes.length) body.set('docs', JSON.stringify(Array.from(docBoxes, (box) => box.checked)));
        const token = document.querySelector('meta[name="csrf-token"]');
        if (token) body.set('csrf_token', token.content);

        form.dataset.submitting = 'true';
        if (errorBox) errorBox.hidden = true;
        psSetLoading(submitBtn, true, 'Saving…');

        let result = null;
        let failure = '';
        try {
            const response = await fetch(form.dataset.adminForm, {
                method: 'POST',
                body,
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });
            result = await response.json().catch(() => null);
            if (response.status === 401) {
                location.href = 'login.html';
                return;
            }
            if (!response.ok || !result || !result.ok) {
                failure = (result && result.error) || 'Could not save the change. Please try again.';
            }
        } catch (_) {
            failure = 'Could not reach the server. Check your connection and try again.';
        } finally {
            delete form.dataset.submitting;
            psSetLoading(submitBtn, false);
        }

        if (failure) {
            if (errorBox) {
                errorBox.textContent = failure;
                errorBox.hidden = false;
            } else {
                showToast(failure, 'error');
            }
            return;
        }

        if (result.reload) {
            // The page re-renders from the database (e.g. a new announcement).
            try { sessionStorage.setItem('ps-admin-toast', result.message || 'Saved.'); } catch (_) { /* no toast then */ }
            location.reload();
            return;
        }
        if (row && trigger) applyRowUpdate(row, trigger, result.row || {});
        if (openModal === modal && activeTrigger === trigger) close();
        showToast(result.message || 'Saved.');
    }

    document.querySelectorAll('[data-modal-trigger]').forEach((trigger) => {
        const modal = document.getElementById(trigger.dataset.modalTrigger);
        if (modal) trigger.addEventListener('click', () => open(modal, trigger));
    });

    modals.forEach((modal) => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal || e.target.closest('[data-modal-close]')) close();
        });
        modal.addEventListener('change', (e) => {
            if (e.target.closest('[data-modal-docs]')) syncSeminarNote(modal);
            if (e.target.matches('select[data-modal-field="status"]')) syncSchedule(modal);
            else if (e.target.closest('[data-modal-schedule]')) checkSchedule(modal);
            const fileInput = e.target.closest('input[type="file"]');
            const fileName = fileInput?.closest('[data-dropzone]')?.querySelector('[data-dropzone-filename]');
            if (fileName) fileName.textContent = fileInput.files[0] ? fileInput.files[0].name : 'No file chosen';
        });

        const form = modal.querySelector('[data-admin-form]');
        if (!form) return;
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            if (!form.reportValidity()) return;
            saveToServer(modal, form);
        });
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && openModal) close();
    });
})();


/**
 * [data-admin-delete="<endpoint>"] buttons (admin-announcements.php): ask
 * first, POST action=delete + data-id with the CSRF token, and remove the
 * row only once the server confirms. Also shows the toast a reloading
 * save left behind (saveToServer() above).
 */
(function initAdminDelete() {
    try {
        const pending = sessionStorage.getItem('ps-admin-toast');
        if (pending) {
            sessionStorage.removeItem('ps-admin-toast');
            psShowToast(pending);
        }
    } catch (_) {
        // Storage unavailable: nothing to show.
    }

    document.querySelectorAll('[data-admin-delete]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            if (btn.dataset.submitting) return;
            const name = btn.dataset.name || 'this item';
            if (!window.confirm(`Delete "${name}"? This can't be undone.`)) return;

            const body = new FormData();
            body.set('action', 'delete');
            body.set('id', btn.dataset.id || '');
            const token = document.querySelector('meta[name="csrf-token"]');
            if (token) body.set('csrf_token', token.content);

            btn.dataset.submitting = 'true';
            psSetLoading(btn, true, 'Deleting…');
            let result = null;
            try {
                const response = await fetch(btn.dataset.adminDelete, {
                    method: 'POST',
                    body,
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json' },
                });
                if (response.status === 401) {
                    location.href = 'login.html';
                    return;
                }
                result = await response.json().catch(() => null);
                if (!response.ok || !result || !result.ok) {
                    psShowToast((result && result.error) || 'Could not delete it. Please try again.', 'error');
                    return;
                }
            } catch (_) {
                psShowToast('Could not reach the server. Check your connection and try again.', 'error');
                return;
            } finally {
                delete btn.dataset.submitting;
                psSetLoading(btn, false);
            }

            btn.closest('[data-admin-row]')?.remove();
            if (!document.querySelector('[data-admin-row]')) {
                // Last one gone: reload for the page's own empty state.
                try { sessionStorage.setItem('ps-admin-toast', result.message || 'Deleted.'); } catch (_) { /* no toast then */ }
                location.reload();
                return;
            }
            document.dispatchEvent(new CustomEvent('ps:admin-rows-changed'));
            psShowToast(result.message || 'Deleted.');
        });
    });
})();


/**
 * Admin topbar bell (includes/topbar.php): re-checks pending-count.php
 * every data-notif-interval seconds (30 minimum) while the tab is
 * visible, keeps the badge current, and toasts when new items arrive.
 * Plain polling -- no websockets/SSE.
 */
(function initNotificationPoll() {
    const bell = document.querySelector('[data-notif-poll]');
    if (!bell || location.protocol === 'file:') return;

    const badge = bell.querySelector('[data-notif-badge]');
    const intervalMs = Math.max(30, Number(bell.dataset.notifInterval) || 45) * 1000;
    let lastTotal = badge && !badge.hidden ? Number(badge.textContent) || 0 : 0;

    async function poll() {
        if (document.hidden) return;
        try {
            const response = await fetch(bell.dataset.notifPoll, {
                cache: 'no-store',
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });
            if (response.status === 401 || response.status === 403) {
                clearInterval(timer);
                return;
            }
            if (!response.ok) return;
            const data = await response.json();
            const total = Number(data.total) || 0;
            const label = `Notifications: ${total} ${total === 1 ? 'item' : 'items'} awaiting review`;
            bell.setAttribute('aria-label', label);
            bell.title = label;
            if (badge) {
                badge.textContent = total;
                badge.hidden = total === 0;
            }
            if (total > lastTotal) {
                const added = total - lastTotal;
                psShowToast(`${added} new ${added === 1 ? 'item' : 'items'} awaiting review.`);
                bell.classList.remove('has-new');
                void bell.offsetWidth;
                bell.classList.add('has-new');
            }
            lastTotal = total;
        } catch (_) {
            // Offline or the server is restarting -- try again next tick.
        }
    }

    const timer = setInterval(poll, intervalMs);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) poll(); });
})();


/**
 * Sidebar: when the links don't all fit (the admin portal has 13), scroll
 * the nav so the current page's link is in view.
 */
(function initActiveNavInView() {
    const nav = document.querySelector('.ps-sidebar .ps-nav');
    const active = nav && nav.querySelector('.ps-nav-link.active');
    if (!active || nav.scrollHeight <= nav.clientHeight) return;
    const navBox = nav.getBoundingClientRect();
    const linkBox = active.getBoundingClientRect();
    if (linkBox.top < navBox.top || linkBox.bottom > navBox.bottom) {
        nav.scrollTop += linkBox.top - navBox.top - (navBox.height - linkBox.height) / 2;
    }
})();


/** Server-rendered stat numbers ([data-count-up], admin-dashboard.php) count up on load. */
(function initCountUp() {
    document.querySelectorAll('[data-count-up]').forEach((el) => psCountUp(el, el.textContent));
})();


/**
 * forgot-password.html / reset-password.html client checks. Same rules
 * as forgot-password.php / reset-password.php, and the same password
 * rules as registration (at least 8 characters, confirmation matches) --
 * keep all of them in sync.
 */
(function initPasswordResetForms() {
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const recheckOnInput = (input, validate) => {
        input.addEventListener('input', () => {
            if (input.closest('.auth-field')?.classList.contains('has-error')) validate();
        });
    };

    const forgotForm = document.querySelector('[data-forgot-form]');
    if (forgotForm) {
        const email = forgotForm.querySelector('input[name="email"]');
        const validateEmail = () => {
            const value = email.value.trim();
            if (!value) { setAuthFieldError(email, 'Please enter your email address.'); return false; }
            if (!emailPattern.test(value)) { setAuthFieldError(email, 'Please enter a valid email address.'); return false; }
            setAuthFieldError(email, null);
            return true;
        };
        email.addEventListener('blur', validateEmail);
        recheckOnInput(email, validateEmail);
        forgotForm.addEventListener('submit', (e) => {
            if (!validateEmail()) { e.preventDefault(); email.focus(); }
        });
    }

    const resetForm = document.querySelector('[data-reset-form]');
    if (resetForm) {
        const password = resetForm.querySelector('input[name="password"]');
        const confirm = resetForm.querySelector('input[name="confirmPassword"]');
        const validatePassword = () => {
            if (!password.value) { setAuthFieldError(password, 'Please create a new password.'); return false; }
            if (password.value.length < 8) { setAuthFieldError(password, 'Password must be at least 8 characters.'); return false; }
            setAuthFieldError(password, null);
            return true;
        };
        const validateConfirm = () => {
            if (!confirm.value) { setAuthFieldError(confirm, 'Please confirm your new password.'); return false; }
            if (confirm.value !== password.value) { setAuthFieldError(confirm, 'Passwords do not match.'); return false; }
            setAuthFieldError(confirm, null);
            return true;
        };
        password.addEventListener('blur', validatePassword);
        confirm.addEventListener('blur', validateConfirm);
        recheckOnInput(password, validatePassword);
        recheckOnInput(confirm, validateConfirm);
        password.addEventListener('input', () => { if (confirm.value) validateConfirm(); });
        resetForm.addEventListener('submit', (e) => {
            const passwordOk = validatePassword();
            const confirmOk = validateConfirm();
            if (!passwordOk || !confirmOk) {
                e.preventDefault();
                (passwordOk ? confirm : password).focus();
            }
        });
    }
})();


/**
 * Busy state for the auth forms that post straight to PHP (OTP verify /
 * resend, forgot password, reset password). Registered after the
 * validators above, so it only kicks in for a submit that's going out.
 */
(function initAuthSubmitLoading() {
    document.querySelectorAll('[data-otp-form], [data-auth-form]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (e.defaultPrevented) return;
            if (form.dataset.submitting) { e.preventDefault(); return; }
            form.dataset.submitting = 'true';
            psSetLoading(e.submitter || form.querySelector('[type="submit"]'), true);
        });
    });
})();


/**
 * donation-request.php: the Donation Purpose picker, if present (a trigger button + a panel of
 * [data-fund-option]s that write the hidden [data-fund-input]) and the
 * "remain anonymous" box, which makes Full Name optional -- the same rule
 * as ps_build_donation() in includes/request-forms.php.
 */
(function initDonationForm() {
    const picker = document.querySelector('[data-fund-picker]');
    if (picker) {
        const trigger = picker.querySelector('[data-fund-trigger]');
        const panel = picker.querySelector('[data-fund-panel]');
        const input = picker.querySelector('[data-fund-input]');
        const options = Array.from(picker.querySelectorAll('[data-fund-option]'));

        const choose = (option) => {
            input.value = option.dataset.value;
            options.forEach((item) => item.classList.toggle('is-selected', item === option));
            picker.querySelector('[data-fund-label]').textContent = option.querySelector('strong').textContent;
            picker.querySelector('[data-fund-desc]').textContent = option.dataset.desc;
            picker.querySelector('[data-fund-icon]').innerHTML = option.querySelector('svg').outerHTML;
        };
        const setOpen = (open) => {
            panel.hidden = !open;
            trigger.setAttribute('aria-expanded', String(open));
        };

        trigger.setAttribute('aria-haspopup', 'true');
        setOpen(false);
        trigger.addEventListener('click', () => setOpen(panel.hidden));
        options.forEach((option) => option.addEventListener('click', () => {
            choose(option);
            setOpen(false);
            input.dispatchEvent(new Event('change', { bubbles: true })); // frontend.js saves the draft
            trigger.focus();
        }));
        document.addEventListener('click', (e) => { if (!picker.contains(e.target)) setOpen(false); });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !panel.hidden) {
                setOpen(false);
                trigger.focus();
            }
        });
        // frontend.js may have restored a different fund from the draft.
        const restored = options.find((option) => option.dataset.value === input.value);
        if (restored) choose(restored);
    }

    const anonymous = document.getElementById('isAnonymous');
    const donorName = document.getElementById('donorName');
    if (anonymous && donorName) {
        const sync = () => { donorName.required = !anonymous.checked; };
        anonymous.addEventListener('change', sync);
        sync();
    }
})();


/** [data-copy-text] buttons (request-confirmation.php's reference number) copy their text. */
(function initCopyButtons() {
    document.querySelectorAll('[data-copy-text]').forEach((button) => {
        const idle = button.textContent;
        button.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(button.dataset.copyText);
                button.textContent = 'Copied!';
            } catch (_) {
                button.textContent = 'Copy failed';
            }
            setTimeout(() => { button.textContent = idle; }, 2000);
        });
    });
})();
