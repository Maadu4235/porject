<?php
// test-login.php - Test page to verify login flow
session_start();
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Test - Dwar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h1 class="mb-4">Login Flow Test</h1>
                
                <div id="testResults" class="alert alert-info">
                    <h5>Testing system components...</h5>
                </div>
                
                <div class="mt-4">
                    <h4>Test Database Connection</h4>
                    <button class="btn btn-primary" onclick="testDatabaseConnection()">Test Database</button>
                    <div id="dbResult" class="mt-2"></div>
                </div>
                
                <div class="mt-4">
                    <h4>Test Login Flow</h4>
                    <form id="testLoginForm">
                        <div class="mb-3">
                            <label class="form-label">Phone Number (from registration)</label>
                            <input type="tel" class="form-control" id="testPhone" placeholder="+919876543210" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password (from registration)</label>
                            <input type="password" class="form-control" id="testPassword" placeholder="Your password" required>
                        </div>
                        <button type="submit" class="btn btn-success">Test Login</button>
                    </form>
                    <div id="loginResult" class="mt-2"></div>
                </div>
                
                <div class="mt-4">
                    <h4>Current Session Status</h4>
                    <button class="btn btn-info" onclick="checkSessionStatus()">Check Status</button>
                    <div id="sessionResult" class="mt-2"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function testDatabaseConnection() {
            fetch('test-db-connection.php')
                .then(response => response.json())
                .then(data => {
                    const resultDiv = document.getElementById('dbResult');
                    if (data.connection_test && data.connection_test.status === 'SUCCESS') {
                        resultDiv.innerHTML = `
                            <div class="alert alert-success">
                                <strong>✓ Database Connected!</strong><br>
                                User Table: ${data.user_table || 'exists'}
                            </div>
                        `;
                    } else {
                        resultDiv.innerHTML = `
                            <div class="alert alert-danger">
                                <strong>✗ Database Connection Failed</strong><br>
                                ${data.connection_test?.message || 'Unknown error'}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    document.getElementById('dbResult').innerHTML = `
                        <div class="alert alert-danger">Error: ${error.message}</div>
                    `;
                });
        }

        document.getElementById('testLoginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('phone', document.getElementById('testPhone').value);
            formData.append('password', document.getElementById('testPassword').value);
            
            fetch('login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const resultDiv = document.getElementById('loginResult');
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <strong>✓ Login Successful!</strong><br>
                            Name: ${data.user_name}<br>
                            Phone: ${data.user_phone}<br>
                            Type: ${data.user_type}<br>
                            <a href="index.html" class="btn btn-sm btn-primary mt-2">Go to Home Page</a>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <strong>✗ Login Failed</strong><br>
                            ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                document.getElementById('loginResult').innerHTML = `
                    <div class="alert alert-danger">Error: ${error.message}</div>
                `;
            });
        });

        function checkSessionStatus() {
            fetch('check_login.php')
                .then(response => response.json())
                .then(data => {
                    const resultDiv = document.getElementById('sessionResult');
                    if (data.logged_in) {
                        resultDiv.innerHTML = `
                            <div class="alert alert-success">
                                <strong>✓ User is Logged In</strong><br>
                                Name: ${data.user_name}<br>
                                Phone: ${data.user_phone}<br>
                                Type: ${data.user_type}
                            </div>
                        `;
                    } else {
                        resultDiv.innerHTML = `
                            <div class="alert alert-warning">
                                No user logged in
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    document.getElementById('sessionResult').innerHTML = `
                        <div class="alert alert-danger">Error: ${error.message}</div>
                    `;
                });
        }
    </script>
</body>
</html>
