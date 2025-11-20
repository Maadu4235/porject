<?php
// login.php - Handle user login requests

session_start();
require_once 'config.php';
header('Content-Type: application/json');

// Check database connection
if (defined('DB_CONNECTION_ERROR')) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error. Ensure WAMP/MySQL is running.',
        'error_details' => DB_CONNECTION_ERROR
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';

    // Normalize phone number
    $phone_digits = preg_replace('/\D+/', '', $phone);
    $phone_last10 = strlen($phone_digits) > 10 ? substr($phone_digits, -10) : $phone_digits;

    // Validate inputs
    if (empty($phone) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
        exit;
    }

    try {
        // Check if user exists
        $stmt = $pdo->prepare(
            "SELECT * FROM user 
             WHERE phone = :phone OR RIGHT(REPLACE(phone, '+', ''), 10) = :phone_last10 
             LIMIT 1"
        );
        $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
        $stmt->bindParam(':phone_last10', $phone_last10, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            echo json_encode(['success' => true, 'message' => 'Login successful.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid phone number or password.']);
        }
    } catch (PDOException $e) {
        error_log('Login error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred during login.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>