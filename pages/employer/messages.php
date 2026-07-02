<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';

// Authentication check
if (!isLoggedIn() || !isEmployer()) {
    header('Location: /pages/public/login.php');
    exit;
}

// Get database connection
$db = Database::getInstance()->getConnection();

// Get user ID from session
$userId = $_SESSION['user_id'];

// Get company ID
$companyQuery = "SELECT companyId FROM companies WHERE userId = ?";
$companyStmt = $db->prepare($companyQuery);
$companyStmt->bindParam(1, $userId, PDO::PARAM_INT);
$companyStmt->execute();
$companyData = $companyStmt->fetch(PDO::FETCH_ASSOC);
$companyId = $companyData['companyId'] ?? 0;

// Get selected conversation if any
$activeConversation = isset($_GET['conversation']) ? (int)$_GET['conversation'] : 0;

// Initialize variables
$error = '';
$success = '';
$conversations = [];
$messages = [];
$activeApplicant = null;

// Process message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $messageContent = trim($_POST['message_content']);
    $applicantId = (int)$_POST['applicant_id'];
    
    if (!empty($messageContent) && $applicantId > 0) {
        // Insert message
        $insertQuery = "INSERT INTO messages (senderId, recipientId, content, isRead, sentAt) 
                       VALUES (?, ?, ?, 0, NOW())";
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->bindParam(1, $userId, PDO::PARAM_INT);
        $insertStmt->bindParam(2, $applicantId, PDO::PARAM_INT);
        $insertStmt->bindParam(3, $messageContent, PDO::PARAM_STR);
        
        if ($insertStmt->execute()) {
            $success = "Message sent successfully!";
            
            // Check if a conversation already exists
            $conversationCheckQuery = "SELECT conversationId FROM conversations 
                                     WHERE (userId1 = ? AND userId2 = ?) OR (userId1 = ? AND userId2 = ?)";
            $conversationCheckStmt = $db->prepare($conversationCheckQuery);
            $conversationCheckStmt->bindParam(1, $userId, PDO::PARAM_INT);
            $conversationCheckStmt->bindParam(2, $applicantId, PDO::PARAM_INT);
            $conversationCheckStmt->bindParam(3, $applicantId, PDO::PARAM_INT);
            $conversationCheckStmt->bindParam(4, $userId, PDO::PARAM_INT);
            $conversationCheckStmt->execute();
            $existingConversation = $conversationCheckStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingConversation) {
                // Update existing conversation
            $updateQuery = "UPDATE conversations 
                          SET lastMessageAt = NOW(), recipientUnread = recipientUnread + 1 
                                WHERE conversationId = ?";
            $updateStmt = $db->prepare($updateQuery);
                $updateStmt->bindParam(1, $existingConversation['conversationId'], PDO::PARAM_INT);
            $updateStmt->execute();
            } else {
                // Create new conversation
                // Need a way to associate this with a job application - this schema is tricky.
                // For now, just create the conversation without direct application link
                // A better approach would be to create conversation upon application or first message
                // and include jobId in the conversations table.
                $insertConversationQuery = "INSERT INTO conversations (userId1, userId2, employerId, lastMessageAt) 
                                          VALUES (?, ?, ?, NOW())";
                $insertConversationStmt = $db->prepare($insertConversationQuery);
                $insertConversationStmt->bindParam(1, $userId, PDO::PARAM_INT);
                $insertConversationStmt->bindParam(2, $applicantId, PDO::PARAM_INT);
                $insertConversationStmt->bindParam(3, $userId, PDO::PARAM_INT);
                $insertConversationStmt->execute();
            }
            
            // Redirect to the conversation after sending
            header('Location: messages.php?conversation=' . $applicantId);
            exit;
            
        } else {
            $error = "Error sending message: " . $db->errorInfo()[2];
        }
    } else {
        $error = "Message cannot be empty!";
    }
}

