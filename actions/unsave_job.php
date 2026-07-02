<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../classes/Job.php';

// Remove content type to JSON header
// header('Content-Type: application/json'); // Removed

error_log("unsave_job.php accessed via POST");

// Check if user is logged in and is a job seeker
if (!isLoggedIn() || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'jobSeeker') {
    error_log("unsave_job.php: Unauthorized access attempt");
    // Redirect on unauthorized access
    header('Location: ' . Config::BASE_URL . '/pages/public/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$userId = $_SESSION['user_id'];

// Check if jobId is provided
if (!isset($_POST['jobId']) || !is_numeric($_POST['jobId'])) {
    error_log("unsave_job.php: Invalid or missing job ID in POST data. Received: " . print_r($_POST, true));
    // Redirect with error message
    header('Location: ' . Config::BASE_URL . '/pages/user/saved-jobs.php?status=error&message=' . urlencode('Invalid job ID.'));
    exit;
}

$jobId = $_POST['jobId'];
error_log("unsave_job.php: Received jobId = " . $jobId . " for userId = " . $userId);

try {
    $db = Database::getInstance()->getConnection();
    
    // Delete the saved job
    $stmt = $db->prepare("DELETE FROM savedjobs WHERE userId = ? AND jobId = ?");
    $result = $stmt->execute([$userId, $jobId]);
    
    error_log("unsave_job.php: DELETE query executed. Result: " . ($result ? 'Success' : 'Failed'));
    $affectedRows = $stmt->rowCount();
    error_log("unsave_job.php: Affected rows: " . $affectedRows);

    if ($result && $affectedRows > 0) {
        error_log("unsave_job.php: Successfully deleted saved job entry.");
        // Redirect with success message
        header('Location: ' . Config::BASE_URL . '/pages/user/saved-jobs.php?status=success&message=' . urlencode('Job successfully removed from saved list.'));
        exit;
    } else if ($result && $affectedRows === 0) {
        error_log("unsave_job.php: Job not found in saved list for user.");
        // Redirect with error message (job not found)
        header('Location: ' . Config::BASE_URL . '/pages/user/saved-jobs.php?status=error&message=' . urlencode('Job not found in saved list.'));
        exit;
    }
     else {
        $errorInfo = $stmt->errorInfo();
        error_log("unsave_job.php: Database execution error: " . $errorInfo[2]);
        // Redirect with database error message
        header('Location: ' . Config::BASE_URL . '/pages/user/saved-jobs.php?status=error&message=' . urlencode('Database error: ' . $errorInfo[2]));
        exit;
    }
} catch (Exception $e) {
    error_log("Error in unsave_job.php: " . $e->getMessage());
    // Redirect with generic error message
    header('Location: ' . Config::BASE_URL . '/pages/user/saved-jobs.php?status=error&message=' . urlencode('An unexpected error occurred.'));
    exit;
} 