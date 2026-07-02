<?php
require_once __DIR__ . '/../../includes/dependencies.php';

$currentPage = max(1, $_GET['page'] ?? 1);
$perPage = 20;
$query = $_GET['q'] ?? '';
$category = $_GET['category'] ?? '';
$companyId = $_GET['companyId'] ?? '';
$filters = [
    'location' => $_GET['location'] ?? '',
    'jobType' => $_GET['jobType'] ?? '',
    'experienceLevel' => $_GET['experienceLevel'] ?? '',
    'exactTitle' => $_GET['exactTitle'] ?? false
];

// Debug current page value
$rawPageValue = $_GET['page'] ?? 'not set';
$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$debugInfo = "<!-- Debug: Raw page value: $rawPageValue, Current page: $currentPage, Current URL: $currentUrl -->";

if (!empty($category)) {
    $filters['category'] = $category;
}

if (!empty($companyId)) {
    $filters['companyId'] = $companyId;
}

$job = new Job();
$result = $job->searchJobs($query, $filters, $currentPage, $perPage);

// Add more debug information
$jobDebugInfo = "<!-- Debug: Page $currentPage of {$result['pages']}, " . 
                "Offset: " . (($currentPage - 1) * $perPage) . ", " .
                "Total jobs: {$result['total']}, " .
                "Jobs in this page: " . count($result['jobs']) . " -->";
$debugInfo .= $jobDebugInfo;

// Get available job types and experience levels from database
$jobTypes = $job->getJobTypes();
$experienceLevels = $job->getExperienceLevels();
$popularLocations = $job->getPopularLocations(5);

$pageTitle = "Tech Jobs | JOBEST";

