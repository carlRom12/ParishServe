(function () {
    const button = document.getElementById('conf-expand-faq');
    const questions = Array.from(document.querySelectorAll('.conf-faq-item'));
    if (!button) return;
    const sync = () => {
        const expanded = questions.every(question => question.open);
        button.setAttribute('aria-expanded', String(expanded));
        button.firstChild.textContent = expanded ? 'Collapse All FAQs ' : 'View All FAQs ';
    };
    button.addEventListener('click', () => {
        const expanded = button.getAttribute('aria-expanded') !== 'true';
        questions.forEach(question => { question.open = expanded; });
        sync();
    });
    questions.forEach(question => question.addEventListener('toggle', sync));
})();
