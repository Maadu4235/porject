<?php
// jobs.php - returns jobs near a given location
require_once 'config.php';
header('Content-Type: application/json');

$location = $_GET['location'] ?? '';
$location = trim($location);

$keyword = $_GET['keyword'] ?? '';
$keyword = trim($keyword);

$qualification = $_GET['qualification'] ?? '';
$qualification = trim($qualification);

// Optional config variables: set these in config.php if you have a real jobs API
// $JOBS_API_URL = 'https://api.example.com/jobs';
// $JOBS_API_KEY = 'your_api_key_here';

if (defined('DB_CONNECTION_ERROR')) {
    // we still allow job search with sample data even if DB has issues
}

// If a real API is configured, attempt to call it
if ((!empty($JOBS_API_URL) && !empty($JOBS_API_KEY)) || (!empty($JOBS_API_KEY) && !empty($JOBS_API_HOST))) {
    if (!empty($JOBS_API_URL)) {
        $queryUrl = $JOBS_API_URL . (strpos($JOBS_API_URL, '?') === false ? '?' : '&') . 'location=' . urlencode($location);
        if (!empty($keyword)) {
            $queryUrl .= '&keyword=' . urlencode($keyword);
        }
        if (!empty($qualification)) {
            $queryUrl .= '&qualification=' . urlencode($qualification);
        }
        $headers = [
            'Authorization: Bearer ' . $JOBS_API_KEY,
            'Accept: application/json'
        ];
    } else {
        // JSearch RapidAPI host provided
        $path = '/search';
        $queryUrl = 'https://' . $JOBS_API_HOST . $path . '?query=' . urlencode($location);
        if (!empty($keyword)) {
            $queryUrl .= '&keyword=' . urlencode($keyword);
        }
        if (!empty($qualification)) {
            $queryUrl .= '&qualification=' . urlencode($qualification);
        }
        $headers = [
            'X-RapidAPI-Key: ' . $JOBS_API_KEY,
            'X-RapidAPI-Host: ' . $JOBS_API_HOST,
            'Accept: application/json'
        ];
    }
    $ch = curl_init($queryUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    error_log("cURL Error: " . $err);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($err) {
        echo json_encode(['success' => false, 'message' => 'API request error: ' . $err]);
        exit;
    }
    if ($code >= 400) {
        echo json_encode(['success' => false, 'message' => 'API returned HTTP ' . $code, 'response' => $resp]);
        exit;
    }
    $data = json_decode($resp, true);
    $jobs = $data['results'] ?? [];
    if (empty($jobs)) {
        $message = $data['error'] ?? 'No jobs found for this location.';
        echo json_encode(['success' => false, 'message' => $message, 'queryUrl' => $queryUrl]);
        exit;
    }
    // Log job search if user is logged in
    session_start();
    if (isset($_SESSION['user_id']) && isset($pdo)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO job_search_logs (user_id, location, keyword, qualification) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['user_id'],
                $location,
                $keyword,
                $qualification
            ]);
        } catch (Exception $e) {
            error_log('Job search log error: ' . $e->getMessage());
        }
    }
    // Notification logic: for each job, notify users with matching search logs
    try {
        $job_ids = array_map(function($j) { return $j['id'] ?? $j['job_id'] ?? null; }, $jobs);
        foreach ($jobs as $job) {
            $job_title = $job['title'] ?? $job['job_title'] ?? '';
            $job_location = $job['location'] ?? $job['city'] ?? '';
            $job_id = $job['id'] ?? $job['job_id'] ?? null;
            if (!$job_id) continue;
            // Find users with matching search logs
            $searchStmt = $pdo->prepare("SELECT DISTINCT user_id FROM job_search_logs WHERE (location = ? OR keyword = ? OR qualification = ?) LIMIT 10");
            $searchStmt->execute([$job_location, $job_title, $qualification]);
            $usersToNotify = $searchStmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($usersToNotify as $uid) {
                // Check if notification already exists for this job/user
                $checkStmt = $pdo->prepare("SELECT id FROM job_notifications WHERE user_id = ? AND job_id = ?");
                $checkStmt->execute([$uid, $job_id]);
                if (!$checkStmt->fetch()) {
                    $msg = "New job available: $job_title in $job_location.";
                    $notifyStmt = $pdo->prepare("INSERT INTO job_notifications (user_id, job_id, message) VALUES (?, ?, ?)");
                    $notifyStmt->execute([$uid, $job_id, $msg]);
                }
            }
        }
    } catch (Exception $e) {
        error_log('Notification error: ' . $e->getMessage());
    }
    echo json_encode(['success' => true, 'jobs' => $jobs, 'queryUrl' => $queryUrl]);
    exit;
}

// Fallback: sample job data
$sampleJobs = [
    [
        'id' => 1,
        'title' => 'Electrician - Local Workshop',
        'company' => 'ABC Services',
        'location' => 'Hubli',
        'description' => 'Skilled electrician required for maintenance',
        'salary' => '15,000 - 20,000'
    ],
    [
        'id' => 2,
        'title' => 'Mobile Repair Technician',
        'company' => 'RepairCo',
        'location' => 'Dharwad',
        'description' => 'Experienced mobile repair technician needed',
        'salary' => '12,000 - 18,000'
    ],
    [
        'id' => 3,
        'title' => 'Tailor',
        'company' => 'Local Tailors',
        'location' => 'Hubli',
        'description' => 'Experienced tailor for garment shop',
        'salary' => '10,000 - 15,000'
    ],
];

// If no location provided, return all
if ($location === '') {
    echo json_encode(['success' => true, 'jobs' => $sampleJobs]);
    exit;
}

$filtered = array_values(array_filter($sampleJobs, function($j) use ($location) {
    return stripos($j['location'], $location) !== false || stripos($j['title'], $location) !== false;
}));


// Log job search if user is logged in (sample data fallback)
session_start();
if (isset($_SESSION['user_id']) && isset($pdo)) {
    try {
        $stmt = $pdo->prepare("INSERT INTO job_search_logs (user_id, location, keyword, qualification) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            $location,
            $keyword,
            $qualification
        ]);
    } catch (Exception $e) {
        error_log('Job search log error: ' . $e->getMessage());
    }
}
// Notification logic for sample jobs
try {
    foreach ($filtered as $job) {
        $job_title = $job['title'] ?? '';
        $job_location = $job['location'] ?? '';
        $job_id = $job['id'] ?? null;
        if (!$job_id) continue;
        $searchStmt = $pdo->prepare("SELECT DISTINCT user_id FROM job_search_logs WHERE (location = ? OR keyword = ? OR qualification = ?) LIMIT 10");
        $searchStmt->execute([$job_location, $job_title, $qualification]);
        $usersToNotify = $searchStmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($usersToNotify as $uid) {
            $checkStmt = $pdo->prepare("SELECT id FROM job_notifications WHERE user_id = ? AND job_id = ?");
            $checkStmt->execute([$uid, $job_id]);
            if (!$checkStmt->fetch()) {
                $msg = "New job available: $job_title in $job_location.";
                $notifyStmt = $pdo->prepare("INSERT INTO job_notifications (user_id, job_id, message) VALUES (?, ?, ?)");
                $notifyStmt->execute([$uid, $job_id, $msg]);
            }
        }
    }
} catch (Exception $e) {
    error_log('Notification error: ' . $e->getMessage());
}
echo json_encode(['success' => true, 'jobs' => $filtered]);
exit;
?>