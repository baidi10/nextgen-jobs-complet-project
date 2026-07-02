<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Report.php';

// Admin authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: /pages/public/login.php');
    exit;
}

// Helper function to build pagination URLs with all parameters
function buildPaginationUrl($tabName, $pageParam, $pageNumber, $additionalParams = []) {
    $params = [
        'tab' => $tabName,
        $pageParam => $pageNumber,
        'start' => $_GET['start'] ?? date('Y-m-01'),
        'end' => $_GET['end'] ?? date('Y-m-d')
    ];
    
    // Add additional parameters if provided
    foreach ($additionalParams as $key => $value) {
        $params[$key] = $value;
    }
    
    return '?' . http_build_query($params);
}

$report = new Report();
$startDate = $_GET['start'] ?? date('Y-m-01');
$endDate = $_GET['end'] ?? date('Y-m-d');

// Get the current tab from the URL, default to 'users'
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'users';

// Generate report data
$reportData = $report->generateSystemReport($startDate, $endDate);

// Prepare chart data
$userActivityData = $report->getUserActivityData();
$jobDistributionData = $report->getJobDistributionData();

$pageTitle = "Reports - JOBEST";
include __DIR__ . '/../../includes/header.php';
?>

<div class="dashboard-container">
  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5 report-header-section" style="gap:25px;">
      <h1 class="h3 fw-bold">System Reports</h1>
      <div class="d-flex gap-3 report-header-actions" style="gap:25px;">
        <form class="d-flex gap-3 report-date-form" style="gap:25px;" method="get" action="">
          <input type="date" name="start" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
          <input type="date" name="end" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
          <select class="form-select" name="tab">
            <option value="users" <?= $tab == 'users' ? 'selected' : '' ?>>Users</option>
            <option value="jobs" <?= $tab == 'jobs' ? 'selected' : '' ?>>Jobs</option>
            <option value="companies" <?= $tab == 'companies' ? 'selected' : '' ?>>Companies</option>
          </select>
          <div class="report-buttons">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-filter"></i> Generate Report
            </button>
            <button type="button" class="btn btn-dark export-pdf-btn" onclick="exportToPDF()">
              <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </button>
          </div>
        </form>
      </div>
    </div>
    <style>
      .report-header-section { gap: 25px !important; }
      .report-header-actions { gap: 25px !important; }
      .report-date-form { gap: 25px !important; }
      .report-buttons {
        display: flex;
        gap: 10px;
      }
      .btn-primary {
        background-color:rgb(106, 105, 117);
        border-color:rgb(25, 34, 57);
      }
      .btn-dark {
        background-color: #1f2937;
        border-color: #1f2937;
      }
      .btn i {
        margin-right: 5px;
      }
      .chart-container {
        height: 300px;
        position: relative;
        margin: 0 auto;
      }
      .chart-card {
        height: 100%;
        display: flex;
        flex-direction: column;
      }
      .chart-card .card-body {
        flex: 1;
        display: flex;
        flex-direction: column;
      }
      .chart-card .card-title {
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
      }
      .chart-card .card-title i {
        color: #2a5bd7;
      }
      .chart-wrapper {
        flex: 1;
        position: relative;
        min-height: 250px;
      }
      .chart-legend {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-top: 0.5rem;
        flex-wrap: wrap;
      }
      .legend-item {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.75rem;
      }
      .legend-color {
        width: 10px;
        height: 10px;
        border-radius: 2px;
      }
      .form-select {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-color: #fff;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        padding: 0.5rem 2.5rem 0.5rem 1rem;
        font-size: 1rem;
        color: #22223b;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' fill='none' stroke='%2322233b' stroke-width='2' viewBox='0 0 24 24'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 1.25rem 1.25rem;
        transition: border-color 0.2s, box-shadow 0.2s;
      }
      .form-select:focus {
        border-color: #2a5bd7;
        outline: none;
        box-shadow: 0 0 0 2px rgba(42,91,215,0.15);
      }
      .form-select:hover {
        border-color: #a5b4fc;
      }
      @media (max-width: 768px) {
        .report-header-section { flex-direction: column; align-items: flex-start !important; gap: 18px !important; }
        .report-header-actions { flex-direction: column; gap: 18px !important; width: 100%; }
        .report-date-form { flex-direction: column; gap: 18px !important; width: 100%; }
        .report-buttons { width: 100%; }
        .report-buttons .btn { flex: 1; }
        .chart-container { height: 250px; }
        .form-select {
          font-size: 0.95rem;
        }
      }
    </style>

    <!-- Report Summary -->
    <div class="row g-4 mb-5">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="h4 fw-bold text-primary"><?= number_format($reportData['total_signups']) ?></div>
            <div class="text-muted small">New Signups</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="h4 fw-bold text-success"><?= number_format($reportData['total_job_postings']) ?></div>
            <div class="text-muted small">Job Postings</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="h4 fw-bold text-info"><?= number_format($reportData['total_applications']) ?></div>
            <div class="text-muted small">Applications</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="h4 fw-bold text-warning"><?= number_format($reportData['total_companies']) ?></div>
            <div class="text-muted small">New Companies</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts -->
    <div class="row g-4 mb-5">
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm chart-card">
          <div class="card-body">
            <h3 class="h6 fw-bold card-title">
              <i class="bi bi-bar-chart-fill"></i>
              User Activity
            </h3>
            <div class="chart-wrapper">
            <canvas id="userActivityChart"></canvas>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm chart-card">
          <div class="card-body">
            <h3 class="h6 fw-bold card-title">
              <i class="bi bi-pie-chart-fill"></i>
              Job Post Distribution
            </h3>
            <div class="chart-wrapper">
            <canvas id="jobDistributionChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Detailed Tables -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0">
        <h3 class="h6 fw-bold mb-0">Detailed Report Data</h3>
      </div>
      <div class="card-body">
        <!-- Tabs for different data views -->
        <ul class="nav nav-tabs mb-4">
          <li class="nav-item">
            <a class="nav-link<?= ($tab == 'users') ? ' active' : '' ?>" href="?tab=users&start=<?= urlencode($startDate) ?>&end=<?= urlencode($endDate) ?>">
              Users
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?= ($tab == 'jobs') ? ' active' : '' ?>" href="?tab=jobs&start=<?= urlencode($startDate) ?>&end=<?= urlencode($endDate) ?>">
              Jobs
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?= ($tab == 'companies') ? ' active' : '' ?>" href="?tab=companies&start=<?= urlencode($startDate) ?>&end=<?= urlencode($endDate) ?>">
              Companies
            </a>
          </li>
        </ul>

        <div class="tab-content">
          <div class="tab-pane fade<?= ($tab == 'users') ? ' show active' : '' ?>" id="users">
            <?php if ($tab == 'users') include __DIR__ . '/report-tabs/users.php'; ?>
          </div>
          <div class="tab-pane fade<?= ($tab == 'jobs') ? ' show active' : '' ?>" id="jobs">
            <?php if ($tab == 'jobs') include __DIR__ . '/report-tabs/jobs.php'; ?>
          </div>
          <div class="tab-pane fade<?= ($tab == 'companies') ? ' show active' : '' ?>" id="companies">
            <?php if ($tab == 'companies') include __DIR__ . '/report-tabs/companies.php'; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- PDF Modal -->
