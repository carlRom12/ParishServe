(() => {
    document.querySelectorAll('.wh-accordion').forEach(details => {
        const summary = details.querySelector('summary');
        details.addEventListener('toggle', () => {
            summary.setAttribute('aria-expanded', String(details.open));
        });
    });
})();
