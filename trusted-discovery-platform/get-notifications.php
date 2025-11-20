<?php
// get-notifications.php - Fetch and return unread job notifications for the logged-in user

session_start();
require_once 'config.php';
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'notifications' => [],
        'message' => 'User not logged in.'
    ]);
    exit;
}

try {
    // Fetch unread notifications for the user
    $stmt = $pdo->prepare(
        "SELECT id, message, notified_at 
         FROM job_notifications 
         WHERE user_id = :user_id AND is_read = 0 
         ORDER BY notified_at DESC 
         LIMIT 10"
    );
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mark fetched notifications as read
    if (!empty($notifications)) {
        $ids = array_column($notifications, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $markStmt = $pdo->prepare(
            "UPDATE job_notifications 
             SET is_read = 1 
             WHERE id IN ($placeholders)"
        );
        $markStmt->execute($ids);
    }

    // Return the notifications in JSON format
    echo json_encode([
        'success' => true,
        'notifications' => $notifications
    ]);
} catch (PDOException $e) {
    // Log the error and return a failure response
    error_log('Notification fetch error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'notifications' => [],
        'message' => 'An error occurred while fetching notifications.'
    ]);
}
?>
