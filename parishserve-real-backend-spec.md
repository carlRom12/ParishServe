# ParishServe — "Make It Real" Spec (real PHP backend, real DB, no more sample data)

Paste this whole document into Claude Code, in the `ParishServe1` project folder. It is
grounded in what's actually in the codebase right now (verified by reading the files,
not guessed) — including two important discoveries that contradict the project's own
older spec docs (`parishserve-claude-code-spec.md`, `parishserve-forms-backend-spec.md`).
Read those two for history/context, but **this document is the current source of truth**
for the four things you asked for.

---

## 0. Ground truth — read this before writing any code

**Discovery A — the request-forms backend already exists but is disconnected.**
`includes/request-forms.php`, `request-confirmation.php`, and `upload-document.php` are
fully written: they validate, generate reference numbers, insert rows, handle file
uploads. `includes/request-forms.php`'s own doc comment says the six final-step pages
are `wedding-request-step3.php`, `baptism-request-step2.php`,
`confirmation-request-step4.php`, `funeral-request-step4.php`,
`mass-intention-request-step3.php`, `donations.php` — **but on disk every one of those
is still a plain `.html` file** with no PHP in it at all, and the previous step's
`<form action="...">` still points at the old `.html` filename (confirmed in
`wedding-request-step2.html`, `mass-intention-request-step2.html`,
`confirmation-request-step3.html`, `funeral-request-step3.html`). So right now, **no
public form actually reaches the backend that was already built for it** — this is the
main blocker behind your item 4.

**Discovery B — three admin pages are still hardcoded despite the real data layer
existing.** `includes/request-types.php` already has real helper functions
(`ps_fetch_requests`, `ps_pending_counts`, `ps_count_requests_by_status`, etc.) that
`dashboard-data.php`, `calendar-events.php`, and `pending-count.php` already use
successfully against the live database. But:
- `admin-requests.php` still has its own doc comment saying *"FRONTEND ONLY this pass:
  `$requests` below is hardcoded... nothing is persisted anywhere"* — and it is: a
  literal PHP array of sample rows (Maria Santos, Baby Sofia, etc.).
- `admin-donations.php` — same pattern, doc comment admits it, one hardcoded seed row.
- `admin-dashboard.php` — doc comment says *"everything below is hardcoded sample data
  shaped like what a real query would return."*
- The Update modal's Save button on both admin pages (`data-mock-form="... Design
  preview only -- not connected to a database."`) only repaints the row on screen —
  even though `admin-update-request.php` (a real, working write endpoint with CSRF +
  role guard) already exists and is unused by these two pages.

**Discovery C — the DB story is already split into "stale demo" vs. "real," you just
need to finish retiring the stale one.** `database/schema.sql` is explicitly documented
in this repo's own `CLAUDE.md`/README as a **stale demo schema with seed data and demo
accounts**, different from the live `parish_serve` database. The *actual* schema changes
that were applied to your real, live database live in `database/migrations/`
(`001-auth-and-requests.sql`, `002-request-forms.sql`) — additive `ALTER TABLE` /
`CREATE TABLE IF NOT EXISTS` scripts meant to be run with `mysql.exe -u root parish_serve
< database\migrations\00N-....sql`. Your real data already lives in phpMyAdmin's
`parish_serve` database, reached through `config.php`'s `mysqli` connection
(`localhost`, `root`, no password, `parish_serve`) — this part is *already* what you
asked for in item 1; it just needs the two gaps above closed so real UI actions actually
write to it.

**What "the sql file" most likely means (item 2).** `database/schema.sql` is the file to
delete — it's demo-only, contradicts the live schema (different `users` columns, only
2-value role enum vs. the live 3-value one), and its seed rows are exactly the sample
data item 3 asks you to remove. Do **not** delete `database/migrations/*.sql` — those
are the real record of what changed the real, live database and should stay as
documentation even after they've been run.

---

## 1. Real PHP backend writing to the real phpMyAdmin database

Most of this already exists (see Discovery A) — the task is to **finish wiring it up**,
not build it from scratch.

1. Confirm (`DESCRIBE`) the live `parish_serve` tables that `database/migrations/001`
   and `002` were supposed to create/alter actually match what's live right now. If
   either migration hasn't been run yet, run it against the real DB before anything
   else (`C:\xampp\mysql\bin\mysql.exe -u root parish_serve < database\migrations\001-auth-and-requests.sql`,
   then `002-...`).
2. For each of the six public flows (wedding, baptism, confirmation, funeral, mass
   intention, donation), verify `includes/request-forms.php`'s validation, reference
   number generation, and insert logic against the live columns — fix any mismatch, but
   don't rewrite what already works.
