<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Job.php';

// Authentication check
if (!isLoggedIn() || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'jobSeeker') {
    header('Location: ' . Config::BASE_URL . '/pages/public/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$job = new Job();
$currentPage = max(1, $_GET['page'] ?? 1);
$perPage = 12;

$savedJobs = $job->getSavedJobs($_SESSION['user_id'], $perPage, ($currentPage - 1) * $perPage);
$totalSaved = $job->countSavedJobs($_SESSION['user_id']);

$pageTitle = "Saved Jobs - JOBEST";
include __DIR__ . '/../../includes/header.php';
?>

<div class="dashboard-container">
  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
      <h1 class="h3 fw-bold">Saved Jobs</h1>
      <div class="text-muted">
        <?= number_format($totalSaved) ?> saved jobs
      </div>
    </div>

    <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['message'] ?? 'Operation successful') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif (isset($_GET['status']) && $_GET['status'] === 'error'): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['message'] ?? 'An error occurred') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
      <?php if (!empty($savedJobs)): ?>
        <?php foreach ($savedJobs as $job): ?>
          <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-body">
                <div class="d-flex align-items-start gap-3 mb-3">
                  <img src="<?= Config::BASE_URL ?>/assets/uploads/company_logos/<?= htmlspecialchars($job['companyLogo']) ?>" 
                       alt="<?= htmlspecialchars($job['companyName']) ?>" 
                       class="rounded-circle" width="48" height="48">
                  <div class="flex-grow-1">
                    <h3 class="h6 fw-bold mb-0">
                      <a href="<?= Config::BASE_URL ?>/pages/user/jobs.php?id=<?= $job['jobId'] ?>" 
                         class="text-decoration-none">
                        <?= htmlspecialchars($job['jobTitle']) ?>
                      </a>
                    </h3>
                    <p class="text-muted small mb-0">
                      <?= htmlspecialchars($job['companyName']) ?>
                    </p>
                  </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                  <div class="text-muted small">
                    <?= timeElapsed($job['createdAt']) ?>
                  </div>
                  <form action="<?= Config::BASE_URL ?>/actions/unsave_job.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to unsave this job?');">
                      <input type="hidden" name="jobId" value="<?= $job['jobId'] ?>">
                      <button type="submit" class="btn btn-link text-danger p-0">
                          <i class="bi bi-trash"></i>
                      </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12">
          <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
              <p class="text-muted">No saved jobs</p>
              <a href="<?= Config::BASE_URL ?>/pages/user/jobs.php" class="btn btn-primary">
                Browse Jobs
              </a>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalSaved > $perPage): ?>
      <nav class="mt-4">
        <?php include __DIR__ . '/../../includes/pagination.php'; ?>
      </nav>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>