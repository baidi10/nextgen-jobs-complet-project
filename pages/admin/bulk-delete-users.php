<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';

// Admin authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if request is POST and has valid JSON content
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON data from request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['user_ids']) || !is_array($data['user_ids']) || empty($data['user_ids'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

// Get user IDs to delete
$userIds = array_map('intval', $data['user_ids']);

// Prevent self-deletion
if (in_array((int)$_SESSION['user_id'], $userIds)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'You cannot delete your own account']);
    exit;
}

try {
    $userObj = new User();
    $conn = $userObj->getConnection();
    $conn->beginTransaction();
    
    // Tables to clean up user data from
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
    
    // Delete related data for each user
    foreach ($userIds as $userId) {
        foreach ($tables as $table => $column) {
            $stmt = $conn->prepare("DELETE FROM $table WHERE $column = ?");
            $stmt->execute([$userId]);
        }
    }
    
    // Create placeholders for SQL IN clause
    $placeholders = implode(',', array_fill(0, count($userIds), '?'));
    
    // Delete the users
    $stmt = $conn->prepare("DELETE FROM users WHERE userId IN ($placeholders)");
    $stmt->execute($userIds);
    
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => count($userIds) . ' users have been successfully deleted'
    ]);
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error deleting users: ' . $e->getMessage()
    ]);
} 