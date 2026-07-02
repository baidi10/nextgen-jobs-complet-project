<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Application.php';

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Authentication check
if (!isLoggedIn() || !isEmployer()) {
    $_SESSION['error_message'] = 'You must be logged in as an employer to access this page.';
    header('Location: /pages/public/login.php');
    exit;
}

$userId = $_SESSION['user_id']; // Logged-in employer user ID
$application = new Application();
$currentApplication = null;
$error = '';
$success = '';

// Get application ID from URL
$applicationId = filter_input(INPUT_GET, 'applicationId', FILTER_SANITIZE_NUMBER_INT);

// Debugging: Log application ID and user ID
error_log('Application ID: ' . $applicationId);
error_log('User ID: ' . $userId);

if (!$applicationId || !is_numeric($applicationId)) {
    $error = 'Invalid application ID provided.';
} else {
    // Fetch application details including applicant name and job title for display
    $currentApplication = $application->getApplicationById($applicationId);

    // Debugging: Echo application details to verify retrieval
    // if ($currentApplication) {
    //     echo 'Application Details: ' . print_r($currentApplication, true);
    // } else {
    //     echo 'Application not found in database.';
    // }

    // Check if application exists and belongs to the current employer's company
    if (!$currentApplication) {
        $error = 'Application not found.';
        $currentApplication = null;
        error_log('Error: ' . $error);
    } elseif (!$application->isApplicationBelongToEmployer($applicationId, $userId)) {
        $error = 'You do not have permission to view this application.';
        $currentApplication = null;
        error_log('Error: ' . $error);
    }
}

// Handle form submission to update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $currentApplication) {
    $newStatus = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

    // Validate status against allowed values
    $allowedStatuses = ['pending', 'viewed', 'interviewing', 'rejected', 'hired'];
    if (!in_array($newStatus, $allowedStatuses)) {
        $error = 'Invalid status value provided.';
    } else {
        try {
            // Update the application status in the database
            if ($application->updateStatus($applicationId, $newStatus)) {
                // Create notification for the job seeker
                $notification = [
                    'userId' => $currentApplication['userId'],
                    'notificationType' => 'application',
                    'title' => 'Application Status Updated',
                    'message' => "Your application for {$currentApplication['jobTitle']} has been {$newStatus}",
                    'importance' => 1,
                    'isRead' => 0,
                    'createdAt' => date('Y-m-d H:i:s')
                ];
                
                // Insert notification using the existing database connection
                $db = Database::getInstance();
                $db->query("INSERT INTO notifications (userId, notificationType, title, message, importance, isRead, createdAt) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)", 
                           [$notification['userId'], $notification['notificationType'], $notification['title'], 
                            $notification['message'], $notification['importance'], $notification['isRead'], 
                            $notification['createdAt']]);
                
                $success = 'Application status updated successfully!';
                // Refresh application data to show updated status on the form
                $currentApplication = $application->getApplicationById($applicationId);
                
                // Redirect back to applicants page after 2 seconds
                header("refresh:2;url=/pages/employer/applicants.php?job_id=" . $currentApplication['jobId']);
            } else {
                $error = 'Failed to update application status.';
            }
        } catch (Exception $e) {
            $error = 'An error occurred while updating the status: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Change Applicant Status | JOBEST';
include __DIR__ . '/../../includes/header.php';
?>

<div class="container py-5">
    <h1 class="h3 fw-bold mb-4">Change Applicant Status</h1>

    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success" role="alert">
            <?= htmlspecialchars($success) ?>
            <p class="mb-0">Redirecting back to applicants page...</p>
        </div>
    <?php endif; ?>

    <?php if ($currentApplication): ?>
        <div class="card shadow-sm border-0 rounded-4 p-4">
            <h5 class="card-title fw-bold mb-3">Applicant: <?= htmlspecialchars($currentApplication['firstName'] . ' ' . $currentApplication['lastName']) ?></h5>
            <p class="card-text"><strong>Job:</strong> <?= htmlspecialchars($currentApplication['jobTitle']) ?></p>
            <p class="card-text"><strong>Current Status:</strong> 
                 <span class="badge bg-<?= statusColor($currentApplication['status']) ?> rounded-pill">
                    <?= ucfirst($currentApplication['status']) ?>
                 </span>
            </p>

            <hr>

            <form method="POST" action="">
                <input type="hidden" name="applicationId" value="<?= $applicationId ?>">
                <div class="mb-3">
                    <label for="statusSelect" class="form-label">Select New Status</label>
                    <select class="form-select" id="statusSelect" name="status" required>
                        <option value="">Choose...</option>
                        <option value="pending" <?= $currentApplication['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="viewed" <?= $currentApplication['status'] == 'viewed' ? 'selected' : '' ?>>Viewed</option>
                        <option value="interviewing" <?= $currentApplication['status'] == 'interviewing' ? 'selected' : '' ?>>Interviewing</option>
                        <option value="hired" <?= $currentApplication['status'] == 'hired' ? 'selected' : '' ?>>Hired</option>
                        <option value="rejected" <?= $currentApplication['status'] == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Update Status</button>
                <a href="applicants.php?job_id=<?= $currentApplication['jobId'] ?>" class="btn btn-secondary ms-2">Back to Applicants</a>
            </form>

        </div>
    <?php elseif (!$error): ?>
        <div class="alert alert-info" role="alert">
            Please select an application to change its status.
        </div>
    <?php endif; ?>

</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?> 