(async function () {
    const key = 'confirmation-file-preview';
    const input = document.querySelector('input[type="file"]');
    const view = document.querySelector('.wr3-doc-view');
    const name = document.querySelector('.wr3-doc-name');
    if (!input && !view) return;
    let db;
    try {
        db = await new Promise((resolve, reject) => {
            const request = indexedDB.open('parishserve-file-previews', 1);
            request.onupgradeneeded = () => request.result.createObjectStore('files');
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    } catch (_) {
        if (view) { view.textContent = 'Please reselect your file'; name.textContent = 'Preview unavailable'; }
        return;
    }
    let token = sessionStorage.getItem(key);
    if (!token) { token = crypto.randomUUID(); sessionStorage.setItem(key, token); }
    const write = file => new Promise((resolve, reject) => {
        const tx = db.transaction('files', 'readwrite');
        if (file) tx.objectStore('files').put({ file, expires: Date.now() + 3600000 }, token);
        else tx.objectStore('files').delete(token);
        tx.oncomplete = resolve; tx.onerror = () => reject(tx.error);
    });
    // Expire temporary local previews after one hour.
    const clean = db.transaction('files', 'readwrite').objectStore('files').openCursor();
    clean.onsuccess = () => { const c = clean.result; if (c) { if (c.value.expires < Date.now()) c.delete(); c.continue(); } };
    if (input) {
        const form = input.form;
        let pending = Promise.resolve();
        input.addEventListener('change', () => {
            const file = input.files[0];
            pending = pending.catch(() => {}).then(() => write(file && /\.(pdf|png|jpe?g)$/i.test(file.name) && file.size <= 5 * 1024 * 1024 ? file : null));
            pending.catch(() => {});
        });
        form.addEventListener('submit', async event => {
            event.preventDefault(); event.stopImmediatePropagation();
            if (!form.reportValidity()) return;
            try {
                await pending;
                await write(input.files[0]);
                location.href = 'confirmation-request-step4.html';
            } catch (_) { alert('The file preview could not be saved locally. Please select the file again and retry.'); }
        }, true);
        return;
    }
    const record = await new Promise(resolve => {
        const request = db.transaction('files').objectStore('files').get(token);
        request.onsuccess = () => resolve(request.result); request.onerror = () => resolve(null);
    });
    if (!record || record.expires < Date.now()) {
        name.textContent = 'No file available. Please return to Upload Document and select it again.';
        view.textContent = 'Select file'; view.title = '';
        const link = document.createElement('a'); link.href = 'confirmation-request-step3.html'; link.textContent = 'Select file'; view.replaceWith(link);
        return;
    }
    const url = URL.createObjectURL(record.file);
    name.textContent = record.file.name;
    const link = document.createElement('a'); link.href = url; link.target = '_blank'; link.rel = 'noopener'; link.textContent = 'View file';
    link.style.cssText = 'color:#8a2642;text-decoration:underline;font-weight:600;font-size:13px';
    view.replaceWith(link);
    window.addEventListener('pagehide', () => URL.revokeObjectURL(url));
})();
