<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Company.php';

// Authentication check using database functions
if (!isLoggedIn() || !isEmployer()) {
    header('Location: /pages/public/login.php');
    exit;
}

$company = new Company();
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

$companyData = $company->getCompanyProfile($companyId);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['logo_only']) && !empty($_FILES['logo']['name'])) {
            $logoPath = $company->uploadLogo($_FILES['logo']);
            $company->updateCompanyProfile($companyId, ['logo' => $logoPath]);
            $success = "Company logo updated successfully";
            $companyData = $company->getCompanyProfile($companyId); // Refresh data
        } else {
        $updateData = [
            'companyName' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'websiteUrl' => $_POST['website'] ?? '',
            'industry' => $_POST['industry'] ?? '',
            'employeeCount' => $_POST['size'] ?? '',
            'foundedYear' => $_POST['founded_year'] ?? null,
            'headquarters' => $_POST['location'] ?? '',
        ];
        if (!empty($_FILES['logo']['name'])) {
            $logoPath = $company->uploadLogo($_FILES['logo']);
            $updateData['logo'] = $logoPath;
        }
        $company->updateCompanyProfile($companyId, $updateData);
        $success = "Company profile updated successfully";
        $companyData = $company->getCompanyProfile($companyId); // Refresh data
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_logo'])) {
    try {
        $company->updateCompanyProfile($companyId, ['logo' => 'default.png']);
        $success = "Company logo removed successfully";
        $companyData = $company->getCompanyProfile($companyId); // Refresh data
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = "Company Profile - JOBEST";

include __DIR__ . '/../../includes/header.php';
?>

<style>
.company-profile-logo {
    width: 200px !important;
    height: 200px !important;
    object-fit: cover;
    transition: all 0.3s ease;
    border: 4px solid rgb(23, 237, 52) !important;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1) !important;
}

.logo-upload-btn {
    background-color: rgb(62, 62, 62);
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.logo-upload-btn:hover {
    transform: scale(1.1);
    background-color: #1a9e3a;
}

.logo-upload-btn i {
    font-size: 24px;
}

.jobs-button {
    padding: 12px 24px !important;
    font-size: 16px !important;
    font-weight: 600 !important;
    border-radius: 8px !important;
    transition: all 0.3s ease !important;
    text-decoration: none !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    background-color:rgb(0, 0, 0) !important;
    color: white !important;
    border: none !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
}

.jobs-button:hover {
    background-color:rgb(48, 50, 53) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2) !important;
    color: white !important;
}

.jobs-button i {
    font-size: 18px !important;
}

.company-info-section, .company-stats-section, .company-description-section {
    background: white;
    border-radius: 10px;
    padding: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.stat-card {
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.1);
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.description-box {
    min-height: 100px;
    border: 1px solid rgba(0,0,0,0.1);
    line-height: 1.6;
}

.company-links .btn {
    transition: all 0.3s ease;
}

.company-links .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>

<div class="dashboard-container bg-light min-vh-100 py-5">
  <div class="container">
    <div class="mb-4">
      <h1 class="h3 fw-bold">Company Profile</h1>
      <p class="text-muted">Manage your company information</p>
    </div>
    <div class="row g-4">
      <div class="col-lg-5 col-md-6 mb-4">
        <div class="card border-0 shadow-sm profile-card h-100 p-4">
          <div class="card-body text-center p-0">
            <div class="d-flex align-items-center mb-4">
              <div class="position-relative me-4">
                <img src="<?= Config::BASE_URL ?>/assets/uploads/company_logos/<?= htmlspecialchars($companyData['logo'] ?? 'default.png') ?>"
                   class="rounded-circle mb-3 company-profile-logo" alt="<?= htmlspecialchars($companyData['companyName'] ?? '') ?>">
                <form method="POST" enctype="multipart/form-data" id="logoUploadForm" style="position: absolute; bottom: 0; right: 0; display:inline;">
                    <input type="hidden" name="logo_only" value="1">
                    <input type="file" name="logo" id="logoInput" accept="image/png, image/jpeg, image/jpg, image/gif, image/webp" style="display:none;" required>
                    <label for="logoInput" class="logo-upload-btn" title="Change company photo" style="cursor:pointer;">
                  <i class="bi bi-camera"></i>
                    </label>
                </form>
              </div>
              <div class="ms-auto" style="padding-left: 20px;">
                <h2 class="h4 fw-bold mb-1 text-end"><?= htmlspecialchars($companyData['companyName'] ?? '') ?></h2>
                <p class="text-muted mb-3 text-end"><?= htmlspecialchars($companyData['industry'] ?? '') ?></p>
              </div>
            </div>
            <div class="d-flex justify-content-center mb-3">
              <?php if (!empty($companyData['websiteUrl'])): ?>
              <a href="<?= htmlspecialchars($companyData['websiteUrl'] ?? '') ?>" class="btn btn-sm btn-outline-secondary me-2" target="_blank">
                <i class="bi bi-globe me-1"></i>Website
              </a>
              <?php endif; ?>
              <a href="/nextgen-jobs/pages/employer/manage-jobs.php" class="jobs-button">
                <i class="bi bi-briefcase"></i>Manage Jobs
              </a>
            </div>
            <div class="text-start mt-4">
              <div class="company-info-section mb-4">
                <h5 class="fw-bold mb-3">Company Overview</h5>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted"><i class="bi bi-calendar3 me-2"></i>Founded:</span>
                  <span class="fw-medium"><?= htmlspecialchars($companyData['foundedYear'] ?? 'N/A') ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted"><i class="bi bi-people me-2"></i>Team Size:</span>
                  <span class="fw-medium"><?= htmlspecialchars($companyData['employeeCount'] ?? 'N/A') ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted"><i class="bi bi-geo-alt me-2"></i>Location:</span>
                  <span class="fw-medium"><?= htmlspecialchars($companyData['headquarters'] ?? '') ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted"><i class="bi bi-building me-2"></i>Industry:</span>
                  <span class="fw-medium"><?= htmlspecialchars($companyData['industry'] ?? 'N/A') ?></span>
                </div>
              </div>

              <div class="company-stats-section mb-4">
                <h5 class="fw-bold mb-3">Company Statistics</h5>
                <div class="row g-3">
                  <div class="col-6">
                    <div class="stat-card p-3 bg-light rounded">
                      <div class="text-muted small mb-1">Active Jobs</div>
                      <div class="h4 mb-0 fw-bold"><?= $company->getActiveJobsCount($companyId) ?? '0' ?></div>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="stat-card p-3 bg-light rounded">
                      <div class="text-muted small mb-1">Total Applications</div>
                      <div class="h4 mb-0 fw-bold"><?= $company->getTotalApplications($companyId) ?? '0' ?></div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="company-description-section">
                <h5 class="fw-bold mb-3">About Company</h5>
                <div class="description-box p-3 bg-light rounded">
                  <p class="mb-0 text-muted">
                    <?= !empty($companyData['description']) ? nl2br(htmlspecialchars($companyData['description'])) : 'No company description available.' ?>
                  </p>
                </div>
              </div>

              <?php if (!empty($companyData['websiteUrl'])): ?>
              <div class="company-links mt-4">
                <h5 class="fw-bold mb-3">Company Links</h5>
                <a href="<?= htmlspecialchars($companyData['websiteUrl']) ?>" class="btn btn-outline-dark w-100 mb-2" target="_blank">
                  <i class="bi bi-globe me-2"></i>Visit Website
                </a>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-7 col-md-6 mb-4">
        <div class="card border-0 shadow-sm profile-card h-100 p-4">
          <div class="card-header bg-white p-0 border-0 mb-4">
            <h3 class="h5 fw-bold mb-0">Edit Company Information</h3>
          </div>
          <div class="card-body p-0">
            <?php if ($error): ?>
              <div class="alert alert-danger m-3"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
              <div class="alert alert-success m-3">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= htmlspecialchars($success) ?>
              </div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
              <!-- Basic Info -->
              <div class="form-section mb-4">
                <h4 class="h6 fw-bold mb-3 section-title">Basic Information</h4>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Company Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" 
                           value="<?= htmlspecialchars($companyData['companyName'] ?? '') ?>" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Industry <span class="text-danger">*</span></label>
                    <select name="industry" class="form-select" required>
                      <option value="">Select Industry</option>
                      <?php foreach ($company->getAllIndustries() as $industry): ?>
                        <option value="<?= htmlspecialchars($industry) ?>" 
                                <?= ($companyData['industry'] ?? '') === $industry ? 'selected' : '' ?>>
                          <?= htmlspecialchars($industry) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>

              <!-- About Company -->
              <div class="form-section mb-4">
                <h4 class="h6 fw-bold mb-3 section-title">About Company</h4>
                <div class="mb-3">
                  <label class="form-label">Company Description</label>
                  <textarea name="description" class="form-control" rows="5" 
                      placeholder="Tell job seekers about your company mission, values, and work culture..."><?= htmlspecialchars($companyData['description'] ?? '') ?></textarea>
                  <div class="form-text">Maximum 1000 characters</div>
                </div>
              </div>

              <!-- Additional Info -->
              <div class="form-section mb-4">
                <h4 class="h6 fw-bold mb-3 section-title">Additional Information</h4>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Website</label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                      <input type="url" name="website" class="form-control" 
                             placeholder="https://example.com"
                             value="<?= htmlspecialchars($companyData['websiteUrl'] ?? '') ?>">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Team Size</label>
                    <select name="size" class="form-select">
                      <option value="">Select Team Size</option>
                      <option value="1-10" <?= ($companyData['employeeCount'] ?? '') === '1-10' ? 'selected' : '' ?>>1-10 employees</option>
                      <option value="11-50" <?= ($companyData['employeeCount'] ?? '') === '11-50' ? 'selected' : '' ?>>11-50 employees</option>
                      <option value="51-200" <?= ($companyData['employeeCount'] ?? '') === '51-200' ? 'selected' : '' ?>>51-200 employees</option>
                      <option value="201-500" <?= ($companyData['employeeCount'] ?? '') === '201-500' ? 'selected' : '' ?>>201-500 employees</option>
                      <option value="501-1000" <?= ($companyData['employeeCount'] ?? '') === '501-1000' ? 'selected' : '' ?>>501-1000 employees</option>
                      <option value="1000+" <?= ($companyData['employeeCount'] ?? '') === '1000+' ? 'selected' : '' ?>>1000+ employees</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Founded Year</label>
                    <input type="number" name="founded_year" class="form-control" 
                           placeholder="YYYY" min="1900" max="<?= date('Y') ?>"
                           value="<?= htmlspecialchars($companyData['foundedYear'] ?? '') ?>">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Headquarters Location</label>
                    <input type="text" name="location" class="form-control" 
                           placeholder="City, Country"
                           value="<?= htmlspecialchars($companyData['headquarters'] ?? '') ?>">
                  </div>
                </div>
              </div>

              <div class="pt-2">
                <button type="submit" class="btn btn-primary px-4">
                  <i class="bi bi-check-lg me-2"></i>Save Changes
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Photo Management Card -->
      <div class="col-lg-5 col-md-6 mb-4">
        <div class="card border-0 shadow-sm profile-card h-100 p-4">
          <div class="card-header bg-white p-0 border-0 mb-4">
            <h3 class="h5 fw-bold mb-0">Company Logo Management</h3>
          </div>
          <div class="card-body p-0">
            <div class="text-center mb-4">
              <div class="position-relative d-inline-block">
                <img src="<?= Config::BASE_URL ?>/assets/uploads/company_logos/<?= htmlspecialchars($companyData['logo'] ?? 'default.png') ?>"
                     class="rounded-circle border border-white shadow mb-3" 
                     style="width: 150px; height: 150px; object-fit: cover;" 
                     alt="<?= htmlspecialchars($companyData['companyName'] ?? '') ?>">
              </div>
              <div class="mt-3">
                <h5 class="fw-bold mb-2">Current Logo</h5>
                <p class="text-muted small mb-3">
                  Recommended size: 400x400 pixels<br>
                  Supported formats: PNG, JPG, GIF, WebP
                </p>
                <?php if ($companyData['logo'] && $companyData['logo'] !== 'default.png'): ?>
                  <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove the company logo?');">
                    <input type="hidden" name="remove_logo" value="1">
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                      <i class="bi bi-trash me-1"></i>Remove Logo
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Logo Upload Modal -->
<div class="modal fade" id="logoModal" tabindex="-1" aria-labelledby="logoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="logoModalLabel">Upload Company Logo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="logo_only" value="1">
        <div class="modal-body">
          <div class="mb-3">
            <label for="logo" class="form-label">Select an image (PNG, JPG)</label>
            <input type="file" name="logo" id="logo" class="form-control" accept="image/png, image/jpeg" required>
            <div class="form-text">Recommended size: 400x400 pixels</div>
          </div>
          <div class="text-center mt-4">
            <img id="logoPreview" src="<?= Config::BASE_URL ?>/assets/uploads/company_logos/<?= htmlspecialchars($companyData['logo'] ?? 'default.png') ?>" 
                 class="rounded-circle company-logo mb-3" alt="Logo Preview">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Upload Logo</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Add script for logo preview -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  var logoInput = document.getElementById('logoInput');
  var logoForm = document.getElementById('logoUploadForm');
  if (logoInput && logoForm) {
    logoInput.addEventListener('change', function() {
      if (this.files && this.files[0]) {
        logoForm.submit();
      }
    });
  }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>