# ParishServe

The frontend uses HTML5, CSS3, and vanilla JavaScript. Open `landingpage.html` directly to browse the site, or use `http://localhost/ParishServe/landingpage.html` with XAMPP.

## Files

- Root `.html` files: editable frontend pages, including their navigation, icons, and sample content.
- `assets/css/`: shared and page-specific styles.
- `assets/js/main.js`: shared interactions and form validation.
- `assets/js/frontend.js`: browser form drafts, date validation, and Mass intention review.
- `assets/js/calendar.js`: month navigation and calendar rendering.
- `assets/js/auth.js`: registration/OTP feedback from the PHP backend.
- `assets/css/responsive.css` and `assets/js/responsive.js`: shared phone/tablet layouts, accessible mobile navigation, and scrollable calendars/tables; loaded after page assets.
- `config.php`, `login_register.php`, `verify-otp.php`, `auth-state.php`: database configuration and account backend handlers.
- `includes/otp-mailer.php`, `includes/mail-config.php`, `includes/PHPMailer/`: backend email delivery.

## Backend And Current Limits

Browsing the HTML pages does not require PHP. Registration and email verification require PHP, the existing `parish_serve` MySQL database with its `users` table, and configured mail delivery. The incoming `database/schema.sql` is a demo schema with seed accounts. Its `users` columns differ from the current registration/OTP handlers (which need separate name fields and OTP fields); reconcile these before using it for account services. Import only into a disposable development database, not over an existing parish database.

Login authentication and final service-request saving are not implemented. Existing sample data and unfinished destinations remain placeholders. Multi-step form drafts use browser session storage; they are not submitted parish records. Do not enter sensitive real information in demo requests.

Shared PHP presentation includes have been expanded into the HTML pages. Update repeated navigation/layout markup across pages when changing it. PHP is only used for backend account operations.

## Merge notes

The frontend remains HTML with expanded navigation and icons. Incoming carousel accessibility/animation changes and page animations are retained. Shared form, upload, review, and responsive styles remain available to Confirmation, Funeral, Baptism, and Mass Intention pages. Calendar events and request submissions remain demo-only. The SQL schema contains demo accounts with documented passwords; it is not a production setup.
