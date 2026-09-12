(function () {
    const form = document.querySelector('[data-funeral-step]');
    if (!form) return;
    const step = Number(form.dataset.funeralStep);
    const key = 'parishserve-draft-funeral';
    let draft = {};
    try {
        const stored = JSON.parse(sessionStorage.getItem(key) || '{}');
        if (stored && typeof stored === 'object' && !Array.isArray(stored)) draft = stored;
    } catch (_) { /* Keep the form usable when storage is unavailable. */ }
    const save = () => {
        for (const input of form.elements) {
            if (input.name && input.type !== 'file' && (input.type !== 'radio' || input.checked)) draft[input.name] = input.value;
        }
        try { sessionStorage.setItem(key, JSON.stringify(draft)); return true; }
        catch (_) { return false; }
    };
    for (const input of form.elements) {
        if (input.name && input.type !== 'file' && typeof draft[input.name] === 'string') {
            if (input.type === 'radio') input.checked = input.value === draft[input.name];
            else input.value = draft[input.name];
        }
    }
    const today = new Date();
    const iso = date => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    const tomorrow = new Date(today.getFullYear(), today.getMonth(), today.getDate() + 1);
    const deathDate = form.elements.namedItem('dateOfDeath');
    const massDate = form.elements.namedItem('preferredMassDate');
    const burialDate = form.elements.namedItem('burialDate');
    if (deathDate) deathDate.max = iso(today);
    if (massDate) massDate.min = iso(tomorrow);
    if (burialDate) burialDate.min = draft.preferredMassDate || iso(tomorrow);
    form.addEventListener('input', save);
    form.addEventListener('change', save);
    const notes = form.elements.namedItem('serviceNotes');
    const notesCount = document.getElementById('serviceNotesCount');
    if (notes && notesCount) {
        const updateCount = () => { notesCount.textContent = `${notes.value.length}/500`; };
        notes.addEventListener('input', updateCount);
        updateCount();
    }

    const documentNotes = form.elements.namedItem('documentNotes');
    const documentCount = document.getElementById('documentNotesCount');
    if (documentNotes && documentCount) {
        const update = () => { documentCount.textContent = documentNotes.value.length + ' / 500'; };
        documentNotes.addEventListener('input', update);
        update();
    }
    const certificate = document.getElementById('deathCertificate');
    if (certificate) {
        // A remembered filename is not a saved or uploaded document.
        delete draft.certificateName;
        save();
        const zone = certificate.closest('[data-dropzone]');
        if (zone) {
            ['dragenter', 'dragover'].forEach(type => zone.addEventListener(type, event => {
                event.preventDefault();
                zone.classList.add('is-dragover');
            }));
            ['dragleave', 'drop'].forEach(type => zone.addEventListener(type, event => {
                event.preventDefault();
                zone.classList.remove('is-dragover');
            }));
            zone.addEventListener('drop', event => {
                if (!event.dataTransfer?.files?.length) return;
                certificate.files = event.dataTransfer.files;
                certificate.dispatchEvent(new Event('change'));
            });
        }
        certificate.addEventListener('change', () => {
            const file = certificate.files[0];
            const error = document.getElementById('certificateError');
            const valid = file && /\.(pdf|jpe?g|png)$/i.test(file.name) && file.size > 0 && file.size <= 5 * 1024 * 1024;
            certificate.setCustomValidity(file && !valid ? 'Choose a PDF, JPG or PNG file no larger than 5 MB.' : '');
            error.hidden = !file || valid;
            error.textContent = certificate.validationMessage;
            document.getElementById('certificateName').textContent = file ? file.name : 'No file chosen';
            if (valid) draft.certificateName = file.name;
            else delete draft.certificateName;
            save();
        });
    }

    const required = ['familyFirstName', 'familyLastName', 'relationship', 'familyMobile', 'familyEmail', 'deceasedName', 'dateOfDeath', 'preferredMassDate', 'burialArrangement', 'serviceType', 'preferredTime', 'differentBurialLocation', 'certificateName'];
    const complete = required.every(name => typeof draft[name] === 'string' && draft[name].trim());
    const submit = form.querySelector('[type="submit"]');
    const confirmation = document.getElementById('confirmAccurate');
    if (step === 4) {
        document.querySelectorAll('[data-funeral-review]').forEach(value => {
            const text = draft[value.dataset.funeralReview];
            value.textContent = typeof text === 'string' && text.trim() ? text : 'Not provided';
            value.classList.toggle('is-empty', !text);
        });
        const progress = document.getElementById('funeralReviewProgress');
        const progressText = document.getElementById('funeralReviewProgressText');
        const filled = required.filter(name => typeof draft[name] === 'string' && draft[name].trim()).length;
        if (progress && progressText) {
            progress.classList.toggle('is-complete', complete);
            progressText.textContent = complete ? 'All required details provided' : filled + ' of ' + required.length + ' required details provided';
        }
        const missing = document.getElementById('missingDetails');
        missing.hidden = complete;
        missing.textContent = 'Please go back and complete the required details and document selection before continuing.';
        const sync = () => { submit.disabled = !complete || !confirmation.checked; };
        confirmation.addEventListener('change', sync);
        sync();
    } else submit.disabled = false;

    form.addEventListener('submit', event => {
        event.preventDefault();
        if (!form.reportValidity()) return;
        if (!save()) {
            let warning = document.getElementById('storageWarning');
            if (!warning) {
                warning = document.createElement('p');
                warning.id = 'storageWarning';
                warning.setAttribute('role', 'alert');
                form.append(warning);
            }
            warning.textContent = 'Your browser cannot save this draft. Please enable session storage before continuing.';
            return;
        }
        if (step < 4) location.href = form.getAttribute('action');
        else if (complete && confirmation.checked) {
            const notice = document.getElementById('funeralSubmitNotice');
            notice.hidden = false;
            notice.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    });
})();
