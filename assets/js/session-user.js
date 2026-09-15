/**
 * session-user.js
 * ---------------------------------------------------------------------
 * Every parishioner page ships its user chip as a guest
 * ([data-session-name] / [data-session-initial] / [data-session-role])
 * and its sidebar link ([data-session-auth-link]) as "Log out". This asks
 * session-user.php who is actually signed in: a signed-in person gets
 * their own name and role, a visitor gets a "Log in" link instead.
 * Fires `ps:session-user` (detail = the reply) once someone is signed in.
 * ---------------------------------------------------------------------
 */
(function () {
    const link = document.querySelector('[data-session-auth-link]');

    function setAuthLink(loggedIn) {
        if (!link) return;
        link.href = loggedIn ? 'logout.php' : 'login.html';
        const label = link.querySelector('span');
        if (label) label.textContent = loggedIn ? 'Log out' : 'Log in';
    }

    if (location.protocol === 'file:') {
        setAuthLink(false);
        return;
    }

    fetch('session-user.php', { cache: 'no-store', credentials: 'same-origin', headers: { Accept: 'application/json' } })
        .then((response) => (response.ok ? response.json() : null))
        .then((user) => {
            const loggedIn = Boolean(user && user.loggedIn);
            setAuthLink(loggedIn);
            if (!loggedIn) return;

            document.querySelectorAll('[data-session-name]').forEach((el) => { el.textContent = user.fullName || user.firstName; });
            document.querySelectorAll('[data-session-initial]').forEach((el) => { el.textContent = (user.firstName || '?').charAt(0).toUpperCase(); });
            document.querySelectorAll('[data-session-role]').forEach((el) => { el.textContent = user.role; });
            document.dispatchEvent(new CustomEvent('ps:session-user', { detail: user }));
        })
        .catch(() => setAuthLink(false));
})();
