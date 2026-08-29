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
        if (slotMinutes <= nowMinutes && slotMinutes > activeMinutes) {
            activeItem = item;
            activeMinutes = slotMinutes;
        }
    });

    items.forEach((item) => item.classList.remove('is-now'));
    if (activeItem) activeItem.classList.add('is-now');
})();
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
(function initBookmarkToggles() {
    document.querySelectorAll('[data-bookmark-btn]').forEach((btn) => {
        btn.addEventListener('click', () => {
            btn.classList.toggle('is-saved');
        });
    });
})();
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
(function initCalendarViewSwitch() {
    const group = document.querySelector('[data-view-switch]');
    if (!group) return;

    group.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-view]');
        if (!btn) return;
        group.querySelectorAll('button').forEach((b) => b.classList.toggle('active', b === btn));
    });
})();
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
    const submitLabel = submitBtn ? submitBtn.querySelector('[data-submit-label]') : null;
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

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        if (alertBox) alertBox.hidden = true;

        const emailOk = validateEmail();
        const passwordOk = validatePassword();

        if (!emailOk) { emailInput.focus(); return; }
        if (!passwordOk) { passwordInput.focus(); return; }
        if (submitBtn) { submitBtn.disabled = true; submitBtn.classList.add('is-loading'); }
        if (submitLabel) submitLabel.textContent = 'Logging inâ€¦';

        window.setTimeout(() => {
            if (submitBtn) { submitBtn.disabled = false; submitBtn.classList.remove('is-loading'); }
            if (submitLabel) submitLabel.textContent = 'Log In';
            passwordInput.value = '';

            if (alertBox) {
                alertBox.hidden = false;
                alertBox.focus();
            }
        }, 650);
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
    const submitLabel = submitBtn ? submitBtn.querySelector('[data-submit-label]') : null;

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

        if (submitBtn) { submitBtn.disabled = true; submitBtn.classList.add('is-loading'); }
        if (submitLabel) submitLabel.textContent = 'Creating accountâ€¦';

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
