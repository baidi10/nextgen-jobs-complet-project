<?php

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Application.php';

// Start session and check if user is logged in and is a job seeker
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'jobSeeker') {
    // Redirect to login page if not authorized
    header('Location: ' . Config::BASE_URL . '/pages/public/login.php');
    exit;
}

// Check if application_id was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'])) {
    $applicationId = filter_var($_POST['application_id'], FILTER_VALIDATE_INT);
    $userId = $_SESSION['user_id'];

    if ($applicationId !== false) {
        $application = new Application();

        // Attempt to delete the application
        if ($application->deleteApplication($applicationId, $userId)) {
            // Redirect back to applications page with success message
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Application deleted successfully.'];
        } else {
            // Redirect back to applications page with error message
            $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Error deleting application or application does not belong to you.'];
        }
    } else {
        // Invalid application ID
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Invalid application ID.'];
    }
} else {
    // No POST data received or application_id is missing
    $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Invalid request to delete application.'];
}

// Redirect back to the applications page
header('Location: ' . Config::BASE_URL . '/pages/user/applications.php');
exit;

?> 