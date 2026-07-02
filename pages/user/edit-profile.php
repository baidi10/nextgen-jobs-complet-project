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
$userData = $user->findById($userId);
$jobSeekerData = $user->getJobSeekerProfile($userId); // Fetch job seeker specific data

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Data for the users table
        $updateUserData = [
            'firstName' => $_POST['firstName'] ?? '',
            'lastName' => $_POST['lastName'] ?? '',
            'location' => $_POST['location'] ?? '',
            'bio' => $_POST['bio'] ?? '',
            'websiteUrl' => $_POST['websiteUrl'] ?? '',
            'linkedinUrl' => $_POST['linkedinUrl'] ?? '',
            'githubUrl' => $_POST['githubUrl'] ?? ''
        ];

        // Data for the job_seekers table
        $updateJobSeekerData = [
            'headline' => $_POST['headline'] ?? '',
            'currentPosition' => $_POST['currentPosition'] ?? '',
            'currentCompany' => $_POST['currentCompany'] ?? '',
            'educationLevel' => $_POST['educationLevel'] ?? null,
            'yearsOfExperience' => $_POST['yearsOfExperience'] ?? null,
            'resumeUrl' => $_POST['resumeUrl'] ?? '',
            'portfolioUrl' => $_POST['portfolioUrl'] ?? '',
        ];

        // Handle Profile Photo Upload
        $profilePhotoFileName = $jobSeekerData['photo'] ?? null; // Keep existing photo by default

        if (isset($_FILES['profilePhoto']) && $_FILES['profilePhoto']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profilePhoto'];
            $uploadDir = __DIR__ . '/../../assets/uploads/profiles/';
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = uniqid() . '_' . basename($file['name']);
            $targetFilePath = $uploadDir . $fileName;
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

            // Validate file type (allow only images)
            $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
            if (in_array(strtolower($fileType), $allowTypes)) {
                // Move uploaded file to destination
                if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
                    // File uploaded successfully, update the photo filename
                    $profilePhotoFileName = $fileName;
                    // Optional: Delete old photo if it exists and is not the default avatar
                     if (!empty($jobSeekerData['photo']) && $jobSeekerData['photo'] !== 'default-avatar.png') { // Assuming default-avatar.png is your default
                         $oldPhotoPath = $uploadDir . $jobSeekerData['photo'];
                         if (file_exists($oldPhotoPath)) {
                             unlink($oldPhotoPath);
                         }
                     }

                } else {
                    $error = "Error uploading your profile photo.";
                }
            } else {
                $error = "Sorry, only JPG, JPEG, PNG, & GIF files are allowed.";
            }
        }

        // Add profile photo filename to job seeker data
        $updateJobSeekerData['photo'] = $profilePhotoFileName;

        $userUpdated = $user->updateProfile($userId, $updateUserData);
        $jobSeekerUpdated = $user->updateJobSeekerProfile($userId, $updateJobSeekerData);

        if ($userUpdated || $jobSeekerUpdated || isset($_FILES['profilePhoto']) && $_FILES['profilePhoto']['error'] === UPLOAD_ERR_OK && $profilePhotoFileName) {
            $success = "Profile updated successfully";
            // Refresh data after successful update
            $userData = $user->findById($userId); 
            $jobSeekerData = $user->getJobSeekerProfile($userId); 
        } else {
             // Optionally, check if data was submitted but no changes were detected by the update methods
             $submittedData = array_merge($updateUserData, $updateJobSeekerData);
             $originalData = array_merge($userData, $jobSeekerData);
             $hasChanges = false;
             foreach($submittedData as $key => $value) {
                 // Basic check - can be more sophisticated
                 if (isset($originalData[$key]) && $originalData[$key] != $value) {
                     $hasChanges = true;
                     break;
                 }
             }
             // Also check if a photo was specifically uploaded, even if other data didn't change
             if (isset($_FILES['profilePhoto']) && $_FILES['profilePhoto']['error'] === UPLOAD_ERR_OK) {
                  $hasChanges = true;
             }

             if (!$hasChanges) {
                 $success = "No changes detected.";
             } else if (empty($error)) { // Only show generic error if no specific file upload error occurred
                  // This might happen if update methods failed silently
                  $error = "Profile update failed. Please try again.";
                  error_log("Profile update failed for user ID " . $userId);
             }
        }

    } catch (Exception $e) {
        $error = "An error occurred: " . $e->getMessage();
        error_log("Profile update exception for user ID " . $userId . ": " . $e->getMessage());
    }
}

$pageTitle = "Profile - JOBEST";

// Define education levels for dropdown
$educationLevels = [
    'high_school' => 'High School',
    'associate' => 'Associate Degree',
    'bachelor' => 'Bachelor\'s Degree',
    'master' => 'Master\'s Degree',
    'phd' => 'PhD',
    'other' => 'Other'
];

include __DIR__ . '/../../includes/header.php';
?>

