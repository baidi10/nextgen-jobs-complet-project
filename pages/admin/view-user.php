<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';

// Admin authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: /pages/public/login.php');
    exit;
}

// Get user ID from URL
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$userId) {
    header('Location: manage-users.php');
    exit;
}

$userObj = new User();
$user = $userObj->getUserById($userId);

if (!$user) {
    header('Location: manage-users.php');
    exit;
}

$pageTitle = "View User - JOBEST";
include __DIR__ . '/../../includes/header.php';
?>

<div class="dashboard-container">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 fw-bold">User Details</h1>
            <div class="d-flex gap-2">
                <a href="edit-user.php?id=<?= $userId ?>" class="btn btn-primary">
                    <i class="bi bi-pencil me-2"></i>Edit User
                </a>
                <a href="manage-users.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Users
                </a>
            </div>
        </div>

        <div class="row g-4">
            <!-- User Profile Card -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <img src="<?= getUserAvatar($userId) ?>" 
                             class="rounded-circle mb-3" width="120" height="120" alt="User avatar">
                        <h4 class="mb-1"><?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?></h4>
                        <span class="badge bg-<?= roleBadgeColor($user['userType']) ?> mb-3">
                            <?= ucfirst($user['userType']) ?>
                        </span>
                        
                        <div class="d-flex flex-column gap-2 text-start">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-envelope me-2"></i>
                                <span><?= htmlspecialchars($user['email']) ?></span>
                            </div>
                            <?php if (!empty($user['phoneNumber'])): ?>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-telephone me-2"></i>
                                <span><?= htmlspecialchars($user['phoneNumber']) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($user['location'])): ?>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-geo-alt me-2"></i>
                                <span><?= htmlspecialchars($user['location']) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-center gap-2">
                            <?php if (!empty($user['websiteUrl'])): ?>
                            <a href="<?= htmlspecialchars($user['websiteUrl']) ?>" class="btn btn-outline-primary btn-sm" target="_blank">
                                <i class="bi bi-globe"></i>
                            </a>
                            <?php endif; ?>
                            <?php if (!empty($user['linkedinUrl'])): ?>
                            <a href="<?= htmlspecialchars($user['linkedinUrl']) ?>" class="btn btn-outline-primary btn-sm" target="_blank">
                                <i class="bi bi-linkedin"></i>
                            </a>
                            <?php endif; ?>
                            <?php if (!empty($user['githubUrl'])): ?>
                            <a href="<?= htmlspecialchars($user['githubUrl']) ?>" class="btn btn-outline-dark btn-sm" target="_blank">
                                <i class="bi bi-github"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Details -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4">About</h5>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($user['bio'] ?? 'No bio provided')) ?></p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4">Account Information</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Account Created</label>
                                <p class="mb-0"><?= date('F j, Y', strtotime($user['createdAt'])) ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Last Login</label>
                                <p class="mb-0"><?= $user['lastLogin'] ? date('F j, Y H:i', strtotime($user['lastLogin'])) : 'Never' ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Email Verification</label>
                                <p class="mb-0">
                                    <?php if ($user['isEmailVerified']): ?>
                                        <span class="badge bg-success">Verified</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Not Verified</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Account Status</label>
                                <p class="mb-0">
                                    <span class="badge bg-success">Active</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?> 