// Mark conversation as read (if active conversation is set)
if ($activeConversation > 0) {
    $markReadQuery = "UPDATE conversations SET employerUnread = 0 
                    WHERE ((userId1 = ? AND userId2 = ?) OR (userId1 = ? AND userId2 = ?)) 
                    AND employerId = ?"; // Ensure employer is the current user
    $markReadStmt = $db->prepare($markReadQuery);
    $markReadStmt->bindParam(1, $userId, PDO::PARAM_INT);
    $markReadStmt->bindParam(2, $activeConversation, PDO::PARAM_INT);
    $markReadStmt->bindParam(3, $activeConversation, PDO::PARAM_INT);
    $markReadStmt->bindParam(4, $userId, PDO::PARAM_INT);
    $markReadStmt->bindParam(5, $userId, PDO::PARAM_INT);
    $markReadStmt->execute();
    
    // Mark messages from this user as read
    $markMessagesQuery = "UPDATE messages SET isRead = 1 
                        WHERE senderId = ? AND recipientId = ?";
    $markMessagesStmt = $db->prepare($markMessagesQuery);
    $markMessagesStmt->bindParam(1, $activeConversation, PDO::PARAM_INT);
    $markMessagesStmt->bindParam(2, $userId, PDO::PARAM_INT);
    $markMessagesStmt->execute();
}

// Get conversations with job applicants
// Modified query to prevent duplicates and get a relevant job title
$conversationsQuery = "
    SELECT 
        u.userId as applicantId,
        u.firstName,
        u.lastName,
        u.profilePhoto,
        -- Get the latest message and its timestamp for this user's conversations with the employer
        (SELECT m.content FROM messages m
         JOIN conversations mc ON (mc.userId1 = m.senderId AND mc.userId2 = m.recipientId) OR (mc.userId2 = m.senderId AND mc.userId1 = m.recipientId)
         WHERE ((mc.userId1 = u.userId AND mc.userId2 = ?) OR (mc.userId2 = u.userId AND mc.userId1 = ?)) -- where conversation is between this applicant and current employer
         ORDER BY m.sentAt DESC LIMIT 1) as lastMessage,
        (SELECT m.sentAt FROM messages m
         JOIN conversations mc ON (mc.userId1 = m.senderId AND mc.userId2 = m.recipientId) OR (mc.userId2 = m.senderId AND mc.userId1 = m.recipientId)
         WHERE ((mc.userId1 = u.userId AND mc.userId2 = ?) OR (mc.userId2 = u.userId AND mc.userId1 = ?)) -- where conversation is between this applicant and current employer
         ORDER BY m.sentAt DESC LIMIT 1) as lastMessageAt,
        -- Get the count of unread messages from this applicant to the current employer
        (SELECT COUNT(*)
         FROM messages m
         WHERE m.senderId = u.userId AND m.recipientId = ? AND m.isRead = 0) as employerUnread,
        -- Subquery to get the job title from their latest application at this company (for context)
        (SELECT j.jobTitle
         FROM applications a
    JOIN jobs j ON a.jobId = j.jobId
         WHERE a.userId = u.userId AND j.companyId = ?
         ORDER BY a.createdAt DESC LIMIT 1) as jobTitle
    FROM users u
    JOIN conversations c ON (c.userId1 = u.userId AND c.userId2 = ?) OR (c.userId2 = u.userId AND c.userId1 = ?)
    WHERE u.userType = 'jobSeeker' -- Ensure we only list job seekers
    -- Group by applicant to get one row per applicant
    GROUP BY u.userId, u.firstName, u.lastName, u.profilePhoto
    -- Order by the timestamp of the latest message
    ORDER BY lastMessageAt DESC
";

$conversationsStmt = $db->prepare($conversationsQuery);
// Bind parameters for the subqueries and main query
$conversationsStmt->bindParam(1, $userId, PDO::PARAM_INT);
$conversationsStmt->bindParam(2, $userId, PDO::PARAM_INT);
$conversationsStmt->bindParam(3, $userId, PDO::PARAM_INT);
$conversationsStmt->bindParam(4, $userId, PDO::PARAM_INT);
$conversationsStmt->bindParam(5, $userId, PDO::PARAM_INT);
$conversationsStmt->bindParam(6, $companyId, PDO::PARAM_INT); // Parameter for jobTitle subquery
$conversationsStmt->bindParam(7, $userId, PDO::PARAM_INT);
$conversationsStmt->bindParam(8, $userId, PDO::PARAM_INT);
$conversationsStmt->execute();

$conversations = [];
// No need for $applicantIdsInList array anymore due to GROUP BY
while ($conversation = $conversationsStmt->fetch(PDO::FETCH_ASSOC)) {
    // The applicantId is directly selected in the query
    // Ensure lastMessageAt is treated as a timestamp for sorting later if needed (though ORDER BY handles it)
    
    // Add to conversations array
    $conversations[] = $conversation;
    
    // If this is the active conversation, set active applicant info
    if ($activeConversation > 0 && $conversation['applicantId'] == $activeConversation) {
        $activeApplicant = [
            'userId' => $conversation['applicantId'],
            'firstName' => $conversation['firstName'],
            'lastName' => $conversation['lastName'],
            'profilePhoto' => $conversation['profilePhoto'],
            'jobTitle' => $conversation['jobTitle']
        ];
    }
}

