# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

ParishServe — a parish services website for Our Lady of the Gate Parish (a capstone project). Plain HTML5/CSS3/vanilla JS frontend with a small PHP + MySQL backend used only for account registration and email OTP verification. There is no build step, package manager, linter, test suite, or git repo.

## Running

Served by XAMPP (Apache + PHP 8.0 + MariaDB 10.4) from `C:\xampp\htdocs\ParishServe1`:

- App: `http://localhost/ParishServe1/index.php` (home page; the README's `landingpage.html` no longer exists)
- Start/stop Apache and MySQL via `C:\xampp\xampp-control.exe` (or `apache_start.bat` / `mysql_start.bat` in `C:\xampp`)
- PHP syntax check: `C:\xampp\php\php.exe -l <file>.php`
- DB shell: `C:\xampp\mysql\bin\mysql.exe -u root parish_serve` (root, no password — see `config.php`)
- Visual check: `chrome.exe --headless=new --user-data-dir=<temp dir> --screenshot=<out.png> --window-size=1280,900 <url>` (a separate `--user-data-dir` is needed when Chrome is already open)

HTML pages can also be opened via `file://`, but `assets/js/auth.js` deliberately blocks register/OTP submission there.

## Architecture

**Pages are static `.html` files with duplicated layout.** The site was originally PHP templates (`includes/header.php`, `sidebar.php`, `topbar.php`, `footer.php`, `icons.php`); those includes were expanded inline into each `.html` page. Consequences:
- Navigation, sidebar, header, and icon SVG markup are repeated in every page — a nav/layout change must be applied across all `.html` files.
- Code comments still reference the old `.php` names (e.g. "see wedding-request-step2.php", "loaded via includes/footer.php"); read those as the corresponding `.html` page.

**The admin portal (`admin-*.php`) is the only part still rendered by PHP.** Each page sets `$pageTitle`, `$pageCss` (string or array), `$userFirstName`, `$userRole`, `$activeNav`, then requires `includes/header.php` → `includes/admin-sidebar.php` → (inside its header section) `includes/topbar.php` → `includes/footer.php`. Icons come from `ps_icon($name, $class)` in `includes/icons.php` — the same SVGs the `.html` pages carry inline; add new icons there. All admin data is hardcoded PHP arrays mirroring `schema.sql` seed rows. Client behavior is data-attribute driven in `main.js`: `initAdminTableFilters()` (`data-admin-row` with `data-type`/`data-status`/`data-search`, type tabs, status select, search, empty state) and `initAdminModals()` (`data-modal-trigger` copies the button's `data-*` into `[data-modal-field]`s, `data-docs` JSON renders the document checklist, `data-mock-form` save updates the row's status pill + toast, `data-mock-delete` removes the row). Nothing is persisted. `ParishServe/admin-announcements.php` is a stray older copy.

**Asset loading convention** (per page, in this order): `style.css` → page-specific CSS → `responsive.css?v=N`; scripts at the end of `<body>`: `frontend.js` → `main.js` → `responsive.js?v=N`, plus an optional page script (`auth.js`, `calendar.js`, `funeral-request.js`, `confirmation-*.js`, `funeral-schedule.js`). Bump the `?v=` query when changing the responsive files. Several CSS files are shared across sacraments (e.g. baptism/confirmation pages reuse `wedding-request.css`, `confirmation-layout.css`).

**JS is IIFE-per-feature, activated by `data-*` attributes** rather than page checks — e.g. `data-wizard-step-form`, `data-confirm-toggle`, `data-max-size-mb`, `data-datepicker`, `data-carousel`. To reuse a behavior on a new page, add the attribute. `responsive.js` injects the mobile header/nav drawer around `.ps-sidebar`/`.ps-main` and wraps tables/calendar grids in scroll containers at runtime.

**Multi-step service request wizards** (wedding, baptism, confirmation, funeral, mass intention) are `<name>-request.html` → `-step2.html` → … Nothing is submitted to a server:
- `frontend.js` detects the flow from the filename, saves form fields to `sessionStorage` under `parishserve-draft-<flow>`, restores them on each step, and on submit navigates to the form's `action` (the next step). It also fills the review steps (`mass-intention-request-step3`, `confirmation-request-step4`) from the draft by index-matching `.wr3-review-row strong` elements to a field list — reordering review rows requires updating those arrays.
- Funeral uses its own `funeral-request.js` (`data-funeral-step`, key `parishserve-draft-funeral`).
- A form with `data-wizard-step-form` shows an inline "not built yet" notice instead of navigating (used where the next page, e.g. `*-request-confirmation.html`, doesn't exist).
- File inputs are never persisted.

**Auth backend (the only real server logic):**
1. `register.html` posts to `login_register.php` → validates, inserts into `users` with a hashed password and hashed 6-digit OTP (180s expiry), emails it via `includes/otp-mailer.php` (bundled PHPMailer, Gmail SMTP from `includes/mail-config.php`), sets `$_SESSION['pending_user_id']`, redirects to `verify-otp.html`.
2. `verify-otp.html` posts `verify` or `resend` to `verify-otp.php`, which sets flash messages in the session and 303-redirects back (or to `login.html` on success).
3. Because pages are static HTML, flash messages/old input reach the page via `auth.js` fetching `auth-state.php?page=<register|verify-otp|login>` (JSON; values are consumed/unset on read).
4. Login is **not implemented** — `main.js` `initLoginForm` validates client-side then shows "Sign-in is not available yet."

Validation rules are duplicated between `main.js` (client) and the PHP handlers (server) — PH mobile format `^09\d{9}$`, password ≥ 8 chars, name regex; keep both in sync.

## Database

- The live `parish_serve` DB has only a `users` table, and its schema **differs from `database/schema.sql`**: live has `firstname/middlename/lastname/suffix`, `date_of_birth`, `gender`, `mobile_number`, `role` enum `Parishioner|Admin|Super Admin`, `email_verified`, `otp_hash`, `otp_expires_at`, `status`. `schema.sql` defines `full_name`/`contact_number` users plus 11 request/content tables with seed rows and demo accounts — it matches the admin/dashboard mock data, not the auth code. Don't import it over the real DB; reconcile the `users` columns first.
- `schema.sql` design notes: requests link to parishioners by `contact_number` + `reference_no` (not a `users.id` FK), and all request tables share one status pipeline `submitted → under_review → approved → scheduled → completed` (or `rejected`) so they can be UNIONed.
- Live `otp_hash`/`otp_expires_at` are `NOT NULL`, yet `verify-otp.php` sets them to `NULL`; this only works because MariaDB's `sql_mode` is non-strict.

## Content caveats

- Dashboard, announcements, calendar events, donations and admin data are hardcoded sample content (calendar is seeded around May 2026; `calendar.js` reads `?month=&year=`).
- `includes/mail-config.php` contains real SMTP credentials — never print, copy, or move them into other files.
