<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'jobSeeker') {
    header('Location: ' . Config::BASE_URL . '/pages/public/login.php');
    exit;
}

$user = new User();
$userId = $_SESSION['user_id'];

// Fetch user and job seeker data
$userData = $user->findById($userId);
$jobSeekerData = $user->getJobSeekerProfile($userId);

$pageTitle = "Profile - JOBEST";
include __DIR__ . '/../../includes/header.php';

// Define education levels for display (if needed, using the array from edit-profile.php)
// This should ideally be in a shared location or fetched differently
$educationLevels = [
    'high_school' => 'High School',
    'associate' => 'Associate Degree',
    'bachelor' => 'Bachelor\'s Degree',
    'master' => 'Master\'s Degree',
    'phd' => 'PhD',
    'other' => 'Other'
];
?>

<style>
    .profile-photo {
        width: 270px;
        height: 270px;
        border-radius: 50%;
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">

            <!-- Profile Header Card -->
            <div class="card border-0 shadow-sm mb-5">
                <div class="card-body position-relative">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($_SESSION['success']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($_SESSION['error']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    
                    <div class="d-flex align-items-center mb-4">
                        <div class="position-relative">
                <?php 
                            // Get the default avatar URL
                            $defaultAvatarUrl = getUserAvatar($userId);
                            
                            // Check if user has uploaded a photo
                            $profilePhotoUrl = $defaultAvatarUrl;
                            if (!empty($jobSeekerData['photo'])) {
                                $uploadedPhotoPath = Config::BASE_URL . '/assets/uploads/profiles/' . htmlspecialchars($jobSeekerData['photo']);
                                // Check if the file exists on the server
                                if (file_exists(__DIR__ . '/../../assets/uploads/profiles/' . $jobSeekerData['photo'])) {
                                    $profilePhotoUrl = $uploadedPhotoPath;
                                }
                            }
                            ?>
                            <img src="<?= $profilePhotoUrl ?>"
                                 class="profile-photo" style="margin-right: 20px;" alt="User Avatar">
                            <!-- Camera icon to change photo -->
                            <form method="POST" action="<?= Config::BASE_URL ?>/actions/update_profile_photo.php" enctype="multipart/form-data" id="photoUploadForm" style="position: absolute; bottom: 0; right: 20px; display:inline;">
                                <input type="file" name="photo" id="photoInput" accept="image/png, image/jpeg, image/jpg, image/gif, image/webp" style="display:none;" required>
                                <label for="photoInput" class="btn btn-light btn-sm rounded-circle" style="width: 10px; height: 40px; display: flex; align-items: center; justify-content: center; border: 1px solid #ccc; cursor: pointer;" title="Change profile photo">
                                    <i class="bi bi-camera-fill" style="font-size: 20px;"></i>
                                </label>
                            </form>
                            <?php /* TODO: Implement modal or link for photo upload, save to uploads/profiles */ ?>
                        </div>
                        <div>
                            <h1 class="h3 fw-bold mb-1"><?= htmlspecialchars($userData['firstName'] . ' ' . $userData['lastName']) ?></h1>
                            <?php if (!empty($jobSeekerData['headline'])): ?>
                                <p class="text-muted mb-2"><?= htmlspecialchars($jobSeekerData['headline']) ?></p>
                            <?php endif; ?>
                            <p class="text-muted mb-0">
                                <?php if (!empty($userData['location'])): ?>
                                    <i class="bi bi-geo-alt" style="margin-right: 10px;"></i><?= htmlspecialchars($userData['location']) ?><br>
                                <?php endif; ?>
                                <?php if (!empty($userData['email'])): ?>
                                    <i class="bi bi-envelope" style="margin-right: 10px;"></i><?= htmlspecialchars($userData['email']) ?><br>
                                <?php endif; ?>
                                <?php if (!empty($userData['phoneNumber'])): ?>
                                    <i class="bi bi-phone" style="margin-right: 10px;"></i><?= htmlspecialchars($userData['phoneNumber']) ?><br>
                                <?php endif; ?>
                            </p>
              </div>
            </div>
                    <!-- Edit Profile Button -->
                    <div class="position-absolute" style="bottom: 1.5rem; right: 1.5rem;">
                        <a href="<?= Config::BASE_URL ?>/pages/user/edit-profile.php" class="btn btn-dark rounded-pill btn-sm">
                            <i class="bi bi-pencil me-2"></i> Edit Profile
                        </a>
          </div>
        </div>
      </div>

            <!-- About Me Card -->
            <?php if (!empty($userData['bio'])): ?>
                <div class="card border-0 shadow-sm mb-5">
          <div class="card-body">
                        <h3 class="h5 fw-bold mb-3">About</h3>
                        <p class="text-muted mb-0"><?= nl2br(htmlspecialchars($userData['bio'])) ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Professional Information Card -->
            <?php if (!empty($jobSeekerData['currentPosition']) || !empty($jobSeekerData['educationLevel']) || (!empty($jobSeekerData['yearsOfExperience']) && $jobSeekerData['yearsOfExperience'] > 0) || !empty($jobSeekerData['openToWork']) || !empty($jobSeekerData['openToRemote']) || !empty($jobSeekerData['desiredSalary']) || !empty($jobSeekerData['desiredJobTypes']) || !empty($jobSeekerData['desiredLocations'])): ?>
                <div class="card border-0 shadow-sm mb-5">
                    <div class="card-body">
                        <h3 class="h5 fw-bold mb-3">Professional Information</h3>
                         <div class="row g-3 text-muted">
                             <?php if (!empty($jobSeekerData['currentPosition'])): ?>
                                 <div class="col-md-6">
                                     <strong>Current Position:</strong> <?= htmlspecialchars($jobSeekerData['currentPosition']) ?><?= !empty($jobSeekerData['currentCompany']) ? ' at ' . htmlspecialchars($jobSeekerData['currentCompany']) : '' ?>
                                 </div>
            <?php endif; ?>
                              <?php if (!empty($jobSeekerData['educationLevel'])): ?>
                  <div class="col-md-6">
                                     <strong>Education Level:</strong> <?= htmlspecialchars($educationLevels[$jobSeekerData['educationLevel']] ?? ucfirst($jobSeekerData['educationLevel'])) ?>
                  </div>
                             <?php endif; ?>
                            <?php if (!empty($jobSeekerData['yearsOfExperience']) && $jobSeekerData['yearsOfExperience'] > 0): ?>
                  <div class="col-md-6">
                                     <strong>Years of Experience:</strong> <?= htmlspecialchars($jobSeekerData['yearsOfExperience']) ?> years
                  </div>
                             <?php endif; ?>
                             <?php if ($jobSeekerData['openToWork'] ?? false): // Use ?? false for safety ?>
                                 <div class="col-md-6">
                                     <strong>Availability:</strong> Open to Work
                </div>
                             <?php endif; ?>
                              <?php if ($jobSeekerData['openToRemote'] ?? false): // Use ?? false for safety ?>
                                 <div class="col-md-6">
                                     <strong>Remote Work:</strong> Open to Remote
              </div>
                             <?php endif; ?>
                             <?php if (!empty($jobSeekerData['desiredSalary'])): ?>
                                 <div class="col-md-6">
                                     <strong>Desired Salary:</strong> $<?= number_format($jobSeekerData['desiredSalary']) ?>+ per year
                  </div>
                             <?php endif; ?>
                              <?php if (!empty($jobSeekerData['desiredJobTypes'])): ?>
                                  <div class="col-md-6">
                                      <strong>Desired Job Types:</strong> <?= htmlspecialchars(str_replace(',', ', ', $jobSeekerData['desiredJobTypes'])) ?>
                </div>
                              <?php endif; ?>
                               <?php if (!empty($jobSeekerData['desiredLocations'])): ?>
                  <div class="col-md-6">
                                      <strong>Preferred Locations:</strong> <?= htmlspecialchars($jobSeekerData['desiredLocations']) ?>
                    </div>
                              <?php endif; ?>
                    </div>
                  </div>
                </div>
            <?php endif; ?>

            <!-- Skills Card -->
            <?php
            try {
                $skills = $user->getSkills($userId);
                if (!empty($skills)):
                ?>
                    <div class="card border-0 shadow-sm mb-5">
                        <div class="card-body">
                            <h3 class="h5 fw-bold mb-3">Skills</h3>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($skills as $skill): ?>
                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($skill) ?></span>
                                <?php endforeach; ?>
          </div>
        </div>
      </div>
                <?php
                endif;
            } catch (Exception $e) {
                error_log('Error loading skills for profile display: ' . $e->getMessage());
                // Optionally display a message to the user
            }
            ?>

             <!-- Links Card -->
             <?php if (!empty($userData['websiteUrl']) || !empty($userData['linkedinUrl']) || !empty($userData['githubUrl']) || !empty($jobSeekerData['resumeUrl']) || !empty($jobSeekerData['portfolioUrl'])): ?>
             <div class="card border-0 shadow-sm mb-5">
                 <div class="card-body">
                     <h3 class="h5 fw-bold mb-3">Links</h3>
                     <div class="d-flex flex-wrap gap-2">
                         <?php if (!empty($userData['websiteUrl'])): ?>
                             <a href="<?= htmlspecialchars($userData['websiteUrl']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill">
                                 <i class="bi bi-globe" style="margin-right: 10px;"></i> Website
                             </a>
                         <?php endif; ?>
                         <?php if (!empty($userData['linkedinUrl'])): ?>
                             <a href="<?= htmlspecialchars($userData['linkedinUrl']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill">
                                 <i class="bi bi-linkedin" style="margin-right: 10px;"></i> LinkedIn
                             </a>
                         <?php endif; ?>
                         <?php if (!empty($userData['githubUrl'])): ?>
                              <a href="<?= htmlspecialchars($userData['githubUrl']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill">
                                 <i class="bi bi-github" style="margin-right: 10px;"></i> GitHub
                             </a>
                         <?php endif; ?>
                         <?php if (!empty($jobSeekerData['resumeUrl'])): ?>
                              <a href="<?= htmlspecialchars($jobSeekerData['resumeUrl']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill">
                                 <i class="bi bi-file-earmark-person" style="margin-right: 10px;"></i> Resume
                             </a>
                         <?php endif; ?>
                          <?php if (!empty($jobSeekerData['portfolioUrl'])): ?>
                              <a href="<?= htmlspecialchars($jobSeekerData['portfolioUrl']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill">
                                 <i class="bi bi-briefcase" style="margin-right: 10px;"></i> Portfolio
                             </a>
              <?php endif; ?>
            </div>
          </div>
          </div>
        <?php endif; ?>

            <?php /* Add other sections like Experience, Education, etc. as needed in similar card structure */ ?>

    </div>
  </div>
</div>

<!-- Add script for photo upload -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var photoInput = document.getElementById('photoInput');
    var photoForm = document.getElementById('photoUploadForm');
    if (photoInput && photoForm) {
        photoInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                photoForm.submit();
      }
    });
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>