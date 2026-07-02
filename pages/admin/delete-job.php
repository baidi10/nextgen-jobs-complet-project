<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Job.php';

// Admin authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: /pages/public/login.php');
    exit;
}

$job = new Job();
$jobId = $_GET['id'] ?? 0;

if (!$jobId) {
    header('Location: manage-jobs.php');
    exit;
}

// Handle job deletion
try {
    // Access the private db property using reflection
    $reflection = new ReflectionClass($job);
    $dbProp = $reflection->getProperty('db');
    $dbProp->setAccessible(true);
    $conn = $dbProp->getValue($job);
    // Delete related applications
    $conn->prepare('DELETE FROM applications WHERE jobId = ?')->execute([$jobId]);
    // Delete related job skills
    $conn->prepare('DELETE FROM jobSkills WHERE jobId = ?')->execute([$jobId]);
    // Delete the job
    if ($job->delete($jobId)) {
        $_SESSION['success'] = "Job deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete job. Please try again.";
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Failed to delete job: " . $e->getMessage();
}

header('Location: manage-jobs.php');
exit; 