// Add custom page styles
$pageStyles = [
  '<style>
    /* Additional header styles */
    .job-header {
      border: 1px solid #dee2e6;
      background-color: #fff;
      padding: 1.25rem 1.5rem;
      margin-bottom: 1.5rem;
      border-radius: 0.375rem;
    }
    .job-header h1 {
      font-size: 1.5rem;
      font-weight: 600;
      color: #212529;
      margin-bottom: 0.25rem;
    }
    .job-header p {
      color: #6c757d;
      font-size: 0.875rem;
      margin: 0;
    }
    .search-input {
      border-radius: 0.375rem;
      border: 1px solid #dee2e6;
      padding: 0.625rem 1rem;
      width: 240px;
      height: 40px;
    }
    .search-input:focus {
      box-shadow: 0 0 0 0.2rem rgba(0,0,0,0.1);
      border-color: #adb5bd;
    }
    .search-button {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background-color: #000;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-left: 0.5rem;
      border: none;
    }
    .search-button:hover {
      background-color: #222;
    }
    
    .hover-shadow:hover {
      box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .transition-300 {
      transition: all 0.3s ease;
    }
    .border-hover:hover {
      border-color: rgba(0,0,0,0.2) !important;
    }
    .job-filter-card {
      position: sticky;
      top: 20px;
      border-radius: 16px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      border: none;
    }
    .text-truncate-3 {
      overflow: hidden;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      line-height: 1.5;
    }
    /* Enhance form elements structure only */
    .form-control, .form-select {
      padding: 0.7rem 1rem;
      font-size: 0.9rem;
      border-radius: 8px;
      transition: all 0.25s ease;
      width: 100%;
      border-color: #dee2e6;
    }
    .form-control:focus, .form-select:focus {
      box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.15);
    }
    .form-label {
      font-size: 0.8rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-weight: 500;
      color: #444;
      margin-bottom: 0.5rem;
      display: flex;
      align-items: center;
    }
    .hover-lift {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .hover-lift:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    }
    .filter-heading {
      position: relative;
      margin-bottom: 1.5rem;
      padding-bottom: 0.75rem;
      border-bottom: 1px solid #e9ecef;
      color: #333;
      font-weight: 600;
    }
    .filter-button {
      font-weight: 600;
      letter-spacing: 0.5px;
      padding: 0.75rem 1rem;
      margin-top: 1.5rem;
      transition: all 0.2s ease;
      background-color: #000;
      border-color: #000;
    }
    .filter-button:hover {
      background-color: #222;
      border-color: #222;
    }
    .filter-card-inner {
      padding: 1.5rem;
    }
    .filter-section {
      margin-bottom: 1.25rem;
    }
    /* Professional job card styles */
    .job-card {
      border-radius: 12px;
      border: none;
      overflow: hidden;
      transition: all 0.3s ease;
      margin-bottom: 1.5rem;
    }
    .job-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
    }
    .company-logo {
      width: 52px;
      height: 52px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #f8f9fa;
      border: 1px solid #e9ecef;
      overflow: hidden;
    }
    .company-logo img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .company-placeholder {
      background: linear-gradient(135deg, #f5f7fa, #e4e8eb);
      color: #6c757d;
      font-weight: bold;
      font-size: 1rem;
    }
    .job-title {
      font-weight: 700;
      color: #212529;
      line-height: 1.4;
      transition: color 0.2s;
    }
    .job-title:hover {
      color:rgb(0, 0, 0);
    }
    .job-meta-badge {
      font-size: 0.75rem;
      padding: 0.5rem 0.75rem;
      border-radius: 50px;
      background-color: #f8f9fa;
      color: #495057;
      display: inline-flex;
      align-items: center;
      margin-right: 0.5rem;
      margin-bottom: 0.5rem;
      border: 1px solid #e9ecef;
    }
    .job-meta-badge i {
      margin-right: 0.25rem;
      color: #6c757d;
    }
    .job-salary {
      font-weight: 600;
      color: #198754;
    }
    .job-date {
      font-size: 0.75rem;
      color: #6c757d;
    }
    .save-job-btn {
      width: 36px;
      height: 36px;
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 10;
      position: relative;
    }
    @media (min-width: 768px) {
      .job-listings-container .col-md-6 {
        margin-bottom: 2rem;
      }
      .job-listings-container .job-card {
        height: calc(100% - 1rem);
        margin-bottom: 0;
      }
    }
    /* Additional spacing and layout improvements */
    .job-listings-container {
      margin-top: 0.5rem;
    }
    .job-meta-badges-container {
      margin-bottom: 1rem;
      min-height: 40px;
    }
    .job-card {
      box-shadow: 0 2px 8px rgba(0,0,0,0.05) !important;
    }
    .job-card:hover {
      box-shadow: 0 12px 30px rgba(0,0,0,0.1) !important;
    }
    .job-card .company-logo {
      border-radius: 8px;
    }
    .job-card .card-body {
      padding: 1.5rem;
    }
    /* Add subtle border between cards */
    @media (max-width: 767px) {
      .job-listings-container .col-md-6:not(:last-child) .job-card {
        border-bottom: 1px solid #eee;
      }
    }
    /* More space around badges */
    .job-meta-badge {
      margin-right: 0.75rem;
      margin-bottom: 0.75rem;
    }
    /* Better spacing for the empty state */
    .empty-state-container {
      padding: 3rem 1.5rem;
    }
    .empty-state-container .btn {
      margin-top: 1rem;
    }
    .navbar, .header {
      z-index: 1030;
      position: sticky;
      top: 0;
    }
    .company-logo-small {
      width: 32px !important;
      height: 32px !important;
      object-fit: cover;
      border-radius: 8px;
      background: #f8fafc;
      border: 1px solid #e0e7ef;
      display: inline-block;
    }
    /* Search input styles */
    .input-group {
      transition: all 0.2s ease;
    }

    .input-group:focus-within {
      box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.15);
    }

    .input-group .form-control {
      border: none;
      background: #f8f9fa;
      font-size: 0.95rem;
      padding: 0.7rem 1rem;
    }

    .input-group .form-control:focus {
      box-shadow: none;
      background: #f8f9fa;
    }

    .input-group .btn-dark {
      border-top-left-radius: 0;
      border-bottom-left-radius: 0;
      padding: 0.7rem 1rem;
      background: #111;
      border: none;
    }

    .input-group .btn-dark:hover {
      background: #222;
    }

    /* Job header styles */
    .job-header {
      background: #fff;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 2px 15px rgba(0,0,0,0.05);
    }

    .job-header h1 {
      font-size: 1.5rem;
      font-weight: 600;
      color: #212529;
      margin-bottom: 0.25rem;
    }

    .job-header p {
      color: #6c757d;
      font-size: 0.875rem;
      margin: 0;
    }

    @media (max-width: 768px) {
      .job-header {
        padding: 1rem;
      }
      
      .job-header .input-group {
        max-width: 100%;
        margin-top: 1rem;
      }
      
      .job-header h1 {
        font-size: 1.25rem;
      }
    }
  </style>'
];

