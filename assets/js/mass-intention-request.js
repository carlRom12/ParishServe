(function () {
    const form = document.getElementById('massIntentionRequest');
    if (!form) return;

    let step = 0;
    const panels = [...form.querySelectorAll('[data-step]')];
    const next = document.getElementById('mrNext');
    const back = document.getElementById('mrBack');
    const cancel = document.getElementById('mrCancel');
    const confirmToggle = document.getElementById('confirmRespectful');
    const stepHeading = document.getElementById('mrStepHeading');
    const stepSub = document.getElementById('mrStepSub');
    const STEP_COPY = [
        ['Step 1 of 3: Intent Details', 'Please provide the details of the Mass Intention you would like to request.'],
        ['Step 2 of 3: Schedule', 'Choose your preferred Mass schedule for this intention.'],
        ['Step 3 of 3: Review & Submit', 'Please review your Mass Intention details before submitting your request.'],
    ];

    const CHECK_SVG = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>';
    const NEXT_HTML = 'Save and Continue <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>';
    const SUBMIT_HTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12 20 4l-6 16-3-7-7-3z"/></svg> Submit Mass Intention Request';

    const mobile = form.elements.namedItem('mobileNumber');
    if (mobile) mobile.addEventListener('change', () => { mobile.value = mobile.value.replace(/[\s()-]/g, ''); });

    const dateInput = form.elements.namedItem('preferredDate');
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    dateInput.min = [tomorrow.getFullYear(), String(tomorrow.getMonth() + 1).padStart(2, '0'), String(tomorrow.getDate()).padStart(2, '0')].join('-');

    document.querySelectorAll('select').forEach(select => {
        const sync = () => select.classList.toggle('is-placeholder', select.value === '');
        sync();
        select.addEventListener('change', sync);
    });

    ['intentionDetails', 'schedulingNotes'].forEach(name => {
        const input = form.elements.namedItem(name);
        const counter = document.getElementById(name + 'Count');
        if (!input || !counter) return;
        const update = () => { counter.textContent = input.value.length + ' / ' + input.maxLength; };
        input.addEventListener('input', update);
        update();
    });

    function updateSubmitState() {
        next.disabled = step === 2 && !confirmToggle.checked;
    }


    const subject = form.elements.namedItem('intentionSubject');
    const occasion = form.elements.namedItem('occasion');
    const soulInputs = ['soulName1', 'soulName2'].map(name => form.elements.namedItem(name));
    const typeCopy = {
        'Thanksgiving Mass': ['Blessing or occasion of thanksgiving', 'e.g., Passing the board examination, birthday, recovery', 'Person or family giving thanks (optional)', 'e.g., Maria Santos and family', 'Give thanks for a blessing received, such as passing an examination or an anniversary.'],
        'Special Intention': ['Person, family, or intention', 'e.g., Santos family', 'Purpose (optional)', 'e.g., Family unity or guidance', 'Share a personal, family, or community intention.'],
        'Petition Mass': ['Person or need to pray for', 'e.g., Juan Dela Cruz or upcoming examination', 'Grace or help requested (optional)', 'e.g., Healing, guidance, strength', 'A petition asks for help or a grace needed. Describe the need respectfully.'],
        'All Souls': ['', '', '', '', 'A general intention for all the faithful departed. No individual names are needed. Offering: PHP 100.'],
        'For the Souls of': ['', '', '', '', 'One soul: PHP 100. Two souls: PHP 200. Please enter names separately.'],
    };
    const selectedType = () => form.querySelector('[name="intentionType"]:checked')?.value || '';
    const soulNames = () => soulInputs.map(input => input.value.trim()).filter(Boolean);
    const offering = () => selectedType() === 'For the Souls of' ? Math.max(1, soulNames().length) * 100 : 100;
    const money = amount => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(amount);
    const intentionSubject = () => selectedType() === 'For the Souls of' ? soulNames().join('; ') : selectedType() === 'All Souls' ? 'All the faithful departed' : subject.value.trim();
    function syncIntention() {
        const type = selectedType();
        const souls = type === 'For the Souls of';
        const general = type === 'All Souls';
        document.getElementById('mrSubjectFields').hidden = souls || general;
        document.getElementById('mrSoulFields').hidden = !souls;
        subject.disabled = occasion.disabled = step !== 0 || souls || general;
        subject.required = !souls && !general;
        soulInputs.forEach((input, index) => {
            input.disabled = step !== 0 || !souls;
            input.required = souls && index === 0;
            const name = input.value.trim();
            input.setCustomValidity(souls && name && !/^[\p{L}\p{M}][\p{L}\p{M} .'\u2019-]*$/u.test(name) ? 'Enter one name using letters, spaces, apostrophes, periods, or hyphens.' : '');
        });
        if (souls && soulInputs[1].value.trim() && soulInputs[0].value.trim().toLocaleLowerCase() === soulInputs[1].value.trim().toLocaleLowerCase()) soulInputs[1].setCustomValidity('Please enter a different person, or leave the second name blank.');
        const copy = typeCopy[type] || typeCopy['Special Intention'];
        if (!souls && !general) {
            form.querySelector('label[for="intentionSubject"]').textContent = copy[0] + ' *';
            subject.placeholder = copy[1];
            form.querySelector('label[for="occasion"]').textContent = copy[2];
            occasion.placeholder = copy[3];
        }
        document.getElementById('mrTypeHint').textContent = copy[4];
        form.querySelector('[data-offering-total]').textContent = money(offering());
        form.querySelector('[data-review-subject-label]').textContent = souls ? 'Names of the deceased' : general ? 'Offered for' : copy[0];
    }
    form.addEventListener('change', event => { if (event.target.name === 'intentionType') syncIntention(); });
    soulInputs.forEach(input => input.addEventListener('input', syncIntention));

    function populateReview() {
        const value = name => (form.elements.namedItem(name)?.value || '').trim();
        const radioLabel = name => {
            const checked = form.querySelector(`input[name="${name}"]:checked`);
            if (!checked) return '';
            return checked.closest('.wr-intent-card')?.querySelector('strong')?.textContent.trim() || checked.value;
        };
        const formatDate = isoDate => {
            if (!/^\d{4}-\d{2}-\d{2}$/.test(isoDate)) return isoDate;
            return new Date(isoDate + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
        };
        const data = {
            intentionType: radioLabel('intentionType'),
            intentionSubject: intentionSubject(),
            offeringTotal: money(offering()),
            occasion: ['All Souls', 'For the Souls of'].includes(selectedType()) ? '' : value('occasion'),
            intentionDetails: value('intentionDetails'),
            requesterName: value('requesterName'),
            mobileNumber: value('mobileNumber'),
            emailAddress: value('emailAddress'),
            preferredDate: formatDate(value('preferredDate')),
            preferredTime: value('preferredTime'),
            schedulingNotes: value('schedulingNotes'),
        };
        form.querySelectorAll('[data-review]').forEach(el => {
            el.textContent = data[el.dataset.review] || 'Not provided';
        });
    }

    function render() {
        panels.forEach((panel, index) => {
            panel.hidden = index !== step;
            panel.querySelectorAll('input, select, textarea').forEach(input => { input.disabled = index !== step; });
        });
        document.querySelectorAll('.wr-stepbar [data-step-item]').forEach(el => {
            const index = Number(el.dataset.stepItem);
            el.classList.toggle('is-current', index === step);
            el.classList.toggle('is-done', index < step);
            const num = el.querySelector('[data-step-num]');
            num.innerHTML = index < step ? CHECK_SVG : String(index + 1);
        });
        document.querySelectorAll('.conf-apply-steps-list [data-step-item]').forEach(el => {
            el.classList.toggle('is-current', Number(el.dataset.stepItem) === step);
        });
        back.hidden = step === 0;
        cancel.hidden = step !== 0;
        next.innerHTML = step === 2 ? SUBMIT_HTML : NEXT_HTML;
        stepHeading.textContent = STEP_COPY[step][0];
        stepSub.textContent = STEP_COPY[step][1];
        syncIntention();
        if (step === 2) populateReview();
        updateSubmitState();
    }

    form.addEventListener('submit', event => {
        event.preventDefault();
        if (!form.reportValidity()) return;
        if (step < 2) {
            step += 1;
            render();
            stepHeading.focus();
            return;
        }
        if (form.dataset.submitting) return;
        // Every panel goes to the server (ps_handle_request_form() in
        // includes/request-forms.php), but render() disables the hidden ones.
        form.querySelectorAll('input, select, textarea').forEach(input => { input.disabled = false; });
        form.dataset.submitting = 'true';
        next.classList.add('is-loading');
        HTMLFormElement.prototype.submit.call(form);
    });

    // Closed and reopened (mass-intention-modal.js resets the form): start again at step 1.
    form.addEventListener("reset", () => { step = 0; setTimeout(render); });

    // Back from the confirmation page (bfcache): the form is usable again.
    window.addEventListener('pageshow', event => {
        if (!event.persisted) return;
        delete form.dataset.submitting;
        next.classList.remove('is-loading');
        render();
    });

    back.addEventListener('click', () => {
        step = Math.max(0, step - 1);
        render();
        stepHeading.focus();
    });

    confirmToggle.addEventListener('change', updateSubmitState);

    form.addEventListener('input', event => {
        if (event.target !== confirmToggle) {
            confirmToggle.checked = false;
            updateSubmitState();
        }
    });

    render();
    // Problems the server found with the last attempt.
    form.querySelector('[data-form-errors]')?.focus();
})();
