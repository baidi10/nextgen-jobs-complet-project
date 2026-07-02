<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';

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

$userId = $_SESSION['user_id'];
$db = Database::getInstance();

// Fetch notifications for the logged-in employer
$notifications = [];
try {
    $query = "SELECT n.*, 
                     CASE 
                         WHEN n.notificationType = 'application' THEN 'bi-person-check'
                         WHEN n.notificationType = 'message' THEN 'bi-chat'
                         WHEN n.notificationType = 'system' THEN 'bi-gear'
                         WHEN n.notificationType = 'approval' THEN 'bi-check-circle'
                         WHEN n.notificationType = 'reminder' THEN 'bi-clock'
                         WHEN n.notificationType = 'connection' THEN 'bi-people'
                         WHEN n.notificationType = 'job_status' THEN 'bi-briefcase'
                         WHEN n.notificationType = 'payment' THEN 'bi-credit-card'
                         ELSE 'bi-bell'
                     END as icon
              FROM notifications n 
              WHERE n.userId = ? 
              ORDER BY n.createdAt DESC 
              LIMIT 50";
    
    $stmt = $db->query($query, [$userId]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error fetching notifications: " . $e->getMessage());
}

$pageTitle = 'Notifications | JOBEST';
$customCSS = '<style>
.notifications-container { max-width: 800px; margin: 0 auto; }
.notifications-list { background: #fff; border-radius: 14px; box-shadow: 0 2px 12px rgba(60,72,88,0.07); }
.notification-item { 
    display: flex; 
    align-items: flex-start; 
    gap: 1rem; 
    padding: 1.2rem; 
    border-bottom: 1px solid #f1f1f1;
    transition: all 0.2s ease;
}
.notification-item:hover { background-color: #f8f9fa; }
.notification-item:last-child { border-bottom: none; }
.notification-icon { 
    font-size: 1.5rem; 
    color: #2563eb; 
    background: #f1f5fb; 
    border-radius: 50%; 
    width: 48px; 
    height: 48px; 
    display: flex; 
    align-items: center; 
    justify-content: center;
    flex-shrink: 0;
}
.notification-content { flex-grow: 1; }
.notification-title { 
    font-weight: 600; 
    color: #1e293b; 
    margin-bottom: 0.25rem;
}
.notification-message { 
    color: #64748b; 
    margin-bottom: 0.5rem;
}
.notification-time { 
    font-size: 0.875rem; 
    color: #94a3b8;
}
.notification-type { 
    font-size: 0.75rem; 
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.notification-important { border-left: 4px solid #f59e42; }
.notification-urgent { border-left: 4px solid #ef4444; }
.notification-actions {
    display: flex;
    gap: 1rem;
    margin-top: 0.5rem;
}
.notification-actions a {
    font-size: 0.875rem;
    color: #64748b;
    text-decoration: none;
    transition: color 0.2s ease;
}
.notification-actions a:hover {
    color: #2563eb;
}
.unread { background-color: #f8fafc; }
</style>';
$pageStyles = [$customCSS];

include __DIR__ . '/../../includes/header.php';
?>

<div class="container py-5 notifications-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-bell-fill fs-2 me-2 text-primary"></i>
            <h1 class="h4 fw-bold mb-0">Notifications</h1>
        </div>
        <div>
            <button class="btn btn-outline-primary btn-sm" onclick="markAllAsRead()">
                <i class="bi bi-check2-all me-1"></i>Mark All as Read
            </button>
        </div>
    </div>

    <div class="notifications-list">
        <?php if (empty($notifications)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-bell-slash fs-1 mb-3"></i>
                <p class="mb-0">No notifications yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notif): ?>
                <?php
                    $importanceClass = '';
                    if ($notif['importance'] == 2) $importanceClass = ' notification-important';
                    if ($notif['importance'] == 3) $importanceClass = ' notification-urgent';
                    $unreadClass = !$notif['isRead'] ? ' unread' : '';
                ?>
                <div class="notification-item<?= $importanceClass ?><?= $unreadClass ?>" data-notification-id="<?= $notif['notificationId'] ?>">
                    <div class="notification-icon">
                        <i class="bi <?= $notif['icon'] ?>"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-type">
                            <span class="badge bg-secondary"><?= htmlspecialchars($notif['notificationType']) ?></span>
                        </div>
                        <div class="notification-title"><?= htmlspecialchars($notif['title']) ?></div>
                        <div class="notification-message"><?= htmlspecialchars($notif['message']) ?></div>
                        <div class="notification-time">
                            <i class="bi bi-clock me-1"></i><?= timeElapsed($notif['createdAt']) ?>
                        </div>
                        <div class="notification-actions">
                            <?php if (!$notif['isRead']): ?>
                                <a href="#" onclick="markAsRead(<?= $notif['notificationId'] ?>)">Mark as Read</a>
                            <?php endif; ?>
                            <a href="#" onclick="deleteNotification(<?= $notif['notificationId'] ?>)">Delete</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function markAsRead(notificationId) {
    // TODO: Implement AJAX call to mark notification as read
    fetch('../../ajax/update_notification_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=mark_read&notificationId=' + notificationId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (notificationElement) {
                notificationElement.classList.remove('unread');
                // Optionally remove the 'Mark as Read' button
                const markAsReadButton = notificationElement.querySelector('a[onclick^="markAsRead"]');
                if(markAsReadButton) markAsReadButton.remove();
            }
            console.log(data.message); // Log success message
        } else {
            console.error('Error marking notification as read:', data.message); // Log error message
            alert('Error: ' + data.message); // Show error to user
        }
    })
    .catch((error) => {
        console.error('AJAX Error:', error); // Log AJAX error
        alert('An unexpected error occurred.'); // Show generic error to user
    });
}

function markAllAsRead() {
    // TODO: Implement AJAX call to mark all notifications as read
     fetch('../../ajax/update_notification_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=mark_all_read'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
                 // Optionally remove all 'Mark as Read' buttons
                const markAsReadButton = item.querySelector('a[onclick^="markAsRead"]');
                if(markAsReadButton) markAsReadButton.remove();
            });
            console.log(data.message); // Log success message
        } else {
            console.error('Error marking all notifications as read:', data.message); // Log error message
             alert('Error: ' + data.message); // Show error to user
        }
    })
    .catch((error) => {
        console.error('AJAX Error:', error); // Log AJAX error
         alert('An unexpected error occurred.'); // Show generic error to user
    });
}

function deleteNotification(notificationId) {
    if (confirm('Are you sure you want to delete this notification?')) {
        // TODO: Implement AJAX call to delete notification
         fetch('../../ajax/update_notification_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=delete&notificationId=' + notificationId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (notificationElement) {
                    notificationElement.remove();
                }
                console.log(data.message); // Log success message
            } else {
                console.error('Error deleting notification:', data.message); // Log error message
                 alert('Error: ' + data.message); // Show error to user
            }
        })
        .catch((error) => {
            console.error('AJAX Error:', error); // Log AJAX error
             alert('An unexpected error occurred.'); // Show generic error to user
        });
    }
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?> 