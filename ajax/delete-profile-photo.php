<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'jobSeeker') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$user = new User();
$userId = $_SESSION['user_id'];
$jobSeekerData = $user->getJobSeekerProfile($userId);

try {
    if (!empty($jobSeekerData['photo'])) {
        // Delete the physical file
        $photoPath = __DIR__ . '/../assets/uploads/profiles/' . $jobSeekerData['photo'];
        if (file_exists($photoPath)) {
            unlink($photoPath);
        }

        // Update the database to remove the photo reference
        $updateData = ['photo' => null];
        $user->updateJobSeekerProfile($userId, $updateData);

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No profile photo found']);
    }
} catch (Exception $e) {
    error_log('Error deleting profile photo: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error deleting profile photo']);
} 