(function () {
    const form = document.getElementById('massIntentionRequest');
    if (!form) return;

    let step = 0;
    const panels = [...form.querySelectorAll('[data-step]')];
    const next = document.getElementById('mrNext');
    const back = document.getElementById('mrBack');
    const cancel = document.getElementById('mrCancel');
    const confirmToggle = document.getElementById('confirmRespectful');
    const notice = form.querySelector('[data-wizard-notice]');
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
            intentionSubject: value('intentionSubject'),
            occasion: value('occasion'),
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
        if (notice) {
            notice.hidden = false;
            notice.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
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
            if (notice) notice.hidden = true;
            updateSubmitState();
        }
    });

    render();
})();