<div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width:90vw;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pdfModalLabel">Exported PDF Preview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="height:80vh;">
        <iframe id="pdfFrame" src="" style="width:100%;height:100%;border:none;"></iframe>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function exportToPDF() {
  const form = document.querySelector('.report-date-form');
  const formData = new FormData(form);
  fetch('export-report-pdf.php', {
    method: 'POST',
    body: formData
  })
  .then(response => {
    if (!response.ok) throw new Error('Failed to generate PDF');
    return response.blob();
  })
  .then(blob => {
    const url = URL.createObjectURL(blob);
    document.getElementById('pdfFrame').src = url;
    const modal = new bootstrap.Modal(document.getElementById('pdfModal'));
    modal.show();
  })
  .catch(err => {
    alert('Error generating PDF: ' + err.message);
  });
}

document.addEventListener('DOMContentLoaded', function() {
    // Helper function to ensure charts resize properly
    function createResponsiveChart(type, ctx, data, options = {}) {
        const defaultOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        boxWidth: 12,
                        padding: 10,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.7)',
                    padding: 10,
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    }
                }
            }
        };
        
        // Merge options
        const mergedOptions = { ...defaultOptions, ...options };
        
        // Create and return chart
        return new Chart(ctx, {
            type,
            data,
            options: mergedOptions
        });
    }

    // Initialize user activity chart - using bar chart with gradient
    const userActivityCtx = document.getElementById('userActivityChart');
    if (userActivityCtx) {
        // Clone the data to modify it
        const activityData = JSON.parse(JSON.stringify(<?= json_encode($userActivityData) ?>));
        
        // Create gradient fill for each dataset
        const gradients = [
            ['rgba(42, 91, 215, 0.8)', 'rgba(42, 91, 215, 0.1)'],
            ['rgba(34, 197, 94, 0.8)', 'rgba(34, 197, 94, 0.1)']
        ];
        
        activityData.datasets.forEach((dataset, index) => {
            const ctx = userActivityCtx.getContext('2d');
            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, gradients[index][0]);
            gradient.addColorStop(1, gradients[index][1]);
            
            // Set properties for the bar chart
            dataset.backgroundColor = gradient;
            dataset.borderRadius = 4;
            dataset.barThickness = 10;
        });
        
        createResponsiveChart('bar', userActivityCtx, activityData, {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                }
            }
        });
    }

    // Initialize job distribution chart - using polarArea instead of doughnut
    const jobDistributionCtx = document.getElementById('jobDistributionChart');
    if (jobDistributionCtx) {
        // Clone the data to modify it
        const jobData = JSON.parse(JSON.stringify(<?= json_encode($jobDistributionData) ?>));
        
        // Adjust the dataset properties for polar area chart
        if (jobData.datasets && jobData.datasets[0]) {
            jobData.datasets[0].backgroundColor = [
                'rgba(42, 91, 215, 0.7)',  // Blue
                'rgba(34, 197, 94, 0.7)',  // Green
                'rgba(239, 68, 68, 0.7)',  // Red
                'rgba(245, 158, 11, 0.7)', // Orange
                'rgba(139, 92, 246, 0.7)'  // Purple
            ];
            jobData.datasets[0].borderWidth = 2;
            jobData.datasets[0].borderColor = [
                'rgba(42, 91, 215, 1)',  // Blue
                'rgba(34, 197, 94, 1)',  // Green
                'rgba(239, 68, 68, 1)',  // Red
                'rgba(245, 158, 11, 1)', // Orange
                'rgba(139, 92, 246, 1)'  // Purple
            ];
        }
        
        createResponsiveChart('polarArea', jobDistributionCtx, jobData, {
            plugins: {
                legend: {
                    position: 'right'
                }
            },
            scales: {
                r: {
                    ticks: {
                        backdropColor: 'transparent',
                        display: false
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                }
            }
        });
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>