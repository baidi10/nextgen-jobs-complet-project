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
$jobId = $_GET['id'] ?? 0;

if (!$jobId) {
    header('Location: manage-jobs.php');
    exit;
}

$jobDetails = $job->getJobById($jobId);

if (!$jobDetails) {
    header('Location: manage-jobs.php');
    exit;
}

$pageTitle = "View Job - JOBEST";
include __DIR__ . '/../../includes/header.php';
?>

<div class="dashboard-container">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 fw-bold">Job Details</h1>
            <div>
                <a href="manage-jobs.php" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Back to Jobs
                </a>
                <a href="edit-job.php?id=<?= $jobId ?>" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Edit Job
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-8">
                        <h2 class="h4 fw-bold mb-3"><?= htmlspecialchars($jobDetails['jobTitle']) ?></h2>
                        
                        <div class="d-flex align-items-center mb-4">
                            <img src="<?= Config::BASE_URL ?>/assets/uploads/company_logos/<?= $jobDetails['companyLogo'] ?>" 
                                 class="rounded-circle me-3" width="48" height="48">
                            <div>
                                <h5 class="mb-1"><?= htmlspecialchars($jobDetails['companyName']) ?></h5>
                                <p class="text-muted mb-0">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    <?= htmlspecialchars($jobDetails['location']) ?>
                                </p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Job Description</h6>
                            <div class="bg-light p-3 rounded">
                                <?= nl2br(htmlspecialchars($jobDetails['jobDescription'])) ?>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Requirements</h6>
                            <div class="bg-light p-3 rounded">
                                <?= nl2br(htmlspecialchars($jobDetails['jobRequirements'])) ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">Job Information</h6>
                                
                                <div class="mb-3">
                                    <label class="text-muted d-block">Status</label>
                                    <span class="badge bg-<?= statusBadgeColor($jobDetails['isActive'] ? 'active' : 'pending') ?>">
                                        <?= $jobDetails['isActive'] ? 'Active' : 'Pending' ?>
                                    </span>
                                </div>

                                <div class="mb-3">
                                    <label class="text-muted d-block">Posted Date</label>
                                    <span><?= date('F j, Y', strtotime($jobDetails['createdAt'])) ?></span>
                                </div>

                                <div class="mb-3">
                                    <label class="text-muted d-block">Applications</label>
                                    <span><?= number_format($jobDetails['applicationsCount'] ?? 0) ?></span>
                                </div>

                                <?php if (!empty($jobDetails['salaryMin']) && !empty($jobDetails['salaryMax'])): ?>
                                <div class="mb-3">
                                    <label class="text-muted d-block">Salary Range</label>
                                    <span>$<?= number_format($jobDetails['salaryMin']) ?> - $<?= number_format($jobDetails['salaryMax']) ?></span>
                                </div>
                                <?php endif; ?>

                                <div class="mb-3">
                                    <label class="text-muted d-block">Job Type</label>
                                    <span><?= ucfirst($jobDetails['jobType']) ?></span>
                                </div>

                                <div class="mb-3">
                                    <label class="text-muted d-block">Experience Level</label>
                                    <span><?= ucfirst($jobDetails['experienceLevel']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?> 