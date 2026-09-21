(() => {
    const root = document.querySelector('[data-mass-intention-history]');
    if (!root) return;
    const status = root.querySelector('[role="status"]');
    const list = root.querySelector('[data-history-list]');
    const button = root.querySelector('button');
    let cursor = null;
    let busy = false;
    const statuses = {submitted:'Awaiting review',under_review:'Under review',approved:'Approved',scheduled:'Scheduled',completed:'Completed',rejected:'Rejected'};
    async function load() {
        if (busy) return;
        busy = true;
        button.disabled = true;
        status.textContent = 'Loading your Mass Intentions…';
        try {
            const response = await fetch('mass-intention-history.php' + (cursor ? '?before=' + cursor : ''), {credentials:'same-origin',cache:'no-store'});
            if (response.status === 401) {
                status.textContent = 'Sign in to see Mass Intentions submitted through your account.';
                const link = document.createElement('a');
                link.href = 'login.html'; link.textContent = 'Sign in'; link.className = 'ps-btn ps-btn-outline';
                list.replaceChildren(link); button.hidden = true; return;
            }
            if (!response.ok) throw new Error('Unable to load');
            const data = await response.json();
            for (const item of data.intentions) {
                const card = document.createElement('article'); card.className = 'dh-entry';
                const heading = document.createElement('h3'); heading.textContent = item.reference_no;
                const type = document.createElement('strong'); type.textContent = item.intention_type;
                const subject = document.createElement('p'); subject.textContent = item.intention_for ? `For: ${item.intention_for}` : '';
                const date = document.createElement('p');
                const parsed = item.mass_date ? new Date(item.mass_date + 'T00:00:00') : null;
                date.textContent = 'Mass date: ' + (parsed && !Number.isNaN(parsed.getTime())
                    ? parsed.toLocaleDateString('en-PH',{year:'numeric',month:'long',day:'numeric'})
                    : 'Not yet scheduled');
                const badge = document.createElement('span'); badge.className = 'dh-status'; badge.textContent = statuses[item.status] || 'Awaiting review';
                card.append(heading,type,subject,date,badge); list.append(card);
            }
            cursor = data.nextCursor;
            button.hidden = !cursor;
            button.textContent = 'Load more Mass Intentions';
            status.textContent = list.children.length ? 'Showing your most recent Mass Intentions first.' : 'No Mass Intentions are linked to your account yet.';
        } catch (_) {
            status.textContent = 'We could not load your Mass Intention history. Please try again.';
            button.hidden = false; button.textContent = 'Try again';
        } finally { busy = false; button.disabled = false; }
    }
    button.addEventListener('click',load);
    load();
})();
