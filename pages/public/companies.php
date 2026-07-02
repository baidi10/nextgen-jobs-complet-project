<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Company.php';

$currentPage = max(1, $_GET['page'] ?? 1);
$perPage = 12;
$searchQuery = $_GET['q'] ?? '';
$industryFilter = $_GET['industry'] ?? '';

$company = new Company();

// Get all industries from the database
$industries = $company->getAllIndustries();

// Check if the searchCompanies method exists in the Company class
if (method_exists($company, 'searchCompanies')) {
    $result = $company->searchCompanies($searchQuery, $industryFilter, $currentPage, $perPage);
} else {
    // If the method doesn't exist, create a default result structure
    $result = [
        'companies' => [],
        'total' => 0,
        'pages' => 0,
        'currentPage' => $currentPage
    ];
}

$pageTitle = "Tech Companies | JOBEST";
include __DIR__ . '/../../includes/header.php';
?>

<style>
  .filter-card {
    border-radius: 18px;
    box-shadow: 0 6px 32px rgba(0,0,0,0.08);
    background: #fff;
    border: none;
    padding: 2rem 1.5rem;
  }
  
  .filter-heading {
    font-size: 1.15rem;
    font-weight: 700;
    color: #222;
    letter-spacing: 0.5px;
    margin-bottom: 2rem;
  }
  
  .search-container {
    position: relative;
  }
  
  .search-container .input-group {
    background: #f8fafc;
    border: 1px solid #e0e7ef;
    border-radius: 50px;
    box-shadow: 0 2px 8px rgba(80,112,255,0.04);
    padding: 0.5rem 1rem;
    transition: all 0.2s ease;
  }
  
  .search-container .input-group:focus-within {
    box-shadow: 0 0 0 2px #e0e7ef;
    background: #f8f9fa;
  }
  
  .search-container .form-control {
    background: transparent;
    border: none;
    padding: 0.5rem 0;
    font-size: 1rem;
  }
  
  .search-container .form-control:focus {
    box-shadow: none;
  }
  
  .search-container .input-group-text {
    padding: 0;
    margin-right: 0.5rem;
  }
  
  .search-container .input-group-text i {
    font-size: 1.1rem;
  }
  
  .filter-label {
    font-size: 0.95rem;
    color: #666;
    font-weight: 500;
    margin-bottom: 0.5rem;
    margin-left: 0.25rem;
  }
  
  .form-select {
    width: 100%;
    height: 42px;
    border-radius: 50px;
    background: #f8fafc;
    border: 1px solid #e0e7ef;
    box-shadow: 0 2px 8px rgba(80,112,255,0.04);
    font-size: 1rem;
    padding: 0.7rem 1.25rem;
    margin-bottom: 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'16\' height=\'16\' fill=\'%23666\' viewBox=\'0 0 16 16\'%3E%3Cpath d=\'M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z\'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 16px 12px;
    padding-right: 2.5rem;
  }
  
  .form-select:focus {
    box-shadow: 0 0 0 2px #e0e7ef;
    background-color: #f8f9fa;
    border-color: #e0e7ef;
  }
  
  .form-select:hover {
    border-color: #e0e7ef;
    background-color: #f8f9fa;
  }

  .company-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
  }

  .company-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
  }

  .company-name {
    color: #212529;
    transition: color 0.2s;
  }

  .company-name:hover {
    color: #0d6efd;
  }

  .company-description {
    font-size: 0.9rem;
    color: #6c757d;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .company-meta-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 0.75rem;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 50px;
    font-size: 0.875rem;
    color: #495057;
  }

  .company-meta-badge i {
    margin-right: 0.5rem;
    color: #6c757d;
  }

  .follow-btn {
    width: 100%;
    padding: 0.75rem;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
  }

  .follow-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(13,110,253,0.15);
  }
  
  @media (max-width: 991px) {
    .filter-card {
      padding: 1.25rem 1rem;
    }
    
    .form-select {
      height: 40px;
      font-size: 0.95rem;
    }
  }
</style>