// If active conversation is set and applicant info is not yet loaded (e.g., direct link or message sent to new user)
// This block is largely redundant with the new query structure, but kept for safety/direct access cases.
if ($activeConversation > 0 && !$activeApplicant) {
    // Fetch active applicant details separately
    // Use the same logic as the main conversation query for consistency
    $applicantQuery = "
        SELECT 
            u.userId,
            u.firstName,
            u.lastName,
            u.profilePhoto,
            (SELECT j.jobTitle
             FROM applications a
             JOIN jobs j ON a.jobId = j.jobId
             WHERE a.userId = u.userId AND j.companyId = ?
             ORDER BY a.createdAt DESC LIMIT 1) as jobTitle
        FROM users u
        WHERE u.userId = ? AND u.userType = 'jobSeeker'
    ";
    $applicantStmt = $db->prepare($applicantQuery);
    $applicantStmt->bindParam(1, $companyId, PDO::PARAM_INT);
    $applicantStmt->bindParam(2, $activeConversation, PDO::PARAM_INT);
    $applicantStmt->execute();
    $activeApplicant = $applicantStmt->fetch(PDO::FETCH_ASSOC);
}

// If active conversation is set, get messages
if ($activeConversation > 0 && $activeApplicant) {
    // Mark messages as read first
    $markReadQuery = "UPDATE messages SET isRead = 1 
                     WHERE senderId = ? AND recipientId = ? AND isRead = 0";
    $markReadStmt = $db->prepare($markReadQuery);
    $markReadStmt->bindParam(1, $activeConversation, PDO::PARAM_INT);
    $markReadStmt->bindParam(2, $userId, PDO::PARAM_INT);
    $markReadStmt->execute();

    $messagesQuery = "
        SELECT 
            m.messageId,
            m.senderId,
            m.recipientId,
            m.content,
            m.isRead,
            m.sentAt,
            u.firstName,
            u.lastName,
            u.profilePhoto
        FROM messages m
        JOIN users u ON m.senderId = u.userId
        WHERE (m.senderId = ? AND m.recipientId = ?) OR (m.senderId = ? AND m.recipientId = ?)
        ORDER BY m.sentAt ASC
    ";
    
    $messagesStmt = $db->prepare($messagesQuery);
    $messagesStmt->bindParam(1, $userId, PDO::PARAM_INT);
    $messagesStmt->bindParam(2, $activeConversation, PDO::PARAM_INT);
    $messagesStmt->bindParam(3, $activeConversation, PDO::PARAM_INT);
    $messagesStmt->bindParam(4, $userId, PDO::PARAM_INT);
    $messagesStmt->execute();
    
    $messages = [];
    while ($message = $messagesStmt->fetch(PDO::FETCH_ASSOC)) {
        $messages[] = $message;
    }
}

// Get applicant name for new conversation if query parameter exists (?new=applicantId)
$newConversationApplicant = null;
if (isset($_GET['new']) && is_numeric($_GET['new'])) {
    $newApplicantId = (int)$_GET['new'];
    
    // Check if a conversation already exists with this applicant and the current employer
     $conversationCheckQuery = "SELECT conversationId FROM conversations 
                                WHERE (userId1 = ? AND userId2 = ?) OR (userId1 = ? AND userId2 = ?)";
    $conversationCheckStmt = $db->prepare($conversationCheckQuery);
    $conversationCheckStmt->bindParam(1, $userId, PDO::PARAM_INT);
    $conversationCheckStmt->bindParam(2, $newApplicantId, PDO::PARAM_INT);
    $conversationCheckStmt->bindParam(3, $newApplicantId, PDO::PARAM_INT);
    $conversationCheckStmt->bindParam(4, $userId, PDO::PARAM_INT);
    $conversationCheckStmt->execute();
    
    if ($conversationCheckStmt->fetch(PDO::FETCH_ASSOC)) {
        // Conversation already exists, redirect to it
        header('Location: messages.php?conversation=' . $newApplicantId);
        exit;
    }

    // If no conversation exists, fetch applicant details to start a new one
    // Use the same logic as fetching active applicant to get jobTitle context
    $applicantQuery = "
        SELECT 
            u.userId,
            u.firstName,
            u.lastName,
            u.profilePhoto,
            (SELECT j.jobTitle
             FROM applications a
             JOIN jobs j ON a.jobId = j.jobId
             WHERE a.userId = u.userId AND j.companyId = ?
             ORDER BY a.createdAt DESC LIMIT 1) as jobTitle
        FROM users u
        WHERE u.userId = ? AND u.userType = 'jobSeeker'
    ";
    
    $applicantStmt = $db->prepare($applicantQuery);
    $applicantStmt->bindParam(1, $companyId, PDO::PARAM_INT);
    $applicantStmt->bindParam(2, $newApplicantId, PDO::PARAM_INT);
    $applicantStmt->execute();
    
    if ($applicantData = $applicantStmt->fetch(PDO::FETCH_ASSOC)) {
        $newConversationApplicant = $applicantData;
        // Set activeConversation to the new applicant's ID to display the chat pane
        $activeConversation = $newApplicantId;
    }
}

