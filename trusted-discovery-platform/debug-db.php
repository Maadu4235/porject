<?php
// Check database structure and test password hashing

require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Database Debug Info</h1>";

try {
    // Show user table structure
    echo "<h2>User Table Structure</h2>";
    $stmt = $pdo->query("DESCRIBE user");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
    
    // Show users
    echo "<h2>Users in Database</h2>";
    $stmt = $pdo->query("SELECT id, first_name, last_name, phone, user_type, LENGTH(password) as pwd_length FROM user");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Name</th><th>Phone</th><th>Type</th><th>Password Length</th></tr>";
    
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['first_name']) . " " . htmlspecialchars($user['last_name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['phone']) . "</td>";
        echo "<td>" . htmlspecialchars($user['user_type']) . "</td>";
        echo "<td>" . $user['pwd_length'] . " chars</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Test password hashing
    echo "<h2>Password Hash Test</h2>";
    echo "<p>To test login, use a password you registered with.</p>";
    
    echo "<form method='POST'>";
    echo "<input type='password' name='test_password' placeholder='Enter a password to hash'>";
    echo "<button type='submit'>Hash & Test</button>";
    echo "</form>";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_password'])) {
        $pwd = $_POST['test_password'];
        $hash = password_hash($pwd, PASSWORD_DEFAULT);
        echo "<p>Original: <code>$pwd</code></p>";
        echo "<p>Hashed: <code>$hash</code></p>";
        echo "<p>Verify result: " . (password_verify($pwd, $hash) ? "<strong style='color:green'>✓ OK</strong>" : "<strong style='color:red'>✗ FAILED</strong>") . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

?>
