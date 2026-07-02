<?php
// ajax/notifications.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../classes/User.php';

header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
    $perPage = 10;
    
    $user = new User();
    $notifications = $user->getNotifications($_SESSION['user_id'], $page, $perPage);

    // Mark as read
    if ($page === 1) {
        $user->markNotificationsRead($_SESSION['user_id']);
    }

    echo json_encode([
        'success' => true,
        'notifications' => $notifications
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load notifications'
    ]);
}