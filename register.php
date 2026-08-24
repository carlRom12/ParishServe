<?php
/**
 * register.php
 * ---------------------------------------------------------------------
 * The public "Create an Account" page, linked from login.php's
 * "Create an Account" button and index.php's "Get Started". Same
 * frontend-only rule as login.php (see that file's header comment for
 * the full reasoning): no backend exists yet, so this is a real form
 * with real client-side validation that shows an honest "not wired up
 * yet" notice on submit instead of faking account creation.
 *
 * TERMS OF SERVICE / PRIVACY POLICY: removed entirely at the group's
 * request (not just left as dead links) -- the reference mockup had a
 * "I agree to the Privacy Notice and Terms of Service" checkbox, but
 * neither page exists for Login OR Register, so it was dropped rather
 * than teased. Only the "I confirm the information provided is true
 * and correct" checkbox remains, which doesn't depend on a page that
 * doesn't exist.
 *
 * FIELD MISMATCH WITH database/schema.sql (same category of flag as
 * wedding-request.php's): the `users` table has a single `full_name`
 * VARCHAR column and no date_of_birth/gender columns at all, but this
 * form collects first/middle/last/suffix separately plus DOB and
 * gender. Whoever wires this up will need to either concatenate the
 * name parts before INSERT and add two new columns, or migrate the
 * table to match the form's granularity. Not fixing schema.sql this
 * session -- flagging so it isn't a surprise.
 *
 * PASSWORD MINIMUM LENGTH: the group's spec didn't set a password
 * policy for registration (only login's empty/format checks were
 * specified). Added an 8-character minimum as a reasonable default
 * for a "create account" flow -- flag if a different policy is
 * wanted once the real backend enforces this server-side too.
 *
 * Reuses login.css's auth-shell/auth-visual/auth-card/.ps-field-icon/
 * password-toggle/.auth-alert wholesale (see that file's updated
 * header) -- register.css is only the pieces unique to this page: the
 * wider card, section headers, checkbox rows, and the footer bar.
 * ---------------------------------------------------------------------
 */
