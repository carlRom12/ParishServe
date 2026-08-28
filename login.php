<?php
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
