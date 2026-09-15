/**
 * request-uploads.js
 * ---------------------------------------------------------------------
 * Uploads each request document the moment it's chosen
 * (upload-document.php), so a file picked on an earlier wizard step is
 * still there when the final step is submitted -- files can't live in the
 * sessionStorage draft the way the text fields do (frontend.js).
 *
 *   form[data-upload-flow="<flow>"]  every named <input type="file"> inside
 *                                    is uploaded under that flow
 *   [data-uploaded-docs="<flow>"]    a review list (<ul>) filled with what's
 *                                    been uploaded and what's still missing
 *
 * A document that's already uploaded drops its input's `required`, so the
 * step can continue without choosing the file again, and the input fires
 * `ps:upload-staged` / `ps:upload-cleared` for page scripts
 * (funeral-request.js). The form won't submit while an upload is running.
 * The server repeats every check made here (type, size, real content).
 * ---------------------------------------------------------------------
 */
(function () {
    if (location.protocol === 'file:') return;

    const endpoint = 'upload-document.php';
    const icons = {
        done: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>',
        missing: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 8v5"/><path d="M12 16.5h.01"/></svg>',
    };
    const sizeLabel = (bytes) => (bytes >= 1048576 ? `${(bytes / 1048576).toFixed(1)} MB` : `${Math.max(1, Math.round(bytes / 1024))} KB`);

    async function call(flow, body) {
        const response = await fetch(body ? endpoint : `${endpoint}?flow=${encodeURIComponent(flow)}`, {
            method: body ? 'POST' : 'GET',
            body,
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { Accept: 'application/json' },
        });
        const data = await response.json().catch(() => null);
        if (!data) throw new Error('The server did not respond. Please try again.');
        if (!data.ok) throw new Error(data.error || 'The upload failed. Please try again.');
        return data;
    }

    document.querySelectorAll('form[data-upload-flow]').forEach((form) => {
        const flow = form.dataset.uploadFlow;
        const inputs = Array.from(form.querySelectorAll('input[type="file"][name]'));
        if (!inputs.length) return;
        let pending = 0;

        inputs.forEach((input) => { input.dataset.uploadRequired = input.required ? 'true' : ''; });

        function statusFor(input) {
            let status = form.querySelector(`[data-upload-status="${input.name}"]`);
            if (!status) {
                status = document.createElement('div');
                status.className = 'ps-upload-status';
                status.dataset.uploadStatus = input.name;
                status.setAttribute('aria-live', 'polite');
                status.hidden = true;
                (input.closest('[data-dropzone]') || input).after(status);
            }
            return status;
        }

        function show(input, state, text, removable) {
            const status = statusFor(input);
            status.className = 'ps-upload-status' + (state ? ' is-' + state : '');
            const label = document.createElement('span');
            label.textContent = text;
            status.replaceChildren(label);
            if (removable) {
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'ps-upload-remove';
                remove.textContent = 'Remove';
                remove.addEventListener('click', () => unstage(input));
                status.append(remove);
            }
            status.hidden = !text;
        }

        function setFilename(input, name) {
            const filename = input.closest('[data-dropzone]')?.querySelector('[data-dropzone-filename]');
            if (filename) filename.textContent = name || 'No file chosen';
        }

        function markUploaded(input, doc) {
            input.dataset.uploaded = 'true';
            input.required = false;
            input.setCustomValidity('');
            setFilename(input, doc.name);
            show(input, 'done', `Uploaded: ${doc.name} (${sizeLabel(doc.size)})`, true);
            input.dispatchEvent(new CustomEvent('ps:upload-staged', { detail: doc }));
        }

        function markEmpty(input) {
            delete input.dataset.uploaded;
            input.required = input.dataset.uploadRequired === 'true';
            setFilename(input, '');
            show(input, '', '', false);
            input.dispatchEvent(new CustomEvent('ps:upload-cleared'));
        }

        async function unstage(input) {
            const body = new FormData();
            body.set('flow', flow);
            body.set('field', input.name);
            body.set('action', 'remove');
            try {
                await call(flow, body);
                markEmpty(input);
            } catch (error) {
                show(input, 'error', error.message, Boolean(input.dataset.uploaded));
            }
        }

        inputs.forEach((input) => {
            input.addEventListener('change', async () => {
                const file = input.files && input.files[0];
                if (!file) return; // cleared by another size check (main.js)

                const maxMb = parseFloat(input.dataset.maxSizeMb) || 5;
                const allowed = (input.accept || '').split(',').map((type) => type.trim().toLowerCase()).filter(Boolean);
                const extension = '.' + (file.name.split('.').pop() || '').toLowerCase();
                if (file.size > maxMb * 1048576 || (allowed.length && !allowed.includes(extension))) {
                    input.value = '';
                    const types = allowed.map((type) => type.slice(1).toUpperCase()).filter((type) => type !== 'JPEG').join(', ');
                    show(input, 'error', `Please choose a ${types} file no larger than ${maxMb} MB.`, Boolean(input.dataset.uploaded));
                    return;
                }

                const body = new FormData();
                body.set('flow', flow);
                body.set('field', input.name);
                body.set('file', file);
                pending += 1;
                show(input, 'busy', `Uploading ${file.name}…`, false);
                try {
                    const data = await call(flow, body);
                    input.value = ''; // the server has it now; don't send it again with the form
                    markUploaded(input, data.document);
                } catch (error) {
                    input.value = '';
                    const kept = input.dataset.uploaded ? ' Your earlier file is still saved.' : '';
                    show(input, 'error', error.message + kept, Boolean(input.dataset.uploaded));
                    input.required = input.dataset.uploadRequired === 'true' && !input.dataset.uploaded;
                } finally {
                    pending -= 1;
                }
            });
        });

        // "Remove File" in a page's own preview (upload-preview.js, baptism-upload.js).
        inputs.forEach((input) => {
            input.addEventListener('ps:request-remove', () => {
                if (input.dataset.uploaded) unstage(input);
            });
        });

        form.addEventListener('submit', (event) => {
            if (pending === 0) return;
            event.preventDefault();
            event.stopImmediatePropagation();
            const busy = inputs.find((input) => statusFor(input).classList.contains('is-busy')) || inputs[0];
            statusFor(busy).scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, true);

        call(flow).then((data) => {
            data.documents.forEach((doc) => {
                const input = inputs.find((item) => item.name === doc.field);
                if (!input || (input.files && input.files.length)) return;
                if (doc.uploaded) markUploaded(input, doc);
                else input.dispatchEvent(new CustomEvent('ps:upload-cleared'));
            });
        }).catch(() => { /* the inputs still work; the server checks on submit */ });
    });

    document.querySelectorAll('[data-uploaded-docs]').forEach((list) => {
        call(list.dataset.uploadedDocs).then((data) => {
            const docs = data.documents.filter((doc) => doc.uploaded || doc.required);
            list.replaceChildren(...docs.map((doc) => {
                const item = document.createElement('li');
                const mark = document.createElement('span');
                mark.className = 'wr3-doc-check' + (doc.uploaded ? '' : ' is-missing');
                mark.innerHTML = doc.uploaded ? icons.done : icons.missing;
                const name = document.createElement('span');
                name.className = 'wr3-doc-name';
                name.textContent = doc.label;
                const file = document.createElement('span');
                file.className = 'wr3-doc-file';
                file.textContent = doc.uploaded ? doc.name : 'Not uploaded yet (required)';
                item.append(mark, name, file);
                return item;
            }));
        }).catch(() => {
            list.replaceChildren();
        });
    });
})();