include __DIR__ . '/../../includes/header.php';
?>

<div class="container py-5">
  <?= $debugInfo ?>
  <div class="row">
    <!-- Filters Sidebar -->
    <div class="col-lg-3 mb-4">
      <div class="card shadow-sm job-filter-card position-sticky" style="top: 20px; max-height: calc(100vh - 40px); overflow-y: auto;">
        <div class="card-body filter-card-inner p-4 d-flex flex-column" style="height:100%;">
          <h3 class="h5 fw-bold filter-heading mb-4">Filter Jobs</h3>
          
          <form action="<?= Config::BASE_URL ?>/pages/public/jobs.php" method="get" class="filter-form d-flex flex-column h-100">
            <!-- Keep current search query -->
            <?php if (!empty($query)): ?>
            <input type="hidden" name="q" value="<?= htmlspecialchars($query) ?>">
            <?php endif; ?>
            
            <!-- Location Filter -->
            <div class="filter-section mb-4">
              <label class="form-label d-flex align-items-center text-muted mb-2">
                <i class="bi bi-geo-alt me-2"></i>LOCATION
              </label>
              <?php if (!empty($popularLocations)): ?>
                <select class="form-select" name="location">
                  <option value="">All Locations</option>
                  <?php foreach ($popularLocations as $location): ?>
                    <option value="<?= htmlspecialchars($location) ?>" <?= $filters['location'] === $location ? 'selected' : '' ?>><?= htmlspecialchars($location) ?></option>
                  <?php endforeach; ?>
                  <option value="custom" <?= !empty($filters['location']) && !in_array($filters['location'], $popularLocations) ? 'selected' : '' ?>>Other location...</option>
                </select>
                <div id="customLocationField" class="mt-2 <?= (!empty($filters['location']) && !in_array($filters['location'], $popularLocations) && $filters['location'] !== 'custom') ? '' : 'd-none' ?>">
                  <div class="input-group">
                    <span class="input-group-text border-end-0 bg-light">
                      <i class="bi bi-geo-alt text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0 ps-0" id="customLocation" placeholder="Enter city or region" 
                           value="<?= (!empty($filters['location']) && !in_array($filters['location'], $popularLocations)) ? htmlspecialchars($filters['location']) : '' ?>">
                  </div>
                </div>
              <?php else: ?>
                <div class="input-group">
                  <span class="input-group-text border-end-0 bg-light">
                    <i class="bi bi-geo-alt text-muted"></i>
                  </span>
                  <input type="text" class="form-control border-start-0 ps-0" 
                         name="location" placeholder="City or country"
                         value="<?= htmlspecialchars($filters['location']) ?>">
                  </div>
              <?php endif; ?>
            </div>
            
            <!-- Job Type Filter -->
            <div class="filter-section mb-4">
              <label class="form-label d-flex align-items-center text-muted mb-2">
                <i class="bi bi-briefcase me-2"></i>JOB TYPE
              </label>
              <select class="form-select" name="jobType">
                <option value="">All Types</option>
                <?php if (!empty($jobTypes)): ?>
                  <?php foreach ($jobTypes as $type): ?>
                    <option value="<?= htmlspecialchars($type) ?>" <?= $filters['jobType'] === $type ? 'selected' : '' ?>><?= htmlspecialchars(ucfirst($type)) ?></option>
                  <?php endforeach; ?>
                <?php else: ?>
                  <option value="fullTime" <?= $filters['jobType'] === 'fullTime' ? 'selected' : '' ?>>Full Time</option>
                  <option value="partTime" <?= $filters['jobType'] === 'partTime' ? 'selected' : '' ?>>Part Time</option>
                  <option value="remote" <?= $filters['jobType'] === 'remote' ? 'selected' : '' ?>>Remote</option>
                <?php endif; ?>
              </select>
            </div>
            
            <!-- Experience Filter -->
            <div class="filter-section mb-4">
              <label class="form-label d-flex align-items-center text-muted mb-2">
                <i class="bi bi-award me-2"></i>EXPERIENCE LEVEL
              </label>
              <select class="form-select" name="experienceLevel">
                <option value="">All Levels</option>
                <option value="entry" <?= $filters['experienceLevel'] === 'entry' ? 'selected' : '' ?>>Entry Level</option>
                <option value="mid" <?= $filters['experienceLevel'] === 'mid' ? 'selected' : '' ?>>Mid Level</option>
                <option value="senior" <?= $filters['experienceLevel'] === 'senior' ? 'selected' : '' ?>>Senior Level</option>
                <option value="lead" <?= $filters['experienceLevel'] === 'lead' ? 'selected' : '' ?>>Lead Level</option>
                <option value="executive" <?= $filters['experienceLevel'] === 'executive' ? 'selected' : '' ?>>Executive Level</option>
              </select>
            </div>
            <button type="submit" class="btn btn-dark filter-button w-100 mt-auto">
              <i class="bi bi-funnel-fill me-2"></i>Apply Filters
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Job Listings -->
    <div class="col-lg-9">
      <div class="job-header d-flex flex-wrap justify-content-between align-items-center">
        <div>
          <h1>Tech Jobs</h1>
          <p><?= number_format($result['total']) ?> opportunities available</p>
        </div>
        <div>
          <form action="<?= Config::BASE_URL ?>/pages/public/jobs.php" method="get" class="d-flex align-items-center">
            <?php foreach ($filters as $key => $value): ?>
              <?php if (!empty($value)): ?>
                <input type="hidden" name="<?= $key ?>" value="<?= htmlspecialchars($value) ?>">
              <?php endif; ?>
            <?php endforeach; ?>
            
            <div class="input-group rounded shadow-sm" style="max-width: 350px;">
              <input type="text" class="form-control border-0 py-2 px-3 bg-light" 
                     name="q" placeholder="Search by job title" 
                     value="<?= htmlspecialchars($query) ?>" 
                     style="font-size: 1rem;">
              <button type="submit" class="btn btn-dark px-3">
                <i class="bi bi-search"></i>
              </button>
            </div>
          </form>
        </div>
      </div>

      <?php if (!empty($query) || !empty(array_filter($filters))): ?>
      <div class="mb-4 p-3 bg-light rounded-3 border">
        <div class="d-flex align-items-center">
          <div class="me-3">
            <span class="fw-medium">Active filters:</span>
          </div>
          <div class="d-flex flex-wrap gap-2">
            <?php if (!empty($query)): ?>
              <span class="badge bg-primary rounded-pill px-3 py-2">
                Search: <?= htmlspecialchars($query) ?>
                <a href="<?= Config::BASE_URL ?>/pages/public/jobs.php<?= !empty(array_filter($filters)) ? '?' . http_build_query(array_filter($filters)) : '' ?>" class="text-white ms-2 text-decoration-none">
                  <i class="bi bi-x-circle"></i>
                </a>
              </span>
            <?php endif; ?>
            
            <?php foreach ($filters as $key => $value): ?>
              <?php if (!empty($value)): ?>
                <?php 
                  $filterName = [
                    'location' => 'Location',
                    'jobType' => 'Job Type',
                    'experienceLevel' => 'Experience'
                  ][$key] ?? $key;
                  
                  $displayValue = $value;
                  // Clean up display values
                  if ($key === 'jobType') {
                    $displayValue = ucfirst($value);
                  } else if ($key === 'experienceLevel') {
                    $displayValue = ucfirst(preg_replace('/([a-z])([A-Z])/', '$1 $2', $value));
                  }
                ?>
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
                  <?= $filterName ?>: <?= htmlspecialchars($displayValue) ?>
                  <a href="<?= Config::BASE_URL ?>/pages/public/jobs.php?<?= http_build_query(array_merge(array_filter($filters, function($k) use ($key) { return $k !== $key; }, ARRAY_FILTER_USE_KEY), !empty($query) ? ['q' => $query] : [])) ?>" class="text-primary ms-2 text-decoration-none">
                    <i class="bi bi-x-circle"></i>
                  </a>
                </span>
              <?php endif; ?>
            <?php endforeach; ?>
            
            <a href="<?= Config::BASE_URL ?>/pages/public/jobs.php" class="btn btn-sm btn-link text-decoration-none">Clear all</a>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <div class="row gy-5 gx-4 job-listings-container">
        <?php if (count($result['jobs']) > 0): ?>
          <?php foreach ($result['jobs'] as $job): ?>
            <div class="col-md-6 mb-4 pb-2">
              <div class="card job-card h-100">
                <div class="card-body">
                  <div class="d-flex align-items-center mb-3">
                    <?php if (!empty($job['companyLogo'])): ?>
                      <div class="company-logo me-3">
                        <img src="<?= Config::BASE_URL ?>/assets/uploads/company_logos/<?= htmlspecialchars($job['companyLogo']) ?>" alt="<?= htmlspecialchars($job['companyName']) ?>" class="company-logo-small rounded" width="32" height="32" style="object-fit: cover;">
                      </div>
                    <?php else: ?>
                      <div class="company-logo company-placeholder me-3">
                        <?= substr(htmlspecialchars($job['companyName'] ?? 'CO'), 0, 2) ?>
                      </div>
                    <?php endif; ?>
                    <div>
                      <h5 class="card-title mb-1 fs-6"><a href="<?= Config::BASE_URL ?>/pages/public/job-details.php?id=<?= $job['jobId'] ?>" class="text-decoration-none job-title stretched-link"><?= htmlspecialchars($job['jobTitle']) ?></a></h5>
                      <p class="text-muted small mb-0"><?= htmlspecialchars($job['companyName']) ?></p>
                    </div>
                  </div>
                  
                  <?php if (!empty($job['description'])): ?>
                    <p class="card-text small text-muted mb-3 text-truncate-3"><?= htmlspecialchars(strip_tags($job['description'])) ?></p>
                  <?php endif; ?>
                  
                  <div class="d-flex flex-wrap job-meta-badges-container">
                    <?php if (!empty($job['location'])): ?>
                      <span class="job-meta-badge">
                        <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($job['location']) ?>
                      </span>
                    <?php endif; ?>
                    
                    <?php if (!empty($job['jobType'])): ?>
                      <span class="job-meta-badge">
                        <i class="bi bi-briefcase"></i> <?= htmlspecialchars(ucfirst($job['jobType'])) ?>
                      </span>
                    <?php endif; ?>
                    
                    <?php if (!empty($job['experienceLevel'])): ?>
                      <span class="job-meta-badge">
                        <i class="bi bi-layers"></i> <?= htmlspecialchars(ucfirst(preg_replace('/([a-z])([A-Z])/', '$1 $2', $job['experienceLevel']))) ?>
                      </span>
                    <?php endif; ?>
                  </div>
                  
                  <?php if (!empty($job['salaryMin']) && !empty($job['salaryMax'])): ?>
                    <div class="job-salary mb-3">
                      <i class="bi bi-currency-dollar"></i> $<?= number_format($job['salaryMin']) ?> - $<?= number_format($job['salaryMax']) ?>
                    </div>
                  <?php endif; ?>
                  
                  <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                    <span class="job-date">
                      <i class="bi bi-clock me-1"></i> 
                      <?php 
                        $jobDate = new DateTime($job['createdAt']);
                        $now = new DateTime();
                        $diff = $now->diff($jobDate);
                        
                        if ($diff->days == 0) {
                          echo 'Today';
                        } elseif ($diff->days == 1) {
                          echo 'Yesterday';
                        } elseif ($diff->days < 7) {
                          echo $diff->days . ' days ago';
                        } else {
                          echo $jobDate->format('M d, Y');
                        }
                      ?>
                    </span>
                    <?php if (isLoggedIn() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'jobSeeker'): ?>
                      <button class="btn btn-sm btn-outline-primary rounded-circle save-job-btn" data-job-id="<?= $job['jobId'] ?>" title="Save Job">
                        <i class="bi bi-bookmark"></i>
                      </button>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12">
            <div class="card border-0">
              <div class="card-body text-center empty-state-container">
                <div class="mb-3">
                  <i class="bi bi-search fs-1 text-muted"></i>
                </div>
                <h3 class="h5 mb-3">No jobs found</h3>
                <p class="text-muted mb-4">Try adjusting your filters or search terms</p>
                <a href="<?= Config::BASE_URL ?>/pages/public/jobs.php" class="btn btn-outline-primary rounded-pill px-4">Clear all filters</a>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Pagination -->
      <?php if ($result['pages'] > 1): ?>
        <nav class="mt-5">
          <?php 
          include_once __DIR__ . '/../../includes/pagination.php';
          
          // Build the base URL with query parameters but without page param
          $queryParams = array_filter($filters);
          if (!empty($query)) {
            $queryParams['q'] = $query;
          }
          
          // Generate the full URL
          $fullBaseUrl = Config::BASE_URL . '/pages/public/jobs.php';
          if (!empty($queryParams)) {
            $fullBaseUrl .= '?' . http_build_query($queryParams);
          }
          
          // Call the pagination function with the correct parameters
          renderPagination($currentPage, $result['pages'], $fullBaseUrl, $result['total']);
          ?>
        </nav>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Add pagination styles -->
