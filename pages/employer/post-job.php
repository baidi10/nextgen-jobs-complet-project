<?php
require_once __DIR__ . '/../../includes/dependencies.php';

// Authentication check using database functions
if (!isLoggedIn() || !isEmployer()) {
    header('Location: /pages/public/login.php');
    exit;
}

$company = new Company();
$job = new Job();
$userId = $_SESSION['user_id'];
$companyId = $company->getCompanyIdByUser($userId);

// If no company exists for this employer, create a default one
if (!$companyId) {
    $userData = getCurrentUser(); // Use helper function to get user data
    
    if ($userData) {
        $defaultCompanyData = [
            'companyName' => $userData['firstName'] . ' ' . $userData['lastName'] . '\'s Company',
            'industry' => 'Technology',
            'description' => 'Company profile',
            'location' => 'United States'
        ];
        
        $companyId = $company->create($userId, $defaultCompanyData);
    }
}

$error = '';
$success = '';
$jobDetails = null;
$jobSkills = [];
$isEditing = false;
$isDraft = false;
$pageTitle = $isEditing ? "Edit Job - JOBEST" : "Post a Job - JOBEST";

// Check if we're editing an existing job
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $jobId = (int)$_GET['edit'];
    $jobDetails = $job->getJobById($jobId);
    
    // Make sure the job belongs to this employer
    if ($jobDetails && $jobDetails['companyId'] == $companyId) {
        $isEditing = true;
        $jobSkills = $job->getJobSkills($jobId);
        $isDraft = isset($jobDetails['isDraft']) && $jobDetails['isDraft'] == 1;
        $pageTitle = "Edit Job - JOBEST";
    } else {
        header('Location: manage-jobs.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $jobData = [
            'companyId' => $companyId,
            'postedBy' => $userId,
            'jobTitle' => $_POST['title'],
            'jobDescription' => $_POST['description'],
            'jobRequirements' => $_POST['requirements'],
            'jobBenefits' => $_POST['benefits'] ?? null,
            'jobType' => $_POST['job_type'],
            'experienceLevel' => $_POST['experience_level'],
            'location' => $_POST['location'],
            'isRemote' => isset($_POST['is_remote']) ? 1 : 0,
            'salaryMin' => $_POST['salary_min'] ?: null,
            'salaryMax' => $_POST['salary_max'] ?: null,
            'salaryCurrency' => $_POST['salary_currency'] ?? 'USD',
            'salaryPeriod' => $_POST['salary_period'] ?? 'yearly',
            'skills' => !empty($_POST['skills']) ? explode(',', $_POST['skills']) : [],
            'isDraft' => isset($_POST['save_draft']) ? 1 : 0
        ];

        if ($isEditing) {
            // Update existing job
            $jobData['jobId'] = $jobDetails['jobId'];
            $job->updateJob($jobData);
            $success = isset($_POST['save_draft']) ? "Job saved as draft!" : "Job updated successfully!";
        } else {
            // Create new job
            $jobId = $job->create($jobData);
            $success = isset($_POST['save_draft']) ? "Job saved as draft!" : "Job posted successfully!";
        }
        
        // Set success message in session
        $_SESSION['success_message'] = $success;
        header("Location: manage-jobs.php");
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Add CSS to hide duplicate navigation if it exists
$customCSS = "<style>
.duplicate-nav, .duplicate-navigation, .employer-top-nav {
    display: none !important;
}

.form-section {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #eee;
}

.form-section:last-child {
    border-bottom: 0;
    margin-bottom: 0;
    padding-bottom: 0;
}

.section-title {
    margin-bottom: 1.5rem;
    color: #333;
    font-weight: 600;
}

.tag-badge {
    display: inline-block;
    background-color: #f0f0f0;
    color: #333;
    border-radius: 30px;
    padding: 5px 12px;
    margin: 0 5px 5px 0;
    font-size: 0.875rem;
}

.tag-badge .close {
    margin-left: 5px;
    font-size: 0.875rem;
    cursor: pointer;
}

.mce-content-body {
    font-family: 'Inter', sans-serif;
}
</style>";

// Add custom CSS to head
$pageStyles = [$customCSS];

include __DIR__ . '/../../includes/header.php';
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-9">
      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4 p-sm-5">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 fw-bold mb-0"><?= $isEditing ? 'Edit Job' : 'Post a New Job' ?></h2>
            <?php if ($isEditing): ?>
              <span class="badge bg-<?= $isDraft ? 'secondary' : 'success' ?> rounded-pill px-3 py-2">
                <?= $isDraft ? 'Draft' : 'Published' ?>
              </span>
            <?php endif; ?>
          </div>

          <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
              <div class="d-flex">
                <i class="bi bi-exclamation-circle-fill me-2 fs-5"></i>
                <div><?= htmlspecialchars($error) ?></div>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>

          <form method="POST" id="jobForm">
            <!-- Basic Info Section -->
            <div class="form-section">
              <h3 class="h6 section-title">Basic Information</h3>
              <div class="mb-4">
                <label class="form-label fw-medium">Job Title *</label>
                <input type="text" name="title" class="form-control rounded-3" 
                       value="<?= htmlspecialchars($jobDetails['jobTitle'] ?? '') ?>" required>
              </div>
              
              <?php if ($isEditing): ?>
              <!-- Status toggle removed -->
              <?php endif; ?>

              <div class="row g-4 mb-4">
                <div class="col-md-6">
                  <label class="form-label fw-medium">Job Type *</label>
                  <select name="job_type" class="form-select rounded-3" required>
                    <option value="fullTime" <?= (($jobDetails['jobType'] ?? '') == 'fullTime') ? 'selected' : '' ?>>Full-time</option>
                    <option value="partTime" <?= (($jobDetails['jobType'] ?? '') == 'partTime') ? 'selected' : '' ?>>Part-time</option>
                    <option value="contract" <?= (($jobDetails['jobType'] ?? '') == 'contract') ? 'selected' : '' ?>>Contract</option>
                    <option value="freelance" <?= (($jobDetails['jobType'] ?? '') == 'freelance') ? 'selected' : '' ?>>Freelance</option>
                    <option value="internship" <?= (($jobDetails['jobType'] ?? '') == 'internship') ? 'selected' : '' ?>>Internship</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-medium">Experience Level *</label>
                  <select name="experience_level" class="form-select rounded-3" required>
                    <option value="entry" <?= (($jobDetails['experienceLevel'] ?? '') == 'entry') ? 'selected' : '' ?>>Entry Level</option>
                    <option value="mid" <?= (($jobDetails['experienceLevel'] ?? '') == 'mid') ? 'selected' : '' ?>>Mid Level</option>
                    <option value="senior" <?= (($jobDetails['experienceLevel'] ?? '') == 'senior') ? 'selected' : '' ?>>Senior Level</option>
                    <option value="lead" <?= (($jobDetails['experienceLevel'] ?? '') == 'lead') ? 'selected' : '' ?>>Lead</option>
                    <option value="executive" <?= (($jobDetails['experienceLevel'] ?? '') == 'executive') ? 'selected' : '' ?>>Executive</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Location & Salary Section -->
            <div class="form-section">
              <h3 class="h6 section-title">Location & Compensation</h3>
              <div class="row g-4 mb-4">
                <div class="col-md-6">
                  <label class="form-label fw-medium">Location *</label>
                  <input type="text" name="location" class="form-control rounded-3" 
                         value="<?= htmlspecialchars($jobDetails['location'] ?? '') ?>" required>
                  <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="is_remote" id="isRemote"
                          <?= (($jobDetails['isRemote'] ?? 0) == 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="isRemote">
                      This is a remote position
                    </label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="row g-3">
                    <div class="col-6">
                      <label class="form-label fw-medium">Min Salary</label>
                      <input type="number" name="salary_min" class="form-control rounded-3" 
                            value="<?= htmlspecialchars($jobDetails['salaryMin'] ?? '') ?>">
                    </div>
                    <div class="col-6">
                      <label class="form-label fw-medium">Max Salary</label>
                      <input type="number" name="salary_max" class="form-control rounded-3" 
                            value="<?= htmlspecialchars($jobDetails['salaryMax'] ?? '') ?>">
                    </div>
                  </div>
                  <div class="row g-3 mt-2">
                    <div class="col-6">
                      <select name="salary_currency" class="form-select rounded-3">
                        <option value="USD" <?= (($jobDetails['salaryCurrency'] ?? 'USD') == 'USD') ? 'selected' : '' ?>>USD</option>
                        <option value="EUR" <?= (($jobDetails['salaryCurrency'] ?? 'USD') == 'EUR') ? 'selected' : '' ?>>EUR</option>
                        <option value="GBP" <?= (($jobDetails['salaryCurrency'] ?? 'USD') == 'GBP') ? 'selected' : '' ?>>GBP</option>
                      </select>
                    </div>
                    <div class="col-6">
                      <select name="salary_period" class="form-select rounded-3">
                        <option value="yearly" <?= (($jobDetails['salaryPeriod'] ?? 'yearly') == 'yearly') ? 'selected' : '' ?>>Per Year</option>
                        <option value="monthly" <?= (($jobDetails['salaryPeriod'] ?? 'yearly') == 'monthly') ? 'selected' : '' ?>>Per Month</option>
                        <option value="hourly" <?= (($jobDetails['salaryPeriod'] ?? 'yearly') == 'hourly') ? 'selected' : '' ?>>Per Hour</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Job Description Section -->
            <div class="form-section">
              <h3 class="h6 section-title">Job Description</h3>
              <!-- Job Description -->
              <div class="mb-4">
                <label class="form-label fw-medium">Job Description *</label>
                <textarea id="editor-description" name="description" class="form-control rounded-3" rows="6" required><?= htmlspecialchars($jobDetails['jobDescription'] ?? '') ?></textarea>
                <div class="form-text">Describe the role, responsibilities, and what a typical day looks like</div>
              </div>

              <!-- Requirements -->
              <div class="mb-4">
                <label class="form-label fw-medium">Requirements *</label>
                <textarea id="editor-requirements" name="requirements" class="form-control rounded-3" rows="6" required><?= htmlspecialchars($jobDetails['jobRequirements'] ?? '') ?></textarea>
                <div class="form-text">List the skills, qualifications, and experience needed for this role</div>
              </div>

              <!-- Benefits -->
              <div class="mb-4">
                <label class="form-label fw-medium">Benefits & Perks</label>
                <textarea id="editor-benefits" name="benefits" class="form-control rounded-3" rows="6"><?= htmlspecialchars($jobDetails['jobBenefits'] ?? '') ?></textarea>
                <div class="form-text">What benefits and perks do you offer to employees?</div>
              </div>
            </div>

            <!-- Skills Section -->
            <div class="form-section">
              <h3 class="h6 section-title">Skills & Keywords</h3>
              <div class="mb-3">
                <label class="form-label fw-medium">Skills (comma separated) *</label>
                <input type="text" id="skills-input" name="skills" class="form-control rounded-3" required 
                       value="<?= htmlspecialchars(implode(', ', $jobSkills)) ?>"
                       placeholder="e.g. JavaScript, React, Node.js, AWS">
                <div class="form-text">These will help job seekers find your posting</div>
              </div>
              <div id="skills-tags" class="mb-3"></div>
            </div>

            <div class="d-flex gap-3">
              <?php if ($isEditing): ?>
                <a href="manage-jobs.php" class="btn btn-light rounded-pill py-3 px-4 fw-medium">
                  Cancel
                </a>
                
                <button type="submit" class="btn btn-primary rounded-pill py-3 px-4 fw-medium flex-grow-1">
                  <i class="bi bi-check-lg me-2"></i>Update Job
                </button>
              <?php else: ?>
                
                <button type="submit" class="btn btn-primary rounded-pill py-3 px-4 fw-medium flex-grow-1">
                  <i class="bi bi-plus-circle me-2"></i>Post Job
                </button>
              <?php endif; ?>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- TinyMCE from CDN (using public CDN that doesn't require API key) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.7.3/tinymce.min.js" integrity="sha512-ZEvjyUz8HYCw9ilBe3UAeZR7MN3YaFdxVIniUAr4zwX5mh+aPP9k/WFKb5kZXf4IZ59GzDYFSFQyYwCzYUwbQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
  // Initialize TinyMCE for all rich text editors
  tinymce.init({
    selector: '#editor-description, #editor-requirements, #editor-benefits',
    height: 300,
    menubar: false,
    skin: 'oxide',
    plugins: [
      'advlist', 'autolink', 'lists', 'link', 'charmap', 'preview',
      'searchreplace', 'visualblocks', 'code', 'fullscreen',
      'insertdatetime', 'table', 'wordcount'
    ],
    toolbar: 'undo redo | blocks | formatselect | ' +
      'bold italic forecolor backcolor | alignleft aligncenter ' +
      'alignright alignjustify | bullist numlist outdent indent | ' +
      'removeformat',
    content_style: 'body { font-family: Inter, sans-serif; font-size: 16px; }',
    branding: false,
    promotion: false,
    placeholder: 'Start typing here...'
  });

  // Skills tags handling
  document.addEventListener('DOMContentLoaded', function() {
    const skillsInput = document.getElementById('skills-input');
    const skillsTags = document.getElementById('skills-tags');
    
    // Function to render tags
    function renderTags() {
      const skills = skillsInput.value.split(',').map(s => s.trim()).filter(s => s);
      skillsTags.innerHTML = '';
      
      skills.forEach(skill => {
        if (!skill) return;
        
        const badge = document.createElement('span');
        badge.className = 'tag-badge';
        badge.innerHTML = `${skill} <span class="close">&times;</span>`;
        
        badge.querySelector('.close').addEventListener('click', function() {
          const updatedSkills = skillsInput.value
            .split(',')
            .map(s => s.trim())
            .filter(s => s !== skill)
            .join(', ');
          
          skillsInput.value = updatedSkills;
          renderTags();
        });
        
        skillsTags.appendChild(badge);
      });
    }
    
    // Initial render
    renderTags();
    
    // Update on input change
    skillsInput.addEventListener('input', renderTags);
  });
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>