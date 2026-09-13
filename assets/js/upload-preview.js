(function () {
    document.querySelectorAll('input[type="file"]').forEach(input => {
        if (input.dataset.previewReady || input.closest('[data-baptism-upload]')) return;
        const zone = input.closest('[data-dropzone]');
        if (!zone) return;
        input.dataset.previewReady = 'true';
        const preview = document.createElement('section');
        preview.className = 'service-upload-preview'; preview.hidden = true;
        zone.after(preview);
        let url = null, previous = null;
        const error = document.createElement('p'); error.className = 'service-upload-error'; error.hidden = true; preview.after(error);
        function update() {
            const file = input.files[0];
            const accepted = input.accept.split(',').map(x => x.trim().toLowerCase()).filter(Boolean);
            const matches = !file || !accepted.length || accepted.some(type => type.startsWith('.') ? file.name.toLowerCase().endsWith(type) : type.endsWith('/*') ? file.type.startsWith(type.slice(0,-1)) : file.type === type);
            const limit = Number(input.dataset.maxSizeMb || 5) * 1024 * 1024;
            const message = file && (!matches || file.size > limit || !file.size) ? 'Choose a supported, nonempty file no larger than ' + (limit / 1024 / 1024) + ' MB.' : '';
            input.setCustomValidity(message); error.textContent = message; error.hidden = !message;
            const valid = file && !message;
            zone.hidden = !!valid; preview.hidden = !valid;
            if (previous === file && valid) return;
            preview.replaceChildren(); if (url) URL.revokeObjectURL(url); url = null; previous = valid ? file : null;
            if (!valid) return;
            url = URL.createObjectURL(file);
            const header = document.createElement('div'); header.className = 'service-upload-header';
            const name = document.createElement('strong'); name.textContent = file.name + ' (' + Math.ceil(file.size / 1024) + ' KB)';
            const remove = document.createElement('button'); remove.type = 'button'; remove.className = 'ps-btn ps-btn-outline'; remove.textContent = 'Remove File';
            remove.addEventListener('click', () => { input.value = ''; input.dispatchEvent(new Event('change', {bubbles:true})); input.dispatchEvent(new Event('input', {bubbles:true})); update(); input.focus(); });
            header.append(name, remove); preview.append(header);
            if (/\.(png|jpe?g|gif|webp)$/i.test(file.name)) { const image = document.createElement('img'); image.src = url; image.alt = 'Preview of selected file'; preview.append(image); }
            else if (/\.pdf$/i.test(file.name)) { const frame = document.createElement('iframe'); frame.src = url; frame.title = 'Selected PDF preview'; preview.append(frame); }
            const open = document.createElement('a'); open.href = url; open.target = '_blank'; open.rel = 'noopener'; open.textContent = 'Open full preview';
            const note = document.createElement('p'); note.textContent = 'Local preview only. Your file has not been sent. If the preview does not display, use Open full preview.'; preview.append(open,note);
        }
        input.addEventListener('change', update);
        zone.addEventListener('dragover', event => event.preventDefault());
        zone.addEventListener('drop', event => { event.preventDefault(); const file = event.dataTransfer?.files[0]; if (!file) return; const transfer = new DataTransfer(); transfer.items.add(file); input.files = transfer.files; input.dispatchEvent(new Event('change', {bubbles:true})); });
        window.addEventListener('pagehide', () => { if(url) URL.revokeObjectURL(url); url=null; previous=null; });
        window.addEventListener('pageshow', update);
        update();
    });
})();
