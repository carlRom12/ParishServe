# Frontend handoff

This is a frontend prototype, not a production-ready submission system.

## Verified in this audit
- All assets/js/*.js files pass Node syntax checks.
- Local HTML href/src/action targets were scanned.
- Broken contacts.html links now point to dashboard.html#parish-contacts.
- No browser-based end-to-end or responsive visual verification was completed.

## Required before release
- Implement or agree on destinations for facility-reservation.html, profile.html, settings.html, my-requests.html and forgot-password.html. These are currently missing.
- Resolve legacy index.html in baptism-guidelines.html, baptism-request-step3.html form action and confirmation-request-confirmation.html form action. Some are intercepted by JavaScript, but lack valid fallback destinations.
- Replace static user name/avatar, notification badges, dashboard request statuses, schedules and announcement data with authenticated API data. Do not treat displayed sample approval statuses as real approvals.
- Verify parish phone numbers and office hours. Confirmed address from the project owner: Daraga, Albay, Philippines.
- Publish a verified parish GCash QR before accepting donations; the current QR is a placeholder.
- Confirm all request flows submit to actual endpoints and return genuine success/error responses. Existing local-only notices should be replaced only after integration.
- Store and validate documents on the server. Browser previews, filenames, sessionStorage and IndexedDB are not evidence of an upload.
- Validate file content/type and size, required fields, dates and conditional requirements server-side; enforce authorization for document access.

## Frontend inconsistencies to finish
- Wedding doc2 (bride permit) is required for everyone in the form, although the parish guideline is conditional. Add an explicit applicability choice and matching validation.
- Wedding step 1 has one shared contact mobile/email, not separate bride/groom contacts. Review intentionally displays shared contact details; agree on the final data model.
- Test selected-file continuity after Back, refresh and tab navigation. Wedding IndexedDB previews expire logically after one hour but expired records still need physical cleanup; storage failures must not silently navigate past uploads.
- Baptism and Wedding inject request content with fetch and reload scripts; consider a single shared initialization function to avoid duplicate event listeners. Confirm hero heading and active tab stay synchronized.
- Announcement Read full links currently return to the listing; provide a detail view or modal backed by actual content. Bookmark state is visual only.
- Test search, category filtering, sort, calendar month boundaries, upload replacement/removal, checkbox validation, and all review mappings in the browser at desktop and mobile widths.

## Data currently held locally
- Wedding/Baptism drafts: sessionStorage keys parishserve-draft-wedding and parishserve-draft-baptism (frontend.js).
- Wedding review mapping/file previews: assets/js/wedding-review.js; IndexedDB parishserve-wedding-documents.
- Confirmation/Funeral preview persistence: assets/js/review-file.js; IndexedDB parishserve-file-previews.
- Baptism upload and inline review: assets/js/baptism-upload.js.
- Shared upload presentation: assets/js/upload-preview.js and assets/css/upload-preview.css.
- Shared review confirmation appearance: assets/css/service-review.css.

Do not commit real parishioner documents, credentials, or production secrets. Review staged changes locally before pushing.
