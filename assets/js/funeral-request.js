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
    // ps_* fields (the submit token, the injected draft) belong to a single page load.
    const skip = input => !input.name || input.name.startsWith('ps_') || input.type === 'file';
    const save = () => {
        for (const input of form.elements) {
            if (!skip(input) && (input.type !== 'radio' || input.checked)) draft[input.name] = input.value;
        }
        try { sessionStorage.setItem(key, JSON.stringify(draft)); return true; }
        catch (_) { return false; }
    };
    for (const input of form.elements) {
        if (!skip(input) && typeof draft[input.name] === 'string') {
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
        // The file itself is uploaded by request-uploads.js the moment it's
        // chosen (type/size errors are shown there too); certificateName
        // only records that it's on the server, for the Step 4 checklist.
        certificate.addEventListener('ps:upload-staged', event => {
            draft.certificateName = event.detail.name;
            save();
        });
        certificate.addEventListener('ps:upload-cleared', () => {
            delete draft.certificateName;
            save();
        });
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
            if (file) document.getElementById('certificateName').textContent = file.name;
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
        missing.textContent = 'Please go back and complete the required details and upload the death certificate before continuing.';
        const sync = () => { submit.disabled = !complete || !confirmation.checked; };
        confirmation.addEventListener('change', sync);
        sync();
        // Problems the server found with the last attempt (includes/request-forms.php).
        form.querySelector('[data-form-errors]')?.focus();
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
        if (step < 4) {
            (event.submitter || submit).classList.add('is-loading');
            location.href = form.getAttribute('action');
        } else if (complete && confirmation.checked && !form.dataset.submitting) {
            // Real submit: the review above is only text, so copy the whole
            // draft into the form for the server (ps_handle_request_form()).
            let field = form.querySelector('input[name="ps_draft"]');
            if (!field) {
                field = document.createElement('input');
                field.type = 'hidden';
                field.name = 'ps_draft';
                form.append(field);
            }
            field.value = JSON.stringify(draft);
            form.dataset.submitting = 'true';
            (event.submitter || submit).classList.add('is-loading');
            HTMLFormElement.prototype.submit.call(form);
        }
    });

    // Back/forward cache restores the page mid-submit; clear the busy state.
    window.addEventListener('pageshow', event => {
        if (!event.persisted) return;
        delete form.dataset.submitting;
        form.querySelectorAll('.is-loading').forEach(button => button.classList.remove('is-loading'));
    });
})();
