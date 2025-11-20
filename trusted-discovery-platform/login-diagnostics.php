<?php
/**
 * Login System Diagnostics
 */

session_start();
require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Diagnostics - Dwar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { padding-top: 20px; }
        .status-box { margin: 20px 0; padding: 15px; border-radius: 10px; }
        .status-ok { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .status-error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .status-warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
        code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4"><i class="fas fa-stethoscope"></i> Login System Diagnostics</h1>
        
        <!-- 1. Database Connection -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">1. Database Connection</h5>
            </div>
            <div class="card-body">
                <?php
                if (defined('DB_CONNECTION_ERROR')) {
                    echo '<div class="status-box status-error">';
                    echo '<strong>✗ Database Connection Failed</strong><br>';
                    echo 'Error: ' . htmlspecialchars(DB_CONNECTION_ERROR);
                    echo '</div>';
                } else if (isset($pdo)) {
                    echo '<div class="status-box status-ok">';
                    echo '<strong>✓ Database Connection OK</strong>';
                    echo '</div>';
                } else {
                    echo '<div class="status-box status-error">';
                    echo '<strong>✗ PDO Not Available</strong>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>

        <!-- 2. User Table Check -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">2. User Table</h5>
            </div>
            <div class="card-body">
                <?php
                try {
                    if (isset($pdo)) {
                        $stmt = $pdo->query("SHOW TABLES LIKE 'user'");
                        if ($stmt->rowCount() > 0) {
                            echo '<div class="status-box status-ok">';
                            echo '<strong>✓ User Table Exists</strong>';
                            
                            // Show table columns
                            $stmt = $pdo->query("DESCRIBE user");
                            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            echo '<div class="mt-2"><strong>Columns:</strong>';
                            echo '<ul>';
                            foreach ($columns as $col) {
                                echo '<li><code>' . htmlspecialchars($col['Field']) . '</code> - ' . htmlspecialchars($col['Type']) . '</li>';
                            }
                            echo '</ul></div>';
                            
                            // Count users
                            $stmt = $pdo->query("SELECT COUNT(*) as count FROM user");
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            echo '<p class="mt-2"><strong>Users in database:</strong> ' . $result['count'] . '</p>';
                            echo '</div>';
                        } else {
                            echo '<div class="status-box status-error">';
                            echo '<strong>✗ User Table Not Found</strong>';
                            echo '<p class="mt-2">Run this SQL in phpMyAdmin:</p>';
                            echo '<pre><code>CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type VARCHAR(50) DEFAULT \'seeker\',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);</code></pre>';
                            echo '</div>';
                        }
                    }
                } catch (Exception $e) {
                    echo '<div class="status-box status-error">';
                    echo '<strong>✗ Error Checking Table:</strong> ' . htmlspecialchars($e->getMessage());
                    echo '</div>';
                }
                ?>
            </div>
        </div>

        <!-- 3. Session Check -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">3. Current Session</h5>
            </div>
            <div class="card-body">
                <?php
                if (isset($_SESSION['user_id'])) {
                    echo '<div class="status-box status-ok">';
                    echo '<strong>✓ User Logged In</strong><br>';
                    echo 'ID: ' . htmlspecialchars($_SESSION['user_id']) . '<br>';
                    echo 'Name: ' . htmlspecialchars($_SESSION['user_name']) . '<br>';
                    echo 'Phone: ' . htmlspecialchars($_SESSION['user_phone']) . '<br>';
                    echo 'Type: ' . htmlspecialchars($_SESSION['user_type']);
                    echo '</div>';
                } else {
                    echo '<div class="status-box status-warning">';
                    echo '<strong>ⓘ No User Logged In</strong>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>

        <!-- 4. Test Login Form -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">4. Test Login</h5>
            </div>
            <div class="card-body">
                <form id="testLoginForm">
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="testPhone" placeholder="+919876543210" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" id="testPassword" placeholder="Password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Test Login</button>
                </form>
                <div id="testResult" class="mt-3"></div>
            </div>
        </div>

        <!-- 5. PHP Configuration -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">5. PHP Configuration</h5>
            </div>
            <div class="card-body">
                <div class="status-box status-ok">
                    PHP Version: <code><?php echo phpversion(); ?></code><br>
                    PDO Support: <?php echo extension_loaded('pdo') ? '<strong style="color:green">✓ Yes</strong>' : '<strong style="color:red">✗ No</strong>'; ?><br>
                    PDO MySQL: <?php echo extension_loaded('pdo_mysql') ? '<strong style="color:green">✓ Yes</strong>' : '<strong style="color:red">✗ No</strong>'; ?><br>
                    MySQLi: <?php echo extension_loaded('mysqli') ? '<strong style="color:green">✓ Yes</strong>' : '<strong style="color:red">✗ No</strong>'; ?>
                </div>
            </div>
        </div>

        <div class="alert alert-info mb-4">
            <strong>Next Steps:</strong>
            <ul class="mb-0">
                <li>Check all status indicators above are green</li>
                <li>If database connection fails, start WAMP and verify MySQL is running</li>
                <li>If user table missing, run the SQL provided above</li>
                <li>Use the Test Login form to verify login is working</li>
                <li>After successful test, go to <a href="login.html">login.html</a></li>
            </ul>
        </div>
    </div>

    <script>
        document.getElementById('testLoginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('phone', document.getElementById('testPhone').value);
            formData.append('password', document.getElementById('testPassword').value);
            
            console.log('Testing login...');
            
            fetch('login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.text();
            })
            .then(text => {
                console.log('Response text:', text);
                const data = JSON.parse(text);
                console.log('Parsed data:', data);
                
                const resultDiv = document.getElementById('testResult');
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="status-box status-ok">
                            <strong>✓ Login Successful!</strong><br>
                            Name: ${data.user_name}<br>
                            Phone: ${data.user_phone}<br>
                            Type: ${data.user_type}<br>
                            <p class="mt-2"><a href="index.html" class="btn btn-sm btn-primary">Go to Home</a></p>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="status-box status-error">
                            <strong>✗ Login Failed</strong><br>
                            ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('testResult').innerHTML = `
                    <div class="status-box status-error">
                        <strong>✗ Error:</strong> ${error.message}
                    </div>
                `;
            });
        });
    </script>
</body>
</html>
