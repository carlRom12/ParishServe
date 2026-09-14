(function () {
    document.addEventListener('click', async function (event) {
        const link = event.target.closest('.ca-tabs a[href="baptism-request.html"]');
        if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) return;
        if (location.pathname.endsWith('/baptism-request.html')) return;
        event.preventDefault();
        try {
            const response = await fetch(link.href);
            if (!response.ok) throw new Error('Request unavailable');
            const page = new DOMParser().parseFromString(await response.text(), 'text/html');
            const incomingNav = page.querySelector('.ca-tabs');
            const currentNav = document.querySelector('.ca-tabs');
            if (!incomingNav || !currentNav) throw new Error('Missing navigation');
            page.querySelectorAll('link[rel="stylesheet"]').forEach(style => {
                if (![...document.querySelectorAll('link[rel="stylesheet"]')].some(existing => existing.href === style.href)) document.head.append(style.cloneNode());
            });
            while (currentNav.nextSibling) currentNav.nextSibling.remove();
            let node = incomingNav.nextSibling;
            while (node) { currentNav.parentElement.append(node.cloneNode(true)); node = node.nextSibling; }
            currentNav.replaceWith(incomingNav.cloneNode(true));
            history.pushState(null, '', link.href);
            document.title = page.title;
            for (const source of ['assets/js/frontend.js', 'assets/js/main.js']) {
                await new Promise((resolve, reject) => { const script = document.createElement('script'); script.src = source; script.onload = resolve; script.onerror = reject; document.body.append(script); });
            }
        } catch (_) { location.href = link.href; }
    });
    window.addEventListener('popstate', () => location.reload());
})();
