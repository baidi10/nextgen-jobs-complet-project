<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Application.php';

// Start session to use flash messages
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging - ensure it's on
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log entry point
error_log("[actions/update-application-status.php] Script started via POST.");

// Authentication check
if (!isLoggedIn() || !isEmployer()) {
    $_SESSION['error_message'] = 'You must be logged in as an employer to perform this action.';
    error_log("[actions/update-application-status.php] Authentication failed. User ID: " . ($_SESSION['user_id'] ?? 'NULL'));
    header('Location: /pages/public/login.php');
    exit;
}

// Check if form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicationId = filter_input(INPUT_POST, 'application_id', FILTER_SANITIZE_NUMBER_INT);
    $newStatus = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

    error_log("[actions/update-application-status.php] Received POST data - Application ID: " . $applicationId . ", Status: " . $newStatus);

    // Validate input
    if (!$applicationId || !is_numeric($applicationId) || empty($newStatus)) {
        $_SESSION['error_message'] = 'Invalid application data provided.';
        error_log("[actions/update-application-status.php] Input validation failed.");
        header('Location: /pages/employer/applicants.php'); // Redirect back to applicants page
        exit;
    }

    // Validate status against allowed values
    $allowedStatuses = ['pending', 'viewed', 'interviewing', 'rejected', 'hired'];
    if (!in_array($newStatus, $allowedStatuses)) {
        $_SESSION['error_message'] = 'Invalid status value.';
         error_log("[actions/update-application-status.php] Invalid status value: " . $newStatus);
        header('Location: /pages/employer/applicants.php'); // Redirect back to applicants page
        exit;
    }

    $userId = $_SESSION['user_id']; // Get logged-in employer user ID
    $application = new Application();

    error_log("[actions/update-application-status.php] Input validated. Checking ownership for App ID: " . $applicationId . " by User ID: " . $userId);

    // Validate if the application belongs to a job posted by the current employer's company
    if ($application->isApplicationBelongToEmployer($applicationId, $userId)) {
        error_log("[actions/update-application-status.php] Ownership confirmed. Attempting to update status to: " . $newStatus);
        // Update the application status
        if ($application->updateStatus($applicationId, $newStatus)) {
            $_SESSION['success_message'] = 'Application status updated successfully!';
            error_log("[actions/update-application-status.php] Status update successful. Setting success message.");
        } else {
            $_SESSION['error_message'] = 'Failed to update application status.';
            error_log("[actions/update-application-status.php] Status update failed via Application->updateStatus. Setting error message.");
        }
    } else {
        $_SESSION['error_message'] = 'Unauthorized: You do not have permission to update this application.';
        error_log("[actions/update-application-status.php] Unauthorized attempt. App ID: " . $applicationId . ", User ID: " . $userId);
    }

} else {
    // Not a POST request, redirect to applicants page
    $_SESSION['error_message'] = 'Invalid request method.';
    error_log("[actions/update-application-status.php] Received non-POST request.");
}

// Log session messages before redirect
error_log("[actions/update-application-status.php] Session Success Message: " . ($_SESSION['success_message'] ?? 'NOT SET'));
error_log("[actions/update-application-status.php] Session Error Message: " . ($_SESSION['error_message'] ?? 'NOT SET'));

// Redirect back to the applicants page
header('Location: /pages/employer/applicants.php');
exit;

?> 