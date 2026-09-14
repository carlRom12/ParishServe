(function () {
    const form = document.getElementById('counselingRequest');
    if (!form) return;
    // Keep this private draft in the current page only; no storage or transmission.
    let step = 0;
    const panels = [...form.querySelectorAll('[data-step]')];
    const next = document.getElementById('crNext');
    const back = document.getElementById('crBack');
    const cancel = document.getElementById('crCancel');
    const mobile = form.elements.mobile;
    mobile.pattern = '^09[0-9]{9}$';
    mobile.title = 'Format: 09XXXXXXXXX (11 digits)';
    mobile.addEventListener('change', () => { mobile.value = mobile.value.replace(/[\s()-]/g, ''); });
    const todayIso = () => {
        const d = new Date();
        return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    };
    const preferredDate = form.elements.preferredDate;
    const preferredTime = form.elements.preferredTime;
    preferredDate.min = todayIso();
    function validatePreferredTime() {
        preferredTime.setCustomValidity('');
        if (!preferredTime.value || preferredDate.value !== todayIso()) return;
        const now = new Date();
        const nowHHMM = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
        if (preferredTime.value <= nowHHMM) {
            preferredTime.setCustomValidity('For a same-day request, please choose a time later than now.');
        }
    }
    preferredDate.addEventListener('change', validatePreferredTime);
    preferredTime.addEventListener('change', validatePreferredTime);
    preferredTime.addEventListener('input', validatePreferredTime);
    function render() {
        panels.forEach((panel, index) => {
            panel.hidden = index !== step;
            panel.querySelectorAll('input, select, textarea').forEach(input => { input.disabled = index !== step; });
        });
        document.querySelectorAll('[data-progress], .cr-side-steps li').forEach((item, index) => {
            index = item.hasAttribute('data-progress') ? Number(item.dataset.progress) : [...item.parentElement.children].indexOf(item);
            item.classList.toggle('is-complete', index < step);
            item.querySelector(':scope > span').textContent = index < step ? '\u2713' : String(index + 1);
            if (index === step) item.setAttribute('aria-current', 'step');
            else item.removeAttribute('aria-current');
        });
        back.hidden = step === 0;
        cancel.hidden = step !== 0;
        next.hidden = false;
        next.textContent = step === 0 ? 'Next: Counseling Details \u2192' : step === 1 ? 'Next: Review & Submit \u2192' : 'Submit Counseling Request \u2192';
        if (step === 2) {
            const value = name => form.elements.namedItem(name).value.trim();
            const formatTime = time => {
                if (!/^\d{2}:\d{2}$/.test(time)) return time;
                const [hour, minute] = time.split(':').map(Number);
                const period = hour < 12 ? 'AM' : 'PM';
                const hour12 = hour % 12 || 12;
                return `${hour12}:${String(minute).padStart(2, '0')} ${period}`;
            };
            const groups = [
                ['crPersonalReview', [['Full Name', ['firstName', 'middleName', 'lastName'].map(value).filter(Boolean).join(' ')], ['Mobile Number', value('mobile')], ['Email Address', value('email')], ['Address', value('address')]]],
                ['crDetailsReview', [['Requesting For', value('sessionType')], ['Main Concern / Reason', value('reason')], ['Preferred Date', value('preferredDate')], ['Preferred Time', formatTime(value('preferredTime'))], ['Brief Description', value('concerns')], ['Additional Notes', value('additionalNotes')]]]
            ];
            groups.forEach(([id, rows]) => {
                const review = document.getElementById(id);
                review.replaceChildren();
                rows.forEach(([label, text]) => {
                    const dt = document.createElement('dt');
                    const dd = document.createElement('dd');
                    dt.textContent = label;
                    dd.textContent = text || 'Not provided';
                    review.append(dt, dd);
                });
            });
        }
    }
    form.addEventListener('submit', event => {
        event.preventDefault();
        if (!form.reportValidity()) return;
        if (step === 2) {
            const status = document.getElementById('crSubmitStatus');
            status.hidden = false;
            status.textContent = 'Your request has not been sent. Online submission is not available yet; please contact the parish office using the link above.';
            return;
        }
        step += 1;
        render();
        panels[step].querySelector('h2').focus();
    });
    back.addEventListener('click', () => {
        step = Math.max(0, step - 1);
        render();
        panels[step].querySelector('h2').focus();
    });
    form.querySelectorAll('[data-edit]').forEach(button => {
        button.addEventListener('click', () => {
            step = Number(button.dataset.edit);
            render();
            panels[step].querySelector('h2').focus();
        });
    });
    form.addEventListener('input', event => {
        if (event.target.id !== 'crConfirm') {
            document.getElementById('crConfirm').checked = false;
            document.getElementById('crSubmitStatus').hidden = true;
        }
    });
    ['concerns', 'additionalNotes'].forEach(name => {
        const input = form.elements.namedItem(name);
        const update = () => { document.getElementById(name + 'Count').textContent = input.value.length + '/' + input.maxLength; };
        input.addEventListener('input', update);
        update();
    });
    next.disabled = false;
    render();
})();
