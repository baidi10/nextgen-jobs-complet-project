<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Job.php';
require_once __DIR__ . '/../../classes/Application.php';
require_once __DIR__ . '/../../classes/Company.php';

// Authentication check using database functions
if (!isLoggedIn() || !isEmployer()) {
    header('Location: /pages/public/login.php');
    exit;
}

$company = new Company();
$job = new Job();
$application = new Application();

// Get employer stats
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

$stats = [
    'activeJobs' => $job->countActiveJobs($companyId),
    'totalApplications' => $application->countCompanyApplications($companyId),
    'hiredCount' => $application->countHired($companyId),
    'profileViews' => $company->getProfileViews($companyId)
];

// Recent applicants
$recentApplicants = $application->getRecentApplicants($companyId, 5);

// Get application status data
$statusCounts = $application->getApplicationStatusCounts($companyId);

// Get job performance data
$performanceData = $job->getJobPerformance($companyId, 6);

// Format the data for charts
$chartMonths = [];
$applicationsData = [];
$viewsData = [];

foreach ($performanceData as $data) {
    $chartMonths[] = $data['month_name'];
    $applicationsData[] = $data['count'];
    // Use view data from database if available, otherwise fallback to a calculated value
    $viewsData[] = isset($data['views']) ? $data['views'] : $data['count'] * 2;
}

$pageTitle = "Employer Dashboard - JOBEST";

// Add CSS to hide duplicate navigation if it exists
$customCSS = "
<style>
.duplicate-nav, .duplicate-navigation, .employer-top-nav {
    display: none !important;
}

.stats-card {
    transition: all 0.3s ease;
    border-radius: 12px;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
}

.stats-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
}

.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}

/* Recent Applicants exact 10px spacing */
.recent-applicants-card .d-flex.align-items-center.gap-3 {
    gap: 10px !important;
}

.recent-applicants-card .d-flex.gap-3.align-items-center {
    gap: 10px !important;
}

/* Space between icon and text in view button */
.recent-applicants-card .btn i.bi-eye {
    margin-right: 5px;
}

.time-period-selector select {
    cursor: pointer;
    transition: all 0.2s ease;
    background-image: url('data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"%236c757d\" viewBox=\"0 0 16 16\"><path d=\"M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z\"/></svg>');
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 16px 12px;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    padding-right: 2.5rem;
}

.time-period-selector select:hover {
    background-color: #e9ecef !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
}

.time-period-selector select:focus {
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15) !important;
    border-color: transparent !important;
}

.time-period-selector select option {
    padding: 0.5rem;
    font-size: 0.875rem;
}
</style>";

// Add custom CSS to head
$pageStyles = [
    $customCSS,
    '<link href="' . Config::BASE_URL . '/assets/css/dashboard-custom.css" rel="stylesheet">'
];

include __DIR__ . '/../../includes/header.php';
?>

