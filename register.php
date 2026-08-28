<?php
session_start();
require __DIR__ . '/includes/icons.php';
$currentYear = date('Y');
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);
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
    <div class="auth-panel">
        <div class="auth-card auth-card-wide">

            <div class="ps-heading-ornament auth-card-ornament"><span></span><?php ps_icon('cross'); ?><span></span></div>
            <h1>Create an Account</h1>
            <p class="auth-sub">Fill in the details below to create your ParishServe account.</p>
             <?php if (isset($_SESSION['register_error'])): ?>
                <div class="auth-alert" role="alert">
                    <?php ps_icon('info'); ?>
                    <span><?php echo htmlspecialchars($_SESSION['register_error']); ?></span>
                </div>
                <?php unset($_SESSION['register_error']); ?>
            <?php endif; ?>
            <form action="login_register.php" method="post" data-register-form novalidate>

                <h2 class="auth-section-title"><?php ps_icon('user'); ?> Personal Information</h2>

                <div class="ps-form-row-3 auth-row">
                    <div class="ps-field auth-field">
                        <label for="firstName">First Name <span class="auth-required">*</span></label>
                        <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($old['firstName'] ?? ''); ?>"
                                    placeholder="First name" autocomplete="given-name" required>
                    </div>
                    <div class="ps-field auth-field">
                        <label for="middleName">Middle Name</label>
                        <input type="text" id="middleName" name="middleName" value="<?php echo htmlspecialchars($old['middleName'] ?? ''); ?>"
                                     placeholder="Middle name (optional)" autocomplete="additional-name">
                    </div>
                    <div class="ps-field auth-field">
                        <label for="lastName">Last Name <span class="auth-required">*</span></label>
                        <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($old['lastName'] ?? ''); ?>"
                                     placeholder="Last name" autocomplete="family-name" required>
                        <small class="auth-field-error" id="lastNameError" data-field-error="lastName" hidden></small>
                    </div>
                </div>

                <div class="ps-form-row-3 auth-row">
                    <div class="ps-field auth-field">
                        <label for="suffix">Suffix (Optional)</label>
                        <span class="ps-select is-block">
                            <select id="suffix" name="suffix">
                                <option value=""<?php echo (($old['suffix'] ?? '') === '') ? 'selected' : ''; ?>>   Select suffix</option>
                                <option value="Jr."<?php echo (($old['suffix'] ?? '') === 'Jr.') ? 'selected' : ''; ?>>Jr.</option>
                                <option value="Sr."<?php echo (($old['suffix'] ?? '') === 'Sr.') ? 'selected' : ''; ?>>Sr.</option>
                                <option value="II"<?php echo (($old['suffix'] ?? '') === 'II') ? 'selected' : ''; ?>>II</option>
                                <option value="III"<?php echo (($old['suffix'] ?? '') === 'III') ? 'selected' : ''; ?>>III</option>
                            </select>
                            <?php ps_icon('chevron-down'); ?>
                        </span>
                    </div>
                    <div class="ps-field auth-field">
                        <label for="dateOfBirth">Date of Birth <span class="auth-required">*</span></label>
                        <span class="ps-input-icon">
                            <input type="date" id="dateOfBirth" name="dateOfBirth" value="<?php echo htmlspecialchars($old['dateOfBirth'] ?? ''); ?>"
                                         max="<?php echo htmlspecialchars(date('Y-m-d')); ?>" required>
                            <?php ps_icon('calendar'); ?>
                        </span>
                        <small class="auth-field-error" id="dateOfBirthError" data-field-error="dateOfBirth" hidden></small>
                    </div>
                    <div class="ps-field auth-field">
                        <label for="gender">Gender <span class="auth-required">*</span></label>
                        <span class="ps-select is-block">
                            <select id="gender" name="gender" required>
                                <option value=""<?php echo (($old['gender'] ?? '') === '') ? 'selected' : ''; ?>>Select gender</option>
                                <option value="Female"<?php echo (($old['gender'] ?? '') === 'Female') ? 'selected' : ''; ?>>Female</option>
                                <option value="Male"<?php echo (($old['gender'] ?? '') === 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Prefer not to say"<?php echo (($old['gender'] ?? '') === 'Prefer not to say') ? 'selected' : ''; ?>>Prefer not to say</option>
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
                            <input type="email" id="registerEmail" name="email" value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>"
                                         placeholder="name@example.com" autocomplete="email" required>
                        </span>
                        <small class="auth-field-error" id="registerEmailError" data-field-error="email" hidden></small>
                    </div>
                    <div class="ps-field auth-field">
                        <label for="mobileNumber">Mobile Number <span class="auth-required">*</span></label>
                        <input type="tel" id="mobileNumber" name="mobileNumber" value="<?php echo htmlspecialchars($old['mobileNumber'] ?? ''); ?>"
                                     placeholder="09XXXXXXXXX" pattern="^09[0-9]{9}$" maxlength="11" inputmode="numeric" autocomplete="tel" required>
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

                <button type="submit" name="register" class="ps-btn ps-btn-primary auth-submit" data-register-submit>
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