$pageTitle = "Messages - JOBEST";

include __DIR__ . '/../../includes/header.php';
?>

<style>
/* Messenger-like Styles */
.messenger-container {
    height: calc(100vh - 180px);
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

/* Conversations List */
.conversations-list {
    height: 100%;
    overflow-y: auto;
    background: #fff;
    border-right: 1px solid #e4e6eb;
}

.conversation-item {
    padding: 12px 16px;
    border: none;
    border-radius: 0;
    transition: background-color 0.2s;
    cursor: pointer;
    position: relative;
}

.conversation-item:hover {
    background-color: #f2f2f2;
}

.conversation-item.active {
    background-color: #e7f3ff;
}

.conversation-item.active .conversation-name {
    color: #1877f2;
}

.user-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e4e6eb;
    background-color: #f0f2f5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: #65676b;
    position: relative;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}

.user-avatar.default {
    background-color: #e4e6eb;
}

.user-avatar.default::before {
    content: '\F4E7';  /* Bootstrap Icons person icon */
    font-family: 'bootstrap-icons';
    font-size: 28px;
    color: #65676b;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.conversation-name {
    font-weight: 600;
    color: #1c1e21;
    margin-bottom: 2px;
}

.conversation-preview {
    color: #65676b;
    font-size: 0.9rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.job-title {
    font-size: 0.8rem;
    color: #65676b;
}

.unread-badge {
    background-color: #1877f2;
    color: white;
    font-size: 0.75rem;
    padding: 2px 6px;
    border-radius: 10px;
    min-width: 20px;
    text-align: center;
}

/* Messages Area */
.messages-area {
    height: 100%;
    display: flex;
    flex-direction: column;
    background: #ffffff;
}

.chat-header {
    background: #fff;
    padding: 12px 16px;
    border-bottom: 1px solid #e4e6eb;
    display: flex;
    align-items: center;
    gap: 12px;
}

.chat-header .user-avatar {
    width: 40px;
    height: 40px;
    font-size: 18px;
}

.chat-header .user-avatar.default::before {
    font-size: 20px;
}

.chat-header-info h5 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: #1c1e21;
}

.chat-header-info p {
    margin: 0;
    font-size: 0.85rem;
    color: #65676b;
}

.messages-list {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    background: #ffffff;
}

.message-date-divider {
    text-align: center;
    margin: 16px 0;
    position: relative;
}

.message-date-divider::before {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    top: 50%;
    height: 1px;
    background: #e4e6eb;
    z-index: 1;
}

.message-date-badge {
    background: #e4e6eb;
    color: #65676b;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.8rem;
    position: relative;
    z-index: 2;
    display: inline-block;
}

.message-bubble {
    max-width: 65%;
    padding: 8px 12px;
    border-radius: 18px;
    position: relative;
    margin-bottom: 4px;
}

.message-bubble.sent {
    background: #0084ff;
    color: white;
    margin-left: auto;
    border-bottom-right-radius: 4px;
}

.message-bubble.received {
    background: #f0f2f5;
    color: #1c1e21;
    margin-right: auto;
    border-bottom-left-radius: 4px;
}

.message-time {
    font-size: 0.75rem;
    color: #65676b;
    margin-top: 2px;
    padding: 0 4px;
}

.message-input-area {
    background: #fff;
    padding: 12px 16px;
    border-top: 1px solid #e4e6eb;
}

.message-input-wrapper {
    display: flex;
    gap: 8px;
    align-items: flex-end;
}

