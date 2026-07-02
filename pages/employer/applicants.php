<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Company.php';
require_once __DIR__ . '/../../classes/Job.php';
require_once __DIR__ . '/../../classes/Application.php';

// Authentication check using database functions
if (!isLoggedIn() || !isEmployer()) {
    header('Location: /pages/public/login.php');
    exit;
}

$company = new Company();
$application = new Application();
$job = new Job();

$userId = $_SESSION['user_id'];
$companyId = $company->getCompanyIdByUser($userId);
$jobId = $_GET['job_id'] ?? null;
$applicationId = $_GET['application_id'] ?? null;
$statusFilter = $_GET['status'] ?? 'all';

// Get jobs for dropdown
$jobs = $job->getPostedJobsForEmployer($companyId, 'active');

// Get applicants
$applicants = [];
if ($jobId) {
    if ($applicationId) {
        // Get single application
        $singleApplication = $application->getApplicationById($applicationId);
        if ($singleApplication && $singleApplication['jobId'] == $jobId) {
            $applicants = [$singleApplication];
        }
    } else {
        // Get all applications for this job
        $applicants = $application->getApplicantsForJob($jobId, $statusFilter);
    }
}

$jobTitle = isset($jobId) ? htmlspecialchars($job->getJobById($jobId)['jobTitle'] ?? 'Job') : 'All Jobs';
$pageTitle = "Applicants for {$jobTitle} | JOBEST";

// Add CSS to hide duplicate navigation if it exists
$customCSS = "<style>
.duplicate-nav, .duplicate-navigation {
    display: none !important;
}

.applicant-header-filters {
    background-color: #ffffff;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    /* Ensure dropdowns take full width of their flex item */
    .form-select {
        flex-grow: 1;
    }
}

/* New styles for the applicants table */
.applicants-table {
    border-collapse: separate;
    border-spacing: 0 10px; /* Add space between rows */
    width: 100%; /* Ensure table takes full width */
}

.applicants-table th {
    background-color: #f8f9fa; /* Light grey background for headers */
    border-bottom: none; /* Remove bottom border from headers */
    padding: 1rem;
}

.applicants-table td {
    background-color: #ffffff; /* White background for table data */
    border-bottom: none; /* Remove default border */
    padding: 1rem;
    vertical-align: middle; /* Vertically align cell content */
}

.applicants-table tbody tr {
    box-shadow: 0 2px 8px rgba(0,0,0,0.05); /* Subtle shadow for rows */
    border-radius: 8px; /* Rounded corners for rows */
    overflow: hidden; /* Ensure shadow and border-radius work together */
    transition: all 0.2s ease-in-out; /* Smooth transition for hover effects */
}

.applicants-table tbody tr:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.1); /* Enhanced shadow on hover */
    transform: translateY(-2px); /* Slightly lift row on hover */
}

/* Style for the first and last cells in a row to handle border radius */
.applicants-table tbody tr td:first-child {
    border-top-left-radius: 8px;
    border-bottom-left-radius: 8px;
}

.applicants-table tbody tr td:last-child {
    border-top-right-radius: 8px;
    border-bottom-right-radius: 8px;
}

/* Remove padding from the parent card body if needed */
.card-body.p-0 > .table-responsive {
    margin-bottom: 0; /* Remove default bottom margin */
}

</style>";

// Add custom CSS to head
$pageStyles = [$customCSS];

include __DIR__ . '/../../includes/header.php';
?>

