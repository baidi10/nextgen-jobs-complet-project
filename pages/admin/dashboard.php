<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Job.php';
require_once __DIR__ . '/../../classes/Application.php';

// Authentication check
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ' . Config::BASE_URL . '/pages/public/login.php');
    exit;
}

// Get time filter from query parameter, default to 6 months
$timeFilter = isset($_GET['time_filter']) ? $_GET['time_filter'] : '6m';
$validFilters = [
    '24h' => 'Last 24 Hours',
    '1w' => 'Last Week',
    '1m' => 'Last Month',
    '3m' => '3 Months',
    '6m' => '6 Months',
    '1y' => 'Last Year'
];

if (!array_key_exists($timeFilter, $validFilters)) {
    $timeFilter = '6m';
}

// Initialize classes
$userObj = new User();
$jobObj = new Job();
$applicationObj = new Application();
$db = Database::getInstance()->getConnection();

// Get statistics
$totalUsers = $userObj->getTotalUsers();
$totalJobs = $jobObj->getTotalJobs();
$totalApplications = $applicationObj->getTotalApplications();
$recentUsers = $userObj->getRecentUsers(5);

// Get user type distribution
$userTypeDistribution = $userObj->getUserTypeDistribution();

// Prepare date format and interval based on time filter
$dateFormat = '%b %Y'; // default format
$groupBy = 'DATE(date)';
switch($timeFilter) {
    case '24h':
        $dateFormat = '%H:00';
        $groupBy = 'HOUR(date)';
        $interval = 'INTERVAL 24 HOUR';
        break;
    case '1w':
        $dateFormat = '%a';
        $groupBy = 'DATE(date)';
        $interval = 'INTERVAL 7 DAY';
        break;
    case '1m':
        $dateFormat = '%d %b';
        $groupBy = 'DATE(date)';
        $interval = 'INTERVAL 1 MONTH';
        break;
    case '3m':
        $interval = 'INTERVAL 3 MONTH';
        break;
    case '6m':
        $interval = 'INTERVAL 6 MONTH';
        break;
    case '1y':
        $interval = 'INTERVAL 1 YEAR';
        break;
}

