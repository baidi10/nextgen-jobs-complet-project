<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Job.php';

// Admin authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: /pages/public/login.php');
    exit;
}

$job = new Job();
$currentPage = max(1, $_GET['page'] ?? 1);
$perPage = 25;
$statusFilter = $_GET['status'] ?? 'all';
$dateFrom = $_GET['dateFrom'] ?? '';
$dateTo = $_GET['dateTo'] ?? '';

// Validate dates
if (!empty($dateFrom) && !empty($dateTo)) {
    if (strtotime($dateFrom) > strtotime($dateTo)) {
        $_SESSION['error'] = "From date cannot be later than To date";
        header('Location: manage-jobs.php');
        exit;
    }
}

$jobs = $job->getAllJobs($currentPage, $perPage, $statusFilter, '', $dateFrom, $dateTo);
$totalJobs = $job->countAllJobs($statusFilter, '', $dateFrom, $dateTo);

$pageTitle = "Manage Jobs - JOBEST";
include __DIR__ . '/../../includes/header.php';
?>

<div class="dashboard-container">
  <div class="container py-5">
    <style>
      /* Custom Select Styling */
      .form-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236c757d' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 16px 12px;
        padding-right: 2.5rem;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
      }

      .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
      }

      .form-select:hover {
        border-color: #adb5bd;
      }

      /* Custom Date Input Styling */
      .form-control[type="date"] {
        padding-right: 0.75rem;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
      }

      .form-control[type="date"]:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
      }

      .form-control[type="date"]:hover {
        border-color: #adb5bd;
      }

      /* Form Label Styling */
      .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 0.5rem;
      }

      /* Filter Card Styling */
      .filter-card {
        background-color: #fff;
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
      }

      .filter-card .card-body {
        padding: 1.5rem;
      }

      /* Button Styling */
      .btn-filter {
        background-color:rgb(0, 0, 0);
        border-color:rgb(0, 0, 0);
        color: #fff;
        transition: all 0.2s ease-in-out;
      }

      .btn-filter:hover {
        background-color: #0b5ed7;
        border-color: #0a58ca;
        transform: translateY(-1px);
      }

      .btn-reset {
        background-color: #f8f9fa;
        border-color: #dee2e6;
        color: #212529;
        transition: all 0.2s ease-in-out;
      }

      .btn-reset:hover {
        background-color: #e9ecef;
        border-color: #dee2e6;
        transform: translateY(-1px);
      }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3 fw-bold">Job Management</h1>
    </div>

    <!-- Date Filter Section -->
    <div class="card border-0 shadow-sm mb-4 filter-card">
      <div class="card-body">
        <form method="GET" action="" class="row g-3 align-items-end">
          <div class="col-md-3">
            <label for="dateFrom" class="form-label">From Date</label>
            <input type="date" id="dateFrom" name="dateFrom" class="form-control" 
                   value="<?= htmlspecialchars($_GET['dateFrom'] ?? '') ?>">
          </div>
          
          <div class="col-md-3">
            <label for="dateTo" class="form-label">To Date</label>
            <input type="date" id="dateTo" name="dateTo" class="form-control" 
                   value="<?= htmlspecialchars($_GET['dateTo'] ?? '') ?>">
          </div>
          
          <div class="col-md-2">
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-select">
              <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All Statuses</option>
              <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
              <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
              <option value="expired" <?= $statusFilter === 'expired' ? 'selected' : '' ?>>Expired</option>
            </select>
          </div>

          <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100 btn-filter">
              <i class="bi bi-filter"></i> Filter
            </button>
          </div>

          <div class="col-md-2">
            <a href="manage-jobs.php" class="btn btn-outline-secondary w-100 btn-reset">
              <i class="bi bi-arrow-counterclockwise"></i> Reset
            </a>
          </div>
        </form>
      </div>
    </div>

    <!-- Results Summary -->
    <?php if (!empty($_GET['dateFrom']) || !empty($_GET['dateTo']) || $statusFilter !== 'all'): ?>
      <div class="alert alert-info mb-4">
        <i class="bi bi-info-circle"></i>
        Showing <?= count($jobs) ?> of <?= $totalJobs ?> jobs
        <?php if (!empty($_GET['dateFrom']) || !empty($_GET['dateTo'])): ?>
          posted between 
          <?= !empty($_GET['dateFrom']) ? date('M d, Y', strtotime($_GET['dateFrom'])) : 'any time' ?>
          and
          <?= !empty($_GET['dateTo']) ? date('M d, Y', strtotime($_GET['dateTo'])) : 'now' ?>
        <?php endif; ?>
        <?php if ($statusFilter !== 'all'): ?>
          with status "<?= ucfirst($statusFilter) ?>"
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="bg-light">
              <tr>
                <th>Title</th>
                <th>Company</th>
                <th>Location</th>
                <th>Status</th>
                <th>Applications</th>
                <th>Posted</th>
                <th>View</th>
                <th>Edit</th>
                <th>Delete</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($jobs as $job): ?>
                <tr>
                  <td><?= htmlspecialchars($job['title']) ?></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <img src="<?= Config::BASE_URL ?>/assets/uploads/company_logos/<?= $job['company_logo'] ?>" 
                           class="rounded-circle" width="24" height="24">
                      <?= htmlspecialchars($job['company_name']) ?>
                    </div>
                  </td>
                  <td><?= htmlspecialchars($job['location']) ?></td>
                  <td>
                    <span class="badge bg-<?= statusBadgeColor($job['status']) ?>">
                      <?= ucfirst($job['status']) ?>
                    </span>
                  </td>
                  <td><?= number_format($job['application_count']) ?></td>
                  <td><?= timeElapsed($job['created_at']) ?></td>
                  <td>
                    <a href="view-job.php?id=<?= $job['id'] ?>" 
                       class="btn btn-sm btn-outline-primary">
                      <i class="bi bi-eye"></i>
                    </a>
                  </td>
                  <td>
                    <a href="edit-job.php?id=<?= $job['id'] ?>" 
                       class="btn btn-sm btn-outline-warning">
                      <i class="bi bi-pencil"></i>
                    </a>
                  </td>
                  <td>
                    <a href="delete-job.php?id=<?= $job['id'] ?>" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal-<?= $job['id'] ?>">
                      <i class="bi bi-trash"></i>
                    </a>

                    <!-- Delete Modal -->
                    <div class="modal fade" id="deleteModal-<?= $job['id'] ?>" tabindex="-1">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Confirm Delete</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                            <p>Are you sure you want to delete the job "<?= htmlspecialchars($job['title']) ?>"?</p>
                            <p class="text-danger mb-0">This action cannot be undone.</p>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <a href="delete-job.php?id=<?= $job['id'] ?>" class="btn btn-danger">Delete Job</a>
                          </div>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <?php if (empty($jobs)): ?>
          <div class="text-center py-5">
            <p class="text-muted">No jobs found</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Pagination -->
    <?php if ($totalJobs > $perPage): ?>
      <nav class="mt-4">
        <?php include __DIR__ . '/../../includes/pagination.php'; ?>
      </nav>
    <?php endif; ?>
  </div>
</div>

<script>
function toggleFeatured(jobId) {
    fetch(`/admin/toggle-featured.php?id=${jobId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) window.location.reload();
        });
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>