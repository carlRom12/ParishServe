# ParishServe — Public Request Forms Backend Spec

Paste this into Claude Code. Grounded in the actual code (including a comment already
sitting in `assets/js/main.js` that anticipates exactly this change).

---

## 0. Ground truth for this task specifically

- **Nothing on the public side is submitted to a server today.** Every multi-step
  request wizard (wedding, baptism, confirmation, funeral, mass intention) and the
  donation form only ever writes to browser `sessionStorage` and shows an inline
  **"not built yet"** notice on the final step instead of submitting — see
  `initWizardStepForm()` in `assets/js/main.js` and the equivalent logic in
  `assets/js/funeral-request.js` for the funeral flow (funeral has its own script,
  keyed by `data-funeral-step` instead of `data-wizard-step-form`).
- **The code already expects this exact change.** The comment above
  `initWizardStepForm()` literally says: *"wedding-request-step2.php's own 'Save and
  Continue'... Once a step's 'next' page is real..., drop the
  `data-wizard-step-form` attribute from that form so it goes back to submitting
  normally."* — i.e. converting the relevant step to `.php` and wiring a real backend
  is the intended next step, not a deviation from the project's design.
- **How the multi-step flow currently carries data**: `frontend.js` saves each step's
  form fields into `sessionStorage` under `parishserve-draft-<flow>` as the parishioner
  moves between steps, and the *final* review step (`*-step3`/`*-step4`) fills its
  on-screen summary from that draft by reading it back into plain text — **not into
  real form inputs**. That means the final step's `<form>` currently has little or no
  actual submittable data in it. Whoever builds this must inject the complete draft
  object into the form as hidden inputs (or one hidden JSON field) before it posts,
  or the backend will receive an almost-empty `$_POST`.
- **Final step per flow** (the one to convert, per `data-wizard-step-form` /
  `data-funeral-step="4"`):
  | Flow | Final step file | Target table (see `database/schema.sql`) |
  |---|---|---|
  | Wedding | `wedding-request-step3.html` | `wedding_requests` |
  | Baptism | `baptism-request-step2.html` (2-step flow, also has the file upload) | `baptism_requests` |
  | Confirmation | `confirmation-request-step4.html` | `confirmation_requests` |
  | Funeral | `funeral-request-step4.html` (own script, `funeral-request.js`) | `funeral_requests` |
  | Mass Intention | `mass-intention-request-step3.html` | `mass_intentions` |
  | Donation | `donations.html` (single page, own form) | `donations` |
- **Reference number pattern already established** in `admin-requests.php`'s mock data:
  `WED-2026-0001`, `BAP-2026-0002`, `CNF-2026-0001`, `FUN-2026-0001`, `MI-2026-0001`,
  `DON-2026-0001`. Reuse this format — 3-letter type prefix, year, 4-digit sequence —
  generated server-side, never trusted from the client.
- **No `user_id` linkage by design** — `schema.sql`'s own header comment explains
  requests are tracked by reference number + `contact_number`, not a `users.id` FK,
  specifically so a request can be submitted and later looked up without requiring
  login. Follow this — don't add a login requirement to these forms as part of this
  task.
- **Two public forms don't exist yet at all**: `facility-reservation.html` is linked
  from every page's nav but the file was never created, and there is no public
  Counseling request page either (only `counseling_appointments` exists on the admin
  side, from mock data). These are **out of scope** for this task since there's no
  existing form to wire up — flag them back to me rather than inventing new pages,
  unless you'd like Claude Code to build those too.
- **Document uploads have nowhere to go in the schema.** `baptism-request-step2.html`
  has `enctype="multipart/form-data"` and `data-max-size-mb` file inputs (client-side
  size validation only exists — `initFileUploadValidation()` in `main.js`), and
  `admin-requests.php` already has a per-sacrament-type document checklist
  (`$documentChecklists`) it expects to check off — but none of `wedding_requests`,
  `baptism_requests`, `confirmation_requests`, `funeral_requests` have any column to
  store an uploaded file's path. `donations` is the one table that already has this
  right (`proof_of_payment VARCHAR(255)`). **Add a small `request_documents` table**
  (columns like `id`, `request_type`, `request_id`, `document_label`, `file_path`,
  `uploaded_at`) rather than bolting one file-path column per document onto each
  request table — it's the same shape `$documentChecklists` already uses, just
  persisted.
- **Do not manually insert rows into any of these tables via phpMyAdmin.** Seed data,
  if any is needed for a demo, should come from actually submitting the real forms —
  the only exception already agreed elsewhere in this project is the one-time Super
  Admin account seed, which is unrelated to these request tables.

---

## 1. Backend: real PHP handling for every existing public request form

For each flow in the table above:

