# ParishServe — Claude Code Build Spec (v2)

Paste this whole document into Claude Code. It's grounded in what's actually in your
zipped project (including its own `CLAUDE.md`), not a generic guess — file/table/column
names below are real.

---

## 0. Ground truth (Claude Code should confirm, not assume, before coding)

- Stack: static HTML5/CSS3/vanilla JS pages + a thin PHP+MySQL backend. XAMPP (Apache,
  PHP 8.0, MariaDB 10.4). No build step, no package manager, no framework, no tests.
  Read the repo's own `CLAUDE.md` first — don't contradict it.
- **The live `parish_serve.users` table already has a 3-value role enum**:
  `Parishioner|Admin|Super Admin`, plus `firstname/middlename/lastname/suffix`,
  `date_of_birth`, `gender`, `mobile_number`, `email`, `password_hash`,
  `email_verified`, `otp_hash`, `otp_expires_at`, `status`.
  `database/schema.sql` in the repo is a **stale demo schema** (different column names,
  only `parishioner`/`admin`) — don't import it over the live DB; use it only as a
  reference for the *request tables* (see below), not for `users`.
- `login_register.php` never sets `role` on insert — confirm the column default is
  `Parishioner`; if there's no default, add one so public self-registration can never
  create an Admin/Super Admin account.
- **Login is not implemented.** `main.js`'s `initLoginForm` just fakes a delay and shows
  "Sign-in is not available yet." There's no session anywhere.
- **No admin page has an auth guard.** `admin-*.php` pages are reachable by anyone who
  types the URL — `includes/admin-sidebar.php`'s own comment says so.
- `login.html` already links to a `forgot-password.html` that **doesn't exist**.
- Reusable OTP infra: `includes/otp-mailer.php` (PHPMailer, Gmail SMTP via
  `includes/mail-config.php`), 6-digit OTP, bcrypt-hashed, `verify-otp.php` shows the
  verify/resend pattern. **Never print, copy, or move the credentials in
  `includes/mail-config.php`** — always `require_once` it.
- **`admin-requests.php` and `admin-donations.php` are 100% hardcoded PHP arrays**
  (both files say so in their own doc comments) mirroring `database/schema.sql`'s seed
  rows. The "Update" modal's Save button only repaints the row's status pill on screen
  via `initAdminModals()` in `main.js` — nothing is ever persisted.
- The request/donation tables already share one status pipeline by design:
  `submitted → under_review → approved → scheduled → completed` (or `rejected` at any
  point before `completed`) — see `schema.sql`'s comments. Confirm which of these
  tables (`wedding_requests`, `baptism_requests`, `confirmation_requests`,
  `funeral_requests`, `counseling_appointments`, `mass_intentions`,
  `facility_reservations`, `donations`) actually exist in the **live** DB vs. only in
  the stale `schema.sql` — `CLAUDE.md` says live currently only has `users`. Create
  whichever are missing, matching `schema.sql`'s column design, rather than faking data
  again.
- `admin-requests.php` already defines the 7 request type keys, their labels, the 6
  status values, and (for sacrament types) a per-type required-document checklist —
  reuse these constants, don't reinvent them.
- `ParishServe/admin-announcements.php` is a stray duplicate of the root one — flag it,
  don't silently keep both in sync.
- JS convention: **IIFE-per-feature, activated by `data-*` attributes**
  (`data-wizard-step-form`, `data-mock-form`, `data-admin-row`, `data-modal-trigger`,
  `data-admin-type-tab`), not page-by-page checks or a new framework.
- Asset load order per page: `style.css` → page CSS → `responsive.css?v=N`; scripts:
  `frontend.js` → `main.js` → `responsive.js?v=N` → optional page script.

---

## 1. Create a Super Admin account directly in phpMyAdmin

No app UI needed for this one — it's a one-time manual seed.

1. Confirm the live `users` table's exact required columns (`DESCRIBE users;`).
2. Because phpMyAdmin can't compute PHP's bcrypt, generate the password hash first:
   `php -r "echo password_hash('CHOOSE-A-TEMP-PASSWORD', PASSWORD_DEFAULT);"`
3. Give me the exact `INSERT INTO users (...) VALUES (...)` statement to run in
   phpMyAdmin's SQL tab, with `role = 'Super Admin'`, `status = 'active'`,
   `email_verified = 1` (so it isn't stuck behind the OTP flow that regular
   registration goes through), and the hash from step 2 in `password_hash`.
4. Tell me explicitly to change the temp password after first login — don't leave a
   real plaintext password committed anywhere in code or docs.

*(This account is only useful once real login exists — see §2 — so if you do §1 and §2
in the same Claude Code session, do §2 first so you can verify the seed actually logs
in.)*

## 2. Super Admin has access to all admin features

