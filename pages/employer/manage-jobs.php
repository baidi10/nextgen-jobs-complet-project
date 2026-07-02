<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Company.php';
require_once __DIR__ . '/../../classes/Job.php';

// Authentication check using database functions
if (!isLoggedIn() || !isEmployer()) {
    header('Location: /pages/public/login.php');
    exit;
}

$company = new Company();
$job = new Job();
$userId = $_SESSION['user_id'];
$companyId = $company->getCompanyIdByUser($userId);

$statusFilter = $_GET['status'] ?? 'active';
// Removed searchQuery variable
$currentPage = max(1, $_GET['page'] ?? 1);
$perPage = 10;

$jobs = $job->getPostedJobsForEmployer($companyId, $statusFilter, '', $perPage, ($currentPage - 1) * $perPage);
$totalJobs = $job->countPostedJobsForEmployer($companyId, $statusFilter, '');

// Check for success or error messages
$successMessage = $_SESSION['success_message'] ?? null;
$errorMessage = $_SESSION['error_message'] ?? null;

// Clear the messages from session after retrieving them
unset($_SESSION['success_message'], $_SESSION['error_message']);

$pageTitle = "Manage Jobs - JOBEST";

// Add CSS to hide duplicate navigation if it exists
$customCSS = "<style>
.duplicate-nav, .duplicate-navigation, .employer-top-nav {
    display: none !important;
}

/* Enhanced table styling */
.table th {
    font-weight: 600;
    border-top: none;
}

.table td, .table th {
    padding: 1rem;
    vertical-align: middle;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.04);
}

/* Button styling */
.action-btn {
    transition: all 0.2s;
    border-radius: 50px;
    padding: 0.375rem 1rem;
    font-weight: 500;
}

.action-btn:hover {
    transform: translateY(-1px);
}

.action-btn-edit {
    color: #0d6efd;
    border-color: #0d6efd;
}

.action-btn-edit:hover {
    background-color: #0d6efd;
    color: white;
}

.action-btn-delete {
    color: #dc3545;
    border-color: #dc3545;
}

.action-btn-delete:hover {
    background-color: #dc3545;
    color: white;
}

/* Removed search section styling */

.status-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 10px;
    display: inline-block;
}

.status-dot-active {
    background-color: #198754;
}

.status-dot-closed {
    background-color: #6c757d;
}

.status-dot-all {
    background-color: #0d6efd;
}

.dropdown-menu {
    border-radius: 8px;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    overflow: hidden;
    border: none;
}

.dropdown-item {
    padding: 0.6rem 1.25rem;
}

.dropdown-item.active,
.dropdown-item:active {
    background-color: #0d6efd;
}

/* Enhanced search and filter styling */
.search-filter-container {
    background-color: #f8f9fa;
    border-radius: 10px;
    padding: 1.25rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.03);
}

.search-input {
    border-radius: 50px;
    padding-left: 1rem;
    border: 1px solid #dee2e6;
    box-shadow: none;
    transition: all 0.2s;
    height: 48px;
}

.search-input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13,110,253,0.15);
}

.search-btn {
    border-radius: 0 50px 50px 0;
    padding: 0.5rem 1.25rem;
    border-left: none;
}

.filter-dropdown .btn {
    border-radius: 50px;
    padding: 0.5rem 1.25rem;
    height: 48px;
    border: 1px solid #dee2e6;
    background-color: white;
    color: #495057;
    font-weight: 500;
    min-width: 200px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.filter-dropdown .btn::after {
    margin-left: 0.75rem;
}

.filter-dropdown .btn:hover,
.filter-dropdown .btn:focus {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.filter-dropdown .dropdown-menu {
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    border: none;
    padding: 0.5rem;
}

.filter-dropdown .dropdown-item {
    border-radius: 6px;
    padding: 0.5rem 1rem;
    margin-bottom: 2px;
}

.filter-dropdown .dropdown-item.active,
.filter-dropdown .dropdown-item:active {
    background-color: #0d6efd;
}

/* All search styling removed */
</style>";

// Add custom CSS to head
$pageStyles = [$customCSS];

include __DIR__ . '/../../includes/header.php';
?>

<div class="dashboard-container">
  <div class="container py-5">
    <!-- Display success or error messages -->
    <?php if ($successMessage): ?>
      <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
          <i class="bi bi-check-circle-fill me-2"></i>
          <div><?= htmlspecialchars($successMessage) ?></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
    
    <?php if ($errorMessage): ?>
      <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
          <i class="bi bi-exclamation-circle-fill me-2"></i>
          <div><?= htmlspecialchars($errorMessage) ?></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
  
    <div class="d-flex justify-content-between align-items-center mb-4">
   
      <a href="post-job.php" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Post New Job
      </a>
    </div>
    
    <!-- Removed search section -->

    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <?php if (!empty($jobs)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
              <tr>
                  <th class="ps-4">Job Title</th>
                <th>Location</th>
                <th>Applications</th>
                <th>Posted</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($jobs as $job): ?>
                <tr>
                    <td class="ps-4">
                    <a href="<?= Config::BASE_URL ?>/pages/public/job-details.php?id=<?= $job['jobId'] ?>" 
                         class="fw-medium text-decoration-none text-dark">
                      <?= htmlspecialchars($job['title']) ?>
                    </a>
                  </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <i class="bi bi-geo-alt text-muted me-2"></i>
                        <?= htmlspecialchars($job['location']) ?>
                        <?php if ($job['isRemote'] ?? false): ?>
                          <span class="badge bg-light text-dark ms-2">Remote</span>
                        <?php endif; ?>
                      </div>
                    </td>
                    <td>
                      <span class="badge bg-light text-dark rounded-pill px-3">
                        <?= number_format($job['applicationCount']) ?>
                      </span>
                    </td>
                    <td><?= timeElapsed($job['createdAt']) ?></td>
                    <td class="text-end pe-4">
                      <div class="d-flex gap-2 justify-content-end">
                      <a href="post-job.php?edit=<?= $job['jobId'] ?>" 
                           class="btn btn-sm btn-outline-primary action-btn action-btn-edit">
                          <i class="bi bi-pencil me-1"></i> Edit
                      </a>
                        <a href="delete-job.php?id=<?= $job['jobId'] ?>" 
                           class="btn btn-sm btn-outline-danger action-btn action-btn-delete">
                          <i class="bi bi-trash me-1"></i> Delete
                        </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php else: ?>
          <div class="text-center py-5">
            <div class="mb-3">
              <i class="bi bi-clipboard-x fs-1 text-muted"></i>
            </div>
            <p class="text-muted mb-4">No jobs found</p>
            <a href="post-job.php" class="btn btn-primary rounded-pill px-4">
              <i class="bi bi-plus-lg me-2"></i> Post New Job
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Pagination -->
    <?php if ($totalJobs > $perPage): ?>
      <nav class="mt-4 d-flex justify-content-center">
        <?php include __DIR__ . '/../../includes/pagination.php'; ?>
      </nav>
    <?php endif; ?>
  </div>
</div>

<!-- Delete Job Modals -->
<?php foreach ($jobs as $job): ?>
  <div class="modal fade" id="deleteModal-<?= $job['jobId'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Delete Job</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete <strong><?= htmlspecialchars($job['title']) ?></strong>?</p>
          <p class="text-danger mb-0">This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <a href="delete-job.php?id=<?= $job['jobId'] ?>" class="btn btn-danger">Delete</a>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?> 