<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Company.php';

// Admin authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: /pages/public/login.php');
    exit;
}

// Check if user ID is provided
if (!isset($_POST['user_id']) || empty($_POST['user_id'])) {
    $_SESSION['error_message'] = "No user selected for deletion.";
    header('Location: manage-users.php');
    exit;
}

$userId = (int)$_POST['user_id'];

$transactionStarted = false;
try {
    $userObj = new User();
    $companyObj = new Company();
    
    // Check if user exists
    $user = $userObj->getUserById($userId);
    if (!$user) {
        throw new Exception("User not found.");
    }
    
    // Prevent self-deletion
    if ($userId === (int)$_SESSION['user_id']) {
        throw new Exception("You cannot delete your own account.");
    }

    // If user is employer, delete related job_monthly_stats rows before deleting
    if (($user['userType'] ?? $user['user_type'] ?? '') === 'employer') {
        $companyId = $companyObj->getCompanyIdByUser($userId);
        if ($companyId) {
            $conn = $userObj->getConnection();
            // Delete related job_monthly_stats rows
            $stmt = $conn->prepare('DELETE FROM job_monthly_stats WHERE companyId = ?');
            $stmt->execute([$companyId]);
        }
    }
    
    // Get database connection from User class
    $conn = $userObj->getConnection();
    
    $conn->beginTransaction();
    $transactionStarted = true;
    
    // Delete user's related data first
    $tables = [
        'applications' => 'userId',
        'savedjobs' => 'userId',
        'user_skills' => 'userId',
        'profile_views' => 'viewedUserId',
        'job_seekers' => 'userId',
        'job_views' => 'userId',
        'messages' => 'senderId',
        'notifications' => 'userId',
        'user_settings' => 'userId'
    ];
    
    foreach ($tables as $table => $column) {
        $stmt = $conn->prepare("DELETE FROM $table WHERE $column = ?");
        $stmt->execute([$userId]);
    }
    
    // Finally delete the user
    $stmt = $conn->prepare("DELETE FROM users WHERE userId = ?");
    $stmt->execute([$userId]);
    
    $conn->commit();
    
    $_SESSION['success_message'] = "User has been successfully deleted.";
    
} catch (Exception $e) {
    if (isset($conn) && $transactionStarted) {
        $conn->rollBack();
    }
    $_SESSION['error_message'] = "Error deleting user: " . $e->getMessage();
}

header('Location: manage-users.php');
exit; 