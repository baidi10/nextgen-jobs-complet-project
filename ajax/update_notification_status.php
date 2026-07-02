<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../classes/Database.php';

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated.']);
    exit;
}

$userId = $_SESSION['user_id'];
$db = Database::getInstance();
$pdo = $db->getConnection();

$action = $_POST['action'] ?? null;
$notificationId = $_POST['notificationId'] ?? null;

$response = ['success' => false, 'message' => 'Invalid action.'];

try {
    switch ($action) {
        case 'mark_read':
            if ($notificationId) {
                // Ensure the notification belongs to the user
                $stmtCheck = $pdo->prepare("SELECT userId FROM notifications WHERE notificationId = ?");
                $stmtCheck->execute([$notificationId]);
                $notif = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                if ($notif && $notif['userId'] == $userId) {
                    $stmtUpdate = $pdo->prepare("UPDATE notifications SET isRead = 1, readAt = NOW() WHERE notificationId = ?");
                    if ($stmtUpdate->execute([$notificationId])) {
                        $response = ['success' => true, 'message' => 'Notification marked as read.'];
                    } else {
                        $response = ['success' => false, 'message' => 'Failed to mark notification as read.'];
                    }
                } else {
                     $response = ['success' => false, 'message' => 'Notification not found or does not belong to user.'];
                }
            } else {
                $response = ['success' => false, 'message' => 'Missing notificationId.'];
            }
            break;

        case 'mark_all_read':
            $stmtUpdate = $pdo->prepare("UPDATE notifications SET isRead = 1, readAt = NOW() WHERE userId = ? AND isRead = 0");
             if ($stmtUpdate->execute([$userId])) {
                $response = ['success' => true, 'message' => 'All notifications marked as read.'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to mark all notifications as read.'];
            }
            break;
            
        case 'delete':
             if ($notificationId) {
                // Ensure the notification belongs to the user
                $stmtCheck = $pdo->prepare("SELECT userId FROM notifications WHERE notificationId = ?");
                $stmtCheck->execute([$notificationId]);
                $notif = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                if ($notif && $notif['userId'] == $userId) {
                    $stmtDelete = $pdo->prepare("DELETE FROM notifications WHERE notificationId = ?");
                    if ($stmtDelete->execute([$notificationId])) {
                         $response = ['success' => true, 'message' => 'Notification deleted.'];
                    } else {
                         $response = ['success' => false, 'message' => 'Failed to delete notification.'];
                    }
                } else {
                     $response = ['success' => false, 'message' => 'Notification not found or does not belong to user.'];
                }
            } else {
                $response = ['success' => false, 'message' => 'Missing notificationId for delete.'];
            }
            break;

        default:
            // Invalid action handled by initial response value
            break;
    }
} catch (Exception $e) {
    error_log("Error updating notification status: " . $e->getMessage());
    $response = ['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()];
}

echo json_encode($response); 