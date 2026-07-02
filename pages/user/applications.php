<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Application.php';
require_once __DIR__ . '/../../classes/Job.php';
require_once __DIR__ . '/../../includes/pagination.php'; // Include pagination helper

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'jobSeeker') {
    header('Location: ' . Config::BASE_URL . '/pages/public/login.php');
    exit;
}

$user = new User();
$application = new Application();
$job = new Job();

$userId = $_SESSION['user_id'];

// Get status filter from URL, default to null (all statuses)
$statusFilter = isset($_GET['status']) && $_GET['status'] !== 'all' ? $_GET['status'] : null;

// Pagination parameters
$perPage = 20; // 20 applications per page
$currentPage = max(1, $_GET['page'] ?? 1); // Get current page from URL, default to 1
$offset = ($currentPage - 1) * $perPage; // Calculate offset

// Get total number of applications for the current filter
$totalApplications = $application->countUserApplications($userId, $statusFilter);
$totalPages = ceil($totalApplications / $perPage); // Calculate total pages

// Get applications for the user with pagination and optional status filter
$applications = $application->getApplicationsForUser($userId, $statusFilter, $perPage, $offset);

$pageTitle = "My Applications - JOBEST";

// Define available statuses for the filter based on database enum
$availableStatuses = ['all', 'pending', 'viewed', 'interviewing', 'hired', 'rejected'];

include __DIR__ . '/../../includes/header.php';
?>

<div class="applications-container">
  <div class="container py-5">
        <h1 class="h3 fw-bold mb-4">My Applications</h1>

        <!-- Status Filter -->
        <div class="mb-4 pb-3 border-bottom">
            <ul class="nav nav-pills flex-wrap gap-2">
                <?php foreach ($availableStatuses as $statusOption): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($statusFilter === $statusOption || ($statusFilter === null && $statusOption === 'all')) ? 'active' : '' ?>"
                           href="?status=<?= $statusOption === 'all' ? 'all' : urlencode($statusOption) ?>">
                        <?= ucfirst($statusOption) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
    </div>

        <!-- Applications List -->
        <?php if (!empty($applications)): ?>
            <div class="list-group list-group-flush">
            <?php foreach ($applications as $app): ?>
                    <div class="list-group-item border-bottom py-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div class="d-flex align-items-center flex-grow-1" style="gap: 15px;">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width: 50px; height: 50px; font-size: 20px; font-weight: bold;">
                                    <i class="bi bi-briefcase text-muted"></i>
                                </div>
                                <div class="flex-grow-1">
                    <h3 class="h6 fw-bold mb-1">
                                        <a href="<?= Config::BASE_URL ?>/pages/user/job-details.php?id=<?= $app['jobId'] ?>"
                         class="text-decoration-none">
                        <?= htmlspecialchars($app['jobTitle']) ?>
                      </a>
                    </h3>
                                    <p class="text-muted small mb-0">
                                        <?= htmlspecialchars($app['companyName']) ?> • Applied <?= timeElapsed($app['createdAt']) ?>
                                    </p>
                    </div>
                  </div>
                            <div class="d-flex align-items-center flex-shrink-0" style="gap: 10px;">
                                <span class="badge bg-<?= statusColor($app['status']) ?> px-3 py-2">
                      <?= ucfirst($app['status']) ?>
                    </span>
                                <a href="<?= Config::BASE_URL ?>/pages/user/job-details.php?id=<?= $app['jobId'] ?>"
                                   class="btn btn-outline-dark rounded-pill btn-sm px-4">
                                    <i class="bi bi-eye me-1"></i>View Job
                                </a>
                                <!-- Delete Application Form -->
                                <form action="<?= Config::BASE_URL ?>/actions/delete_application.php" method="post" onsubmit="return confirm('Are you sure you want to delete this application?');" class="d-inline-block ms-2 delete-application-form">
                                    <input type="hidden" name="application_id" value="<?= $app['applicationId'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3">
                                        <i class="bi bi-trash me-1"></i>Delete
                          </button>
                                </form>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

            <!-- Pagination -->
            <?php
            // Build the base URL for pagination links, including the status filter if present
            $baseUrl = Config::BASE_URL . '/pages/user/applications.php';
            $queryParams = [];
            if ($statusFilter !== null) {
                $queryParams['status'] = $statusFilter;
            }
            // Pass the base URL with existing query parameters to renderPagination
            $paginationBaseUrl = $baseUrl . (empty($queryParams) ? '' : '?' . http_build_query($queryParams));

            renderPagination($currentPage, $totalPages, $paginationBaseUrl, $totalApplications);
            ?>

        <?php else: ?>
          <div class="text-center py-5">
                <div class="mb-3">
                    <i class="bi bi-file-earmark-text text-muted" style="font-size: 3rem;"></i>
                </div>
                <p class="text-muted mb-3">No applications found for this status.</p>
                <a href="<?= Config::BASE_URL ?>/pages/user/jobs.php" class="btn btn-primary rounded-pill">
                    <i class="bi bi-search me-2"></i>Browse Jobs
            </a>
          </div>
        <?php endif; ?>

  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<!-- Add JavaScript for handling pagination links -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Select all pagination links within the pagination container
        const paginationLinks = document.querySelectorAll('.pagination-container .page-link');

        paginationLinks.forEach(link => {
            // Check if the link has a valid href attribute (not just #)
            if (link.getAttribute('href') && link.getAttribute('href') !== '#') {
                const url = link.getAttribute('href');
                // Add a click event listener to force direct navigation
                link.addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent default link behavior
                    window.location.href = url; // Navigate directly to the URL
                });
            }
        });

        // Function to highlight the current page button
        function highlightCurrentPage() {
            const urlParams = new URLSearchParams(window.location.search);
            const currentPage = urlParams.get('page') || '1'; // Get page from URL, default to '1'

            paginationLinks.forEach(link => {
                // Remove existing active class
                link.closest('.page-item').classList.remove('active');
                link.removeAttribute('aria-current');

                // Add active class if the link's text matches the current page
                if (link.textContent.trim() === currentPage) {
                    link.closest('.page-item').classList.add('active');
                    link.setAttribute('aria-current', 'page');
                }
                 // Handle first page explicitly if no page param is present
                if (currentPage === '1' && link.textContent.trim() === '1') {
                     link.closest('.page-item').classList.add('active');
                     link.setAttribute('aria-current', 'page');
                }
            });
        }

        // Call the highlight function on page load
        highlightCurrentPage();

        // Add debug logging
        console.log('Current page:', <?= $currentPage ?>);
        console.log('Total pages:', <?= $totalPages ?>);
        console.log('Total applications:', <?= $totalApplications ?>);
        console.log('Applications per page:', <?= $perPage ?>);
        console.log('Current offset:', <?= $offset ?>);
    });
</script>

<style>
.delete-application-form {
    margin-top:15px !important;
}
</style>
