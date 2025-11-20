<?php
// verify_email_otp.php - Verify the OTP entered by the user

session_start();
require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$otp = $_POST['otp'] ?? '';

try {
    // Fetch the OTP from the database
    $stmt = $pdo->prepare(
        "SELECT email_otp 
         FROM user 
         WHERE id = :user_id"
    );
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && $row['email_otp'] === $otp) {
        // Update the user's email verification status
        $update = $pdo->prepare(
            "UPDATE user 
             SET email_confirmed = 1, email_otp = NULL 
             WHERE id = :user_id"
        );
        $update->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $update->execute();

        echo json_encode([
            'success' => true,
            'message' => 'Email verified successfully.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid OTP. Please try again.'
        ]);
    }
} catch (PDOException $e) {
    error_log('OTP verification error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred during OTP verification.'
    ]);
}
?>
