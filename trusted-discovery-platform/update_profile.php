<?php
// update_profile.php - updates user profile info in the database
session_start();
require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Accept only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = $_SESSION['user_id'];
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$user_type = $_POST['user_type'] ?? '';

try {
    $stmt = $pdo->prepare("UPDATE user SET phone = ?, user_type = ?, email = ? WHERE id = ?");
    $stmt->execute([$phone, $user_type, $email, $user_id]);
    echo json_encode(['success' => true, 'message' => 'Profile updated']);
} catch (Exception $e) {
    error_log('Profile update error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
