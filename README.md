# ParishServe

Parish services portal for Our Lady of the Gate Parish: HTML5, CSS3 and vanilla JavaScript pages with a PHP + MySQL backend. Run it with XAMPP from `C:\xampp\htdocs\ParishServe1` and open `http://localhost/ParishServe1/index.php`.

## Files

- Root `.html` files: parishioner pages. Navigation and icons are repeated in each page.
- Root `.php` pages: the final step of each request form (`wedding-request-step3.php`, `baptism-request-step2.php`, `confirmation-request-step4.php`, `funeral-request-step4.php`, `mass-intention-request.php`, `donation-request.php`), `request-confirmation.php`, the account handlers (`login.php`, `login_register.php`, `verify-otp.php`, password reset) and the admin portal (`admin-*.php`: dashboard, one page per request type, donations, announcements, calendar, reports and accounts).
- JSON endpoints: `announcements-data.php`, `calendar-events.php`, `booking-availability.php`, `dashboard-data.php`, `session-user.php`, `auth-state.php`, `upload-document.php`, `pending-count.php`, `admin-schedule-check.php`, and the admin write endpoints `admin-update-request.php`, `admin-save-announcement.php`, `admin-update-account.php`.
- `includes/`: shared PHP — `request-forms.php` (form rules and saving), `request-types.php` (request tables, status pipeline and schedule-conflict rules), `admin-request-page.php` (the per-type admin page), `notifications.php` (status-change emails), `reports.php` and `pdf.php` (reports and their CSV/PDF export), `uploads.php`, `announcements.php`, `auth-guard.php`, the admin layout and the mailer.
- `assets/css/`, `assets/js/`: styles and scripts (`frontend.js` form drafts, `main.js` shared behavior and the admin tables/modals, `request-uploads.js` document uploads, `booking-hint.js` already-booked times under the request forms' date fields, `session-user.js` signed-in user chip, plus page scripts).
- `database/migrations/`: the schema of the `parish_serve` database, as numbered SQL files.
- `uploads/`: uploaded documents, proofs of payment and announcement images. Private — not reachable from the web and not committed.

## Database

Create an empty `parish_serve` database, then run every file in `database/migrations/` in order:

    C:\xampp\mysql\bin\mysql.exe -u root parish_serve < database\migrations\000-users.sql

(then `001-...`, `002-...`, `003-...`, `004-...`). The files are additive and safe to re-run on an existing database. There is no seed data: requests, donations and announcements only appear after someone submits a form or staff post them. Create the first Super Admin account directly in the database; Admins are promoted on the Accounts page.

## Notes

- Registration, password reset and request status updates send email through `includes/mail-config.php`, which holds SMTP credentials — keep it private.
- The GCash QR on the donation page is still a placeholder; publish the verified parish QR before accepting donations.
- Counseling requests are not connected to the backend yet.
