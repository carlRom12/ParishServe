/**
 * service-payment.js
 * [data-service-payment] sections on request step 2 (wedding, baptism):
 * keeps "Amount to send" in sync with what the visitor chose.
 *  - Wedding: radios named paymentOption, each with data-amount.
 *  - Baptism: the type was picked on Step 1, so data-amount-from="<draft
 *    key>:<field>" reads it from frontend.js's draft and data-amount-map
 *    turns it into a label + amount.
 * The screenshot input is deliberately unnamed so request-uploads.js doesn't
 * send it to a server that has no field for it.
 */
(function () {
    document.querySelectorAll('[data-service-payment]').forEach(section => {
        const amount = section.querySelector('[data-pay-amount]');
        const label = section.querySelector('[data-pay-label]');
        const map = section.dataset.amountMap ? JSON.parse(section.dataset.amountMap) : null;
        const update = () => {
            if (map) {
                const [key, field] = section.dataset.amountFrom.split(':');
                let draft = {};
                try { draft = JSON.parse(sessionStorage.getItem(key) || '{}') || {}; } catch (_) { /* storage off */ }
                const entry = map[draft[field]];
                amount.textContent = entry ? entry.amount : 'Choose a type in Step 1';
                if (label) label.textContent = entry ? entry.label : '';
                return;
            }
            const checked = section.querySelector('input[name="paymentOption"]:checked');
            amount.textContent = checked ? checked.dataset.amount : 'Choose an option';
        };
        section.addEventListener('change', update);
        // frontend.js restores the saved draft before this runs, so reflect it.
        update();
    });
})();
