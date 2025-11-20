<?php
// config.php
$host = 'localhost';
$dbname = 'project';  // Your database name
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    error_log("Database connected successfully");
} catch(PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    define('DB_CONNECTION_ERROR', $e->getMessage());
}

// Optional: configure a jobs API endpoint + API key for live job searches
// Example:
// $JOBS_API_URL = 'https://api.example.com/jobs';
// $JOBS_API_KEY = 'your_api_key_here';

// Adzuna API integration
$JOBS_API_URL = '';
$JOBS_API_HOST = 'jsearch.p.rapidapi.com';
$JOBS_API_KEY = 'e04e3dfc3fmshe9441d3117c0c81p112d76jsn82e9bc912ad7';
$JOBS_API_APP_ID = '';
$JOBS_API_APP_KEY = '';

// Security note: storing keys in source is convenient but less secure.
// For production, consider using environment variables instead.
?>