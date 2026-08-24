<?php
/**
 * login.php
 * ---------------------------------------------------------------------
 * The public authentication page. Per the group's decision this
 * session (see conversation): there is NO existing auth backend
 * anywhere in this project yet -- no config.php, no session handling,
 * no login route, nothing wired to database/schema.sql's `users`
 * table. So this is built the same way as every other page this
 * session: a REAL form with REAL client-side validation, but
 * submitting it shows an honest "not wired up yet" notice instead of
 * faking a successful or failed login.
 *
 * WHAT'S ACTUALLY REAL HERE:
 *   - Required-field + email-format validation, with the EXACT
 *     wording the group specified ("Please enter your email
 *     address.", "Please enter a valid email address.", "Please
 *     enter your password."), shown next to the relevant field.
 *   - Show/hide password toggle.
 *   - Submit disables the button, shows a loading state, then reveals
 *     a form-level notice -- never a browser alert() box.
 *   - Email is preserved after the notice appears; password is
 *     cleared (standard practice -- never re-populate a password
 *     field after a failed/incomplete attempt, real backend or not).
 *   - Focus moves to the first invalid field on validation failure,
 *     and to the form-level alert once the "not wired up" notice
 *     appears, so screen reader users aren't left stranded.
 *
 * WHAT'S NOT REAL (clearly labeled, not faked):
 *   - "The email or password you entered is incorrect." -- this is
 *     the message a REAL backend should show (deliberately generic,
 *     never revealing whether the email exists), but nothing here can
 *     actually check credentials. It's quoted inside the "not wired
 *     up yet" notice as a preview of what happens once the backend
 *     exists, not fired as if it were a genuine failed login.
 *   - "Forgot Password?" and "Create an Account" link to pages that
 *     don't exist yet (forgot-password.php, register.php) -- same
 *     "link to where it will live" pattern as everywhere else in this
 *     app. Terms of Service / Privacy Policy were removed entirely
 *     (not just left as dead links) at the group's request, since
 *     those pages don't exist for either Login or Register yet --
 *     see register.php for the same call.
 *
 * WHEN THE REAL BACKEND GETS BUILT: this form already posts to
 * itself (action="login.php" method="post") with name="email" and
 * name="password" -- a real handler just needs to check
 * $_SERVER['REQUEST_METHOD'] === 'POST' at the top of this file,
 * validate, query `users` WHERE email = ?, password_verify(), start
 * a session, and redirect by role. The schema's `email` column (no
 * username field) already matches this form, so no backend/DB
 * mismatch to resolve there.
 * ---------------------------------------------------------------------
 */
require __DIR__ . '/includes/icons.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Log In · ParishServe</title>
<meta name="description" content="Log in to your ParishServe account to manage parish services and requests for Our Lady of the Gate Parish.">
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

<div class="auth-shell">

    <!-- ============================ LEFT: VISUAL ============================ -->
    <div class="auth-visual">
        <div class="auth-visual-media">
            <img src="assets/images/parish-login.svg" alt="Our Lady of the Gate Parish church at golden hour">
        </div>

        <div class="auth-brand">
            <span class="auth-brand-crest"><?php ps_icon('crest'); ?></span>
            <span class="auth-brand-text">
                <strong>Our Lady<br>of the Gate Parish</strong>
                <em>ParishServe</em>
            </span>
        </div>

        <div class="auth-welcome">
            <div class="ps-heading-ornament"><span></span><?php ps_icon('cross'); ?><span></span></div>
            <h2>Welcome Back!</h2>
            <p>Log in to access your ParishServe account and continue managing your parish services and requests.</p>
        </div>
    </div>

    <!-- ============================ RIGHT: FORM ============================= -->
    <div class="auth-panel">
        <div class="auth-card">

            <div class="ps-heading-ornament auth-card-ornament"><span></span><?php ps_icon('cross'); ?><span></span></div>
            <h1>Log In</h1>
            <p class="auth-sub">Enter your credentials to access your account.</p>

            <!-- form-level status: the "not wired up yet" notice lands
                 here once client-side validation passes. role="alert"
                 so screen readers announce it the moment it's shown;
                 tabindex="-1" lets main.js move keyboard focus onto it
                 without adding it to the normal tab order. Icon is
                 rendered server-side (not built via JS innerHTML) so
                 it can never drift out of sync with icons.php. -->
            <div class="auth-alert" data-auth-alert role="alert" tabindex="-1" hidden>
                <?php ps_icon('info'); ?>
                <span>There's no backend wired up yet to verify this against real accounts. Once it exists, this is where you'd see either a successful login or &ldquo;The email or password you entered is incorrect.&rdquo; if the credentials didn't match.</span>
            </div>

            <form action="login.php" method="post" data-login-form novalidate>

                <div class="ps-field auth-field">
                    <label for="loginEmail">Email Address</label>
                    <span class="ps-field-icon">
                        <?php ps_icon('mail'); ?>
                        <input type="email" id="loginEmail" name="email" autocomplete="username" placeholder="Enter your email address" aria-describedby="loginEmailError" required>
                    </span>
                    <small class="auth-field-error" id="loginEmailError" data-field-error="email" hidden></small>
                </div>

                <div class="ps-field auth-field">
                    <label for="loginPassword">Password</label>
                    <span class="ps-field-icon has-trailing">
                        <?php ps_icon('lock'); ?>
                        <input type="password" id="loginPassword" name="password" autocomplete="current-password" placeholder="Enter your password" aria-describedby="loginPasswordError" required>
                        <button type="button" class="auth-password-toggle" data-password-toggle aria-label="Show password" aria-pressed="false">
                            <?php ps_icon('eye', 'auth-eye-show'); ?>
                            <?php ps_icon('eye-off', 'auth-eye-hide'); ?>
                        </button>
                    </span>
                    <small class="auth-field-error" id="loginPasswordError" data-field-error="password" hidden></small>
                </div>

                <div class="auth-forgot">
                    <a href="forgot-password.php">Forgot Password?</a>
                </div>

                <button type="submit" class="ps-btn ps-btn-primary auth-submit" data-login-submit>
                    <?php ps_icon('log-in'); ?> <span data-submit-label>Log In</span>
                </button>

                <div class="auth-divider"><span>or</span></div>

                <a href="register.php" class="ps-btn ps-btn-outline auth-submit"><?php ps_icon('user'); ?> Create an Account</a>

            </form>

            <p class="auth-privacy-note"><?php ps_icon('lock'); ?> Your privacy and security are important to us.</p>

        </div>
    </div>

</div>

<script src="assets/js/main.js"></script>
</body>
</html>