// Get growth data based on selected time filter
$stmt = $db->prepare("
    SELECT 
        DATE_FORMAT(date, ?) as month,
        SUM(newJobSeekers) as newJobSeekers,
        SUM(newEmployers) as newEmployers,
        SUM(newJobs) as newJobs,
        SUM(newApplications) as newApplications
    FROM dailystats
    WHERE date >= DATE_SUB(CURRENT_TIMESTAMP, {$interval})
    GROUP BY {$groupBy}
    ORDER BY date ASC
");
$stmt->execute([$dateFormat]);
$growthData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format chart data
$chartMonths = [];
$newUsers = [];
$newJobs = [];
$newApplications = [];

foreach ($growthData as $data) {
    $chartMonths[] = $data['month'];
    $newUsers[] = $data['newJobSeekers'] + $data['newEmployers'];
    $newJobs[] = $data['newJobs'];
    $newApplications[] = $data['newApplications'];
}

// If no data exists, provide empty arrays with appropriate intervals
if (empty($chartMonths)) {
    switch($timeFilter) {
        case '24h':
            for ($i = 23; $i >= 0; $i--) {
                $chartMonths[] = date('H:00', strtotime("-$i hours"));
                $newUsers[] = 0;
                $newJobs[] = 0;
                $newApplications[] = 0;
            }
            break;
        case '1w':
            for ($i = 6; $i >= 0; $i--) {
                $chartMonths[] = date('D', strtotime("-$i days"));
                $newUsers[] = 0;
                $newJobs[] = 0;
                $newApplications[] = 0;
            }
            break;
        case '1m':
            for ($i = 29; $i >= 0; $i--) {
                $chartMonths[] = date('d M', strtotime("-$i days"));
                $newUsers[] = 0;
                $newJobs[] = 0;
                $newApplications[] = 0;
            }
            break;
        default:
            $months = intval(substr($timeFilter, 0, 1)) * (substr($timeFilter, 1) == 'y' ? 12 : 1);
            for ($i = $months - 1; $i >= 0; $i--) {
                $chartMonths[] = date('M Y', strtotime("-$i months"));
                $newUsers[] = 0;
                $newJobs[] = 0;
                $newApplications[] = 0;
            }
    }
}

$pageTitle = "Admin Dashboard - JOBEST";

// Custom CSS
$customCSS = "
<style>
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

.recent-users-card .user-avatar {
    width: 48px;
    height: 48px;
}
</style>";

include __DIR__ . '/../../includes/header.php';
?>

<div class="dashboard-container">
  <div class="container py-5">
    <!-- Dashboard Header -->
    <div class="d-flex justify-content-between align-items-center mb-5">
      <div>
                <h1 class="h3 fw-bold">Admin Dashboard</h1>
                <p class="text-muted mb-0">System overview and statistics</p>
      </div>
      <div class="d-flex gap-3">
                <a href="settings.php" class="btn btn-outline-secondary">
                    <i class="bi bi-gear me-2"></i>Settings
        </a>
                <a href="manage-users.php" class="btn btn-primary">
                    <i class="bi bi-people me-2"></i>Manage Users
        </a>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-5">
      <div class="col-md-6 col-xl-3">
                <a href="manage-users.php" class="text-decoration-none">
        <div class="card border-0 shadow-sm h-100 stats-card">
          <div class="card-body p-4">
            <div class="d-flex align-items-center">
              <div class="stats-icon bg-primary-subtle text-primary me-3">
                                    <i class="bi bi-people fs-4"></i>
              </div>
              <div>
                                    <h3 class="h2 fw-bold mb-0"><?= $totalUsers ?></h3>
                                    <span class="text-muted small">Total Users</span>
              </div>
            </div>
          </div>
          <div class="card-footer bg-white border-0 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-primary">Manage users</span>
              <i class="bi bi-arrow-right text-primary"></i>
          </div>
        </div>
                    </div>
                </a>
      </div>
      
      <div class="col-md-6 col-xl-3">
                <a href="manage-jobs.php" class="text-decoration-none">
        <div class="card border-0 shadow-sm h-100 stats-card">
          <div class="card-body p-4">
            <div class="d-flex align-items-center">
              <div class="stats-icon bg-success-subtle text-success me-3">
                                    <i class="bi bi-briefcase fs-4"></i>
              </div>
              <div>
                                    <h3 class="h2 fw-bold mb-0"><?= $totalJobs ?></h3>
                                    <span class="text-muted small">Total Jobs</span>
              </div>
            </div>
          </div>
          <div class="card-footer bg-white border-0 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-success">View jobs</span>
              <i class="bi bi-arrow-right text-success"></i>
          </div>
        </div>
                    </div>
                </a>
      </div>
      
      <div class="col-md-6 col-xl-3">
                <a href="reports.php" class="text-decoration-none">
        <div class="card border-0 shadow-sm h-100 stats-card">
          <div class="card-body p-4">
            <div class="d-flex align-items-center">
              <div class="stats-icon bg-info-subtle text-info me-3">
                                    <i class="bi bi-file-text fs-4"></i>
              </div>
              <div>
                                    <h3 class="h2 fw-bold mb-0"><?= $totalApplications ?></h3>
                                    <span class="text-muted small">Applications</span>
              </div>
            </div>
          </div>
          <div class="card-footer bg-white border-0 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-info">View reports</span>
              <i class="bi bi-arrow-right text-info"></i>
          </div>
        </div>
                    </div>
                </a>
      </div>
      
      <div class="col-md-6 col-xl-3">
                <a href="delete-user.php" class="text-decoration-none">
        <div class="card border-0 shadow-sm h-100 stats-card">
          <div class="card-body p-4">
            <div class="d-flex align-items-center">
              <div class="stats-icon bg-warning-subtle text-warning me-3">
                                    <i class="bi bi-buildings fs-4"></i>
              </div>
              <div>
                                    <h3 class="h2 fw-bold mb-0"><?= isset($userTypeDistribution['employer']) ? $userTypeDistribution['employer'] : 0 ?></h3>
                                    <span class="text-muted small">Companies</span>
              </div>
            </div>
          </div>
          <div class="card-footer bg-white border-0 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-warning">View companies</span>
              <i class="bi bi-arrow-right text-warning"></i>
                            </div>
                        </div>
                    </div>
            </a>
      </div>
    </div>

    <div class="row g-4 mb-5">
            <!-- User Distribution -->
            <div class="col-12">
        <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h2 class="h5 fw-bold mb-0">User Distribution</h2>
          </div>
          <div class="card-body">
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="userDistributionChart"></canvas>
            </div>
          </div>
        </div>
      </div>
      
            <!-- Growth Chart -->
            <div class="col-12">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h2 class="h5 fw-bold mb-0">Platform Growth</h2>
                            <div class="d-flex align-items-center">
                                <label class="me-2 text-muted">Time Range:</label>
                                <select class="form-select form-select-sm" style="width: 150px;" onchange="window.location.href='?time_filter=' + this.value">
                                    <?php foreach ($validFilters as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $timeFilter == $value ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
          </div>
          <div class="card-body">
                        <div class="chart-container" style="height: 400px;">
                            <canvas id="growthChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

        <!-- Recent Users -->
        <div class="card border-0 shadow-sm recent-users-card">
      <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h2 class="h5 fw-bold mb-0">Recent Users</h2>
                <a href="manage-users.php" class="btn btn-sm btn-outline-dark rounded-pill px-4">View All</a>
      </div>
      <div class="card-body p-0">
                <?php if (!empty($recentUsers)): ?>
          <div class="list-group list-group-flush">
                        <?php foreach ($recentUsers as $user): ?>
              <div class="list-group-item border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <h3 class="h6 fw-bold mb-0">
                                            <?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?>
                      </h3>
                                        <p class="text-muted small mb-0"></p>
                                            <?= htmlspecialchars($user['email']) ?>
                      </p>
                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-<?= roleBadgeColor($user['userType']) ?> px-3 py-2">
                                            <?= ucfirst($user['userType']) ?>
                    </span>
                                        <a href="manage-users.php?user_id=<?= $user['userId'] ?>" 
                                           class="btn btn-outline-dark rounded-pill px-4" style="margin-left: 20px;">
                                            <i class="bi bi-pencil me-2"></i>Edit
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
                        <p class="text-muted">No recent users</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Load Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Growth Chart
    const growthChart = new Chart(
        document.getElementById('growthChart').getContext('2d'),
    {
      type: 'bar',
      data: {
        labels: <?= json_encode($chartMonths) ?>,
        datasets: [
          {
            label: 'New Users',
            data: <?= json_encode($newUsers) ?>,
            backgroundColor: 'rgba(13, 110, 253, 0.5)',
            borderColor: '#0d6efd',
            borderWidth: 1
          },
          {
            label: 'New Jobs',
            data: <?= json_encode($newJobs) ?>,
            backgroundColor: 'rgba(25, 135, 84, 0.5)',
            borderColor: '#198754',
            borderWidth: 1
          },
          {
            label: 'Applications',
            data: <?= json_encode($newApplications) ?>,
            backgroundColor: 'rgba(13, 202, 240, 0.5)',
            borderColor: '#0dcaf0',
            borderWidth: 1
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
          },
          tooltip: {
            mode: 'index',
            intersect: false
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
            },
            ticks: {
              maxRotation: 45,
              minRotation: 45
            }
          }
        }
      }
    }
  );
  
    // User Distribution Chart
    const userDistributionChart = new Chart(
        document.getElementById('userDistributionChart').getContext('2d'),
    {
      type: 'doughnut',
      data: {
                labels: ['Job Seekers', 'Employers', 'Admins'],
        datasets: [{
          data: [
                        <?= $userTypeDistribution['jobSeeker'] ?? 0 ?>,
                        <?= $userTypeDistribution['employer'] ?? 0 ?>,
                        <?= $userTypeDistribution['admin'] ?? 0 ?>
          ],
          backgroundColor: [
                        '#0d6efd',
                        '#198754',
                        '#dc3545'
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
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
          }
        },
        cutout: '70%'
      }
    }
  );
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>