<div class="container py-5">
  <div class="row">
    <!-- Company List -->
    <div class="col-lg-9">
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div>
              <h1 class="h4 mb-2">Companies</h1>
          </div>
          <div>
              <p class="text-muted mb-0 fw-medium"><?= number_format($result['total']) ?> companies found</p>
            </div>
          </div>
        </div>
      </div>

      <?php if (!empty($searchQuery) || !empty($industryFilter)): ?>
      <div class="mb-4 p-3 bg-light rounded-3 border">
        <div class="d-flex align-items-center">
          <div class="me-3">
            <span class="fw-medium">Active filters:</span>
    </div>
          <div class="d-flex flex-wrap gap-2">
            <?php if (!empty($searchQuery)): ?>
              <span class="badge bg-primary rounded-pill px-3 py-2">
                Search: <?= htmlspecialchars($searchQuery) ?>
                <a href="<?= Config::BASE_URL ?>/pages/public/companies.php<?= !empty($industryFilter) ? '?industry=' . urlencode($industryFilter) : '' ?>" class="text-white ms-2 text-decoration-none">
                  <i class="bi bi-x-circle"></i>
                </a>
              </span>
            <?php endif; ?>
            
            <?php if (!empty($industryFilter)): ?>
              <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
                Industry: <?= htmlspecialchars($industryFilter) ?>
                <a href="<?= Config::BASE_URL ?>/pages/public/companies.php<?= !empty($searchQuery) ? '?q=' . urlencode($searchQuery) : '' ?>" class="text-primary ms-2 text-decoration-none">
                  <i class="bi bi-x-circle"></i>
                </a>
              </span>
            <?php endif; ?>
            
            <a href="<?= Config::BASE_URL ?>/pages/public/companies.php" class="btn btn-sm btn-link text-decoration-none">Clear all</a>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <div class="row g-4">
        <?php if (!empty($result['companies']) && is_array($result['companies'])): ?>
          <?php foreach ($result['companies'] as $companyItem): ?>
            <div class="col-md-6 col-lg-4 mb-4">
              <div class="card company-card h-100 d-flex flex-column">
                <div class="card-body d-flex flex-column p-4">
                  <div class="d-flex align-items-start" style="gap: 30px;">
                    <?php 
                    $logoFile = !empty($companyItem['logo']) ? $companyItem['logo'] : 'default.png';
                    ?>
                    <div class="company-logo flex-shrink-0" style="
                      width: 60px;
                      height: 60px;
                      border-radius: 12px;
                      overflow: hidden;
                      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                      border: 1px solid #eee;
                      background: white;
                    ">
                    <img src="<?= Config::BASE_URL ?>/assets/uploads/company_logos/<?= htmlspecialchars($logoFile) ?>" 
                        alt="<?= htmlspecialchars($companyItem['companyName'] ?? 'Company') ?>" 
                          style="
                            width: 100%;
                            height: 100%;
                            object-fit: cover;
                            object-position: center;
                          "
                        onerror="this.src='<?= Config::BASE_URL ?>/assets/uploads/company_logos/default.png'">
                    </div>
                    <div class="flex-grow-1">
                    <h2 class="h6 fw-bold mb-0">
                        <a href="<?= Config::BASE_URL ?>/pages/public/company-details.php?id=<?= $companyItem['companyId'] ?>" 
                           class="text-decoration-none company-name">
                          <?= htmlspecialchars($companyItem['companyName'] ?? 'Company Name') ?>
                      </a>
                    </h2>
                    <p class="small text-muted mb-0">
                        <?= htmlspecialchars($companyItem['industry'] ?? 'Technology') ?>
                    </p>
                  </div>
                </div>

                  <p class="company-description mb-3 flex-grow-1">
                    <?= htmlspecialchars($companyItem['description'] ?? 'No description available.') ?>
                </p>

                  <div class="d-flex flex-wrap gap-2 mb-3">
                    <?php if (!empty($companyItem['employeeCount'])): ?>
                      <span class="company-meta-badge">
                        <i class="bi bi-people"></i> <?= htmlspecialchars($companyItem['employeeCount']) ?>
                  </span>
                    <?php endif; ?>
                    
                    <?php if (!empty($companyItem['isVerified']) && $companyItem['isVerified']): ?>
                      <span class="company-meta-badge">
                        <i class="bi bi-check-circle"></i> Verified
                    </span>
                  <?php endif; ?>
                </div>

                  <div class="d-flex justify-content-center align-items-center mt-auto pt-3 border-top">
                    <a href="<?= Config::BASE_URL ?>/pages/public/company-details.php?id=<?= $companyItem['companyId'] ?>" 
                       class="btn btn-primary follow-btn">
                      View Company
                    </a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12">
            <div class="card border-0">
              <div class="card-body text-center py-5">
                <div class="mb-3">
                  <i class="bi bi-building fs-1 text-muted"></i>
                </div>
                <h3 class="h5 mb-3">No companies found</h3>
                <p class="text-muted mb-4">Try adjusting your filters or search terms</p>
                <a href="<?= Config::BASE_URL ?>/pages/public/companies.php" class="btn btn-outline-primary rounded-pill px-4">Clear all filters</a>
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
          $queryParams = [];
          if (!empty($searchQuery)) {
            $queryParams['q'] = $searchQuery;
          }
          if (!empty($industryFilter)) {
            $queryParams['industry'] = $industryFilter;
          }
          
          // Generate the full URL
          $fullBaseUrl = Config::BASE_URL . '/pages/public/companies.php';
          if (!empty($queryParams)) {
            $fullBaseUrl .= '?' . http_build_query($queryParams);
          }
          
          // Call the pagination function with the correct parameters
          renderPagination($currentPage, $result['pages'], $fullBaseUrl, $result['total']);
          ?>
        </nav>
      <?php endif; ?>
    </div>

    <!-- Filters -->
    <div class="col-lg-3 mb-4">
      <div class="filter-card" style="
        position: sticky;
        top: 20px;
        max-height: calc(100vh - 40px);
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #ddd #f8f9fa;
      ">
        <h3 class="filter-heading">Filter Companies</h3>
        
        <form method="get" class="filter-form">
          <!-- Search Form -->
          <div class="search-container mb-4">
            <div class="input-group">
              <span class="input-group-text border-0 bg-transparent">
                <i class="bi bi-search text-muted"></i>
              </span>
              <input type="text" 
                     class="form-control border-0 ps-0" 
                     name="q" 
                     placeholder="Search companies"
                     value="<?= htmlspecialchars($searchQuery) ?>">
            </div>
          </div>

          <!-- Industry Filter -->
          <div class="filter-group">
            <label class="filter-label">Industry</label>
            <select class="form-select" name="industry" onchange="this.form.submit()">
              <option value="">All Industries</option>
              <?php foreach ($industries as $industry): ?>
                <option value="<?= htmlspecialchars($industry) ?>" <?= $industryFilter === $industry ? 'selected' : '' ?>>
                  <?= htmlspecialchars($industry) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>