<?php
// confirm_email.php - handles email confirmation
require_once 'config.php';
header('Content-Type: text/html; charset=utf-8');

$email = $_GET['email'] ?? '';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo '<h2>Invalid email address.</h2>';
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE user SET email_confirmed = 1 WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        echo '<h2>Email confirmed successfully!</h2><p>You can now log in and use all features.</p>';
    } else {
        echo '<h2>Email confirmation failed.</h2><p>Email not found or already confirmed.</p>';
    }
} catch (Exception $e) {
    echo '<h2>Error confirming email.</h2><p>' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>
