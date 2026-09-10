<?php
    session_start();
    require_once 'config.php';

    // Walang pending registration = walang dapat i-verify dito.
    // Nangyayari ito kung direktang tine-type ang URL, o kung tapos na
    // ang verification at binalikan pa ang page.
    if (!isset($_SESSION['pending_user_id'])) {
        header("Location: register.html");
        exit;
    }

    $user_id = $_SESSION['pending_user_id'];
    $email   = $_SESSION['pending_email'];
    $currentYear = date('Y');

    $error   = "";
    $success = "";

    // Kung nabigo ang unang pagpapadala ng email (galing sa
    // login_register.html), ipakita agad ang babala.
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
                // agad ito sa INSERT (tingnan ang login_register.html).
                $stmt = $conn->prepare("UPDATE users SET email_verified = 1, otp_hash = NULL, otp_expires_at = NULL WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $stmt->close();

                unset($_SESSION['pending_user_id']);
                unset($_SESSION['pending_email']);

                $_SESSION['login_success'] = "Your account is verified! You can now log in.";
                header("Location: login.html");
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

    $_SESSION['otp_error'] = $error;
    $_SESSION['otp_success'] = $success;
    header("Location: verify-otp.html", true, 303);
    exit;
