<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';

// Get user ID from URL
$userId = isset($_GET['userId']) ? (int)$_GET['userId'] : 0;

// Initialize variables
$user = null;
$jobSeeker = null;

if ($userId) {
    try {
        $pdo = Database::getInstance()->getConnection();
        
        // Get user and job seeker information
        $stmt = $pdo->prepare("
            SELECT 
                u.*,
                js.headline,
                js.currentPosition,
                js.currentCompany,
                js.educationLevel,
                js.yearsOfExperience,
                js.skills,
                js.resumeUrl,
                js.portfolioUrl,
                js.openToWork,
                js.openToRemote,
                js.desiredSalary,
                js.desiredJobTypes,
                js.desiredLocations,
                js.photo
            FROM users u
            LEFT JOIN job_seekers js ON u.userId = js.userId
            WHERE u.userId = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Log error and show user-friendly message
        error_log("Error fetching user profile: " . $e->getMessage());
        $error = "Unable to load profile. Please try again later.";
    }
}

$pageTitle = $user ? htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) . ' - Profile' : 'User Profile';
include __DIR__ . '/../../includes/header.php';
?>
<style>
/* Add 15px gap between cards */
.profile-cards > .row > [class^="col-"]:not(:last-child) {
    margin-bottom: 15px;
}

/* Fix avatar style for users without a profile photo */
.profile-avatar-placeholder {
    width: 120px;
    height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #6c757d;
    color: #fff;
    font-size: 3rem;
    border-radius: 50%;
    margin: 0 auto 1rem auto;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

/* Profile photo size */
.profile-photo {
    width: 120px !important;
    height: 120px !important;
    object-fit: cover;
    border-radius: 50%;
    display: block;
    margin: 0 auto 1rem auto;
}
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($user): ?>
               
                    <!-- Profile Header -->
                    <div class="text-center mb-4">
                        <?php
                        // Use profile photo from job_seekers (js.photo) if available
                        $profilePhoto = !empty($user['photo']) ? $user['photo'] : null;
                        $hasPhoto = !empty($profilePhoto);
                        ?>
                        <?php if ($hasPhoto): ?>
                            <img src="../../assets/uploads/profiles/<?= htmlspecialchars($profilePhoto) ?>" 
                                 class="profile-photo rounded-circle mb-3" alt="Profile Photo">
                        <?php else: ?>
                            <div class="profile-avatar-placeholder">
                                <?= strtoupper(substr($user['firstName'],0,1)) ?>
                            </div>
                        <?php endif; ?>
                        <h2 class="h4 fw-bold mb-1"><?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?></h2>
                        <?php if (!empty($user['headline'])): ?>
                            <p class="text-muted mb-2"><?= htmlspecialchars($user['headline']) ?></p>
                        <?php endif; ?>
                        <?php if ($user['openToWork']): ?>
                            <span class="badge bg-success">Open to Work</span>
                        <?php endif; ?>
                    </div>

                    <div class="profile-cards">
                        <div class="row g-4">
                            <!-- Contact Information -->
                            <div class="col-md-6">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h3 class="h6 fw-bold mb-3">Contact Information</h3>
                                        <ul class="list-unstyled mb-0">
                                            <li class="mb-2">
                                                <i class="bi bi-envelope me-2"></i>
                                                <?= htmlspecialchars($user['email']) ?>
                                            </li>
                                            <?php if (!empty($user['phoneNumber'])): ?>
                                                <li class="mb-2">
                                                    <i class="bi bi-phone me-2"></i>
                                                    <?= htmlspecialchars($user['phoneNumber']) ?>
                                                </li>
                                            <?php endif; ?>
                                            <?php if (!empty($user['location'])): ?>
                                                <li class="mb-2">
                                                    <i class="bi bi-geo-alt me-2"></i>
                                                    <?= htmlspecialchars($user['location']) ?>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Professional Information -->
                            <div class="col-md-6">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h3 class="h6 fw-bold mb-3">Professional Information</h3>
                                        <ul class="list-unstyled mb-0">
                                            <?php if (!empty($user['currentPosition'])): ?>
                                                <li class="mb-2">
                                                    <i class="bi bi-briefcase me-2"></i>
                                                    <?= htmlspecialchars($user['currentPosition']) ?>
                                                    <?php if (!empty($user['currentCompany'])): ?>
                                                        at <?= htmlspecialchars($user['currentCompany']) ?>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endif; ?>
                                            <?php if (!empty($user['educationLevel'])): ?>
                                                <li class="mb-2">
                                                    <i class="bi bi-mortarboard me-2"></i>
                                                    <?= ucfirst(str_replace('_', ' ', $user['educationLevel'])) ?>
                                                </li>
                                            <?php endif; ?>
                                            <?php if (!empty($user['yearsOfExperience'])): ?>
                                                <li class="mb-2">
                                                    <i class="bi bi-clock-history me-2"></i>
                                                    <?= $user['yearsOfExperience'] ?> years of experience
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Skills -->
                            <?php if (!empty($user['skills'])): ?>
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body">
                                            <h3 class="h6 fw-bold mb-3">Skills</h3>
                                            <div class="d-flex flex-wrap gap-2">
                                                <?php foreach (explode(',', $user['skills']) as $skill): ?>
                                                    <span class="badge bg-light text-dark border">
                                                        <?= htmlspecialchars(trim($skill)) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Job Preferences -->
                            <div class="col-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <h3 class="h6 fw-bold mb-3">Job Preferences</h3>
                                        <div class="row g-3">
                                            <?php if (!empty($user['desiredSalary'])): ?>
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Desired Salary:</strong></p>
                                                    <p class="text-muted mb-0">$<?= number_format($user['desiredSalary']) ?></p>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($user['desiredJobTypes'])): ?>
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Job Types:</strong></p>
                                                    <p class="text-muted mb-0">
                                                        <?= ucwords(str_replace(',', ', ', $user['desiredJobTypes'])) ?>
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($user['desiredLocations'])): ?>
                                                <div class="col-12">
                                                    <p class="mb-1"><strong>Preferred Locations:</strong></p>
                                                    <p class="text-muted mb-0"><?= htmlspecialchars($user['desiredLocations']) ?></p>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($user['openToRemote']): ?>
                                                <div class="col-12">
                                                    <span class="badge bg-info">Open to Remote Work</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bio -->
                            <?php if (!empty($user['bio'])): ?>
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body">
                                            <h3 class="h6 fw-bold mb-3">About</h3>
                                            <p class="mb-0"><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Links -->
                            <div class="col-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <h3 class="h6 fw-bold mb-3">Links</h3>
                                        <div class="d-flex flex-wrap gap-3">
                                            <?php if (!empty($user['websiteUrl'])): ?>
                                                <a href="<?= htmlspecialchars($user['websiteUrl']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-globe me-1"></i>Website
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($user['linkedinUrl'])): ?>
                                                <a href="<?= htmlspecialchars($user['linkedinUrl']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-linkedin me-1"></i>LinkedIn
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($user['githubUrl'])): ?>
                                                <a href="<?= htmlspecialchars($user['githubUrl']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-github me-1"></i>GitHub
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($user['portfolioUrl'])): ?>
                                                <a href="<?= htmlspecialchars($user['portfolioUrl']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-briefcase me-1"></i>Portfolio
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($user['resumeUrl'])): ?>
                                                <a href="<?= htmlspecialchars($user['resumeUrl']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-file-earmark-text me-1"></i>Resume
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-person-x display-4 text-muted mb-3"></i>
                        <h3 class="fw-bold">User not found</h3>
                        <p class="text-muted">The user profile you are looking for does not exist.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?> 