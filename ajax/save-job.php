<?php
// ajax/save-job.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../classes/Job.php';

// Default response
$response = [
    'success' => false,
    'message' => 'An error occurred'
];

// Check if user is logged in
if (!isLoggedIn() || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'jobSeeker') {
    $response = [
        'success' => false,
        'message' => 'Please log in to save this job',
        'redirect' => Config::BASE_URL . '/pages/public/login.php?redirect=' . urlencode($_SERVER['HTTP_REFERER'] ?? '/pages/public/jobs.php')
    ];
} else {
    // Get the job ID from the request
    $jobId = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
    
    if ($jobId > 0) {
        $job = new Job();
        $saved = $job->saveJob($_SESSION['user_id'], $jobId);
        
        if ($saved) {
            $response = [
                'success' => true,
                'message' => 'Job saved successfully'
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Failed to save job'
            ];
        }
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;