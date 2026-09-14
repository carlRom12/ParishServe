(function () {
    const dialog = document.getElementById('request-counseling');
    document.querySelector('[data-counseling-request]')?.addEventListener('click', () => dialog.showModal());
})();
