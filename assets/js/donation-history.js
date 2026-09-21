(() => {
    const root = document.querySelector('[data-donation-history]');
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
        status.textContent = 'Loading your donations…';
        try {
            const response = await fetch('donation-history.php' + (cursor ? '?before=' + cursor : ''), {credentials:'same-origin',cache:'no-store'});
            if (response.status === 401) {
                status.textContent = 'Sign in to see donations submitted through your account.';
                const link = document.createElement('a');
                link.href = 'login.html'; link.textContent = 'Sign in'; link.className = 'ps-btn ps-btn-outline';
                list.replaceChildren(link); button.hidden = true; return;
            }
            if (!response.ok) throw new Error('Unable to load');
            const data = await response.json();
            for (const item of data.donations) {
                const card = document.createElement('article'); card.className = 'dh-entry';
                const heading = document.createElement('h3'); heading.textContent = item.reference_no;
                const amount = document.createElement('strong'); amount.textContent = new Intl.NumberFormat('en-PH',{style:'currency',currency:'PHP'}).format(Number(item.amount));
                const purpose = document.createElement('p'); purpose.textContent = item.purpose || 'General Parish Fund';
                const date = document.createElement('p');
                const parsed = new Date(item.created_at.replace(' ', 'T'));
                date.textContent = 'Submitted ' + (Number.isNaN(parsed.getTime()) ? item.created_at : parsed.toLocaleDateString('en-PH',{year:'numeric',month:'long',day:'numeric'}));
                const badge = document.createElement('span'); badge.className = 'dh-status'; badge.textContent = statuses[item.status] || 'Awaiting review';
                card.append(heading,amount,purpose,date,badge); list.append(card);
            }
            cursor = data.nextCursor;
            button.hidden = !cursor;
            button.textContent = 'Load more donations';
            status.textContent = list.children.length ? 'Showing your most recent donations first.' : 'No donations are linked to your account yet.';
        } catch (_) {
            status.textContent = 'We could not load your donation history. Please try again.';
            button.hidden = false; button.textContent = 'Try again';
        } finally { busy = false; button.disabled = false; }
    }
    button.addEventListener('click',load);
    load();
})();