<style>
  .pagination-container {
    margin: 3rem 0 1.5rem;
  }
  .pagination {
    gap: 0.35rem;
  }
  .page-link {
    border-radius: 4px !important;
    padding: 0.6rem 0.9rem;
    color: #444;
    font-weight: 500;
    border-color: #dee2e6;
    transition: all 0.2s ease;
    min-width: 40px;
    text-align: center;
  }
  .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
    font-weight: 600;
    box-shadow: 0 2px 5px rgba(13, 110, 253, 0.25);
  }
  .page-link:focus {
    box-shadow: 0 0 0 0.25rem rgba(13,110,253,0.15);
    z-index: 3;
  }
  .page-link:hover {
    background-color: #f0f4ff;
    color: #0d6efd;
    border-color: #c2d6ff;
    transform: translateY(-2px);
  }
  .page-item.disabled .page-link {
    color: #adb5bd;
    background-color: #f8f9fa;
    border-color: #eee;
  }
  .pagination-info {
    text-align: center;
    color: #555;
    font-size: 0.9rem;
    margin-bottom: 1rem;
    background-color: #f8f9fa;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    display: inline-block;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  }
  /* Mobile responsiveness */
  @media (max-width: 576px) {
    .pagination {
      gap: 0.25rem;
    }
    .page-link {
      padding: 0.5rem 0.7rem;
      font-size: 0.875rem;
    }
    .pagination-info {
      font-size: 0.8rem;
      width: 100%;
    }
    .page-item:nth-child(1), 
    .page-item:nth-child(2),
    .page-item:nth-last-child(1), 
    .page-item:nth-last-child(2) {
      display: flex;
    }
  }
  /* Filter sidebar styles */
  .job-filter-card {
    border: none;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 2px 15px rgba(0,0,0,0.05);
  }

  .job-filter-card::-webkit-scrollbar {
    width: 6px;
  }

  .job-filter-card::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
  }

  .job-filter-card::-webkit-scrollbar-thumb {
    background: #ddd;
    border-radius: 10px;
  }

  .job-filter-card::-webkit-scrollbar-thumb:hover {
    background: #ccc;
  }

  .filter-section {
    position: relative;
  }

  .filter-section:not(:last-child)::after {
    content: '';
    position: absolute;
    bottom: -1rem;
    left: 0;
    right: 0;
    height: 1px;
    background: #f0f0f0;
  }

  .form-label {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
  }

  .form-select, .form-control {
    border-radius: 8px;
    border: 1px solid #e0e0e0;
    padding: 0.6rem 1rem;
    font-size: 0.9rem;
    transition: all 0.2s ease;
  }

  .form-select:focus, .form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.15);
  }

  .filter-button {
    background: #111 !important;
    border: none;
    color: #fff !important;
  }

  .filter-button:hover {
    background: #222 !important;
  }

  /* Mobile styles */
  @media (max-width: 991.98px) {
    .job-filter-card {
      position: relative !important;
      top: 0 !important;
      max-height: none !important;
    }
    
    .filter-card-inner {
      padding: 1.5rem !important;
    }
  }

  .filter-card-inner {
    padding-bottom: 2rem !important;
  }

  .input-group .form-control:focus {
    box-shadow: none;
    background: #f8f9fa;
  }
  .input-group .btn-dark {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<!-- Add JavaScript to improve the search form functionality -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Fix pagination arrow buttons by forcing direct URL navigation
    const paginationArrows = document.querySelectorAll('.first-page-btn .page-link, .prev-page-btn .page-link, .next-page-btn .page-link, .last-page-btn .page-link');
    paginationArrows.forEach(link => {
      if (link.getAttribute('href') && link.getAttribute('href') !== '#') {
        // Force direct navigation instead of event handling
        const url = link.getAttribute('href');
        link.setAttribute('onclick', 'window.location.href = "' + url + '"; return false;');
        
        // Add data attribute to prevent Bootstrap's pagination handlers
        link.setAttribute('data-bs-custom', 'true');
        
        // Remove any Bootstrap event listeners
        link.classList.remove('page-link');
        link.classList.add('page-link-custom');
        setTimeout(() => {
          link.classList.remove('page-link-custom');
          link.classList.add('page-link');
        }, 0);
      }
    });
    
    // Also fix all pagination links to ensure they work correctly
    const allPaginationLinks = document.querySelectorAll('.pagination .page-link');
    allPaginationLinks.forEach(link => {
      if (link.getAttribute('href') && link.getAttribute('href') !== '#') {
        const url = link.getAttribute('href');
        link.setAttribute('onclick', 'window.location.href = "' + url + '"; return false;');
      }
    });
    
    // Auto-submit the form when the select fields change
    const filterSelects = document.querySelectorAll('select[name="jobType"], select[name="experienceLevel"]');
    filterSelects.forEach(select => {
      select.addEventListener('change', function() {
        this.closest('form').submit();
      });
    });
    
    // Handle custom location field
    const locationSelect = document.querySelector('select[name="location"]');
    const customLocationField = document.getElementById('customLocationField');
    const customLocationInput = document.getElementById('customLocation');
    
    if (locationSelect && customLocationField && customLocationInput) {
      locationSelect.addEventListener('change', function() {
        if (this.value === 'custom') {
          customLocationField.classList.remove('d-none');
          customLocationInput.focus();
        } else {
          customLocationField.classList.add('d-none');
        }
      });
      
      // Handle form submission with custom location
      document.querySelector('form').addEventListener('submit', function(e) {
        if (locationSelect.value === 'custom' && customLocationInput.value.trim() !== '') {
          e.preventDefault();
          // Replace hidden input or add a new one
          locationSelect.value = customLocationInput.value.trim();
          this.submit();
        }
      });
    }
    
    // Add save job functionality
    const saveButtons = document.querySelectorAll('.save-job-btn');
    saveButtons.forEach(button => {
      button.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const jobId = this.getAttribute('data-job-id');
        
        // Add visual feedback
        button.disabled = true;
        const originalContent = button.innerHTML;
        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
        
        // Send AJAX request to save the job
        fetch('<?= Config::BASE_URL ?>/ajax/save-job.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'job_id=' + jobId
        })
        .then(response => response.json())
        .then(data => {
          button.disabled = false;
          
          if (data.success) {
            // Update button appearance
            button.innerHTML = '<i class="bi bi-bookmark-fill"></i>';
            button.classList.replace('btn-outline-primary', 'btn-primary');
            
            // Show success toast
            const toast = document.createElement('div');
            toast.className = 'position-fixed bottom-0 end-0 p-3';
            toast.style.zIndex = '5';
            toast.innerHTML = `
              <div class="toast show bg-success text-white" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-body d-flex align-items-center">
                  <i class="bi bi-check-circle me-2 fs-5"></i> 
                  <div>
                    <div class="fw-medium">Job saved!</div>
                    <div class="small">Added to your favorites</div>
                  </div>
                  <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
              </div>
            `;
            document.body.appendChild(toast);
            
            setTimeout(() => {
              toast.remove();
            }, 3000);
            
            // Close toast when clicking the close button
            const closeBtn = toast.querySelector('.btn-close');
            if (closeBtn) {
              closeBtn.addEventListener('click', function() {
                toast.remove();
              });
            }
          } else if (data.redirect) {
            // Show login prompt
            const loginPrompt = document.createElement('div');
            loginPrompt.className = 'position-fixed bottom-0 end-0 p-3';
            loginPrompt.style.zIndex = '5';
            loginPrompt.innerHTML = `
              <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                  <strong class="me-auto">Sign in required</strong>
                  <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                  <p>Please sign in as a job seeker to save jobs to your profile</p>
                  <div class="mt-2 pt-2 border-top">
                    <a href="${data.redirect}" class="btn btn-primary btn-sm">Sign in</a>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="toast">Not now</button>
                  </div>
                </div>
              </div>
            `;
            document.body.appendChild(loginPrompt);
            
            // Close prompt when clicking the close button
            const closePromptBtn = loginPrompt.querySelector('.btn-close');
            if (closePromptBtn) {
              closePromptBtn.addEventListener('click', function() {
                loginPrompt.remove();
              });
            }
            
            // Close prompt when clicking "Not now"
            const notNowBtn = loginPrompt.querySelector('.btn-secondary');
            if (notNowBtn) {
              notNowBtn.addEventListener('click', function() {
                loginPrompt.remove();
              });
            }
          }
        })
        .catch(error => {
          console.error('Error saving job:', error);
          button.disabled = false;
          button.innerHTML = originalContent;
        });
      });
    });
    
    // Add responsive behavior for filters on mobile
    const screenWidth = window.innerWidth;
    if (screenWidth < 992) { // Bootstrap lg breakpoint
      const filterCard = document.querySelector('.job-filter-card');
      if (filterCard) {
        // Add a toggle button for filters on mobile
        const toggleButton = document.createElement('button');
        toggleButton.className = 'btn btn-outline-primary rounded-pill mb-3 w-100 d-lg-none';
        toggleButton.innerHTML = '<i class="bi bi-funnel me-2"></i> Show Filters';
        toggleButton.addEventListener('click', function() {
          const filterCardBody = filterCard.querySelector('.card-body');
          if (filterCardBody.classList.contains('d-none')) {
            filterCardBody.classList.remove('d-none');
            this.innerHTML = '<i class="bi bi-funnel-fill me-2"></i> Hide Filters';
          } else {
            filterCardBody.classList.add('d-none');
            this.innerHTML = '<i class="bi bi-funnel me-2"></i> Show Filters';
          }
        });
        
        // Insert the toggle button before the filter card
        filterCard.parentNode.insertBefore(toggleButton, filterCard);
        
        // Hide filters by default on mobile
        const filterCardBody = filterCard.querySelector('.card-body');
        filterCardBody.classList.add('d-none', 'd-lg-block');
      }
    }

    // Make sure pagination correctly highlights the current page
    function highlightCurrentPage() {
      // Get current page from URL
      const urlParams = new URLSearchParams(window.location.search);
      const currentPage = parseInt(urlParams.get('page') || 1);
      const totalPages = parseInt(document.querySelector('.pagination-info')?.textContent.match(/of (\d+)/)?.[1] || 1);
      
      // Find and highlight the correct page button
      const pageButtons = document.querySelectorAll('.pagination .page-item');
      pageButtons.forEach(button => {
        const link = button.querySelector('.page-link');
        if (link && link.textContent.trim() === currentPage.toString()) {
          button.classList.add('active');
          link.setAttribute('aria-current', 'page');
        } else if (link && /^\d+$/.test(link.textContent.trim())) {
          // Only remove active class from numeric page links
          button.classList.remove('active');
          link.removeAttribute('aria-current');
        }
      });
      
      // Update the pagination-info text if it exists
      const paginationInfo = document.querySelector('.pagination-info .fw-medium');
      if (paginationInfo) {
        const totalPagesMatch = paginationInfo.textContent.match(/of (\d+)/);
        if (totalPagesMatch && totalPagesMatch[1]) {
          paginationInfo.textContent = 'Showing page ' + currentPage + ' of ' + totalPagesMatch[1];
        }
      }
      
      // Enable/disable arrow buttons based on current page
      const firstPageBtn = document.querySelector('.first-page-btn');
      const prevPageBtn = document.querySelector('.prev-page-btn');
      const nextPageBtn = document.querySelector('.next-page-btn');
      const lastPageBtn = document.querySelector('.last-page-btn');
      
      if (currentPage <= 1) {
        // Disable first and previous buttons on first page
        if (firstPageBtn) firstPageBtn.classList.add('disabled');
        if (prevPageBtn) prevPageBtn.classList.add('disabled');
      } else {
        // Enable first and previous buttons
        if (firstPageBtn) firstPageBtn.classList.remove('disabled');
        if (prevPageBtn) prevPageBtn.classList.remove('disabled');
      }
      
      if (currentPage >= totalPages) {
        // Disable next and last buttons on last page
        if (nextPageBtn) nextPageBtn.classList.add('disabled');
        if (lastPageBtn) lastPageBtn.classList.add('disabled');
      } else {
        // Enable next and last buttons
        if (nextPageBtn) nextPageBtn.classList.remove('disabled');
        if (lastPageBtn) lastPageBtn.classList.remove('disabled');
      }
    }
    
    // Run on page load
    highlightCurrentPage();
  });
</script>