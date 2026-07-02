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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobData = [
        'jobTitle' => $_POST['jobTitle'],
        'companyId' => $_POST['companyId'],
        'location' => $_POST['location'],
        'jobDescription' => $_POST['jobDescription'],
        'jobRequirements' => $_POST['jobRequirements'],
        'salaryMin' => $_POST['salaryMin'],
        'salaryMax' => $_POST['salaryMax'],
        'jobType' => $_POST['jobType'],
        'experienceLevel' => $_POST['experienceLevel'],
        'isActive' => $_POST['isActive']
    ];

    if ($job->update($jobId, $jobData)) {
        $_SESSION['success'] = "Job updated successfully!";
        header('Location: manage-jobs.php');
        exit;
    } else {
        $error = "Failed to update job. Please try again.";
    }
}

$pageTitle = "Edit Job - JOBEST";
include __DIR__ . '/../../includes/header.php';
?>

<div class="dashboard-container">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 fw-bold">Edit Job</h1>
            <div>
                <a href="manage-jobs.php" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Back to Jobs
                </a>
                <a href="view-job.php?id=<?= $jobId ?>" class="btn btn-primary">
                    <i class="bi bi-eye"></i> View Job
                </a>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="jobTitle" class="form-label">Job Title</label>
                                <input type="text" class="form-control" id="jobTitle" name="jobTitle" 
                                       value="<?= htmlspecialchars($jobDetails['jobTitle']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="companyId" class="form-label">Company</label>
                                <select class="form-select" id="companyId" name="companyId" required>
                                    <?php
                                    $companies = $job->getAllCompanies();
                                    foreach ($companies as $company):
                                    ?>
                                    <option value="<?= $company['companyId'] ?>" 
                                            <?= $company['companyId'] == $jobDetails['companyId'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($company['companyName']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location" 
                                       value="<?= htmlspecialchars($jobDetails['location']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="jobDescription" class="form-label">Job Description</label>
                                <textarea class="form-control" id="jobDescription" name="jobDescription" 
                                          rows="5" required><?= htmlspecialchars($jobDetails['jobDescription']) ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="jobRequirements" class="form-label">Requirements</label>
                                <textarea class="form-control" id="jobRequirements" name="jobRequirements" 
                                          rows="5" required><?= htmlspecialchars($jobDetails['jobRequirements']) ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="salaryMin" class="form-label">Minimum Salary</label>
                                        <input type="number" class="form-control" id="salaryMin" name="salaryMin" 
                                               value="<?= htmlspecialchars($jobDetails['salaryMin']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="salaryMax" class="form-label">Maximum Salary</label>
                                        <input type="number" class="form-control" id="salaryMax" name="salaryMax" 
                                               value="<?= htmlspecialchars($jobDetails['salaryMax']) ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="jobType" class="form-label">Job Type</label>
                                        <select class="form-select" id="jobType" name="jobType" required>
                                            <option value="full-time" <?= $jobDetails['jobType'] === 'full-time' ? 'selected' : '' ?>>Full Time</option>
                                            <option value="part-time" <?= $jobDetails['jobType'] === 'part-time' ? 'selected' : '' ?>>Part Time</option>
                                            <option value="contract" <?= $jobDetails['jobType'] === 'contract' ? 'selected' : '' ?>>Contract</option>
                                            <option value="internship" <?= $jobDetails['jobType'] === 'internship' ? 'selected' : '' ?>>Internship</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="experienceLevel" class="form-label">Experience Level</label>
                                        <select class="form-select" id="experienceLevel" name="experienceLevel" required>
                                            <option value="entry" <?= $jobDetails['experienceLevel'] === 'entry' ? 'selected' : '' ?>>Entry Level</option>
                                            <option value="mid" <?= $jobDetails['experienceLevel'] === 'mid' ? 'selected' : '' ?>>Mid Level</option>
                                            <option value="senior" <?= $jobDetails['experienceLevel'] === 'senior' ? 'selected' : '' ?>>Senior Level</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="isActive" class="form-label">Status</label>
                                <select class="form-select" id="isActive" name="isActive" required>
                                    <option value="1" <?= $jobDetails['isActive'] ? 'selected' : '' ?>>Active</option>
                                    <option value="0" <?= !$jobDetails['isActive'] ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">Job Information</h6>
                                    
                                    <div class="mb-3">
                                        <label class="text-muted d-block">Posted Date</label>
                                        <span><?= date('F j, Y', strtotime($jobDetails['createdAt'])) ?></span>
                                    </div>

                                    <div class="mb-3">
                                        <label class="text-muted d-block">Applications</label>
                                        <span><?= number_format($jobDetails['applicationsCount'] ?? 0) ?></span>
                                    </div>

                                    <div class="mb-3">
                                        <label class="text-muted d-block">Last Updated</label>
                                        <span><?= date('F j, Y', strtotime($jobDetails['updatedAt'] ?? $jobDetails['createdAt'])) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Changes
                        </button>
                        <a href="view-job.php?id=<?= $jobId ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?> 