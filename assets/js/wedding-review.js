/**
 * wedding-review.js
 * ---------------------------------------------------------------------
 * wedding-request-step3.php's review cards, filled from the wedding draft
 * that frontend.js keeps in sessionStorage. The documents list
 * ([data-uploaded-docs]) is filled by request-uploads.js from what the
 * server actually received, and the form submits for real through
 * initWizardStepForm() in main.js.
 * ---------------------------------------------------------------------
 */
(function () {
    const review = document.getElementById('weddingReview');
    if (!review) return;

    let draft = {};
    try { draft = JSON.parse(sessionStorage.getItem('parishserve-draft-wedding') || '{}') || {}; } catch (_) { /* no storage */ }

    const date = (value) => (value ? new Date(value + 'T00:00:00').toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : '');
    const name = (prefix) => ['FirstName', 'MiddleName', 'LastName', 'Suffix'].map((key) => draft[prefix + key]).filter(Boolean).join(' ');
    const groups = [
        ['1. The Couple', [['Groom', name('groom')], ['Bride', name('bride')], ['Contact mobile number', draft.mobileNumber], ['Contact email address', draft.emailAddress]]],
        ['2. Preferred Schedule', [['Wedding date', date(draft.weddingDate)], ['Seminar date', date(draft.seminarDate)], ['Seminar time', draft.seminarTime], ['Seminar location', draft.seminarLocation]]],
    ];

    for (const [title, rows] of groups) {
        const card = document.createElement('section');
        card.className = 'wr3-review-card';
        const heading = document.createElement('h3');
        heading.textContent = title;
        card.append(heading);
        for (const [label, value] of rows) {
            const row = document.createElement('div');
            row.className = 'wr3-review-row';
            const labelEl = document.createElement('span');
            const valueEl = document.createElement('strong');
            labelEl.textContent = label;
            valueEl.textContent = value || 'Not provided';
            row.append(labelEl, valueEl);
            card.append(row);
        }
        review.append(card);
    }
})();