require __DIR__ . '/includes/icons.php';
$currentYear = date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create an Account · ParishServe</title>
<meta name="description" content="Create a ParishServe account to submit requests and manage parish services for Our Lady of the Gate Parish.">
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/login.css">
<link rel="stylesheet" href="assets/css/register.css">
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
            <h2>Create Your Account</h2>
            <p>Join ParishServe to submit requests, manage your parish services, and stay connected with our parish community.</p>
        </div>
    </div>

    <!-- ============================ RIGHT: FORM ============================= -->
    <div class="auth-panel">
        <div class="auth-card auth-card-wide">

            <div class="ps-heading-ornament auth-card-ornament"><span></span><?php ps_icon('cross'); ?><span></span></div>
            <h1>Create an Account</h1>
            <p class="auth-sub">Fill in the details below to create your ParishServe account.</p>

            <div class="auth-alert" data-auth-alert role="alert" tabindex="-1" hidden>
                <?php ps_icon('info'); ?>
                <span>There's no backend wired up yet to actually create an account. Once it exists, this is where you'd see either a successful registration or a specific error, such as an email already being in use.</span>
            </div>

            <form action="register.php" method="post" data-register-form novalidate>

                <h2 class="auth-section-title"><?php ps_icon('user'); ?> Personal Information</h2>

                <div class="ps-form-row-3 auth-row">
                    <div class="ps-field auth-field">
                        <label for="firstName">First Name <span class="auth-required">*</span></label>
                        <input type="text" id="firstName" name="firstName" placeholder="First name" autocomplete="given-name" required>
                        <small class="auth-field-error" id="firstNameError" data-field-error="firstName" hidden></small>
                    </div>
                    <div class="ps-field auth-field">
                        <label for="middleName">Middle Name</label>
                        <input type="text" id="middleName" name="middleName" placeholder="Middle name (optional)" autocomplete="additional-name">
                    </div>
                    <div class="ps-field auth-field">
                        <label for="lastName">Last Name <span class="auth-required">*</span></label>
                        <input type="text" id="lastName" name="lastName" placeholder="Last name" autocomplete="family-name" required>
                        <small class="auth-field-error" id="lastNameError" data-field-error="lastName" hidden></small>
                    </div>
                </div>

                <div class="ps-form-row-3 auth-row">
                    <div class="ps-field auth-field">
                        <label for="suffix">Suffix (Optional)</label>
                        <span class="ps-select is-block">
                            <select id="suffix" name="suffix">
                                <option value="" selected>Select suffix</option>
                                <option value="Jr.">Jr.</option>
                                <option value="Sr.">Sr.</option>
                                <option value="II">II</option>
                                <option value="III">III</option>
                            </select>
                            <?php ps_icon('chevron-down'); ?>
                        </span>
                    </div>
                    <div class="ps-field auth-field">
                        <label for="dateOfBirth">Date of Birth <span class="auth-required">*</span></label>
                        <span class="ps-input-icon">
                            <input type="date" id="dateOfBirth" name="dateOfBirth" max="<?php echo htmlspecialchars(date('Y-m-d')); ?>" required>
                            <?php ps_icon('calendar'); ?>
                        </span>
                        <small class="auth-field-error" id="dateOfBirthError" data-field-error="dateOfBirth" hidden></small>
                    </div>
                    <div class="ps-field auth-field">
                        <label for="gender">Gender <span class="auth-required">*</span></label>
                        <span class="ps-select is-block">
                            <select id="gender" name="gender" required>
                                <option value="" selected>Select gender</option>
                                <option value="Female">Female</option>
                                <option value="Male">Male</option>
                                <option value="Prefer not to say">Prefer not to say</option>
                            </select>
                            <?php ps_icon('chevron-down'); ?>
                        </span>
                        <small class="auth-field-error" id="genderError" data-field-error="gender" hidden></small>
                    </div>
                </div>

                <div class="ps-form-row-2 auth-row">
                    <div class="ps-field auth-field">
                        <label for="registerEmail">Email Address <span class="auth-required">*</span></label>
                        <span class="ps-field-icon">
                            <?php ps_icon('mail'); ?>
                            <input type="email" id="registerEmail" name="email" placeholder="name@example.com" autocomplete="email" required>
                        </span>
                        <small class="auth-field-error" id="registerEmailError" data-field-error="email" hidden></small>
                    </div>
                    <div class="ps-field auth-field">
                        <label for="mobileNumber">Mobile Number <span class="auth-required">*</span></label>
                        <input type="tel" id="mobileNumber" name="mobileNumber" placeholder="09XXXXXXXXX" pattern="^09[0-9]{9}$" maxlength="11" inputmode="numeric" autocomplete="tel" required>
                        <small class="auth-field-error" id="mobileNumberError" data-field-error="mobileNumber" hidden></small>
                    </div>
                </div>

                <h2 class="auth-section-title"><?php ps_icon('lock'); ?> Account Security</h2>

                <div class="ps-form-row-2 auth-row">
                    <div class="ps-field auth-field">
                        <label for="registerPassword">Password <span class="auth-required">*</span></label>
                        <span class="ps-field-icon has-trailing">
                            <?php ps_icon('lock'); ?>
                            <input type="password" id="registerPassword" name="password" placeholder="Create a password" autocomplete="new-password" minlength="8" required>
                            <button type="button" class="auth-password-toggle" data-password-toggle aria-label="Show password" aria-pressed="false">
                                <?php ps_icon('eye', 'auth-eye-show'); ?>
                                <?php ps_icon('eye-off', 'auth-eye-hide'); ?>
                            </button>
                        </span>
                        <small class="auth-field-error" id="registerPasswordError" data-field-error="password" hidden></small>
                    </div>
                    <div class="ps-field auth-field">
                        <label for="confirmPassword">Confirm Password <span class="auth-required">*</span></label>
                        <span class="ps-field-icon has-trailing">
                            <?php ps_icon('lock'); ?>
                            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" autocomplete="new-password" required>
                            <button type="button" class="auth-password-toggle" data-password-toggle aria-label="Show password" aria-pressed="false">
                                <?php ps_icon('eye', 'auth-eye-show'); ?>
                                <?php ps_icon('eye-off', 'auth-eye-hide'); ?>
                            </button>
                        </span>
                        <small class="auth-field-error" id="confirmPasswordError" data-field-error="confirmPassword" hidden></small>
                    </div>
                </div>

                <label class="auth-checkbox-row">
                    <input type="checkbox" id="agreeTruthful" name="agreeTruthful" required>
                    <span>I confirm that the information provided is true and correct.</span>
                </label>
                <small class="auth-field-error auth-checkbox-error" id="agreeTruthfulError" data-field-error="agreeTruthful" hidden></small>

                <button type="submit" class="ps-btn ps-btn-primary auth-submit" data-register-submit>
                    <?php ps_icon('user-plus'); ?> <span data-submit-label>Create Account</span>
                </button>

                <p class="auth-switch">Already have an account? <a href="login.php">Log In</a></p>

            </form>
        </div>
    </div>

</div>

<footer class="auth-footer">
    <div class="auth-footer-inner">
        <div class="auth-footer-text">
            <span class="auth-footer-icon"><?php ps_icon('shield-check'); ?></span>
            <span>
                <strong>Your privacy and security are important to us.</strong>
                <small>All data is protected and handled in accordance with applicable data privacy laws.</small>
            </span>
        </div>
        <p class="auth-footer-copy">&copy; <?php echo htmlspecialchars($currentYear); ?> Our Lady of the Gate Parish. All rights reserved.</p>
    </div>
</footer>

<script src="assets/js/main.js"></script>
</body>
</html>