<div class="container py-5">
    <!-- Display Success or Error Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Applicant Management</h1>
            <?php 
            $employerCompany = $company->getDetails($companyId); 
            if ($employerCompany):
            ?>
                <p class="text-muted mb-0">For: <span class="fw-medium"><?= htmlspecialchars($employerCompany['companyName']) ?></span></p>
            <?php endif; ?>
            <p class="text-muted mb-0">Manage your job applicants efficiently</p>
        </div>
        <div class="d-flex align-items-center gap-3 applicant-header-filters" style="max-width: 500px;">
            <select class="form-select form-select-lg shadow-none border-0" id="jobSelect" onchange="location = this.value;" style="min-width: 220px;">
                <option value="">Select Job from <?= htmlspecialchars($employerCompany['companyName']) ?></option>
                <?php foreach ($jobs as $job): ?>
                    <option value="?job_id=<?= $job['jobId'] ?>" 
                        <?= $jobId == $job['jobId'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($job['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select class="form-select form-select-lg shadow-none border-0" id="statusSelect"
                    onchange="location = '?status=' + this.value + '<?= $jobId ? "&job_id=$jobId" : '' ?>';"
                    style="min-width: 180px;">
                <option value="all" <?= $statusFilter == 'all' ? 'selected' : '' ?>>All</option>
                <option value="pending" <?= $statusFilter == 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="viewed" <?= $statusFilter == 'viewed' ? 'selected' : '' ?>>Viewed</option>
                <option value="interviewing" <?= $statusFilter == 'interviewing' ? 'selected' : '' ?>>Interviewing</option>
                <option value="hired" <?= $statusFilter == 'hired' ? 'selected' : '' ?>>Hired</option>
                <option value="rejected" <?= $statusFilter == 'rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
        </div>
    </div>

    <?php if ($jobId): // Only display this if a job is selected ?>
        <div class="mb-4">
            <p class="h5 mb-2">Applicants for: <span class="fw-bold"><?= htmlspecialchars($jobTitle) ?></span></p>
            <p class="h6 text-muted mb-0">Current Filter: <span class="badge bg-info"><?= ucfirst(htmlspecialchars($statusFilter)) ?></span></p>
        </div>
    <?php endif; ?>

    <?php if ($jobId): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 applicants-table">
                        <thead class="bg-light border-0">
                            <tr>
                                <th class="py-3">Candidate</th>
                                <th class="py-3">Applied</th>
                                <th class="py-3">Status</th>
                                <th class="py-3">Resume</th>
                                <th class="py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applicants as $applicant): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3" style="gap: 10px !important;">
                                            <img src="<?= getUserAvatar($applicant['userId']) ?>" 
                                                class="rounded-circle" width="40" height="40">
                                            <div>
                                                <h3 class="h6 fw-bold mb-0">
                                                    <?= htmlspecialchars($applicant['firstName'] . ' ' . $applicant['lastName']) ?>
                                                </h3>
                                                <p class="text-muted small mb-0">
                                                    <?= htmlspecialchars($applicant['email']) ?>
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                      <?php if (!empty($applicant['appliedAt'])): ?>
                                        <?= timeElapsed($applicant['appliedAt']) ?>
                                      <?php else: ?>
                                        <span class="text-muted small">N/A</span>
                                      <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= statusColor($applicant['status']) ?> rounded-pill">
                                            <?= ucfirst($applicant['status']) ?>
                                        </span>
                                        <a href="change_status.php?applicationId=<?= $applicant['applicationId'] ?>" class="btn btn-sm btn-outline-secondary ms-3" title="Change Status" style="margin-left: 10px !important;">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if (!empty($applicant['resumePath'])): ?>
                                            <a href="<?= Config::BASE_URL ?><?= htmlspecialchars($applicant['resumePath']) ?>" 
                                            class="btn btn-sm btn-outline-primary rounded-pill px-3" download>
                                                <i class="bi bi-download me-1"></i>Download
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">No resume</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <select class="form-select form-select-sm" onchange="handleApplicantAction(this, '<?= $applicant['userId'] ?>', '<?= $applicant['applicationId'] ?>')" 
                                                style="min-width: 140px; border-radius: 20px; padding: 6px 12px; border: 1px solid #e0e0e0; background-color: #f8f9fa; cursor: pointer; transition: all 0.2s ease;">
                                            <option value="" class="text-muted">Actions...</option>
                                            <option value="view_profile" class="py-2">
                                                <i class="bi bi-person me-2"></i>View Profile
                                            </option>
                                            <option value="message" class="py-2">
                                                <i class="bi bi-chat me-2"></i>Message
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                                <!-- Status Change Modal -->
                                <div class="modal fade" id="statusModal-<?= $applicant['applicationId'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header border-0">
                                                <h5 class="modal-title fw-bold">Change Application Status</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="/actions/update-application-status.php" method="post">
                                                    <input type="hidden" name="application_id" value="<?= $applicant['applicationId'] ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label">New Status</label>
                                                        <select name="status" class="form-select">
                                                            <option value="pending" <?= $applicant['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                                            <option value="viewed" <?= $applicant['status'] == 'viewed' ? 'selected' : '' ?>>Viewed</option>
                                                            <option value="interviewing" <?= $applicant['status'] == 'interviewing' ? 'selected' : '' ?>>Interviewing</option>
                                                            <option value="hired" <?= $applicant['status'] == 'hired' ? 'selected' : '' ?>>Hired</option>
                                                            <option value="rejected" <?= $applicant['status'] == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                                        </select>
                                                    </div>
                                                    <div class="d-grid">
                                                        <button type="submit" class="btn btn-primary">Update Status</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (empty($applicants)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-4 text-muted mb-3"></i>
                        <p class="text-muted mb-0">No applicants found for this job</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-5 text-center">
                <i class="bi bi-briefcase display-4 text-muted mb-3"></i>
                <h3 class="h5 fw-bold mb-3">Select a job to view applicants</h3>
                <p class="text-muted mb-4">Choose a job from the dropdown menu above to see all applicants.</p>
                <a href="/pages/employer/post-job.php" class="btn btn-primary rounded-pill px-4">
                    <i class="bi bi-plus-circle me-2"></i>Post a New Job
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<script></script>
document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
<script>
function handleApplicantAction(select, userId, applicationId) {
    var action = select.value;
    if (!action) return;
    switch(action) {
        case 'view_profile':
            window.location.href = 'profile_user.php?userId=' + userId;
            break;
        case 'message':
            // Redirect to messages page for this user
            window.location.href = 'messages.php?conversation=' + userId;
            break;
        case 'note':
            alert('Add Note action for application ' + applicationId);
            select.value = ''; // Reset select to default
            break;
    }
    // select.value = ''; // Keep the selected value until action is confirmed or page reloads
}
</script>