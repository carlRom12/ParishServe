(function () {
    const page = location.pathname.split('/').pop();
    // The final page of each request form is .php (it submits to the
    // server, includes/request-forms.php); the earlier steps stay .html.
    const pageName = page.replace(/\.(html|php)$/, '');
    const flow = pageName.startsWith('mass-intention-request') ? 'mass-intention'
        : pageName.startsWith('wedding-request') ? 'wedding'
        : pageName.startsWith('baptism-request') ? 'baptism'
        : pageName.startsWith('confirmation-request') ? 'confirmation'
        : pageName === 'donation-request' ? 'donations'
        : null;
    const key = 'parishserve-draft-' + flow;
    const isoDate = date => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    let draft = {};
    try { draft = JSON.parse(sessionStorage.getItem(key) || '{}'); } catch (_) { /* Storage may be disabled. */ }
    const form = document.querySelector('form:not([data-login-form]):not([data-register-form]):not([data-otp-form])');
    if (flow && form) {
        // ps_* fields (the submit token, the injected draft) belong to a single page load.
        const skip = input => !input.name || input.name.startsWith('ps_') || input.type === 'file' || input.tagName === 'BUTTON';
        for (const input of form.elements) {
            if (skip(input) || !(input.name in draft)) continue;
            if (input.type === 'radio' || input.type === 'checkbox') input.checked = input.value === draft[input.name];
            else input.value = draft[input.name];
        }
        const save = () => {
            for (const input of form.elements) {
                if (skip(input) || input.disabled) continue;
                if (input.type === 'radio') {
                    if (input.checked) draft[input.name] = input.value;
                } else if (input.type === 'checkbox') {
                    draft[input.name] = input.checked ? input.value : '';
                } else {
                    draft[input.name] = input.value;
                }
            }
            try { sessionStorage.setItem(key, JSON.stringify(draft)); } catch (_) { /* Keep the form usable without storage. */ }
        };
        form.addEventListener('input', save);
        form.addEventListener('change', save);
        // These submit to the server themselves: the final steps
        // (initWizardStepForm() in main.js), baptism step 2 (baptism-upload.js)
        // and the one-page Mass Intention form (mass-intention-request.js).
        if (!form.matches('[data-wizard-step-form], [data-baptism-upload], [data-multi-step]')) {
            form.addEventListener('submit', event => {
                event.preventDefault();
                if (!form.reportValidity()) return;
                save();
                // Busy cue on "Next" while the next step loads (style.css .is-loading).
                (event.submitter || form.querySelector('[type="submit"]'))?.classList.add('is-loading');
                location.href = form.getAttribute('action');
            });
        }
    }

    document.querySelectorAll('input[type="date"][data-max-today]').forEach(input => {
        input.max = isoDate(new Date());
    });
    // The server's rule for these is "a future date" (includes/request-forms.php).
    document.querySelectorAll('input[type="date"][data-min-tomorrow]').forEach(input => {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        input.min = isoDate(tomorrow);
    });

    document.querySelectorAll('[data-datepicker]').forEach(picker => {
        const input = picker.querySelector('input');
        input.type = 'date';
        input.removeAttribute('pattern');
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        input.min = isoDate(tomorrow);
        const validate = () => {
            input.setCustomValidity('');
            if (!input.value) return;
            const date = new Date(input.value + 'T00:00:00');
            const regular = document.querySelector('[name="baptismType"]:checked')?.value === 'regular';
            if (date <= today) input.setCustomValidity('Please choose a future date.');
            else if (regular && date.getDay() !== 6) input.setCustomValidity('Regular Baptism is only available on Saturdays.');
        };
        input.addEventListener('change', validate);
        document.querySelectorAll('[name="baptismType"]').forEach(radio => radio.addEventListener('change', validate));
        picker.querySelector('[data-datepicker-toggle]').addEventListener('click', () => {
            if (input.showPicker) input.showPicker();
            else input.focus();
        });
        validate();
        // What's already booked that day: booking-hint.js (data-booking-type).
    });

    // Review steps: [data-review="field [field ...]"] shows those draft values
    // joined with spaces (data-review-format="date" formats a date).
    document.querySelectorAll('[data-review]').forEach(value => {
        let text = value.dataset.review.split(/\s+/).map(name => String(draft[name] || '').trim()).filter(Boolean).join(' ');
        if (text && value.dataset.reviewFormat === 'date' && /^\d{4}-\d{2}-\d{2}$/.test(text)) {
            text = new Date(text + 'T00:00:00').toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
        }
        value.textContent = text || 'Not provided';
        value.classList.toggle('is-empty', !text);
    });

    if (pageName === 'confirmation-request-step4') {
        const fullName = [draft.candidateFirstName, draft.candidateMiddleName, draft.candidateLastName, draft.candidateSuffix].filter(Boolean).join(' ');
        const dob = /^\d{4}-\d{2}-\d{2}$/.test(draft.candidateDob || '')
            ? new Date(draft.candidateDob + 'T00:00:00').toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
            : '';
        const values = [
            fullName, dob, draft.candidateAddress, draft.candidateMobile, draft.candidateEmail,
            draft.parishName, draft.guardianName, draft.guardianRelationship, draft.schoolName, draft.gradeLevel,
            draft.emergencyContact, draft.firstCommunion, draft.sponsorName, draft.sponsorContact, draft.parishNotes,
        ];
        // Name of School/Grade Level/Sponsor Name/Sponsor Contact/Notes are
        // optional on Steps 1-2 -- only count the genuinely required fields
        // toward the "ready to submit" badge below, but still mute an
        // EMPTY optional row's "Not provided" the same as a missing one.
        const required = [true, true, true, true, true, true, true, true, false, false, true, true, false, false, false];
        let filled = 0;
        document.querySelectorAll('.wr3-review-row strong').forEach((value, index) => {
            const hasValue = Boolean(values[index]);
            value.textContent = hasValue ? values[index] : 'Not provided';
            value.classList.toggle('is-empty', !hasValue);
            if (required[index] && hasValue) filled += 1;
        });

        const requiredCount = required.filter(Boolean).length;
        const progress = document.getElementById('confReviewProgress');
        const progressText = document.getElementById('confReviewProgressText');
        if (progress && progressText) {
            const complete = filled === requiredCount;
            progress.classList.toggle('is-complete', complete);
            progressText.textContent = complete
                ? 'All required details provided'
                : `${filled} of ${requiredCount} required details provided`;
            progress.querySelector('svg').innerHTML = complete
                ? '<path d="M5 13l4 4L19 7"/>'
                : '<circle cx="12" cy="12" r="9"/><path d="M12 7.5v5l3.5 2"/>';
        }
    }

    // request-confirmation.php: that request went through, so its draft is finished.
    document.querySelectorAll('[data-clear-draft]').forEach(marker => {
        try { sessionStorage.removeItem(marker.dataset.clearDraft); } catch (_) { /* nothing to clear */ }
    });

    const greeting = document.querySelector('.db-hero h1')?.firstChild;
    if (greeting && greeting.nodeType === Node.TEXT_NODE) {
        const hour = new Date().getHours();
        greeting.textContent = greeting.textContent.replace(/Good (morning|afternoon|evening)/, hour < 12 ? 'Good morning' : hour < 18 ? 'Good afternoon' : 'Good evening');
    }
})();
