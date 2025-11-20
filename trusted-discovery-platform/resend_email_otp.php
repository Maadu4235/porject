<?php
// resend_email_otp.php - generates and sends a new OTP to the user's email
session_start();
require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT first_name, email FROM user WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user || empty($user['email'])) {
        echo json_encode(['success' => false, 'message' => 'Email not found.']);
        exit;
    }
    $emailOtp = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    $update = $pdo->prepare("UPDATE user SET email_otp = ? WHERE id = ?");
    $update->execute([$emailOtp, $_SESSION['user_id']]);
    $subject = "Dwar Email Verification OTP";
    $message = "Hello {$user['first_name']},\n\nYour new OTP for email verification is: $emailOtp\n\nPlease enter this OTP on the website to verify your email.";
    $headers = "From: no-reply@dwar.local\r\n";
    mail($user['email'], $subject, $message, $headers);
    echo json_encode(['success' => true, 'message' => 'OTP resent to your email.']);
} catch (Exception $e) {
    error_log('Resend OTP error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
?>
