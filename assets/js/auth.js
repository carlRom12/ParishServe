function setAuthFieldError(input, message) {
    const field = input.closest('.auth-field');
    let error = document.getElementById(input.id + 'Error') || field?.querySelector('[data-field-error]');
    if (!error) {
        error = document.createElement('small');
        error.id = input.id + 'Error';
        error.className = 'auth-field-error';
        (field || input.parentElement).append(error);
    }
    input.setAttribute('aria-describedby', error.id);
    input.setAttribute('aria-invalid', String(Boolean(message)));
    field?.classList.toggle('has-error', Boolean(message));
    error.textContent = message || '';
    error.hidden = !message;
}

(async function () {
    const alert = document.querySelector('[data-auth-alert]');
    const message = document.querySelector('[data-auth-message]');
    const show = text => {
        message.textContent = text;
        alert.hidden = false;
    };
    const page = location.pathname.split('/').pop().replace('.html', '');
    const birthDate = document.getElementById('dateOfBirth');
    if (birthDate) {
        const today = new Date();
        birthDate.max = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
    }
    document.querySelectorAll('[data-otp-form], [data-register-form]').forEach(form => {
        form.addEventListener('submit', event => {
            if (location.protocol === 'file:') {
                event.preventDefault();
                event.stopImmediatePropagation();
                show('Account services are unavailable offline. Please open ParishServe through the parish server.');
            }
        }, true);
    });
    if (location.protocol === 'file:') return;
    try {
        const response = await fetch(`auth-state.php?page=${encodeURIComponent(page)}`, { cache: 'no-store' });
        if (!response.ok) return;
        const state = await response.json();
        if (state.error || state.success) show(state.error || state.success);
        const email = document.querySelector('[data-pending-email]');
        if (email && state.email) email.textContent = state.email;
        const form = document.querySelector('[data-register-form]');
        for (const [name, value] of Object.entries(state.oldInput || {})) {
            const input = form?.elements.namedItem(name);
            if (!input || input.type === 'password' || input.type === 'file') continue;
            if (input.type === 'checkbox') input.checked = Boolean(value);
            else input.value = value;
            input.dispatchEvent(new Event('change'));
        }
    } catch (_) {
        // Static hosting still supports browsing; account actions require PHP.
    }
})();