.message-input {
    flex: 1;
    border: 1px solid #e4e6eb;
    border-radius: 20px;
    padding: 8px 16px;
    resize: none;
    max-height: 120px;
    min-height: 40px;
    font-size: 0.95rem;
}

.message-input:focus {
    outline: none;
    border-color: #1877f2;
}

.send-button {
    background: #1877f2;
    color: white;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.2s;
}

.send-button:hover {
    background: #166fe5;
}

/* Empty States */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #65676b;
    text-align: center;
    padding: 24px;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    color: #1877f2;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .messenger-container {
        height: calc(100vh - 120px);
    }
    
    .conversations-list {
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 100%;
        z-index: 1000;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    .conversations-list.show {
        transform: translateX(0);
    }
    
    .messages-area {
        width: 100%;
    }
}
</style>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 fw-bold mb-0">Messages</h1>
    </div>
    
    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($success); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
            <div class="messenger-container">
                <div class="row g-0 h-100">
                <!-- Conversations List -->
                    <div class="col-md-4 col-lg-3 conversations-list">
                    <?php if (empty($conversations) && !$newConversationApplicant): ?>
                            <div class="empty-state">
                                <i class="bi bi-chat-dots"></i>
                                <p>No conversations yet.</p>
                        </div>
                    <?php else: ?>
                            <?php foreach ($conversations as $conversation): ?>
                                <a href="?conversation=<?php echo $conversation['applicantId']; ?>" 
                                   class="conversation-item d-flex align-items-center <?php echo ($activeConversation == $conversation['applicantId']) ? 'active' : ''; ?>">
                                    <?php 
                                    $avatarUrl = getUserAvatar($conversation['applicantId']);
                                    $isDefaultAvatar = strpos($avatarUrl, 'default-avatar.png') !== false;
                                    ?>
                                    <div class="user-avatar me-3 <?php echo $isDefaultAvatar ? 'default' : ''; ?>">
                                        <?php if (!$isDefaultAvatar): ?>
                                            <img src="<?php echo $avatarUrl; ?>" 
                                                 alt="<?php echo htmlspecialchars($conversation['firstName']); ?>">
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1 min-width-0">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h6 class="conversation-name mb-0">
                                                <?php echo htmlspecialchars($conversation['firstName'] . ' ' . $conversation['lastName']); ?>
                                            </h6>
                                            <small class="text-muted">
                                                <?php echo formatMessageTime($conversation['lastMessageAt']); ?>
                                            </small>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <p class="conversation-preview mb-0">
                                                <?php echo htmlspecialchars($conversation['lastMessage']); ?>
                                            </p>
                                            <?php if ($conversation['employerUnread'] > 0): ?>
                                                <span class="unread-badge ms-2"><?php echo $conversation['employerUnread']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="job-title mb-0">
                                            <i class="bi bi-briefcase me-1"></i>
                                            <?php echo htmlspecialchars($conversation['jobTitle']); ?>
                                        </p>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                            
                            <?php if ($newConversationApplicant): ?>
                                <a href="?new=<?php echo $newConversationApplicant['userId']; ?>" 
                                   class="conversation-item d-flex align-items-center active">
                                    <?php 
                                    $avatarUrl = getUserAvatar($newConversationApplicant['userId']);
                                    $isDefaultAvatar = strpos($avatarUrl, 'default-avatar.png') !== false;
                                    ?>
                                    <div class="user-avatar me-3 <?php echo $isDefaultAvatar ? 'default' : ''; ?>">
                                        <?php if (!$isDefaultAvatar): ?>
                                            <img src="<?php echo $avatarUrl; ?>" 
                                                 alt="<?php echo htmlspecialchars($newConversationApplicant['firstName']); ?>">
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1 min-width-0">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h6 class="conversation-name mb-0">
                                                <?php echo htmlspecialchars($newConversationApplicant['firstName'] . ' ' . $newConversationApplicant['lastName']); ?>
                                            </h6>
                                            <span class="badge bg-success">New</span>
                                        </div>
                                        <p class="conversation-preview mb-0">Start a new conversation</p>
                                        <p class="job-title mb-0">
                                            <i class="bi bi-briefcase me-1"></i>
                                            <?php echo htmlspecialchars($newConversationApplicant['jobTitle']); ?>
                                        </p>
                                    </div>
                                </a>
                            <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                    <!-- Messages Area -->
                    <div class="col-md-8 col-lg-9 messages-area">
                    <?php if ($activeConversation > 0 && ($activeApplicant || $newConversationApplicant)): ?>
                        <?php 
                        $applicant = $activeApplicant ?? $newConversationApplicant;
                        $avatarUrl = getUserAvatar($applicant['userId']);
                        $isDefaultAvatar = strpos($avatarUrl, 'default-avatar.png') !== false;
                        ?>
                        
                        <!-- Chat Header -->
                            <div class="chat-header">
                                <div class="user-avatar <?php echo $isDefaultAvatar ? 'default' : ''; ?>">
                                    <?php if (!$isDefaultAvatar): ?>
                                        <img src="<?php echo $avatarUrl; ?>" 
                                             alt="<?php echo htmlspecialchars($applicant['firstName']); ?>">
                                    <?php endif; ?>
                                </div>
                                <div class="chat-header-info">
                                    <h5><?php echo htmlspecialchars($applicant['firstName'] . ' ' . $applicant['lastName']); ?></h5>
                                    <p>
                                        <i class="bi bi-briefcase me-1"></i>
                                        Applicant for <?php echo htmlspecialchars($applicant['jobTitle']); ?>
                                    </p>
                            </div>
                        </div>
                        
                        <!-- Messages List -->
                            <div class="messages-list" id="messageList">
                            <?php if (empty($messages)): ?>
                                    <div class="empty-state">
                                        <i class="bi bi-chat-text"></i>
                                    <p>No messages yet.<br>Send a message to start the conversation.</p>
                                </div>
                            <?php else: ?>
                                <?php 
                                $lastDate = null;
                                foreach ($messages as $message): 
                                    $messageDate = date('Y-m-d', strtotime($message['sentAt']));
                                    if ($lastDate !== $messageDate):
                                ?>
                                        <div class="message-date-divider">
                                            <span class="message-date-badge">
                                            <?php echo formatDateHeader($message['sentAt']); ?>
                                        </span>
                                    </div>
                                <?php 
                                    endif;
                                    $lastDate = $messageDate;
                                    $isSender = $message['senderId'] == $userId;
                                    ?>
                                        <div class="message-bubble <?php echo $isSender ? 'sent' : 'received'; ?>">
                                                    <?php echo nl2br(htmlspecialchars($message['content'])); ?>
                                            <div class="message-time <?php echo $isSender ? 'text-end' : 'text-start'; ?>">
                                                <?php echo date('h:i A', strtotime($message['sentAt'])); ?>
                                            </div>
                                        </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Message Input -->
                            <div class="message-input-area">
                            <form method="POST">
                                <input type="hidden" name="applicant_id" value="<?php echo $applicant['userId']; ?>">
                                    <div class="message-input-wrapper">
                                        <textarea class="message-input" name="message_content" id="messageInput" 
                                                  placeholder="Type your message..." required></textarea>
                                        <button type="submit" name="send_message" class="send-button">
                                        <i class="bi bi-send"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                            <div class="empty-state">
                                <i class="bi bi-chat-text"></i>
                            <h4 class="h5 fw-bold mb-2">Your Messages</h4>
                            <p class="text-center text-muted">
                                Select a conversation from the list or<br>view applicants to start a new conversation.
                            </p>
                            <a href="applicants.php" class="btn btn-primary mt-2">
                                <i class="bi bi-person-badge me-2"></i>View Applicants
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Scroll to bottom of message list
    const messageList = document.getElementById('messageList');
    if (messageList) {
        messageList.scrollTop = messageList.scrollHeight;
    }
    
    // Auto-resize textarea
    const messageInput = document.getElementById('messageInput');
    if (messageInput) {
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });
        
        // Trigger focus event to resize on page load
        const event = new Event('input');
        messageInput.dispatchEvent(event);
        
        // Focus on textarea
        messageInput.focus();
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<?php
// Helper functions
function formatMessageTime($datetime) {
    $timestamp = strtotime($datetime);
    $now = time();
    $diff = $now - $timestamp;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . 'm ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . 'h ago';
    } elseif (date('Y-m-d', $timestamp) === date('Y-m-d', strtotime('yesterday'))) {
        return 'Yesterday';
    } else {
        return date('M j', $timestamp);
    }
}

function formatDateHeader($datetime) {
    $timestamp = strtotime($datetime);
    $now = time();
    $diff = $now - $timestamp;
    
    if (date('Y-m-d', $timestamp) === date('Y-m-d', $now)) {
        return 'Today';
    } elseif (date('Y-m-d', $timestamp) === date('Y-m-d', strtotime('yesterday'))) {
        return 'Yesterday';
    } else {
        return date('F j, Y', $timestamp);
    }
}
?>