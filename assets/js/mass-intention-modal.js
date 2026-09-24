/**
 * mass-intention-modal.js
 * ---------------------------------------------------------------------
 * mass-intention-request.php: the 3-step request form lives in
 * <dialog id="massIntentionModal">, the way donation-request.php keeps its
 * form in #donationModal.
 *   [data-mi-open]     "Request a Mass Intention" opens the form
 *   [data-mi-cancel]   Cancel / x (and Esc) close it and discard what was
 *                      entered, so the next visit starts on step 1
 *   [data-auto-submit] the history's status filter applies on change
 *                      (the search applies on Enter)
 * After a failed submit the server sends the page back with the errors
 * inside the form, so it opens again. The steps themselves, validation and
 * the draft are mass-intention-request.js's and frontend.js's work.
 * ---------------------------------------------------------------------
 */
document.querySelectorAll('[data-auto-submit]').forEach((select) => select.addEventListener('change', () => select.form.requestSubmit()));

(function () {
    const dialog = document.getElementById('massIntentionModal');
    if (!dialog) return;
    const form = dialog.querySelector('form');

    function open() {
        document.body.classList.add('dn-modal-open');
        dialog.showModal();
        dialog.scrollTop = 0;
        form.querySelector('[data-form-errors]')?.focus();
    }

    // Closing throws the half-finished request away: the form goes back to
    // step 1 (mass-intention-request.js listens for reset) and its draft is cleared.
    function close() {
        form.reset();
        form.dispatchEvent(new Event('change', { bubbles: true }));
        try { sessionStorage.removeItem('parishserve-draft-mass-intention'); } catch (_) { /* nothing to clear */ }
        document.body.classList.remove('dn-modal-open');
        if (dialog.open) dialog.close();
    }

    document.querySelectorAll('[data-mi-open]').forEach((button) => button.addEventListener('click', open));
    dialog.querySelectorAll('[data-mi-cancel]').forEach((button) => button.addEventListener('click', close));
    dialog.addEventListener('cancel', (event) => { event.preventDefault(); close(); }); // Esc

    if (form.querySelector('[data-form-errors]') || location.hash === '#request') {
        open();
    }
})();
