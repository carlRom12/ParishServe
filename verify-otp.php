    <?php
    session_start();
    require_once 'config.php';
    require __DIR__ . '/includes/icons.php';

    // Walang pending registration = walang dapat i-verify dito.
    // Nangyayari ito kung direktang tine-type ang URL, o kung tapos na
    // ang verification at binalikan pa ang page.
    if (!isset($_SESSION['pending_user_id'])) {
        header("Location: register.php");
        exit;
    }

    $user_id = $_SESSION['pending_user_id'];
    $email   = $_SESSION['pending_email'];
    $currentYear = date('Y');

    $error   = "";
    $success = "";

    // Kung nabigo ang unang pagpapadala ng email (galing sa
    // login_register.php), ipakita agad ang babala.
    if (isset($_SESSION['otp_send_failed'])) {
        $error = "We couldn't send the email. Please use \"Resend code\" below.";
        unset($_SESSION['otp_send_failed']);
    }

    // ---- Kapag pinindot ang "Verify Account" ----
    if (isset($_POST['verify'])) {
        $enteredOtp = trim($_POST['otp'] ?? '');

        if (empty($enteredOtp)) {
            $error = "Please enter the 6-digit code.";
        } else {
            $stmt = $conn->prepare("SELECT otp_hash, otp_expires_at FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if (!$user) {
                $error = "Account not found. Please register again.";
            } elseif (strtotime($user['otp_expires_at']) < time()) {
                $error = "This code has expired. Please request a new one.";
            } elseif (!password_verify($enteredOtp, $user['otp_hash'])) {
                $error = "Incorrect code. Please try again.";
            } else {
                // TAMA -- markahan ang account na verified.
                // Hindi na kailangang i-set ang status dito, 'active' na
                // agad ito sa INSERT (tingnan ang login_register.php).
                $stmt = $conn->prepare("UPDATE users SET email_verified = 1, otp_hash = NULL, otp_expires_at = NULL WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $stmt->close();

                unset($_SESSION['pending_user_id']);
                unset($_SESSION['pending_email']);

                $_SESSION['login_success'] = "Your account is verified! You can now log in.";
                header("Location: login.php");
                exit;
            }
        }
    }

    // ---- Kapag pinindot ang "Resend code" ----
    if (isset($_POST['resend'])) {
        require_once 'includes/otp-mailer.php';

        $new_otp        = random_int(100000, 999999);
        $new_otp_hash   = password_hash((string) $new_otp, PASSWORD_DEFAULT);
        $new_expires_at = date('Y-m-d H:i:s', time() + 180);

        $stmt = $conn->prepare("UPDATE users SET otp_hash = ?, otp_expires_at = ? WHERE id = ?");
        $stmt->bind_param("ssi", $new_otp_hash, $new_expires_at, $user_id);
        $stmt->execute();
        $stmt->close();

        if (sendOtpEmail($email, "", (string) $new_otp)) {
            $success = "A new code has been sent to your email.";
        } else {
            $error = "Could not resend the code. Please try again in a moment.";
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email · ParishServe</title>
    <meta name="description" content="Verify your email address to activate your ParishServe account.">
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
                <h2>Almost There!</h2>
                <p>We've sent a verification code to your email. Enter it to activate your ParishServe account.</p>
            </div>
        </div>

        <!-- ============================ RIGHT: FORM ============================= -->
        <div class="auth-panel">
            <div class="auth-card">

                <div class="ps-heading-ornament auth-card-ornament"><span></span><?php ps_icon('cross'); ?><span></span></div>
                <h1>Verify Your Email</h1>
                <p class="auth-sub">We sent a 6-digit code to <strong><?php echo htmlspecialchars($email); ?></strong></p>

                <?php if ($error): ?>
                    <div class="auth-alert" role="alert">
                        <?php ps_icon('info'); ?>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="auth-alert" role="status">
                        <?php ps_icon('info'); ?>
                        <span><?php echo htmlspecialchars($success); ?></span>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="ps-field auth-field">
                        <label for="otp">Verification Code</label>
                        <input type="text" id="otp" name="otp" class="otp-input"
                            maxlength="6" inputmode="numeric" pattern="\d{6}"
                            placeholder="000000" autocomplete="one-time-code" autofocus required>
                        <small class="ps-form-hint">The code expires 3 minutes after it was sent.</small>
                    </div>

                    <button type="submit" name="verify" class="ps-btn ps-btn-primary auth-submit">
                        <?php ps_icon('check'); ?> Verify Account
                    </button>
                </form>

                <div class="auth-divider"><span>or</span></div>

                <form method="post">
                    <button type="submit" name="resend" class="ps-btn ps-btn-outline auth-submit">
                        <?php ps_icon('mail'); ?> Resend Code
                    </button>
                </form>

                <p class="auth-switch">Wrong email? <a href="register.php">Start over</a></p>
                <p class="auth-privacy-note"><?php ps_icon('lock'); ?> Your privacy and security are important to us.</p>

            </div>
        </div>

    </div>

    <style>
    /* Ang OTP field lang ang may espesyal na hitsura -- malaki at
    magkakalayo ang digits, para madaling basahin habang ikinukumpara
    sa email. Inline muna ito habang isang page lang ang gumagamit;
    ilipat sa login.css kung magkakaroon pa ng ibang OTP screen
    (halimbawa forgot-password). */
    .otp-input {
        font-size: 28px;
        text-align: center;
        letter-spacing: 10px;
        font-weight: 600;
    }
    </style>

    <script src="assets/js/main.js"></script>
    </body>
    </html>