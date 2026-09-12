(function () {
    const page = location.pathname.split('/').pop();
    const flow = page.startsWith('mass-intention-request') ? 'mass-intention' : page.startsWith('wedding-request') ? 'wedding' : page.startsWith('baptism-request') ? 'baptism' : page.startsWith('confirmation-request') ? 'confirmation' : null;
    const key = 'parishserve-draft-' + flow;
    let draft = {};
    try { draft = JSON.parse(sessionStorage.getItem(key) || '{}'); } catch (_) { /* Storage may be disabled. */ }
    const form = document.querySelector('form:not([data-login-form]):not([data-register-form]):not([data-otp-form])');
    if (flow && form) {
        for (const input of form.elements) {
            if (!input.name || !(input.name in draft) || input.type === 'file') continue;
            if (input.type === 'radio' || input.type === 'checkbox') input.checked = input.value === draft[input.name];
            else input.value = draft[input.name];
        }
        const save = () => {
            for (const [name, value] of new FormData(form)) {
                if (typeof value === 'string') draft[name] = value;
            }
            try { sessionStorage.setItem(key, JSON.stringify(draft)); } catch (_) { /* Keep the form usable without storage. */ }
        };
        form.addEventListener('input', save);
        form.addEventListener('change', save);
        if (!form.hasAttribute('data-wizard-step-form')) {
            form.addEventListener('submit', event => {
                event.preventDefault();
                if (!form.reportValidity()) return;
                save();
                location.href = page === 'baptism-request.html' ? 'baptism-request-step2.html' : form.getAttribute('action');
            });
        }
    }

    document.querySelectorAll('input[type="date"][data-max-today]').forEach(input => {
        const today = new Date();
        input.max = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
    });

    document.querySelectorAll('[data-datepicker]').forEach(picker => {
        const input = picker.querySelector('input');
        input.type = 'date';
        input.removeAttribute('pattern');
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        const iso = date => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
        input.min = iso(tomorrow);
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
    });

    if (page === 'mass-intention-request-step3.html') {
        const fields = ['intentionType', 'intentionSubject', 'occasion', 'intentionDetails', 'requesterName', 'mobileNumber', 'emailAddress', 'preferredDate', 'preferredTime', 'massType', 'schedulingNotes'];
        const types = { regular: 'Regular Parish Mass', special: 'Special / Subject to Parish Confirmation' };
        document.querySelectorAll('.wr3-review-row strong').forEach((value, index) => {
            const field = fields[index];
            let text = draft[field] || 'Not provided';
            if (field === 'massType') text = types[draft[field]] || 'Not provided';
            if (field === 'preferredDate' && /^\d{4}-\d{2}-\d{2}$/.test(draft[field] || '')) {
                text = new Date(draft[field] + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
            }
            value.textContent = text;
        });
    }

    if (page === 'confirmation-request-step4.html') {
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
    const greeting = document.querySelector('.db-hero h1')?.firstChild;
    if (greeting && greeting.nodeType === Node.TEXT_NODE) {
        const hour = new Date().getHours();
        greeting.textContent = greeting.textContent.replace(/Good (morning|afternoon|evening)/, hour < 12 ? 'Good morning' : hour < 18 ? 'Good afternoon' : 'Good evening');
    }
})();
