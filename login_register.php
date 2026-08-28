<?php
session_start();
require_once 'config.php';
function backToRegister($message) {
    $_SESSION['register_error'] = $message;
 
 
    unset($_POST['password'], $_POST['confirmPassword']);
    $_SESSION['old_input'] = $_POST;
 
    header("Location: register.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstName']);
    $middlename = trim($_POST['middleName']);
    $lastname = trim($_POST['lastName']);
    $suffix = trim($_POST['suffix']);
    $date_of_birth = $_POST['dateOfBirth'];
    $gender = $_POST['gender'];
    $email = trim($_POST['email']);
    $mobileNum = trim($_POST['mobileNumber']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $agreeTruthful = isset($_POST['agreeTruthful']);

    $namePattern = '/^[a-zA-ZÑñ .\'-]+$/u';
    if(empty($firstname)|| !preg_match($namePattern, $firstname)){
        backToRegister("Please enter a valid firstname");
    }
    if(empty($lastname)|| !preg_match($namePattern, $lastname)){
        backToRegister("Please enter a valid lastname");
    }
    if (empty($date_of_birth) || strtotime($date_of_birth) === false || $date_of_birth > date('Y-m-d')) {
        backToRegister("Please enter a valid date of birth.");
    }
    if (empty($gender)) {
        backToRegister("Please select your gender.");
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        backToRegister("Please enter a valid email address.");
    }
    if (!preg_match('/^09\d{9}$/', $mobileNum)) {
        backToRegister("Please enter a valid mobile number (format: 09XXXXXXXXX).");
    }
    if (strlen($password) < 8) {
        backToRegister("Password must be at least 8 characters.");
    }
    if ($password !== $confirmPassword) {
        backToRegister("Passwords do not match.");
    }
    if (!$agreeTruthful) {
        backToRegister("Please confirm that the information provided is true and correct.");
    }

    $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmail->store_result();
    if($checkEmail->num_rows > 0){
        $checkEmail->close();
        backToRegister("An account with that email already exist");
    }
    $checkEmail->close();
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $otp_code = random_int(100000, 999999);
    $otp_hash = password_hash((string) $otp_code, PASSWORD_DEFAULT);
    $otp_expires_at = date('Y-m-d H:i:s', time() + 180);

    $stmt = $conn->prepare("INSERT INTO users(firstname, middlename, lastname, suffix,
                                             date_of_birth,  gender, mobile_number, email, 
                                             password_hash, email_verified, otp_hash,
                                             otp_expires_at,status)
                            VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, 'active')");
    $stmt->bind_param("sssssssssss", $firstname, $middlename, $lastname,
                       $suffix, $date_of_birth, $gender, $mobileNum,
                       $email, $password_hash, $otp_hash,
                       $otp_expires_at);
    if($stmt->execute()){
       $user_id = $stmt->insert_id;
       $stmt->close();

       require_once 'includes/otp-mailer.php';
       $sent = sendOtpEmail($email, $firstname, (string)$otp_code);
       unset($_SESSION['old_input']);
       $_SESSION['pending_user_id'] = $user_id;
       $_SESSION['pending_email'] = $email;
        if (!$sent) {
            $_SESSION['otp_send_failed'] = true;
        }
 
        header("Location: verify-otp.php");
        exit;
 
    }else {
        backToRegister("Something went wrong. Please try again.");
    
    }
} 
?>      