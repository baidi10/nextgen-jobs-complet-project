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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'userId' => $userId,
        'email' => $_POST['email'],
        'firstName' => $_POST['firstName'],
        'lastName' => $_POST['lastName'],
        'userType' => $_POST['userType'],
        'phoneNumber' => $_POST['phoneNumber'],
        'location' => $_POST['location'],
        'bio' => $_POST['bio'],
        'websiteUrl' => $_POST['websiteUrl'],
        'linkedinUrl' => $_POST['linkedinUrl'],
        'githubUrl' => $_POST['githubUrl']
    ];
    
    // Only update password if provided
    if (!empty($_POST['password'])) {
        $data['password'] = $_POST['password'];
    }
    
    try {
        $result = $userObj->updateUser($data);
        if ($result) {
            $_SESSION['success_message'] = "User updated successfully!";
            header('Location: manage-users.php');
            exit;
        }
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}

$pageTitle = "Edit User - JOBEST";
include __DIR__ . '/../../includes/header.php';
?>

<div class="dashboard-container">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 fw-bold">Edit User</h1>
            <a href="manage-users.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Users
            </a>
        </div>

        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="" class="needs-validation" novalidate>
                    <div class="row g-4">
                        <!-- Basic Information -->
                        <div class="col-12">
                            <h5 class="mb-3">Basic Information</h5>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="firstName" 
                                   value="<?= htmlspecialchars($user['firstName']) ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="lastName" 
                                   value="<?= htmlspecialchars($user['lastName']) ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Leave blank to keep current password">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="userType" class="form-label">User Type</label>
                            <select class="form-select" id="userType" name="userType" required>
                                <option value="jobSeeker" <?= $user['userType'] === 'jobSeeker' ? 'selected' : '' ?>>Job Seeker</option>
                                <option value="employer" <?= $user['userType'] === 'employer' ? 'selected' : '' ?>>Employer</option>
                                <option value="admin" <?= $user['userType'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="phoneNumber" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" 
                                   value="<?= htmlspecialchars($user['phoneNumber'] ?? '') ?>">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" 
                                   value="<?= htmlspecialchars($user['location'] ?? '') ?>">
                        </div>
                        
                        <div class="col-12">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea class="form-control" id="bio" name="bio" rows="3"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                        </div>
                        
                        <!-- Social Links -->
                        <div class="col-12">
                            <h5 class="mb-3 mt-4">Social Links</h5>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="websiteUrl" class="form-label">Website</label>
                            <input type="url" class="form-control" id="websiteUrl" name="websiteUrl" 
                                   value="<?= htmlspecialchars($user['websiteUrl'] ?? '') ?>">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="linkedinUrl" class="form-label">LinkedIn</label>
                            <input type="url" class="form-control" id="linkedinUrl" name="linkedinUrl" 
                                   value="<?= htmlspecialchars($user['linkedinUrl'] ?? '') ?>">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="githubUrl" class="form-label">GitHub</label>
                            <input type="url" class="form-control" id="githubUrl" name="githubUrl" 
                                   value="<?= htmlspecialchars($user['githubUrl'] ?? '') ?>">
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?> 