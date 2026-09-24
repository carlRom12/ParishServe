/**
 * donation-modal.js
 * ---------------------------------------------------------------------
 * donation-request.php: the donation form lives in <dialog id="donationModal">.
 *   [data-donation-open]         "Give Now" -- a new donation
 *   [data-donation-edit="{json}"] "Edit" on a donation still awaiting
 *                                verification -- the same form, filled with
 *                                that donation and posting its edit_id, so
 *                                the server updates it (ps_submit_donation_edit())
 *   [data-donation-cancel]       Cancel / x (and Esc) close it and discard what
 *                                was entered, including a staged proof upload
 * After a failed submit the server sends the page back with the errors
 * inside the form (and edit_id, if it was an edit), so it opens again.
 * frontend.js keeps the fields in the parishserve-draft-donations draft.
 * ---------------------------------------------------------------------
 */
// The history's status filter applies as soon as it's changed (the search applies on Enter).
document.querySelectorAll('[data-auto-submit]').forEach((select) => select.addEventListener('change', () => select.form.requestSubmit()));

(function () {
    const dialog = document.getElementById('donationModal');
    if (!dialog) return;
    const form = dialog.querySelector('form');
    const editId = form.querySelector('[data-edit-id]');
    const proof = form.elements.proofOfPayment;
    const anonymous = form.elements.isAnonymous;
    const gcash = form.elements.gcashReference;
    const note = form.elements.donationNote;
    const title = dialog.querySelector('[data-donation-modal-title]');
    const submit = form.querySelector('[data-donation-submit]');
    const currentProof = form.querySelector('[data-current-proof]');
    const proofField = currentProof.closest('.ps-field');
    const submitLabel = submit.innerHTML;
    const fields = ['donationPurpose', 'donationAmount', 'gcashReference', 'donorName', 'donorEmail', 'donorContact', 'donationNote'];

    const editButtonFor = (id) => Array.from(document.querySelectorAll('[data-donation-edit]'))
        .map((button) => JSON.parse(button.dataset.donationEdit))
        .find((data) => String(data.id) === String(id));

    // Edit mode: the donation already has a proof of payment, so a new one is optional.
    function setMode(data) {
        editId.value = data ? data.id : '';
        title.textContent = data ? `Edit Donation No. ${data.reference}` : 'Give Now';
        submit.innerHTML = data ? 'Save Changes &rarr;' : submitLabel;
        proof.dataset.uploadRequired = data ? '' : 'true';
        proof.required = !data && !proof.dataset.uploaded;
        currentProofUrl = data && data.proofUrl ? data.proofUrl : '';
        if (currentProofUrl) {
            currentProof.querySelector('[data-current-proof-link]').href = currentProofUrl;
            currentProof.querySelector('[data-current-proof-image]').src = currentProofUrl;
        }
        showCurrentProof();
    }

    // While editing, the proof already on file is shown in place of the
    // dropzone until a new screenshot is chosen; removing that one brings it back.
    let currentProofUrl = '';
    function showCurrentProof() {
        const show = Boolean(currentProofUrl) && !proof.dataset.uploaded && !(proof.files && proof.files.length);
        currentProof.hidden = !show;
        proofField.classList.toggle('is-showing-current-proof', show);
    }

    const changed = (element, type = 'change') => element.dispatchEvent(new Event(type, { bubbles: true }));

    // Back to a blank form (account name as the default) and an empty draft.
    function clear(keepErrors = false) {
        if (proof.dataset.uploaded) proof.dispatchEvent(new CustomEvent('ps:request-remove'));
        form.reset();
        editId.value = '';
        proof.value = '';
        if (!keepErrors) form.querySelector('[data-form-errors]')?.remove();
        changed(anonymous);   // main.js unlocks Full Name; frontend.js saves the draft
        changed(note, 'input'); // the 0/500 counter
        changed(proof);       // upload-preview.js drops its preview
    }

    function fill(data) {
        anonymous.checked = false;
        changed(anonymous);
        fields.forEach((name) => { form.elements[name].value = data[name] ?? ''; });
        anonymous.checked = data.isAnonymous !== '';
        changed(anonymous);
        changed(note, 'input');
    }

    function open() {
        document.body.classList.add('dn-modal-open');
        dialog.showModal();
        dialog.scrollTop = 0;
    }

    function close() {
        clear();
        setMode(null);
        document.body.classList.remove('dn-modal-open');
        if (dialog.open) dialog.close();
    }

    document.querySelectorAll('[data-donation-open]').forEach((button) => button.addEventListener('click', () => {
        if (editId.value) clear(); // leaving an edit: start the new donation blank
        setMode(null);
        open();
    }));

    document.querySelectorAll('[data-donation-edit]').forEach((button) => button.addEventListener('click', () => {
        const data = JSON.parse(button.dataset.donationEdit);
        clear();
        fill(data);
        setMode(data);
        changed(form); // save the filled values as the draft
        open();
    }));

    dialog.querySelectorAll('[data-donation-cancel]').forEach((button) => button.addEventListener('click', close));
    dialog.addEventListener('cancel', (event) => { event.preventDefault(); close(); }); // Esc

    // GCash reference numbers are digits only (spaces as shown on the receipt are dropped).
    gcash.addEventListener('input', () => {
        const digits = gcash.value.replace(/\D/g, '');
        if (digits !== gcash.value) gcash.value = digits;
    });

    // request-uploads.js restores a staged proof after this runs; keep it optional while editing.
    proof.addEventListener('ps:upload-cleared', () => { if (editId.value) proof.required = false; showCurrentProof(); });
    proof.addEventListener('ps:upload-staged', showCurrentProof);
    proof.addEventListener('change', showCurrentProof);
    currentProof.querySelector('[data-current-proof-replace]').addEventListener('click', () => proof.click());

    // A saved edit: frontend.js restored the old draft before it was cleared, so blank the form too.
    const errors = form.querySelector('[data-form-errors]');
    if (document.querySelector('.dh-flash[data-clear-draft]')) {
        form.reset();
        editId.value = '';
        changed(anonymous);
        try { sessionStorage.removeItem('parishserve-draft-donations'); } catch (_) { /* nothing to clear */ }
    }

    // Reopen after a failed submit (the errors are inside the form), in
    // edit mode if it was an edit. An edit left in the draft from an
    // earlier visit (closed some other way) is discarded instead.
    const editing = editId.value ? editButtonFor(editId.value) : null;
    if (editId.value && (!errors || !editing)) {
        clear(true);
        setMode(null);
    } else {
        setMode(editing);
    }
    if (errors) {
        open();
        errors.focus();
    } else if (location.hash === '#give') {
        open(); // donations.html's "Donate via GCash QR" goes straight to the form
    }
})();
