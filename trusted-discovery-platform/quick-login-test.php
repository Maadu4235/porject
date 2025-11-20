<?php
// Simple login test to verify it works

session_start();
require_once 'config.php';

// Test with a real user from the database
$testPhone = '';
$testPassword = '';

// First, get the first user from database
try {
    $stmt = $pdo->query("SELECT phone FROM user LIMIT 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $testPhone = $result['phone'];
        echo "Found test user with phone: " . htmlspecialchars($testPhone) . "<br>";
    }
} catch (Exception $e) {
    echo "Error getting test user: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Quick Login Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Quick Login Test</h1>
        
        <div class="alert alert-info">
            <p><strong>Instructions:</strong></p>
            <ol>
                <li>Enter the phone number and password for a user you registered</li>
                <li>Click "Test Login"</li>
                <li>Check the result below</li>
            </ol>
        </div>
        
        <form id="quickLoginForm">
            <div class="mb-3">
                <label class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="phone" placeholder="<?php echo htmlspecialchars($testPhone); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" id="password" placeholder="Your password" required>
            </div>
            <button type="submit" class="btn btn-primary">Test Login</button>
        </form>
        
        <div id="result" class="mt-4"></div>
    </div>
    
    <script>
        document.getElementById('quickLoginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const phone = document.getElementById('phone').value;
            const password = document.getElementById('password').value;
            
            console.log('Testing with phone:', phone);
            
            const formData = new FormData();
            formData.append('phone', phone);
            formData.append('password', password);
            
            fetch('login.php', {
                method: 'POST',
                body: formData
            })
            .then(r => {
                console.log('Response status:', r.status);
                return r.text();
            })
            .then(text => {
                console.log('Response:', text);
                const data = JSON.parse(text);
                
                const resultDiv = document.getElementById('result');
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <strong>✓ Success!</strong><br>
                            Name: ${data.user_name}<br>
                            Phone: ${data.user_phone}<br>
                            Type: ${data.user_type}
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <strong>✗ Failed:</strong> ${data.message}
                        </div>
                    `;
                }
            })
            .catch(e => {
                console.error('Error:', e);
                document.getElementById('result').innerHTML = `
                    <div class="alert alert-danger">
                        <strong>✗ Error:</strong> ${e.message}
                    </div>
                `;
            });
        });
    </script>
</body>
</html>