3. Wire `admin-requests.php` and `admin-donations.php` to real queries using the
   existing `ps_fetch_requests()`/equivalent helpers in `includes/request-types.php`
   (extend those helpers rather than writing new ad hoc SQL, so `dashboard-data.php`,
   `calendar-events.php`, `pending-count.php`, and these two admin pages all agree on
   one status/type mapping).
4. Point both admin pages' Update modal at the **existing** `admin-update-request.php`
   endpoint instead of `data-mock-form` — it already has the CSRF check, role guard, and
   status-pipeline validation built; you're changing the frontend JS call, not writing a
   new backend endpoint.
5. Wire `admin-dashboard.php`'s stat cards to real `COUNT(...)` queries the same way
   `dashboard-data.php` already does for the parishioner-facing dashboard.

**Acceptance:** submitting any of the six public forms end-to-end creates one real row
in phpMyAdmin, visible immediately without anyone typing it in by hand. Approving,
rejecting, or updating a request/donation in the admin UI persists after a reload or
a fresh login. Dashboard stat cards on both the parishioner and admin dashboards reflect
real row counts.

## 2. Retire `database/schema.sql`

1. Delete `database/schema.sql` from the project.
2. Before deleting, diff its table definitions against `database/migrations/001` and
   `002` one more time — if either migration is missing a table/column that
   `schema.sql` defines and that a still-working feature depends on, add that piece to
   a new additive migration file (`003-....sql`) first. Don't lose real schema pieces
   just because the file they were drafted in is being removed.
3. Update `README.md`/`CLAUDE.md`'s references to `schema.sql` (both currently warn
   "don't import this over the live DB" — once it's gone, replace that warning with a
   pointer to `database/migrations/` as the actual schema history).
4. Do not touch `database/migrations/*.sql` — those stay as the real change log for the
   real database.

**Acceptance:** `database/schema.sql` no longer exists in the repo; nothing else
(`config.php`, any `require`/`include`, any doc) still references it; the live database
is unaffected because nothing in this step runs against it — it's a file deletion and a
doc cleanup, not a migration.

## 3. Remove hardcoded/sample data; everything shown comes from a real user or staff action

This directly follows from closing Discovery B. Be exhaustive — grep for the tells
before you start (`hardcoded`, `mock`, `sample`, `FRONTEND ONLY`, `data-mock-form`,
`Design preview only`) across the `.php`/`.html` files, not just the three below, in
case something was missed:

1. `admin-requests.php` — delete the hardcoded `$requests` array (and its doc comment
   admitting it's fake); replace with the real query from §1.3.
2. `admin-donations.php` — same, for `$donations`.
3. `admin-dashboard.php` — delete the hardcoded `$stats` array; replace with real counts
   from §1.5.
4. Check `dashboard.html`'s own inline "sample preview" content for logged-out visitors
   (mentioned in `dashboard-data.php`'s doc comment: *"Logged-out visitors get
   `{loggedIn: false}` and the page keeps its sample preview"*) — decide with the intent
   of item 3: either show an honest empty/"log in to see your requests" state instead of
   sample rows, or clearly label the preview as an example so a visitor never mistakes
   it for their real data. Flag this choice back to me if unsure which you'd prefer.
5. `announcements.html`, `calendar.html`/`calendar-events.php`, and any other page not
   already covered — confirm each is reading real rows (announcements likely needs its
   own real table + admin management UI if one doesn't already exist; check
   `admin-announcements.php` for the same "FRONTEND ONLY" pattern found in Discovery B,
   and delete the stray duplicate `ParishServe/admin-announcements.php` while you're
   there per the older spec's note 6, if it still exists).
6. Search the codebase once more after finishing 1–5 for any remaining seed-looking data
   (e.g. names like "Maria Santos," "Dela Cruz," reference numbers like `WED-2026-0001`)
   outside of comments/docs, to make sure nothing was missed.

**Acceptance:** a brand-new copy of the live database with zero rows in the request/
donation/announcement tables shows an honest empty state everywhere in the admin and
parishioner UI — no name, request, or donation appears anywhere in the app unless a
real parishioner or staff action put it there.

## 4. Convert the six final-step files from `.html` to `.php` (and fix what points at them)

This is Discovery A's fix — the backend for this already exists, so this is a rename +
rewire job, not new backend code.

1. Rename these six files, keeping their existing markup unchanged:
   - `wedding-request-step3.html` → `wedding-request-step3.php`
   - `baptism-request-step2.html` → `baptism-request-step2.php`
   - `confirmation-request-step4.html` → `confirmation-request-step4.php`
   - `funeral-request-step4.html` → `funeral-request-step4.php`
   - `mass-intention-request-step3.html` → `mass-intention-request-step3.php`
   - `donations.html` → `donations.php`
2. In each renamed file, add the `ps_handle_request_form('<flow>')` call and
   `ps_request_form_fields()` output that `includes/request-forms.php`'s doc comment
   already describes as the intended usage — check that doc comment closely, since it
   was written expecting exactly this file layout.
3. Update every `<form action="...">` that currently points at the old `.html` name to
   point at the new `.php` name — confirmed locations so far: `wedding-request-step2.html`
   (→ `wedding-request-step3.php`), `mass-intention-request-step2.html` (→
   `mass-intention-request-step3.php`), `confirmation-request-step3.html` (→
   `confirmation-request-step4.php`), `funeral-request-step3.html` (→
   `funeral-request-step4.php`). Also check `baptism-request.html`'s form action and
   any link to the old `donations.html` (nav links, buttons, the donation-about/how
   pages) — update all of them.
4. Search `assets/js/main.js`, `assets/js/frontend.js`, and `assets/js/funeral-request.js`
   for any hardcoded `.html` filename string comparisons or the "not built yet" notice
   logic (`initWizardStepForm()` and the equivalent in `funeral-request.js`) — per the
   older spec, this is where the sessionStorage draft should be injected as hidden
   inputs right before the real submit proceeds, replacing the placeholder notice.
5. Leave every earlier step page as `.html` — they only do client-side sessionStorage
   bookkeeping and don't need PHP.
6. Do a final search for any other page that assumes one of these six filenames ends in
   `.html` (breadcrumbs, "back" links, JS redirects) and fix it.

**Acceptance:** filling out any of the six flows end-to-end — including refresh/back
mid-flow — produces one real row in the correct table with a real reference number
(§1), and lands on `request-confirmation.php` with that reference number; no step in
any flow still 404s or shows the old "not built yet" notice.

---

## 5. Split the unified "Requests" admin page into one page per request type

**Ground truth for this task specifically.** `admin-requests.php` currently UNIONs all
seven request types into one page: a single "Requests" link in
`includes/admin-sidebar.php`'s nav, with client-side type tabs
(`data-admin-type-tabs` / `data-admin-type-tab="wedding|baptism|confirmation|funeral|
counseling|massintention|facility"`) filtering one shared table. `admin-donations.php`
is already its own separate page/nav item — that's the pattern to extend to every
request type instead of the tabbed single page.

1. Create one admin page per request type, e.g. `admin-wedding-requests.php`,
   `admin-baptism-requests.php`, `admin-confirmation-requests.php`,
   `admin-funeral-requests.php`, `admin-counseling-requests.php`,
   `admin-mass-intentions.php`, `admin-facility-reservations.php` — each showing only
   its own table's rows (using the real query from §1.3, filtered to that one type),
   with its own status filter/search (reuse `initAdminTableFilters()`), but no type
   tabs since each page is already single-type.
2. Each page keeps the same Update modal pattern as today, wired to
   `admin-update-request.php` with that page's fixed `type` value.
3. Update `includes/admin-sidebar.php`'s `$psNavGroups` to replace the single
   `'requests' => 'admin-requests.php'` entry with one nav item per type (grouped
   under "Manage" alongside the existing Donations/Announcements items) — decide with
   me whether you'd rather group them under one collapsible "Requests" nav section
   with 7 sub-links, or list all 7 as flat top-level items; I don't have a strong
   preference, flag it.
4. Delete `admin-requests.php` (and its hardcoded `$requests`/`$documentChecklists`
   arrays) once every type has its own real page — don't keep it around as a stale
   8th way to view the same data.
5. Any shared per-sacrament document-checklist logic (`$documentChecklists` in the old
   file) moves into `includes/request-types.php` so all 7 new pages read it from one
   place instead of duplicating it seven times.

**Acceptance:** the admin sidebar has a distinct link for each of the 7 request types
(plus the existing Donations/Announcements), each landing on a page showing only that
type's real rows with working status filter/search and a working Update modal wired to
`admin-update-request.php`; `admin-requests.php` no longer exists.

## 6. Real schedule/availability — admin calendar + conflict checking

**Ground truth for this task specifically.** Your capstone paper's own Objectives and
Requirements Analysis explicitly call for an event calendar that lets staff "monitor
date availability and prevent schedule conflicts" — but right now there is no
connection at all between booked dates and either side of the app:

- `calendar-events.php` already exists and its own doc comment says it's meant to feed
  *"the booking hint on the wizard datepickers in `frontend.js`"* — but `frontend.js`
  never calls it. Every date input in the wedding/baptism/confirmation/funeral wizards
  is a plain `<input type="date">` with only client-side rules (e.g. baptism restricts
  to Saturdays); nothing shows a parishioner which dates are already taken.
- `includes/request-forms.php`'s insert logic has no conflict check — two parishioners
  can book the same date/facility with nothing stopping either submission.
- The Update modal in the (soon-to-be-split, per §5) request admin pages has a status
  dropdown only — no date/time field, and no view of who else is booked that day, so
  staff can't spot or resolve a conflict even if one already happened.
- Schema-wise, only `reference_no` is `UNIQUE` on any request table — nothing enforces
  one booking per date/facility at the database level either.

1. **Public side**: wire the wizard datepickers (`data-datepicker` in `frontend.js`) to
   `calendar-events.php?month=&year=` so a parishioner sees which dates are already
   booked (per type, or parish-wide if that's simpler) before choosing one — this is
   the "should parishioner see the available date and see if their schedule is not
   taken" behavior you asked for.
2. **Server-side conflict check**: before `includes/request-forms.php` inserts a row,
   query existing rows for that type (or facility, for `facility_reservations`) on the
   same date and reject the submission with a clear error if it's already
   booked/scheduled — don't rely on the client-side hint alone.
3. **Admin schedule view**: add a calendar page under the admin portal (reuse
   `calendar.js`'s month-grid rendering) showing every booked date across all request
   types, color-coded by type/status, so staff can see the full picture at a glance —
   add it to `includes/admin-sidebar.php`'s nav alongside the per-type request pages
   from §5.
4. **Let staff actually set/change the date**: add a date/time field to each per-type
   admin page's Update modal (from §5) so approving a request into `scheduled` can set
   or adjust its confirmed date — surface any conflict with another booking right there
   rather than only at parishioner-submission time.
5. Decide with me whether "conflict" means the exact same calendar date only, or also
   overlapping time blocks on the same date (e.g. two facility reservations same day,
   different hours might be fine) — the schema currently doesn't record enough to tell,
   flag it if you want time-block-level checking added.

**Acceptance:** submitting a request for a date already booked for that type/facility
is rejected with a clear message before it's saved; the wizard datepickers visually
distinguish available vs. booked dates; an admin calendar page shows every real booking
across all types; approving a request lets staff set its confirmed date/time from the
same screen where they'd see a conflict.

## 7. Notification system on status change

**Ground truth for this task specifically.** Your paper's Objectives explicitly call
for a notification system that "automatically sends confirmations, reminders,
announcements, and status updates" — right now the only email the app ever sends is
the registration OTP (`includes/otp-mailer.php`, via `login_register.php`). Nothing
fires when `admin-update-request.php` changes a request's status.

1. Add a notification function (reuse `includes/otp-mailer.php`'s PHPMailer/Gmail SMTP
   setup from `includes/mail-config.php` — never duplicate those credentials) that
   emails the parishioner's contact email/number when their request's status changes,
   using the reference number + contact number pattern the schema already tracks
   requests by (no login required, matching §0's design).
2. Call it from `admin-update-request.php` right after a successful write, not before —
   same "don't notify optimistically" principle already used for the admin toast in the
   older `parishserve-claude-code-spec.md`.
3. Decide with me whether this pass covers email only, or also the SMS channel your
   paper's storyboard mentions (SMS would need a separate provider/API — flag the
   added complexity and cost before building it, don't just wire it silently).
4. A reminder for upcoming *scheduled* events (as opposed to a one-time status-change
   notice) is a separate, larger feature (needs a scheduled task/cron, which XAMPP
   doesn't run by default) — out of scope for this pass unless you want it added; flag
   back to me rather than building a cron job unprompted.

**Acceptance:** approving, rejecting, or otherwise changing a request's status in any
admin page sends a real email to the parishioner's contact address, sent only after the
database write actually succeeds.

## 8. Reporting feature

**Ground truth for this task specifically.** This is a full paper Objective
("generates summaries and reports related to reservations, church services, donations,
mass intentions, and parish activities... to support monitoring, record management, and
decision-making") with **no code at all** yet — there's no `admin-reports.php` or
equivalent anywhere in the project.

1. Add `admin-reports.php` under the admin portal (nav entry alongside the others),
   letting staff pick a date range and see counts/summaries per request type
   (submitted/approved/rejected/completed), total donations, and mass intentions in
   that range — pulling from the same real tables §1 wires up, not new hardcoded data.
2. Keep it read-only reporting (tables/summary cards), not a new CRUD surface — no new
   editable data model needed for this pass.
3. Decide with me whether this pass needs CSV/PDF export or just an on-screen summary —
   I don't have a strong preference, flag it before spending time on an export format
   nobody asked for.

**Acceptance:** staff can pick a date range on `admin-reports.php` and see real
counts/summaries drawn from the live database for that range.

## 9. Flag, don't silently build: wedding banns posting & seminar attendance tracking

Your paper's storyboard/objectives mention "wedding banns posting" and tracking
"seminar attendance" as workflow steps. Checked and confirmed absent:

- **Wedding banns**: zero matches for "banns" anywhere in the codebase outside this
  spec's own prose — there is no posting mechanism, no banns-related column, nothing.
- **Seminar attendance**: `wedding_requests` already collects a *preferred* seminar
  date/time/location as a form field, and `admin-requests.php`'s Update modal has an
  info banner about it — but nothing tracks whether the couple actually *attended* the
  seminar they picked. It's captured as a preference, not as a workflow step with a
  pass/fail state.

Don't have Claude Code invent a full design for either of these on its own — they're
real gaps but the shape of the feature (e.g., does banns posting need a public-facing
posted-banns list page? does seminar attendance need its own status separate from the
request's main status pipeline?) should be confirmed with you first. Flag both back to
me as their own follow-up items rather than building silently.

## 10. Constraints (don't violate)

- No new frontend framework, bundler, or package manager — vanilla JS/PHP/MySQL only.
- Don't restructure the duplicated-nav-per-HTML-page layout as part of this work.
- Never expose, copy, or move the SMTP credentials in `includes/mail-config.php`.
- Don't add a login requirement to the six public request forms — they're intentionally
  guest-submittable by reference number + contact number.
- Keep `main.js` client-side validation and the PHP server-side validation in sync
  whenever you touch either.
- Don't reintroduce hardcoded/sample data anywhere as a "quick way to demo it working" —
  if you need to see data in the admin UI while testing, submit it through the real
  public forms, the same as a real parishioner would.

## 11. Suggested build order

1. Run/verify `database/migrations/001` and `002` against the live DB (§1.1).
2. Convert the six final-step files to `.php` and fix everything that points at them
   (§4) — without this, §1's backend has nothing to trigger it.
3. Split `admin-requests.php` into the 7 per-type pages and update the sidebar (§5) —
   do this before wiring real data so step 4 writes each type's real query into its own
   file instead of into the file you're about to delete.
4. Wire the new per-type pages, `admin-donations.php`, and `admin-dashboard.php` to
   real data and the existing `admin-update-request.php` endpoint (§1.3–1.5, §3.1–3.3).
5. Sweep the rest of the app for remaining sample data (§3.4–3.6).
6. Delete `database/schema.sql` and clean up references to it (§2).
7. Full end-to-end pass: submit each of the six forms as a fresh "parishioner," confirm
   the row appears in phpMyAdmin and in the admin UI, approve/update it as staff on its
   own dedicated page, confirm the parishioner-facing dashboard reflects the change.
8. Server-side conflict checking + wizard datepicker booking hints (§6.1–6.2) — do this
   once real rows exist to check against (step 7), not before.
9. Admin schedule/calendar page + date/time field in the per-type Update modals
   (§6.3–6.4).
10. Notification email on status change (§7) — needs the real write endpoint from step
    4 already firing successfully.
11. Reporting page (§8) — needs real data across all types to summarize, so do this
    last.
12. Raise wedding banns posting and seminar attendance tracking (§9) as follow-up items
    rather than building either silently.

## 12. Open questions to resolve with Claude Code before/while building

- For `dashboard.html`'s logged-out "sample preview" (§3.4): replace with an honest empty
  state, or keep a clearly-labeled example?
- Does `announcements.html` already have a real backing table, or does this pass need to
  add one plus an admin management UI for it?
- Is there any other hardcoded data outside the three admin files already found — worth
  Claude Code doing one exhaustive grep pass before declaring this done.
- For §5's sidebar nav: one collapsible "Requests" section with 7 sub-links, or 7 flat
  top-level items alongside Donations/Announcements?
- For §6.5: should a "conflict" be same-date-only, or also check overlapping time
  blocks (e.g. two facility reservations same day, different hours)?
- For §7.3: email notifications only, or also SMS (needs a separate provider/API)?
- For §8.3: does the reporting page need CSV/PDF export, or is an on-screen summary
  enough for this pass?
- For §9: what's the intended design for wedding banns posting (public posted-banns
  list page?) and for seminar attendance (its own tracked state separate from the
  request's main status)?