1. **Build real login.** `login.html`'s form should post to a new `login.php`:
   look up by email, `password_verify()`, require `email_verified = 1` and
   `status = 'active'`, then `session_start()` and store `$_SESSION['user_id']`,
   `$_SESSION['user_role']`, `$_SESSION['user_name']`. On failure, reuse the existing
   flash-via-`auth-state.php` pattern (there's already a `login_success` case in there
   to extend). Redirect Parishioner → the parishioner dashboard; Admin/Super Admin →
   `admin-dashboard.php`.
2. **Add `includes/auth-guard.php`**, required at the top of every `admin-*.php`
   file: redirect to `login.html` if there's no session, or if
   `$_SESSION['user_role']` isn't `Admin` or `Super Admin`.
3. **Super Admin sees everything Admin sees, with nothing hidden** — dashboard,
   requests, donations, announcements, and anything else under `admin-*.php`. Don't
   build a narrower Super-Admin-only surface unless a specific feature needs to be
   Admin-restricted (there isn't one in this pass) — the two roles can share the same
   nav for now; just don't let a Parishioner or logged-out visitor in.
4. Expose a small helper (e.g. `ps_current_role()`) in the guard so any future
   Super-Admin-only feature can gate on it without rewriting this.

**Acceptance:** the seeded Super Admin can log in and reach every `admin-*.php` page;
an unauthenticated visitor hitting any admin URL is redirected to `login.html`; a
Parishioner account cannot reach any admin URL.

## 3. Update admin features: real actions on parishioner requests

This is the core piece of work. Replace the hardcoded arrays with real data and real
writes, using the existing UI/JS conventions — don't redesign the page.

1. **`admin-requests.php`**: replace the `$requests` hardcoded array with a query that
   `UNION`s the 7 request tables (matching the type/status/document-checklist shape the
   file already defines), so the existing type tabs, status filter, search, and
   document-checklist modal (`initAdminTableFilters()`, `initAdminModals()`) keep
   working unchanged on the frontend.
2. **`admin-donations.php`**: same pattern for the `donations` table, including
   surfacing `proof_of_payment` so staff can view what was uploaded before approving.
3. **Add a write endpoint**, e.g. `admin-update-request.php`, taking request type +
   id/reference number + new status + optional remarks, and running the matching
   `UPDATE ... SET status = ?, remarks = ? WHERE id = ?` against the right table (map
   `type` → table name using the same `$typeLabels` keys `admin-requests.php` already
   has). Wire the existing modal's "Save" button to POST here instead of only mutating
   the DOM.
4. **Enforce the status pipeline server-side**: don't let the endpoint accept an
   arbitrary status jump if the design intends order (`submitted → under_review →
   approved → scheduled → completed`, or `rejected` before `completed`) — if you're
   unsure whether skips should be allowed (e.g. `submitted` straight to `approved`),
   ask me rather than guessing.
5. The role guard from §2 must sit in front of these — an Admin (or Super Admin) action
   here should never be reachable by a Parishioner or a logged-out request.
6. Delete/consolidate the stray `ParishServe/admin-announcements.php` duplicate rather
   than maintaining two copies.

**Acceptance:** approving/rejecting/scheduling a request (wedding, baptism,
confirmation, funeral, counseling, mass intention, facility, donation) in the admin UI
persists — reloading the page or logging back in later still shows the new status —
and the type tabs/status filter/document checklist still work exactly as before against
real rows instead of the mock array.

## 4. Forgot password page (OTP → new password)

Reuse the existing OTP/PHPMailer plumbing, but keep it separate from the registration
OTP so a pending registration and a pending password reset on the same account can't
clobber each other — add `reset_otp_hash` / `reset_otp_expires_at` columns rather than
reusing `otp_hash`/`otp_expires_at`.

1. **`forgot-password.html`** — static page in the existing auth-page style
   (`login.css`/`register.css` conventions) — this is the missing page `login.html`
   already links to. Email input → posts to:
2. **`forgot-password.php`**: look up the email; generate a 6-digit OTP, bcrypt-hash
   it, set an expiry, email it via a distinct password-reset function in
   `includes/otp-mailer.php` (don't reuse copy that says "verification code" for this).
   Store `$_SESSION['pending_reset_user_id']`, redirect to `verify-reset-otp.html`.
   Decide with me whether to reveal "no account with that email" directly (matches the
   existing registration flow's style) or show a generic "if that email exists..."
   message (more standard practice) — I don't have a strong preference, flag it.
3. **`verify-reset-otp.html` + `verify-reset-otp.php`** — same verify/resend shape as
   `verify-otp.php`, checking the new `reset_otp_*` columns; on success set a
   short-lived `$_SESSION['reset_verified_user_id']` and redirect to
   `reset-password.html`.
4. **`reset-password.html` + `reset-password.php`**: only reachable with a valid
   `reset_verified_user_id` in session; same password rules as registration (≥ 8 chars,
   confirm match); update `password_hash`, clear the reset columns and session key,
   flash success via `auth-state.php`, redirect to `login.html`.
5. Extend `auth-state.php` with cases for `forgot-password` / `verify-reset-otp` /
   `reset-password`, same static-HTML-page-with-flash-fetch pattern as the others.
6. Fix the existing copy/code mismatch while you're in this code: the OTP email text
   says "expires in 5 minutes" but the code sets a 180-second (3-minute) expiry — pick
   one and make both match, for this new flow and ideally the existing one too.

**Acceptance:** "Forgot Password?" on `login.html` reaches a working page; a
parishioner (or admin/staff) can request a code by email, enter it, set a new password,
and log in with it; expired/wrong codes are rejected clearly; resend works; the flow
can't be replayed after completion or used to reset someone else's password without
their email.

## 5. Make the project interactive

Earlier in this conversation I confirmed this means: **front-end polish** (animations,
hover/transition effects, loading states) plus **real-time bits** (live calendar
availability, dashboard stat counts, notifications) — not rewriting every page's visual
design. With §3 now making request/donation data real, this has real data to work with.

1. **Polish**: transitions on sidebar nav hover/active, admin table row hover, modal
   open/close, toast show/dismiss (currently instant), carousel transitions
   (`data-carousel`), calendar month nav. Extend the existing `is-loading` button
   pattern (already used on login/register) to the forgot-password/reset-password
   submit buttons, wizard "Next" buttons, and the admin modal "Save" button. CSS
   transitions / small vanilla JS only — no animation library.
2. **Live calendar availability**: `calendar.html`/`calendar.js` currently render
   seeded/hardcoded events around May 2026. Add a read endpoint (e.g.
   `calendar-events.php?month=&year=`) querying the real request tables so the calendar
   (and ideally the wizard datepickers) reflect actually-booked dates.
3. **Dashboard stat counts**: `admin-dashboard.php` (and the parishioner-facing
   dashboard) currently use hardcoded arrays. Replace with real `COUNT(...)` queries
   using the same status-pipeline grouping (Pending = `submitted` + `under_review`,
   plus Approved/Scheduled/Completed) — this now reflects real approvals/rejections
   made in §3.
4. **Notifications**: since §3 makes status updates real, extend the existing toast
   (already fires on save) to fire only after the `admin-update-request.php` write
   actually succeeds (not optimistically before). Optionally add a small unread-count
   badge on a bell icon that polls a lightweight `pending-count.php` endpoint every
   30–60 seconds. No websockets/SSE — stay within "no new infra."

**Acceptance:** admin interactions (table/modal/toast/nav) feel less abrupt; a month
with real seeded/approved requests shows those dates as booked on the calendar from DB
data; dashboard stat cards change when a request's status changes in §3 and the page is
reloaded; the toast only appears after a real successful save.

---

## 6. Constraints (don't violate)

- No new frontend framework, bundler, or package manager — vanilla JS/PHP/MySQL only.
- Don't restructure the "duplicated nav per HTML page" layout as part of this work —
  `CLAUDE.md` already flags that as a separate, larger refactor.
- Don't import `database/schema.sql` over the live DB. Reconcile column names first, or
  write additive `ALTER TABLE` / `CREATE TABLE IF NOT EXISTS` statements against the
  real live schema.
- Never expose or duplicate the SMTP credentials in `includes/mail-config.php`.
- Keep client-side (`main.js`) and server-side PHP validation rules in sync whenever
  you touch either.
- All new passwords/OTPs: `password_hash()`/`password_verify()` only, never plaintext.

## 7. Suggested build order

1. Confirm the live schema for real (`DESCRIBE users;`, check which request tables
   exist) before writing code.
2. Real login + `includes/auth-guard.php` on all `admin-*.php` pages (§2).
3. Seed the Super Admin account via phpMyAdmin (§1) and verify it logs in.
4. Wire `admin-requests.php` / `admin-donations.php` to real data + the
   `admin-update-request.php` write endpoint (§3).
5. Forgot-password / OTP-reset flow end-to-end (§4).
6. Dashboard stat counts + calendar live availability, now backed by real data (§5.2–5.3).
7. Notification toast/badge tied to real writes (§5.4).
8. Front-end animation/loading-state polish pass across all of the above (§5.1).

## 8. Open questions to resolve with Claude Code before/while building

- Exact live `users` column list and `role` enum default.
- Which request tables actually exist live vs. only in the stale `schema.sql`.
- Should forgot-password reveal "no account with that email" or use a generic message?
- Should the status pipeline strictly enforce step order, or can staff jump straight
  from `submitted` to `approved`/`rejected`?
- Any Super-Admin-only feature intended beyond "sees everything Admin sees" (e.g.
  managing Admin/Staff accounts themselves) — not in this pass unless you say so.