<style>
    .profile-photo {
        width: 148px;
        height: 148px;
        border-radius: 50%;
    }
</style>

<div class="container py-5">
    <div class="row g-4">
      <!-- Profile Sidebar -->
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center p-4">
            <div class="position-relative mb-4">
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
                   class="profile-photo" alt="Profile Photo">
              <?php if (!empty($jobSeekerData['photo'])): ?>
                <button type="button" class="btn btn-danger btn-sm position-absolute bottom-0 end-0" onclick="deleteProfilePhoto()">
                    <i class="bi bi-trash"></i>
                </button>
              <?php endif; ?>
            </div>
            <h2 class="h5 fw-bold mb-3"><?= htmlspecialchars($userData['firstName'] . ' ' . $userData['lastName']) ?></h2>
            <p class="text-muted small mb-4"><?= htmlspecialchars($jobSeekerData['headline'] ?? $userData['location'] ?? '') ?></p>

            <!-- Bio -->
             <?php if (!empty($userData['bio'])): ?>
              <div class="text-start mt-4">
                <h3 class="h6 fw-bold mb-3">About Me</h3>
                <p class="text-muted small"><?= nl2br(htmlspecialchars($userData['bio'])) ?></p>
              </div>
            <?php endif; ?>

             <!-- Contact/Links -->
            <div class="text-start mt-4">
                 <h3 class="h6 fw-bold mb-3">Links</h3>
                 <ul class="list-unstyled small text-muted mb-0">
                     <?php if (!empty($userData['location'])): ?>
                     <li class="mb-2"><i class="bi bi-geo-alt me-2"></i><?= htmlspecialchars($userData['location']) ?></li>
                     <?php endif; ?>
                     <?php if (!empty($userData['websiteUrl'])): ?>
                     <li class="mb-2"><i class="bi bi-globe me-2"></i><a href="<?= htmlspecialchars($userData['websiteUrl']) ?>" target="_blank" class="text-decoration-none text-muted"><?= htmlspecialchars($userData['websiteUrl']) ?></a></li>
                     <?php endif; ?>
                     <?php if (!empty($userData['linkedinUrl'])): ?>
                     <li class="mb-2"><i class="bi bi-linkedin me-2"></i><a href="<?= htmlspecialchars($userData['linkedinUrl']) ?>" target="_blank" class="text-decoration-none text-muted">LinkedIn</a></li>
                     <?php endif; ?>
                     <?php if (!empty($userData['githubUrl'])): ?>
                     <li class="mb-2"><i class="bi bi-github me-2"></i><a href="<?= htmlspecialchars($userData['githubUrl']) ?>" target="_blank" class="text-decoration-none text-muted">GitHub</a></li>
                     <?php endif; ?>
                 </ul>
            </div>

             <?php if (!empty($jobSeekerData['resumeUrl'])): ?>
             <div class="mt-4">
                 <a href="<?= htmlspecialchars($jobSeekerData['resumeUrl']) ?>" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                     <i class="bi bi-file-earmark-person me-1"></i> View Resume
                 </a>
             </div>
             <?php endif; ?>

             <?php if (!empty($jobSeekerData['portfolioUrl'])): ?>
             <div class="mt-3">
                 <a href="<?= htmlspecialchars($jobSeekerData['portfolioUrl']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                     <i class="bi bi-globe me-1"></i> View Portfolio
                 </a>
             </div>
             <?php endif; ?>

          </div>
        </div>
      </div>

      <!-- Main Profile Form -->
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
          <div class="card-body p-4">
            <?php if ($error): ?>
              <div class="alert alert-danger mb-4"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
              <div class="alert alert-success mb-4"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
              <!-- Basic Info -->
              <div class="mb-5">
                <h3 class="h5 fw-bold mb-4">Basic Information</h3>
                <div class="row g-4">
                  <div class="col-md-6">
                    <label for="firstName" class="form-label">First Name</label>
                    <input type="text" id="firstName" name="firstName"
                           class="form-control"
                           value="<?= htmlspecialchars($userData['firstName']) ?>" required>
                  </div>
                  <div class="col-md-6">
                    <label for="lastName" class="form-label">Last Name</label>
                    <input type="text" id="lastName" name="lastName"
                           class="form-control"
                           value="<?= htmlspecialchars($userData['lastName']) ?>" required>
                  </div>
                  <div class="col-12">
                    <label for="bio" class="form-label">Bio/Summary</label>
                    <textarea id="bio" name="bio" class="form-control" rows="4" placeholder="Tell us about yourself..."><?= htmlspecialchars($userData['bio'] ?? '') ?></textarea>
                  </div>
                </div>
              </div>

              <!-- Professional Information -->
              <div class="mb-5">
                <h3 class="h5 fw-bold mb-4">Professional Information</h3>
                <div class="row g-4">
                    <div class="col-12">
                        <label for="headline" class="form-label">Headline</label>
                        <input type="text" id="headline" name="headline" class="form-control"
                               value="<?= htmlspecialchars($jobSeekerData['headline'] ?? '') ?>"
                               placeholder="e.g. Experienced Web Developer">
                    </div>
                    <div class="col-md-6">
                        <label for="currentPosition" class="form-label">Current Position</label>
                        <input type="text" id="currentPosition" name="currentPosition" class="form-control"
                               value="<?= htmlspecialchars($jobSeekerData['currentPosition'] ?? '') ?>"
                               placeholder="e.g. Senior Frontend Engineer">
                    </div>
                    <div class="col-md-6">
                        <label for="currentCompany" class="form-label">Current Company</label>
                        <input type="text" id="currentCompany" name="currentCompany" class="form-control"
                               value="<?= htmlspecialchars($jobSeekerData['currentCompany'] ?? '') ?>"
                               placeholder="e.g. Tech Solutions Inc.">
                    </div>
                    <div class="col-md-6">
                        <label for="educationLevel" class="form-label">Education Level</label>
                        <select id="educationLevel" name="educationLevel" class="form-select">
                            <option value="">Select Education Level</option>
                            <?php foreach ($educationLevels as $value => $label): ?>
                                <option value="<?= $value ?>" <?= (isset($jobSeekerData['educationLevel']) && $jobSeekerData['educationLevel'] === $value) ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                     <div class="col-md-6">
                        <label for="yearsOfExperience" class="form-label">Years of Experience</label>
                        <input type="number" id="yearsOfExperience" name="yearsOfExperience" class="form-control"
                               value="<?= htmlspecialchars($jobSeekerData['yearsOfExperience'] ?? '') ?>" min="0"
                               placeholder="e.g. 5">
                    </div>
                </div>
              </div>

               <!-- Location -->
              <div class="mb-5">
                <h3 class="h5 fw-bold mb-4">Location</h3>
                <div class="row g-4">
                  <div class="col-12">
                    <input type="text" name="location"
                           class="form-control"
                           value="<?= htmlspecialchars($userData['location'] ?? '') ?>"
                           placeholder="City, Country">
                  </div>
                </div>
              </div>

              <!-- Resume and Portfolio -->
               <div class="mb-5">
                   <h3 class="h5 fw-bold mb-4">Resume & Portfolio</h3>
                   <div class="row g-4">
                       <div class="col-md-6">
                           <label for="resumeUrl" class="form-label">Resume URL (Optional)</label>
                           <input type="url" id="resumeUrl" name="resumeUrl" class="form-control"
                                  value="<?= htmlspecialchars($jobSeekerData['resumeUrl'] ?? '') ?>"
                                  placeholder="https://yourwebsite.com/resume.pdf">
                       </div>
                       <div class="col-md-6">
                           <label for="portfolioUrl" class="form-label">Portfolio URL (Optional)</label>
                           <input type="url" id="portfolioUrl" name="portfolioUrl" class="form-control"
                                  value="<?= htmlspecialchars($jobSeekerData['portfolioUrl'] ?? '') ?>"
                                  placeholder="https://yourportfolio.com">
                       </div>
                   </div>
               </div>

              <!-- Social Links -->
              <div class="mb-5">
                <h3 class="h5 fw-bold mb-4">Social Profiles</h3>
                <div class="row g-4">
                  <div class="col-md-6">
                    <label for="linkedinUrl" class="form-label">LinkedIn</label>
                    <div class="input-group">
                      <span class="input-group-text">
                        <i class="bi bi-linkedin"></i>
                      </span>
                      <input type="url" id="linkedinUrl" name="linkedinUrl"
                             class="form-control"
                             value="<?= htmlspecialchars($userData['linkedinUrl'] ?? '') ?>"
                             placeholder="https://linkedin.com/in/username">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label for="githubUrl" class="form-label">GitHub</label>
                    <div class="input-group">
                      <span class="input-group-text">
                        <i class="bi bi-github"></i>
                      </span>
                      <input type="url" id="githubUrl" name="githubUrl"
                             class="form-control"
                             value="<?= htmlspecialchars($userData['githubUrl'] ?? '') ?>"
                             placeholder="https://github.com/username">
                    </div>
                  </div>
                </div>
              </div>

              <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary px-4">Save Changes</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
</div>

<!-- Avatar Modal -->
<div class="modal fade" id="avatarModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Update Profile Picture</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <p>Your avatar is automatically generated based on your email address.</p>
        <img src="<?= getUserAvatar($userId) ?>"
             class="rounded-circle mb-3" width="150" height="150">
      </div>
    </div>
  </div>
</div>

<script>
function deleteProfilePhoto() {
    if (confirm('Are you sure you want to delete your profile photo?')) {
        fetch('<?= Config::BASE_URL ?>/ajax/delete-profile-photo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload the page to show the default avatar
                window.location.reload();
            } else {
                alert(data.message || 'Error deleting profile photo');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the photo');
        });
    }
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>