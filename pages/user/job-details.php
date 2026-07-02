<?php
require_once __DIR__ . '/../../includes/dependencies.php';

if (!isset($_GET['id']) ){
    header("Location: " . Config::BASE_URL . "/pages/user/jobs.php");
    exit;
}

// Authentication check for job seekers
if (!isLoggedIn() || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'jobSeeker') {
    header('Location: ' . Config::BASE_URL . '/pages/public/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$job = new Job();
$jobDetails = $job->getJobById($_GET['id']);
$jobSkills = $job->getJobSkills($_GET['id']);

// Redirect if job not found
if (!$jobDetails) {
    header('Location: ' . Config::BASE_URL . '/pages/user/jobs.php?status=error&message=' . urlencode('Job not found.'));
    exit;
}

$pageTitle = $jobDetails['jobTitle'] . " | JOBEST";
include __DIR__ . '/../../includes/header.php';
?>

<div class="container py-5">
  <div class="row g-4">
    <!-- Main Content -->
    <div class="col-lg-8">
      <!-- Job Header -->
      <div class="mb-5">
          <div class="d-flex align-items-start gap-4">
          <?php if (!empty($jobDetails['companyLogo'])): ?>
            <div class="company-logo bg-white p-3 border rounded shadow-sm">
            <img src="<?= Config::BASE_URL ?>/assets/uploads/company_logos/<?= htmlspecialchars($jobDetails['companyLogo']) ?>" 
                 alt="<?= htmlspecialchars($jobDetails['companyName']) ?>" 
                  width="70" height="70" style="object-fit: contain;">
            </div>
          <?php else: ?>
            <div class="company-placeholder d-flex align-items-center justify-content-center bg-light rounded shadow-sm" style="width:70px;height:70px">
              <span class="fw-bold fs-4"><?= substr(htmlspecialchars($jobDetails['companyName']), 0, 2) ?></span>
            </div>
          <?php endif; ?>
          
            <div class="flex-grow-1">
            <h1 class="h2 fw-bold mb-1"><?= htmlspecialchars($jobDetails['jobTitle']) ?></h1>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
              <a href="<?= Config::BASE_URL ?>/pages/user/company-details.php?id=<?= $jobDetails['companyId'] ?>" class="fw-medium text-decoration-none text-muted"><?= htmlspecialchars($jobDetails['companyName']) ?></a>
              <span class="text-muted">•</span>
            
                <i class="bi bi-geo-alt me-1"></i>
                <?= htmlspecialchars($jobDetails['location']) ?>
            
                <?php if ($jobDetails['isRemote']): ?>
                  <span class="text-muted">•</span>
                <span class="badge bg-light text-dark border">Remote</span>
              <?php endif; ?>
            </div>
            
            <div class="d-flex flex-wrap gap-3 mt-3">
              <?php if ($jobDetails['isSalaryVisible']): ?>
                <div class="d-flex align-items-center">
                  <i class="bi bi-cash-coin me-2 text-success"></i>
                  <span class="fw-medium">
                    $<?= number_format($jobDetails['salaryMin']) ?> - $<?= number_format($jobDetails['salaryMax']) ?>
                  </span>
                </div>
                <?php endif; ?>
              
              <div class="d-flex align-items-center">
                <i class="bi bi-briefcase me-2"></i>
                <span><?= htmlspecialchars($jobDetails['jobType']) ?></span>
              </div>
              
              <div class="d-flex align-items-center">
                <i class="bi bi-calendar-event me-2"></i>
                <span>Posted <?= date('M j', strtotime($jobDetails['createdAt'])) ?></span>
              </div>
            </div>
          </div>
        </div>
        
        <div class="mt-4 d-flex gap-2">
          <a href="#apply-section" class="btn btn-dark rounded-pill px-5 py-2 fw-medium">
            Apply Now
          </a>
          <?php if (isLoggedIn() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'jobSeeker'): ?>
            <button class="btn btn-outline-dark rounded-pill px-4 py-2 save-job-btn" data-job-id="<?= $jobDetails['jobId'] ?>">
              <i class="bi bi-bookmark me-1"></i> Save
            </button>
          <?php else: // This case should technically not be reached due to the auth check at the top, but kept for redundancy ?>
            <a href="<?= Config::BASE_URL ?>/pages/public/login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-outline-dark rounded-pill px-4 py-2">
              <i class="bi bi-bookmark me-1"></i> Save
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Skills Tags -->
      <?php if (!empty($jobSkills)): ?>
        <div class="my-4">
          <h5 class="fw-bold mb-3">Skills</h5>
          <div class="d-flex flex-wrap gap-2 mb-3">
            <?php foreach ($jobSkills as $skill): ?>
              <span class="badge bg-light text-dark border px-3 py-2"><?= htmlspecialchars($skill) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Job Content -->
      <div class="job-content-wrapper bg-white p-4 p-lg-5 rounded border shadow-sm mb-5">
        <h2 class="h5 fw-bold mb-4">About the role</h2>
        <div class="job-content mb-5">
            <?= parsedown($jobDetails['jobDescription']) ?>
          </div>

          <h2 class="h5 fw-bold mt-5 mb-4">Requirements</h2>
        <div class="job-content mb-5">
            <?= parsedown($jobDetails['jobRequirements']) ?>
        </div>
        
        <?php if (!empty($jobDetails['jobBenefits'])): ?>
          <h2 class="h5 fw-bold mt-5 mb-4">Benefits</h2>
          <div class="job-content mb-5">
            <?= parsedown($jobDetails['jobBenefits']) ?>
      </div>
        <?php endif; ?>

        <!-- Apply Section -->
        <div id="apply-section" class="apply-section mt-5 pt-5 border-top">
          <h2 class="h4 fw-bold mb-4">Apply for this position</h2>
          
          <?php if (!empty($jobDetails['applicationUrl'])): ?>
            <a href="<?= htmlspecialchars($jobDetails['applicationUrl']) ?>" 
               class="btn btn-dark rounded-pill px-5 py-3 fw-medium" target="_blank">
              Apply on Company Website
            </a>
          <?php elseif (isLoggedIn() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'jobSeeker'): ?>
            <form method="POST" action="<?= Config::BASE_URL ?>/pages/user/apply.php" class="apply-form" enctype="multipart/form-data">
              <input type="hidden" name="job_id" value="<?= $jobDetails['jobId'] ?>">
              
              <div class="mb-4">
                <label class="form-label fw-medium">Cover Letter / Notes (Optional)</label>
                <textarea name="cover_letter" class="form-control" rows="4" placeholder="Introduce yourself and explain why you're a great fit for this role..."></textarea>
              </div>
              
              <div class="mb-4">
                <label class="form-label fw-medium">Resume</label>
                <div class="input-group">
                  <input type="file" class="form-control" id="resume" name="resume">
                  <label class="input-group-text" for="resume">Upload</label>
                </div>
                <div class="form-text">PDF, DOC or DOCX (Max 5MB)</div>
              </div>
              
              <input type="submit" class="btn btn-dark rounded-pill px-5 py-2 fw-medium" value="Submit Application">
            </form>
          <?php else: // This case should technically not be reached due to the auth check at the top, but kept for redundancy ?>
            <div class="alert alert-info p-4 d-flex align-items-center">
              <i class="bi bi-info-circle-fill fs-4 me-3"></i>
              <div>
                <p class="mb-2 fw-medium">Please log in to apply for this job</p>
                <p class="mb-3 small">Create an account or log in to submit your application</p>
                <div>
                  <a href="<?= Config::BASE_URL ?>/pages/public/login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-primary me-2">Log In</a>
                  <a href="<?= Config::BASE_URL ?>/pages/public/register.php" class="btn btn-outline-primary">Create Account</a>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
      <div class="sticky-top" style="top: 100px;">
        <!-- Company Card -->
        <div class="bg-white p-4 rounded border shadow-sm mb-4">
          <h3 class="h5 fw-bold mb-3">About the company</h3>
          
          <p class="text-muted mb-4"><?= htmlspecialchars($jobDetails['companyDescription'] ?? 'No company description available.') ?></p>
          
          <div class="company-meta">
            <?php if (!empty($jobDetails['companySize'])): ?>
              <div class="d-flex align-items-center mb-3">
                <div class="icon-bg bg-light p-2 rounded-circle me-3">
                  <i class="bi bi-people text-dark"></i>
                </div>
                <div>
                  <div class="text-muted small">Company size</div>
                  <div class="fw-medium"><?= htmlspecialchars($jobDetails['companySize']) ?> employees</div>
                </div>
              </div>
            <?php endif; ?>
            
            <?php if (!empty($jobDetails['companyFounded'])): ?>
              <div class="d-flex align-items-center mb-3">
                <div class="icon-bg bg-light p-2 rounded-circle me-3">
                  <i class="bi bi-calendar-check text-dark"></i>
                </div>
                <div>
                  <div class="text-muted small">Founded</div>
                  <div class="fw-medium"><?= htmlspecialchars($jobDetails['companyFounded']) ?></div>
                </div>
              </div>
            <?php endif; ?>
            
            <?php if (!empty($jobDetails['companySector'])): ?>
              <div class="d-flex align-items-center mb-3">
                <div class="icon-bg bg-light p-2 rounded-circle me-3">
                  <i class="bi bi-briefcase text-dark"></i>
                </div>
                <div>
                  <div class="text-muted small">Industry</div>
                  <div class="fw-medium"><?= htmlspecialchars($jobDetails['companySector']) ?></div>
                </div>
              </div>
            <?php endif; ?>
          </div>
          
          <div class="d-flex gap-2 flex-wrap mt-4">
            <?php if (!empty($jobDetails['companyWebsite'])): ?>
                <a href="<?= htmlspecialchars($jobDetails['companyWebsite']) ?>" 
                 class="btn btn-sm btn-outline-dark rounded-pill px-3" target="_blank">
                <i class="bi bi-globe me-1"></i> Website
              </a>
            <?php endif; ?>
            
            <?php if (!empty($jobDetails['companyLinkedIn'])): ?>
              <a href="<?= htmlspecialchars($jobDetails['companyLinkedIn']) ?>" 
                 class="btn btn-sm btn-outline-dark rounded-pill px-3" target="_blank">
                <i class="bi bi-linkedin me-1"></i> LinkedIn
                </a>
              <?php endif; ?>
            
            <a href="<?= Config::BASE_URL ?>/pages/user/company-details.php?id=<?= $jobDetails['companyId'] ?>" 
               class="btn btn-sm btn-outline-dark rounded-pill px-3">
              <i class="bi bi-building me-1"></i> More jobs
            </a>
          </div>
        </div>

        <!-- Similar Jobs -->
        <div class="bg-white p-4 rounded border shadow-sm">
          <h3 class="h5 fw-bold mb-3">Similar jobs</h3>
          <div class="similar-jobs">
            <p class="text-muted small mb-3">Find more jobs like this by exploring our job board.</p>
            <a href="<?= Config::BASE_URL ?>/pages/user/jobs.php" class="btn btn-sm btn-outline-dark rounded-pill px-3">
              Browse all jobs <i class="bi bi-arrow-right ms-1"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Add custom styles for job-details page -->
<style>
  .job-content h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 1.5rem 0 1rem;
  }
  
  .job-content h4 {
    font-size: 1.1rem;
    font-weight: 600;
    margin: 1.2rem 0 0.8rem;
  }
  
  .job-content p {
    margin-bottom: 1rem;
    color: #4a4a4a;
  }
  
  .job-content ul {
    margin-bottom: 1.5rem;
    padding-left: 1.5rem;
  }
  
  .job-content li {
    margin-bottom: 0.5rem;
    color: #4a4a4a;
  }

  .icon-bg {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  /* Fix sticky sidebar positioning */
  .sticky-top {
    position: sticky;
    top: 100px !important;
    z-index: 1;
  }

  /* Add spacing between elements */
  .mb-5 {
    margin-bottom: 20px !important;
  }

  .mt-4 {
    margin-top: 20px !important;
  }

  .mt-5 {
    margin-top: 20px !important;
  }

  .mb-4 {
    margin-bottom: 20px !important;
  }

  .my-4 {
    margin-top: 20px !important;
    margin-bottom: 20px !important;
  }

  .gap-4 {
    gap: 20px !important;
  }

  .gap-3 {
    gap: 20px !important;
  }

  .gap-2 {
    gap: 20px !important;
  }

  .py-5 {
    padding-top: 20px !important;
    padding-bottom: 20px !important;
  }

  .pt-5 {
    padding-top: 20px !important;
  }

  .pb-5 {
    padding-bottom: 20px !important;
  }

  .p-4 {
    padding: 20px !important;
  }

  .p-3 {
    padding: 20px !important;
  }

  .p-lg-5 {
    padding: 20px !important;
  }

  .border-top {
    margin-top: 20px !important;
    padding-top: 20px !important;
  }

  .d-flex {
    margin-bottom: 20px !important;
  }

  .d-flex:last-child {
    margin-bottom: 0 !important;
  }

  .company-meta > div {
    margin-bottom: 20px !important;
  }

  .company-meta > div:last-child {
    margin-bottom: 0 !important;
  }

  .similar-jobs {
    margin-top: 20px !important;
  }

  .apply-section {
    margin-top: 20px !important;
    padding-top: 20px !important;
  }

  .form-group {
    margin-bottom: 20px !important;
  }

  .alert {
    margin-bottom: 20px !important;
  }
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?> 