<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Application.php';
require_once __DIR__ . '/../../classes/Job.php';

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'jobSeeker') {
    header('Location: /pages/public/login.php');
    exit;
}

$user = new User();
$application = new Application();
$job = new Job();

// Get user data
$userData = $user->findById($_SESSION['user_id']);
$jobSeekerData = $user->getJobSeekerProfile($_SESSION['user_id']);
$applicationStats = $user->getApplicationStats($_SESSION['user_id']);
$profileViews = $user->getProfileViews($_SESSION['user_id']);
$jobSearchInsights = $user->getJobSearchInsights($_SESSION['user_id']);

// Get recent applications
$applications = $user->getRecentApplications($_SESSION['user_id'], 5);

// Get saved jobs count
$savedJobsCount = $applicationStats['savedJobs'];

// Calculate profile completion percentage
$profileFields = [
    'headline' => 10,
    'currentPosition' => 10,
    'currentCompany' => 10,
    'educationLevel' => 10,
    'yearsOfExperience' => 10,
    'resumeUrl' => 20,
    'portfolioUrl' => 10,
    'desiredSalary' => 10,
    'desiredJobTypes' => 10
];

$profileCompletion = 0;
foreach ($profileFields as $field => $weight) {
    if (!empty($jobSeekerData[$field])) {
        $profileCompletion += $weight;
    }
}

// Get application trends for the last 6 months
$applicationTrends = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $applicationTrends[$month] = [
        'total' => 0,
        'pending' => 0,
        'hired' => 0,
        'rejected' => 0
    ];
}

// Get application status breakdown
$statusBreakdown = $application->getStatusBreakdown($_SESSION['user_id']);

// Update application trends with the status breakdown
foreach ($applicationTrends as $month => $data) {
    $applicationTrends[$month] = [
        'total' => array_sum($statusBreakdown),
        'pending' => $statusBreakdown['pending'],
        'hired' => $statusBreakdown['hired'],
        'rejected' => $statusBreakdown['rejected']
    ];
}

$pageTitle = "Dashboard - JOBEST";

// Add custom CSS
$customCSS = "
<style>
/* Fixed profile card */
.col-lg-4 {
    position: sticky;
    top: 100px;
    height: calc(100vh - 120px);
}

.col-lg-4 .card {
    height: 100%;
    overflow-y: auto;
}

/* Right side cards styling */
.col-lg-8 .card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 1rem;
    transition: all 0.2s ease;
}

