<?php
require_once __DIR__ . '/../../includes/dependencies.php';

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'jobSeeker') {
    header('Location: /pages/public/login.php');
    exit;
}

$currentPage = max(1, $_GET['page'] ?? 1);
$perPage = 20;
$query = $_GET['q'] ?? '';
$filters = [
    'location' => $_GET['location'] ?? '',
    'jobType' => $_GET['jobType'] ?? '',
    'experienceLevel' => $_GET['experienceLevel'] ?? '',
    'companyId' => $_GET['company'] ?? null,
    'category' => $_GET['category'] ?? ''
];

// Map category slugs to keywords for better search
$categoryKeywords = [
    'software-development' => ['developer', 'engineer', 'programming', 'software'],
    'data-science' => ['data', 'analyst', 'science', 'ML', 'machine learning'],
    'product-management' => ['product', 'manager', 'management'],
    'ux-design' => ['UX', 'UI', 'design', 'designer'],
    'devops' => ['devops', 'infrastructure', 'cloud', 'systems'],
    'marketing' => ['marketing', 'brand', 'growth', 'SEO'],
    'sales' => ['sales', 'account', 'business development']
];

// If category is set, add its keywords to the search query
if (!empty($filters['category']) && isset($categoryKeywords[$filters['category']])) {
    $categoryQuery = implode(' OR ', array_map(function($keyword) {
        return "jobTitle LIKE '%$keyword%' OR jobDescription LIKE '%$keyword%'";
    }, $categoryKeywords[$filters['category']]));
    $query = !empty($query) ? "($query) AND ($categoryQuery)" : $categoryQuery;
}

$jobObj = new Job();
$result = $jobObj->searchJobs($query, $filters, $currentPage, $perPage);

// Get available job types and experience levels from database
$jobTypes = $jobObj->getJobTypes();
$experienceLevels = $jobObj->getExperienceLevels();
$popularLocations = $jobObj->getPopularLocations(5);

$pageTitle = "Find Jobs | JOBEST";

include __DIR__ . '/../../includes/header.php';
?>

