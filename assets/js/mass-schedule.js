(() => {
    const date = document.getElementById('preferredDate');
    const time = document.getElementById('preferredTime');
    const data = document.getElementById('mass-schedule-data');
    if (!date || !time || !data) return;
    const schedule = JSON.parse(data.textContent);
    function refresh() {
        const previous = time.value;
        const day = new Date(date.value + 'T00:00:00');
        const times = Number.isNaN(day.getTime()) ? [] : schedule[day.getDay() === 0 ? 'sunday' : 'weekday'];
        time.replaceChildren(new Option(times.length ? 'Select preferred time' : 'Choose a Mass date first', ''));
        times.forEach(value => {
            const [hour, minute] = value.split(':').map(Number);
            const label = `${hour % 12 || 12}:${String(minute).padStart(2, '0')} ${hour >= 12 ? 'PM' : 'AM'}`;
            time.add(new Option(label, label));
        });
        time.value = Array.from(time.options).some(option => option.value === previous) ? previous : '';
        time.dispatchEvent(new Event('change', {bubbles: true}));
    }
    date.addEventListener('change', refresh);
    refresh();
})();
