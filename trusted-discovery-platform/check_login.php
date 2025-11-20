<?php
// check_login.php - Verify if the user is logged in and fetch user details

session_start();
require_once 'config.php';
header('Content-Type: application/json');

try {
    if (isset($_SESSION['user_id'])) {
        // Fetch user details from the database
        $stmt = $pdo->prepare(
            "SELECT first_name, last_name, user_type, phone 
             FROM user 
             WHERE id = :user_id"
        );
        $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo json_encode([
                'logged_in' => true,
                'user_name' => $user['first_name'] . ' ' . $user['last_name'],
                'user_type' => $user['user_type'],
                'user_phone' => $user['phone']
            ]);
        } else {
            // User not found in the database
            session_destroy();
            echo json_encode(['logged_in' => false, 'message' => 'User not found.']);
        }
    } else {
        echo json_encode(['logged_in' => false, 'message' => 'User not logged in.']);
    }
} catch (PDOException $e) {
    error_log('Check login error: ' . $e->getMessage());
    echo json_encode(['logged_in' => false, 'message' => 'An error occurred while verifying login.']);
}
?>