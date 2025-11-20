<?php
// Show all users in database for debugging

require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Users in Database</h1>";
echo "<p><a href='login-diagnostics.php'>Back to Diagnostics</a></p>";

try {
    $stmt = $pdo->query("SELECT id, first_name, last_name, phone, user_type FROM user ORDER BY id DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) > 0) {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Name</th><th>Phone</th><th>Type</th></tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
            echo "<td>" . htmlspecialchars($user['first_name']) . " " . htmlspecialchars($user['last_name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['phone']) . "</td>";
            echo "<td>" . htmlspecialchars($user['user_type']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "<p><strong>Total users:</strong> " . count($users) . "</p>";
    } else {
        echo "<p>No users in database. <a href='register.html'>Create an account</a></p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