1. **Validate server-side** (mirror whatever `main.js`/`frontend.js`/
   `funeral-request.js` already validate client-side — required fields, PH mobile
   format `^09\d{9}$`, date not in the past, etc. — don't trust the client).
2. **Generate the reference number** server-side using the established
   `<PREFIX>-<YEAR>-<sequence>` pattern (query the max existing sequence for that
   type+year, or use an auto-increment lookup — either is fine, just make it
   collision-safe under concurrent submissions, e.g. wrap in a transaction or rely on
   the table's own `UNIQUE` constraint on `reference_no` and retry once on conflict).
3. **Insert one row** into the matching table from `database/schema.sql`, with
   `status` defaulting to `'submitted'` (already the column default).
4. **Handle file uploads where applicable** (baptism's documents, any future
   confirmation/funeral document steps, donation's proof of payment): validate
   file type/size server-side too (don't rely on the client-side check alone), store
   the file under a sensible path (e.g. `uploads/<type>/<reference_no>/...`), and
   record it — in `donations.proof_of_payment` directly, and in the new
   `request_documents` table for the sacrament flows.
5. **On success**: clear that flow's `sessionStorage` draft (small bit of JS on the
   confirmation page, since PHP can't clear the browser's `sessionStorage` itself),
   and redirect (HTTP redirect, so a page refresh doesn't resubmit) to a confirmation
   screen showing the reference number and the contact number to look it up by later.
   I'd suggest **one shared confirmation page** (e.g. `request-confirmation.php?
   type=wedding&ref=WED-2026-0002`) instead of six near-identical static confirmation
   pages — tell me if you'd rather match the original per-flow naming
   (`wedding-request-confirmation.html`, etc.) that the placeholder comments imply.
6. **On validation failure**: redisplay the form with the submitted values and error
   messages, following the same flash/old-input pattern already used for
   `register.html`/`login.html` via `auth-state.php`, rather than a blank error page.

## 2. Convert the final-step files from `.html` to `.php`

Only the files that actually need to run PHP — the six "final step" files listed in
§0's table — not the earlier steps in each wizard, since those stay pure client-side
`sessionStorage` bookkeeping and don't touch the server.

1. Rename:
   - `wedding-request-step3.html` → `wedding-request-step3.php`
   - `baptism-request-step2.html` → `baptism-request-step2.php`
   - `confirmation-request-step4.html` → `confirmation-request-step4.php`
   - `funeral-request-step4.html` → `funeral-request-step4.php`
   - `mass-intention-request-step3.html` → `mass-intention-request-step3.php`
   - `donations.html` → `donations.php`
2. Each renamed file should both **render the existing form markup unchanged** (don't
   redesign these pages) and, on `POST`, run the validation/insert logic from §1 —
   using the POST-redirect-GET pattern in point 6 above so the same file handles both
   "show the form" and "process the submission."
3. **Update every link/reference to the old filenames** across the site: the previous
   step's `<form action="...">` in each wizard (e.g.
   `wedding-request-step2.html`'s form currently points to
   `wedding-request-step3.html`), any nav/breadcrumb links, and
   `assets/js/main.js`/`frontend.js`/`funeral-request.js` if any of them hardcode the
   `.html` filename in a string comparison (`frontend.js`'s
   `page === 'baptism-request.html' ? ... : ...` check is about the *first* step and
   is unaffected, but double-check the others).
4. **Repurpose (don't just delete) `initWizardStepForm()`** in `main.js` and the
   equivalent block in `funeral-request.js`: instead of showing the "not built yet"
   notice, this is where the hidden-input injection from the `sessionStorage` draft
   (§0) should happen right before the real submit proceeds.
5. Leave every earlier step page as `.html` — they don't need PHP and shouldn't be
   converted just for consistency's sake.

**Acceptance:**
- Filling out any of the six flows end-to-end (including page refreshes/back button
  mid-flow, which `sessionStorage` should still survive) results in one new row in the
  correct table, with a real generated reference number, visible afterward in
  phpMyAdmin **without anyone having typed it in there by hand**.
- Resubmitting (double-click, refresh on the confirmation page) does not create a
  duplicate row.
- `admin-requests.php`/`admin-donations.php`, once wired to real data (separate spec,
  already in progress), show these newly-submitted rows and their uploaded documents.
- Baptism's file upload and donation's proof-of-payment upload both land on disk and
  are recorded correctly.

## 3. Constraints (don't violate)

- No new frontend framework, bundler, or package manager — vanilla JS/PHP/MySQL only.
- Don't touch tables/columns beyond what's needed (adding `request_documents` is the
  one schema addition this task needs; don't restructure existing columns).
- Don't add a login/account requirement to these forms — the project's own schema
  comment explicitly designs around guest submission by reference number + contact
  number.
- Keep file upload size/type limits enforced server-side, matching whatever
  `data-max-size-mb` already advertises client-side, so the two never disagree.
- Don't leave the earlier wizard steps as dead ends — the previous step's `action`
  must point at the new `.php` filename, not the old `.html` one.

## 4. Open questions to resolve with Claude Code before/while building

- Build the two missing public forms (Counseling, Facility Reservation) in this same
  pass, or leave them for later since there's no existing page to convert?
- One shared confirmation page for all six flows, or six separate pages matching the
  original placeholder naming?
- Any per-document max file size/type beyond what `data-max-size-mb` already implies
  client-side?
- Should a submitted request's confirmation page offer a "track this request" link
  using reference number + contact number, since the schema was explicitly designed to
  support that later — worth doing now while the reference-number generation code is
  already being written, or save it for its own task?
