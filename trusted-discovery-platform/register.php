<?php
// register.php - Handle user registration requests

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

// Check if PDO exists
if (!isset($pdo)) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database connection not established. Make sure WAMP server is running and MySQL is configured.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['firstName'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';

    // Validate inputs
    if (empty($firstName) || empty($lastName) || empty($phone) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
        exit;
    }
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
        exit;
    }
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long.']);
        exit;
    }

    try {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Check if phone already exists - using 'user' table
        $stmt = $pdo->prepare("SELECT id FROM user WHERE phone = ?");
        $stmt->execute([$phone]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Phone number already registered']);
            exit;
        }

        // Insert user into the database
        $stmt = $pdo->prepare(
            "INSERT INTO user (first_name, last_name, phone, password, email) 
             VALUES (:first_name, :last_name, :phone, :password, :email)"
        );
        $stmt->bindParam(':first_name', $firstName, PDO::PARAM_STR);
        $stmt->bindParam(':last_name', $lastName, PDO::PARAM_STR);
        $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        // Auto login after registration
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['user_name'] = $firstName . ' ' . $lastName;
        $_SESSION['user_phone'] = $phone;
        $_SESSION['user_type'] = 'seeker'; // Default user type

        echo json_encode([
            'success' => true, 
            'message' => 'Registration successful!',
            'user_name' => $firstName . ' ' . $lastName,
            'user_type' => 'seeker'
        ]);
        
        // Send confirmation email if email is provided
        if (!empty($email)) {
            $subject = "Dwar Registration Confirmation";
            $message = "Hello $firstName,\n\nThank you for registering. Your account has been created successfully.\n\nBest regards,\nDwar Team";
            $headers = "From: no-reply@dwar.local\r\n";
            mail($email, $subject, $message, $headers);
        }
    } catch (PDOException $e) {
        error_log('Registration error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred during registration.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>