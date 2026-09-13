(function () {
    const form = document.querySelector('[data-baptism-upload]');
    if (!form) return;
    const input = form.querySelector('#birthCertificate');
    const zone = input.closest('[data-dropzone]');
    const filename = zone.querySelector('[data-dropzone-filename]');
    const error = form.querySelector('[data-file-error]');
    const button = form.querySelector('[type="submit"]');
    const status = document.getElementById('baptismStatus');
    let reviewing = false;
    const preview = document.createElement('div');
    preview.className = 'baptism-file-preview';
    preview.hidden = true;
    zone.parentElement.append(preview);
    let previewUrl = null;
    let previewFile = null;
    function showPreview(file) {
        zone.hidden = !!file;
        if (file === previewFile) return;
        preview.replaceChildren();
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = null;
        previewFile = file;
        preview.hidden = !file;
        if (!file) return;
        previewUrl = URL.createObjectURL(file);
        const heading = document.createElement('strong');
        heading.textContent = 'Birth certificate preview';
        const header = document.createElement('div');
        header.className = 'baptism-preview-header';
        const details = document.createElement('div');
        const fileLabel = document.createElement('span');
        fileLabel.textContent = file.name + ' (' + (file.size / 1024).toFixed(0) + ' KB)';
        details.append(heading, fileLabel);
        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'ps-btn ps-btn-outline';
        remove.textContent = 'Remove File';
        remove.addEventListener('click', () => {
            input.value = '';
            validateFile();
            status.hidden = true;
            input.focus();
        });
        header.append(details, remove);
        preview.append(header);
        if (/\.pdf$/i.test(file.name)) {
            const frame = document.createElement('iframe');
            frame.title = 'Selected birth certificate PDF preview';
            frame.src = previewUrl;
            preview.append(frame);
        } else {
            const image = document.createElement('img');
            image.alt = 'Selected birth certificate preview';
            image.src = previewUrl;
            preview.append(image);
        }
        const open = document.createElement('a');
        open.href = previewUrl;
        open.target = '_blank';
        open.rel = 'noopener';
        open.textContent = 'Open full preview';
        const note = document.createElement('p');
        note.textContent = 'Local preview only. Your file has not been sent. If the PDF does not display, use Open full preview.';
        preview.append(open, note);
    }
    window.addEventListener('pagehide', () => {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = null;
        previewFile = null;
    });
    window.addEventListener('pageshow', () => validateFile());
    function validateFile() {
        const file = input.files[0];
        const message = file && !/\.(pdf|jpe?g|png)$/i.test(file.name) ? 'Choose a PDF, JPG, or PNG file.' : file && file.size > 5 * 1024 * 1024 ? 'Choose a file no larger than 5 MB.' : '';
        input.setCustomValidity(message);
        filename.textContent = file ? file.name + ' (' + (file.size / 1024).toFixed(0) + ' KB)' : 'No file chosen';
        error.textContent = message;
        error.hidden = !message;
        showPreview(file && !message ? file : null);
        return !!file && !message;
    }
    input.addEventListener('change', validateFile);
    zone.addEventListener('dragover', event => event.preventDefault());
    zone.addEventListener('drop', event => {
        event.preventDefault();
        if (event.dataTransfer.files.length) {
            const transfer = new DataTransfer();
            transfer.items.add(event.dataTransfer.files[0]);
            input.files = transfer.files;
            validateFile();
        }
    });
    const review = document.createElement('section');
    review.hidden = true;
    review.className = 'baptism-review';
    form.insertBefore(review, form.querySelector('.wr-actions'));
    const original = [...form.children].filter(el => el !== review && !el.classList.contains('wr-actions') && el !== status);
    form.addEventListener('submit', event => {
        event.preventDefault();
        if (!validateFile() || !form.reportValidity()) return;
        if (reviewing) {
            status.hidden = false;
            status.textContent = 'Your request has not been sent. Online submission is not connected yet. Please contact the parish office to complete your request.';
            return;
        }
        let draft = {};
        try { draft = JSON.parse(sessionStorage.getItem('parishserve-draft-baptism') || '{}') || {}; } catch (_) {}
        review.replaceChildren();
        const title = document.createElement('h2');
        title.textContent = 'Step 3 of 3: Review & Submit';
        review.append(title);
        const value = key => typeof draft[key] === 'string' ? draft[key] : '';
        const grid = document.createElement('div'); grid.className = 'br-review-grid';
        function card(title, rows, parent) {
            const section = document.createElement('section'); section.className = 'br-review-card';
            const heading = document.createElement('h3'); heading.textContent = title; section.append(heading);
            const list = document.createElement('dl');
            rows.forEach(([label, text]) => {
                const dt = document.createElement('dt'); const dd = document.createElement('dd');
                dt.textContent = label; dd.textContent = text || 'Not provided'; list.append(dt, dd);
            });
            section.append(list); parent.append(section); return section;
        }
        card('1. Child Information', [
            ['Full Name', ['childFirstName','childMiddleName','childLastName','childSuffix'].map(value).filter(Boolean).join(' ')],
            ['Date of Birth', value('childDob')], ['Place of Birth', value('childPlaceOfBirth')], ['Gender', value('childGender')]
        ], grid);
        card('2. Parent / Guardian Information', [
            ['Full Name', value('requestorName')], ['Relationship to Child', value('relationship')],
            ['Contact Number', value('requestorContact')], ['Email Address', value('requestorEmail')]
        ], grid);
        review.append(grid);
        card('3. Preferred Schedule', [['Baptism Type', value('baptismType')], ['Preferred Date', value('baptismDate')], ['Additional Note', form.elements.officeNotes.value]], review);
        const documentCard = document.createElement('section'); documentCard.className = 'br-review-card';
        const documentHeading = document.createElement('h3'); documentHeading.textContent = '4. Selected Document';
        const documentRow = document.createElement('div'); documentRow.className = 'br-document';
        const filename = document.createElement('span'); filename.textContent = input.files[0].name;
        const view = document.createElement('a'); view.href = previewUrl; view.target = '_blank'; view.rel = 'noopener'; view.textContent = 'View file';
        documentRow.append(filename, view); documentCard.append(documentHeading, documentRow); review.append(documentCard);
        const reminder = document.createElement('p'); reminder.className = 'br-reminder';
        reminder.textContent = 'Your request is subject to parish review and schedule availability. The parish office will confirm the final schedule.'; review.append(reminder);
        const label = document.createElement('label'); label.className = 'br-confirm service-confirm';
        const check = document.createElement('input'); check.type = 'checkbox'; check.required = true;
        const copy = document.createElement('span'); const strong = document.createElement('strong'); strong.textContent = 'I confirm';
        const description = document.createElement('span'); description.textContent = ' that the information provided is true and accurate.';
        copy.append(strong, description); label.append(check, copy); review.append(label);
        const note = document.createElement('p'); note.textContent = 'Preview only: online submission is not connected. Your document remains selected in this page and has not been sent.'; review.append(note);
        original.forEach(el => { el.hidden = true; });
        review.hidden = false; reviewing = true;
        button.textContent = 'Submit Baptism Request';
        const steps = document.querySelectorAll('.wr-stepbar .wr-step');
        steps.forEach((el, i) => el.classList.toggle('is-current', i === 2));
    });
    form.querySelector('.wr-cancel').addEventListener('click', event => {
        if (!reviewing) return;
        event.preventDefault(); reviewing = false;
        review.hidden = true; review.querySelector('input').required = false;
        original.forEach(el => { el.hidden = false; });
        status.hidden = true; button.textContent = 'Next: Review & Submit';
        document.querySelectorAll('.wr-stepbar .wr-step').forEach((el, i) => el.classList.toggle('is-current', i === 1));
    });
    validateFile();
})();