<div class="container py-5">
  <div class="row">
    <!-- Filters Sidebar -->
    <div class="col-lg-3 mb-4">
      <div class="card shadow-sm job-filter-card position-sticky" style="top: 20px; max-height: calc(100vh - 40px); overflow-y: auto;">
        <div class="card-body filter-card-inner p-4 d-flex flex-column" style="height:100%;">
          <h3 class="h5 fw-bold filter-heading mb-4">Filter Jobs</h3>
          
          <form action="<?= Config::BASE_URL ?>/pages/user/jobs.php" method="get" class="filter-form d-flex flex-column h-100">
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
          <h1>Find Jobs</h1>
          <p><?= number_format($result['total']) ?> opportunities available</p>
        </div>
        <div>
          <form action="<?= Config::BASE_URL ?>/pages/user/jobs.php" method="get" class="d-flex align-items-center">
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
      <div class="mb-4 p-3 bg-light rounded-3 shadow-sm active-filters-container">
        <div class="d-flex align-items-center">
          <div class="me-3">
            <span class="fw-medium text-muted">Active filters:</span>
          </div>
          <div class="d-flex flex-wrap gap-2">
            <?php if (!empty($query)): ?>
              <span class="badge bg-primary rounded-pill px-3 py-2 active-filter-badge">
                Search: <?= htmlspecialchars($query) ?>
                <a href="<?= Config::BASE_URL ?>/pages/user/jobs.php<?= !empty(array_filter($filters)) ? '?' . http_build_query(array_filter($filters)) : '' ?>" class="text-white ms-2 text-decoration-none">
                  <i class="bi bi-x-circle"></i>
                </a>
              </span>
            <?php endif; ?>
            
            <?php foreach ($filters as $key => $value): ?>
              <?php if (!empty($value) && $key !== 'companyId'): // Exclude companyId from being displayed as a clearable filter ?>
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
                    $displayValue = match($value) {
                        'entry' => 'Entry Level',
                        'mid' => 'Mid Level',
                        'senior' => 'Senior Level',
                        'lead' => 'Lead Level',
                        'executive' => 'Executive Level',
                        default => ucfirst($value)
                    };
                  }
                ?>
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 active-filter-badge">
                  <?= $filterName ?>: <?= htmlspecialchars($displayValue) ?>
                  <a href="<?= Config::BASE_URL ?>/pages/user/jobs.php?<?= http_build_query(array_merge(array_filter($filters, function($k) use ($key) { return $k !== $key; }, ARRAY_FILTER_USE_KEY), !empty($query) ? ['q' => $query] : [])) ?>" class="text-primary ms-2 text-decoration-none">
                    <i class="bi bi-x-circle"></i>
                  </a>
                </span>
              <?php endif; ?>
            <?php endforeach; ?>

            <?php if (!empty(array_filter($filters, function($v, $k) { return $k !== 'companyId' && !empty($v); }, ARRAY_FILTER_USE_BOTH)) || !empty($query)): // Only show Clear All if there are actual filters to clear ?>
              <a href="<?= Config::BASE_URL ?>/pages/user/jobs.php<?= !empty($filters['companyId']) ? '?company=' . htmlspecialchars($filters['companyId']) : '' ?>" 
                 class="btn btn-sm btn-link text-decoration-none active-filter-clear-all">
                 Clear all
              </a>
            <?php endif; ?>

          </div>
        </div>
      </div>
      <?php endif; ?>
      <div class="row gy-5 gx-4 job-listings-container">
        <?php if (count($result['jobs']) > 0): ?>
          <?php foreach ($result['jobs'] as $jobItem): ?>
            <?php
            $isSaved = false;
            if (isset($_SESSION['user_id'])) {
                $isSaved = $jobObj->isJobSaved($_SESSION['user_id'], $jobItem['jobId']);
            }
            ?>
            <div class="col-md-6 mb-4">
              <div class="card h-100 border-0 shadow-sm p-0" style="background: #fff; border-radius: 18px; box-shadow: 0 6px 32px rgba(0,0,0,0.10);">
                <div class="card-body d-flex flex-column p-4">
                  <div class="d-flex align-items-center mb-3">
                    <?php if (!empty($jobItem['companyLogo'])): ?>
                      <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; overflow: hidden;">
                        <img src="<?= Config::BASE_URL ?>/assets/uploads/company_logos/<?= htmlspecialchars($jobItem['companyLogo']) ?>" alt="<?= htmlspecialchars($jobItem['companyName']) ?>" class="w-100 h-100 object-fit-cover">
                      </div>
                    <?php else: ?>
                      <div class="bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; color: #333; font-weight: 600; font-size: 1rem;">
                        <?= substr(htmlspecialchars($jobItem['companyName'] ?? 'CO'), 0, 2) ?>
                      </div>
                    <?php endif; ?>
                    <div>
                      <div class="fw-bold fs-5 mb-1"><?= htmlspecialchars($jobItem['jobTitle']) ?></div>
                      <div class="text-muted small"><?= htmlspecialchars($jobItem['companyName']) ?></div>
                    </div>
                  </div>
                  <?php if (!empty($jobItem['description'])): ?>
                    <div class="text-muted small mb-3" style="min-height: 48px; line-height: 1.5; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                      <?= htmlspecialchars(strip_tags($jobItem['description'])) ?>
                    </div>
                  <?php endif; ?>
                  <div class="d-flex flex-wrap gap-2 mb-3">
                    <?php if (!empty($jobItem['jobType'])): ?>
                      <span class="badge rounded-pill bg-light text-dark d-flex align-items-center">
                        <i class="bi bi-briefcase me-1"></i> <?= htmlspecialchars(ucfirst($jobItem['jobType'])) ?>
                      </span>
                    <?php endif; ?>
                    <?php if (!empty($jobItem['experienceLevel'])): ?>
                      <span class="badge rounded-pill bg-light text-dark d-flex align-items-center">
                        <i class="bi bi-layers me-1"></i> <?= htmlspecialchars(ucfirst($jobItem['experienceLevel'])) ?>
                      </span>
                    <?php endif; ?>
                    <?php if (!empty($jobItem['location'])): ?>
                      <span class="badge rounded-pill bg-light text-dark d-flex align-items-center">
                        <i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars($jobItem['location']) ?>
                      </span>
                    <?php endif; ?>
                  </div>
                  <?php if (!empty($jobItem['salaryMin']) && !empty($jobItem['salaryMax'])): ?>
                    <div class="text-success fw-semibold mb-3">
                      <i class="bi bi-currency-dollar"></i>
                      $<?= number_format($jobItem['salaryMin']) ?> - $<?= number_format($jobItem['salaryMax']) ?>
                    </div>
                  <?php endif; ?>
                  <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                    <span class="text-muted small d-flex align-items-center">
                      <i class="bi bi-clock me-1"></i>
                      <?php 
                        $jobDate = new DateTime($jobItem['createdAt']);
                        $now = new DateTime();
                        $diff = $now->diff($jobDate);
                        if ($diff->days == 0) echo 'Today';
                        elseif ($diff->days == 1) echo 'Yesterday';
                        elseif ($diff->days < 7) echo $diff->days . ' days ago';
                        else echo $jobDate->format('M d, Y');
                      ?>
                    </span>
                    <div class="d-flex" style="gap: 20px;">
                      <button class="btn btn-sm rounded-circle save-job-btn <?= $isSaved ? 'btn-primary' : 'btn-outline-primary' ?>" 
                              data-job-id="<?= $jobItem['jobId'] ?>" 
                              title="<?= $isSaved ? 'Saved' : 'Save Job' ?>" 
                              <?= $isSaved ? 'disabled' : '' ?>>
                        <i class="bi <?= $isSaved ? 'bi-bookmark-fill' : 'bi-bookmark' ?>"></i>
                      </button>
                      <a href="<?= Config::BASE_URL ?>/pages/user/job-details.php?id=<?= $jobItem['jobId'] ?>" class="btn btn-dark btn-sm px-3">
                        Apply Now
                      </a>
                    </div>
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
                <a href="<?= Config::BASE_URL ?>/pages/user/jobs.php" class="btn btn-outline-primary rounded-pill px-4">Clear all filters</a>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Pagination -->
      <?php if ($result['pages'] > 1): ?>
        <nav >
          <?php 
          include_once __DIR__ . '/../../includes/pagination.php';
          
          // Build the base URL with query parameters but without page param
          $queryParams = array_filter($filters);
          if (!empty($query)) {
            $queryParams['q'] = $query;
          }
          
          // Generate the full URL
          $fullBaseUrl = Config::BASE_URL . '/pages/user/jobs.php';
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

<style>
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