.col-lg-8 .card:hover {
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

.col-lg-8 .card-body {
    padding: 1.25rem;
}

/* Button styles */
.btn-sm {
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 6px;
}

.btn-outline-primary.btn-sm {
    border-width: 1px;
}

/* Stats cards */
.stats-card {
    padding: 1rem;
    text-align: center;
    border-radius: 8px;
    background: #f8f9fa;
    transition: all 0.2s ease;
}

.stats-card:hover {
    background: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.stats-value {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.stats-label {
    font-size: 0.875rem;
    color: #6c757d;
}

/* Application items */
.application-item {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #eee;
}

.application-item:last-child {
    border-bottom: none;
}

/* Badge styles */
.badge {
    padding: 0.35em 0.65em;
    font-size: 0.75rem;
    font-weight: 500;
}

/* Chart container */
.chart-container {
    height: 250px;
    margin: 0.5rem 0;
}

/* Responsive adjustments */
@media (max-width: 991px) {
    .col-lg-4 {
        position: relative;
        top: 0;
        height: auto;
    }
    
    .col-lg-4 .card {
        height: auto;
    }
}
</style>";

// Add custom CSS and Chart.js to head
$pageStyles = [
    $customCSS,
    '<link href="' . Config::BASE_URL . '/assets/css/dashboard-custom.css" rel="stylesheet">',
    '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>'
];

include __DIR__ . '/../../includes/header.php';
?>

<div class="container py-5" style="max-width: 1200px;">
    <div class="row justify-content-center">
        <div class="col-lg-4" style="max-width: 320px;">
            <div class="card border-0 shadow-sm" style="position: sticky; top: 100px;">
                <div class="card-body text-center p-4">
                    <div class="position-relative mb-4">
                        <?php
                        // Get the default avatar URL
                        $defaultAvatarUrl = getUserAvatar($_SESSION['user_id']);
                        
                        // Check if user has uploaded a photo
                        $profilePhotoUrl = $defaultAvatarUrl;
                        if (!empty($jobSeekerData['photo'])) {
                            $uploadedPhotoPath = Config::BASE_URL . '/assets/uploads/profiles/' . htmlspecialchars($jobSeekerData['photo']);
                            // Check if the file exists on the server
                            if (file_exists(__DIR__ . '/../../assets/uploads/profiles/' . $jobSeekerData['photo'])) {
                                $profilePhotoUrl = $uploadedPhotoPath;
                            }
                        }
                        ?>
                        <img src="<?= $profilePhotoUrl ?>"
                             style="width: 145px; height: 145px; object-fit: cover; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1);"
                             alt="Profile Photo">
                    </div>
                    <h2 class="h5 fw-bold mb-3"><?= htmlspecialchars($userData['firstName'] . ' ' . $userData['lastName']) ?></h2>
                    <p class="text-muted small mb-4"><?= htmlspecialchars($jobSeekerData['headline'] ?? $userData['location'] ?? '') ?></p>

                    <!-- Profile Completion -->
                    <div class="text-start mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h3 class="h6 fw-bold mb-0">Profile Completion</h3>
                            <span class="text-muted small"><?= $profileCompletion ?>%</span>
                        </div>
                        <div class="profile-completion">
                            <div class="progress-bar bg-primary" style="width: <?= $profileCompletion ?>%"></div>
                        </div>
                        <?php if ($profileCompletion < 100): ?>
                            <a href="edit-profile.php" class="btn btn-link btn-sm p-0 mt-2">Complete your profile</a>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Actions -->
                    <div class="text-start mb-4">
                        <h3 class="h6 fw-bold mb-3">Quick Actions</h3>
                        <div class="d-flex flex-column" style="gap: 15px;">
                            <a href="edit-profile.php" class="btn btn-outline-primary">
                                <i class="bi bi-pencil me-2"></i>Edit Profile
                            </a>
                            <a href="jobs.php" class="btn btn-outline-primary">
                                <i class="bi bi-search me-2"></i>Find Jobs
                            </a>
                            <a href="saved-jobs.php" class="btn btn-outline-primary">
                                <i class="bi bi-bookmark me-2"></i>Saved Jobs
                            </a>
                        </div>
                    </div>

                    <!-- Job Search Insights -->
                    <div class="text-start mt-4">
                        <h3 class="h6 fw-bold mb-3">Job Search Insights</h3>
                        <ul class="list-unstyled small text-muted mb-0">
                            <li class="mb-2">
                                <i class="bi bi-building me-2"></i>
                                Top Industry: <?= htmlspecialchars($jobSearchInsights['topIndustry']) ?>
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-clock me-2"></i>
                                Avg. Response Time: <?= $jobSearchInsights['avgResponseTime'] ?> days
                            </li>
                            <li>
                                <i class="bi bi-graph-up me-2"></i>
                                Success Rate: <?= $jobSearchInsights['successRate'] ?>%
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Application Stats -->
            <div class="card mb-3">
                <div class="card-body">
                    <h3 class="h6 fw-bold mb-3">Application Statistics</h3>
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-3">
                            <a href="applications.php" class="text-decoration-none">
                                <div class="stats-card">
                                    <div class="stats-value text-primary"><?= $applicationStats['totalApplications'] ?></div>
                                    <div class="stats-label">Total Applications</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <a href="applications.php?status=pending" class="text-decoration-none">
                                <div class="stats-card">
                                    <div class="stats-value text-warning"><?= $applicationStats['pending'] ?></div>
                                    <div class="stats-label">Pending</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <a href="applications.php?status=hired" class="text-decoration-none">
                                <div class="stats-card">
                                    <div class="stats-value text-success"><?= $applicationStats['hired'] ?></div>
                                    <div class="stats-label">Hired</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <a href="applications.php?status=rejected" class="text-decoration-none">
                                <div class="stats-card">
                                    <div class="stats-value text-danger"><?= $applicationStats['rejected'] ?></div>
                                    <div class="stats-label">Rejected</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Application Trends -->
            <div class="card mb-3">
                <div class="card-body">
                    <h3 class="h6 fw-bold mb-3">Application Trends</h3>
                    <div class="chart-container">
                        <canvas id="applicationTrendsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Saved Jobs -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="h6 fw-bold mb-0">Saved Jobs</h3>
                        <a href="saved-jobs.php" class="btn btn-link btn-sm p-0">View All</a>
                    </div>
                    <div class="text-center py-3">
                        <i class="bi bi-bookmark text-muted" style="font-size: 1.5rem;"></i>
                        <p class="mt-2 mb-2"><?= $savedJobsCount ?> saved jobs</p>
                        <a href="jobs.php" class="btn btn-primary btn-sm">Find Jobs</a>
                    </div>
                </div>
            </div>

            <!-- Recent Applications -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="h6 fw-bold mb-0">Recent Applications</h3>
                        <a href="applications.php" class="btn btn-link btn-sm p-0">View All</a>
                    </div>
                    <?php if (!empty($applications)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($applications as $application): ?>
                                <div class="application-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h4 class="h6 fw-bold mb-1"><?= htmlspecialchars($application['jobTitle']) ?></h4>
                                            <p class="text-muted small mb-0"><?= htmlspecialchars($application['companyName']) ?></p>
                                        </div>
                                        <span class="badge bg-<?= getStatusBadgeClass($application['status']) ?>">
                                            <?= ucfirst($application['status']) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="bi bi-file-earmark-text text-muted" style="font-size: 1.5rem;"></i>
                            <p class="mt-2 mb-2">No applications yet</p>
                            <a href="jobs.php" class="btn btn-primary btn-sm">Find Jobs</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize application trends chart
const ctx = document.getElementById('applicationTrendsChart').getContext('2d');
const applicationTrendsChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_map(function($month) { 
            return date('M Y', strtotime($month)); 
        }, array_keys($applicationTrends))) ?>,
        datasets: [
            {
                label: 'Total Applications',
                data: <?= json_encode(array_map(function($data) { 
                    return $data['total']; 
                }, array_values($applicationTrends))) ?>,
                backgroundColor: 'rgba(13, 110, 253, 0.7)',
                borderColor: '#0d6efd',
                borderWidth: 1,
                borderRadius: 4
            },
            {
                label: 'Pending',
                data: <?= json_encode(array_map(function($data) { 
                    return $data['pending']; 
                }, array_values($applicationTrends))) ?>,
                backgroundColor: 'rgba(255, 193, 7, 0.7)',
                borderColor: '#ffc107',
                borderWidth: 1,
                borderRadius: 4
            },
            {
                label: 'Hired',
                data: <?= json_encode(array_map(function($data) { 
                    return $data['hired']; 
                }, array_values($applicationTrends))) ?>,
                backgroundColor: 'rgba(25, 135, 84, 0.7)',
                borderColor: '#198754',
                borderWidth: 1,
                borderRadius: 4
            },
            {
                label: 'Rejected',
                data: <?= json_encode(array_map(function($data) { 
                    return $data['rejected']; 
                }, array_values($applicationTrends))) ?>,
                backgroundColor: 'rgba(220, 53, 69, 0.7)',
                borderColor: '#dc3545',
                borderWidth: 1,
                borderRadius: 4
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>

<?php
// Helper function to get badge class based on status
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'hired':
            return 'success';
        case 'rejected':
            return 'danger';
        default:
            return 'secondary';
    }
}

include __DIR__ . '/../../includes/footer.php';
?>