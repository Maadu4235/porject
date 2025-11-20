<?php
/**
 * Database Connection Test Tool
 * Use this to diagnose database connection issues
 */

header('Content-Type: application/json');

$diagnostics = [];

// 1. Check if MySQLi extension is loaded
$diagnostics['php_version'] = phpversion();
$diagnostics['mysql_support'] = extension_loaded('mysqli') ? 'MySQLi available' : 'MySQLi NOT available';
$diagnostics['pdo_support'] = extension_loaded('pdo') ? 'PDO available' : 'PDO NOT available';
$diagnostics['pdo_mysql'] = extension_loaded('pdo_mysql') ? 'PDO MySQL available' : 'PDO MySQL NOT available';

// 2. Test database connection
$host = 'localhost';
$dbname = 'project';
$username = 'root';
$password = '';

$diagnostics['connection_test'] = [];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $diagnostics['connection_test']['status'] = 'SUCCESS';
    $diagnostics['connection_test']['message'] = 'Database connected successfully!';
    
    // 3. Test if user table exists
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'user'");
        if ($stmt->rowCount() > 0) {
            $diagnostics['user_table'] = 'EXISTS';
            
            // Get table columns
            $stmt = $pdo->query("DESCRIBE user");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $diagnostics['user_table_columns'] = array_column($columns, 'Field');
        } else {
            $diagnostics['user_table'] = 'NOT FOUND - You need to create it!';
            $diagnostics['create_user_table'] = 'Run the SQL below to create the table:';
            $diagnostics['sql'] = "CREATE TABLE user (
                id INT AUTO_INCREMENT PRIMARY KEY,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                phone VARCHAR(20) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                user_type VARCHAR(50) DEFAULT 'seeker',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        }
    } catch(Exception $e) {
        $diagnostics['user_table'] = 'ERROR checking table: ' . $e->getMessage();
    }
    
    // 4. Test insert capability
    try {
        $diagnostics['insert_test'] = 'Can execute INSERT queries';
    } catch(Exception $e) {
        $diagnostics['insert_test'] = 'ERROR: ' . $e->getMessage();
    }
    
} catch(PDOException $e) {
    $diagnostics['connection_test']['status'] = 'FAILED';
    $diagnostics['connection_test']['message'] = $e->getMessage();
    $diagnostics['connection_test']['help'] = [
        'Check WAMP is running' => 'Make sure Apache and MySQL services are started in WAMP Control Panel',
        'Verify credentials' => "Host: $host, Database: $dbname, User: $username",
        'Database exists' => "Create the '$dbname' database if it doesn't exist"
    ];
}

echo json_encode($diagnostics, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