<div class="dashboard-container">
  <div class="container py-5">
    <!-- Dashboard Header -->
    <div class="d-flex justify-content-between align-items-center mb-5">
      <div>
        <h1 class="h3 fw-bold">Welcome, <?= htmlspecialchars($company->getName($companyId)) ?></h1>
        <p class="text-muted mb-0">Your hiring dashboard</p>
      </div>
      <div class="d-flex gap-3">
        <a href="company-profile.php" class="btn btn-outline-secondary">
          <i class="bi bi-person-gear me-2"></i>Edit Profile
        </a>
        <a href="post-job.php" class="btn btn-primary">
          <i class="bi bi-plus-lg me-2"></i>Post New Job
        </a>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-5">
      <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 stats-card">
          <div class="card-body p-4">
            <div class="d-flex align-items-center">
              <div class="stats-icon bg-primary-subtle text-primary me-3">
                <i class="bi bi-briefcase fs-5"></i>
              </div>
              <div>
                <h3 class="h2 fw-bold mb-0"><?= $stats['activeJobs'] ?></h3>
                <span class="text-muted small">Active Jobs</span>
              </div>
            </div>
          </div>
          <div class="card-footer bg-white border-0 py-3">
            <a href="manage-jobs.php" class="text-decoration-none d-flex justify-content-between align-items-center">
              <span class="small text-primary">View all jobs</span>
              <i class="bi bi-arrow-right text-primary"></i>
            </a>
          </div>
        </div>
      </div>
      
      <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 stats-card">
          <div class="card-body p-4">
            <div class="d-flex align-items-center">
              <div class="stats-icon bg-success-subtle text-success me-3">
                <i class="bi bi-person-badge fs-5"></i>
              </div>
              <div>
                <h3 class="h2 fw-bold mb-0"><?= $stats['totalApplications'] ?></h3>
                <span class="text-muted small">Total Applications</span>
              </div>
            </div>
          </div>
          <div class="card-footer bg-white border-0 py-3">
            <a href="applicants.php" class="text-decoration-none d-flex justify-content-between align-items-center">
              <span class="small text-success">View applications</span>
              <i class="bi bi-arrow-right text-success"></i>
            </a>
          </div>
        </div>
      </div>
      
      <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 stats-card">
          <div class="card-body p-4">
            <div class="d-flex align-items-center">
              <div class="stats-icon bg-info-subtle text-info me-3">
                <i class="bi bi-eye fs-5"></i>
              </div>
              <div>
                <h3 class="h2 fw-bold mb-0"><?= $stats['profileViews'] ?></h3>
                <span class="text-muted small">Profile Views</span>
              </div>
            </div>
          </div>
          <div class="card-footer bg-white border-0 py-3">
            <a href="company-profile.php" class="text-decoration-none d-flex justify-content-between align-items-center">
              <span class="small text-info">Enhance profile</span>
              <i class="bi bi-arrow-right text-info"></i>
            </a>
          </div>
        </div>
      </div>
      
      <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 stats-card">
          <div class="card-body p-4">
            <div class="d-flex align-items-center">
              <div class="stats-icon bg-warning-subtle text-warning me-3">
                <i class="bi bi-check-circle fs-5"></i>
              </div>
              <div>
                <h3 class="h2 fw-bold mb-0"><?= $stats['hiredCount'] ?></h3>
                <span class="text-muted small">Hired Candidates</span>
              </div>
            </div>
          </div>
          <div class="card-footer bg-white border-0 py-3">
            <a href="applicants.php?status=hired" class="text-decoration-none d-flex justify-content-between align-items-center">
              <span class="small text-warning">View hired</span>
              <i class="bi bi-arrow-right text-warning"></i>
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-5">
      <!-- Performance Chart -->
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h2 class="h5 fw-bold mb-0">Applications & Views Overview</h2>
            <div class="time-period-selector">
              <select class="form-select form-select-sm border-0 bg-light rounded-pill px-3 py-2 shadow-sm" id="timePeriod" style="width: auto; min-width: 140px; font-size: 0.875rem;">
                <option value="3">Last 3 Months</option>
                <option value="6" selected>Last 6 Months</option>
                <option value="12">Last Year</option>
              </select>
            </div>
          </div>
          <div class="card-body">
            <div class="chart-container">
              <canvas id="performanceChart"></canvas>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Status Distribution -->
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white border-0 py-3">
            <h2 class="h5 fw-bold mb-0">Application Status</h2>
          </div>
          <div class="card-body">
            <div class="chart-container">
              <canvas id="statusChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Applicants -->
    <div class="card border-0 shadow-sm recent-applicants-card">
      <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h2 class="h5 fw-bold mb-0">Recent Applicants</h2>
        <a href="applicants.php" class="btn btn-sm btn-outline-dark rounded-pill px-4">View All</a>
      </div>
      <div class="card-body p-0">
        <?php if (!empty($recentApplicants)): ?>
          <div class="list-group list-group-flush">
            <?php foreach ($recentApplicants as $applicant): ?>
              <div class="list-group-item border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                  <div class="d-flex align-items-center" style="gap: 10px;">
                    <?php 
                      // Generate initials for avatar
                      $initial = strtoupper(substr($applicant['firstName'], 0, 1));
                      
                      // Define colors for specific initials based on the image
                      $avatarColors = [
                        'A' => 'bg-danger', 
                        'O' => 'bg-warning',
                        'U' => 'bg-success', 
                        'B' => 'bg-warning',
                        'J' => 'bg-primary'
                      ];
                      $bgColor = isset($avatarColors[$initial]) ? $avatarColors[$initial] : 'bg-primary';
                    ?>
                    <div class="rounded-circle <?= $bgColor ?> text-white d-flex align-items-center justify-content-center" 
                         style="width: 48px; height: 48px; font-size: 18px; font-weight: bold;">
                      <?= $initial ?>
                    </div>
                    <div>
                      <h3 class="h6 fw-bold mb-0">
                        <?= htmlspecialchars($applicant['firstName'] . ' ' . $applicant['lastName']) ?>
                      </h3>
                      <p class="text-muted small mb-0">
                        Applied for <?= htmlspecialchars($applicant['jobTitle']) ?>
                      </p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center" style="gap: 10px;">
                    <?php 
                      // Status colors matching the image
                      $statusClasses = [
                        'pending' => 'bg-warning',
                        'interviewing' => 'bg-primary',
                        'hired' => 'bg-success', 
                        'rejected' => 'bg-danger'
                      ];
                      $statusClass = isset($statusClasses[$applicant['status']]) ? $statusClasses[$applicant['status']] : statusColor($applicant['status']);
                    ?>
                    <span class="badge <?= $statusClass ?> px-3 py-2">
                      <?= ucfirst($applicant['status']) ?>
                    </span>
                    <a href="applicants.php?job_id=<?= $applicant['jobId'] ?>&application_id=<?= $applicant['applicationId'] ?>" 
                       class="btn btn-outline-dark rounded-pill px-4">
                      <i class="bi bi-eye" style="margin-right: 5px;"></i>View
                    </a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="text-center py-5">
            <div class="mb-3">
              <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
            </div>
            <p class="text-muted">No recent applicants</p>
            <a href="post-job.php" class="btn btn-primary rounded-pill mt-2">
              <i class="bi bi-plus-lg me-2"></i>Post a Job
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Load Chart.js from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
// Initialize Charts
document.addEventListener('DOMContentLoaded', function() {
  let performanceChart;
  
  function initializeChart(months) {
    // Destroy existing chart if it exists
    if (performanceChart) {
      performanceChart.destroy();
    }
    
    // Performance Chart
    performanceChart = new Chart(
      document.getElementById('performanceChart').getContext('2d'), 
      {
        type: 'line',
        data: {
          labels: <?= json_encode($chartMonths) ?>,
          datasets: [
            {
              label: 'Applications',
              data: <?= json_encode($applicationsData) ?>,
              borderColor: '#0d6efd',
              backgroundColor: 'rgba(13, 110, 253, 0.1)',
              tension: 0.4,
              fill: true
            },
            {
              label: 'Profile Views',
              data: <?= json_encode($viewsData) ?>,
              borderColor: '#20c997',
              backgroundColor: 'rgba(32, 201, 151, 0.1)',
              tension: 0.4,
              fill: true
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'top',
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              grid: {
                color: 'rgba(0, 0, 0, 0.05)'
              }
            },
            x: {
              grid: {
                display: false
              }
            }
          }
        }
      }
    );
  }

  // Initialize with default 6 months
  initializeChart(6);

  // Handle time period changes
  document.getElementById('timePeriod').addEventListener('change', function(e) {
    const months = parseInt(e.target.value);
    // Here you would typically make an AJAX call to get new data
    // For now, we'll just reinitialize the chart with existing data
    initializeChart(months);
  });
  
  // Status Chart - Pie chart showing application statuses
  const statusChart = new Chart(
    document.getElementById('statusChart').getContext('2d'), 
    {
      type: 'doughnut',
      data: {
        labels: ['Pending', 'Viewed', 'Interviewing', 'Hired', 'Rejected'],
        datasets: [{
          data: [
            <?= $statusCounts['pending'] ?>, // Pending
            <?= $statusCounts['viewed'] ?>, // Viewed  
            <?= $statusCounts['interviewing'] ?>, // Interviewing
            <?= $statusCounts['hired'] ?>, // Hired
            <?= $statusCounts['rejected'] ?> // Rejected
          ],
          backgroundColor: [
            '#6c757d', // Pending - gray
            '#0dcaf0', // Viewed - info
            '#fd7e14', // Interviewing - orange
            '#198754', // Hired - success
            '#dc3545'  // Rejected - danger
          ],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
          }
        },
        cutout: '70%'
      }
    }
  );
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>