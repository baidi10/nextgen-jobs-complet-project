<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Job.php';

$job = new Job();
$featuredJobs = $job->getFeaturedJobs(8);
$topCompanies = method_exists($job, 'getTopCompanies') ? $job->getTopCompanies(6) : [];

$pageTitle = "Find Your Next Tech Job | JOBEST";
include __DIR__ . '/../../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section position-relative overflow-hidden" style="height: 100vh; min-height: 600px;">
  <!-- Video Background -->
  <div class="video-background position-absolute w-100 h-100" style="top: 0; left: 0; z-index: 0;">
    <video autoplay muted loop playsinline class="w-100 h-100" style="object-fit: cover;">
      <source src="<?= Config::BASE_URL ?>/assets/videos/3209211-uhd_3840_2160_25fps.mp4" type="video/mp4">
    </video>
    <div class="position-absolute w-100 h-100" style="background: rgba(0, 0, 0, 0.5); top: 0; left: 0;"></div>
  </div>

  <div class="container text-center position-relative d-flex flex-column justify-content-center" style="z-index: 1; height: 100%;">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <h1 class="display-3 fw-bold mb-4 text-white">
          Find what's next
        </h1>
    
        <!-- Search Form -->
        <div class="search-box mx-auto mb-4" style="max-width: 720px;">
          <form action="<?= Config::BASE_URL ?>/pages/public/jobs.php" method="get" class="shadow-lg" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 20px;">
            <div class="input-group p-2">
              <span class="input-group-text border-0 bg-transparent ps-4">
                <i class="bi bi-search text-white fs-5"></i>
              </span>
              <input type="text" class="form-control border-0 py-3 bg-transparent text-white" 
                     placeholder="Job title, keywords" name="q"
                     aria-label="Search jobs"
                     style="color: white !important;">
              <button class="btn btn-light rounded-pill px-4 py-2 fw-bold" type="submit">
                Search Jobs
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    
    <!-- Quick Links -->
    <div class="text-center mb-3">
      <h6 class="text-uppercase fw-medium text-white">MOST SEARCHED KEYWORDS</h6>
    </div>
    <div class="quick-links">
      <div class="d-flex flex-wrap justify-content-center mb-4">
        <?php 
        // Get popular job titles from job_views joined with jobs table
        $db = Database::getInstance()->getConnection();
        $query = "SELECT j.jobTitle, COUNT(*) as view_count 
                 FROM job_views jv 
                 JOIN jobs j ON jv.jobId = j.jobId 
                 WHERE j.isActive = 1 
                 GROUP BY j.jobTitle 
                 ORDER BY view_count DESC 
                 LIMIT 8";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $popularTerms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($popularTerms)): 
          foreach ($popularTerms as $term):
            $termSlug = urlencode($term['jobTitle']);
        ?>
          <a href="<?= Config::BASE_URL ?>/pages/public/jobs.php?q=<?= $termSlug ?>&exactTitle=1" class="text-decoration-none text-white mx-3 mb-2"><?= htmlspecialchars($term['jobTitle']) ?></a>
        <?php 
          endforeach;
        else:
          // Fallback to hardcoded links if no terms found
        ?>
          <a href="<?= Config::BASE_URL ?>/pages/public/jobs.php?q=Full+Stack+Developer&exactTitle=1" class="text-decoration-none text-white mx-3 mb-2">Full Stack Developer</a>
          <a href="<?= Config::BASE_URL ?>/pages/public/jobs.php?q=Frontend+Developer&exactTitle=1" class="text-decoration-none text-white mx-3 mb-2">Frontend Developer</a>
          <a href="<?= Config::BASE_URL ?>/pages/public/jobs.php?q=iOS+Developer&exactTitle=1" class="text-decoration-none text-white mx-3 mb-2">iOS Developer</a>
          <a href="<?= Config::BASE_URL ?>/pages/public/jobs.php?q=Android+Developer&exactTitle=1" class="text-decoration-none text-white mx-3 mb-2">Android Developer</a>
          <a href="<?= Config::BASE_URL ?>/pages/public/jobs.php?q=Data+Scientist&exactTitle=1" class="text-decoration-none text-white mx-3 mb-2">Data Scientist</a>
          <a href="<?= Config::BASE_URL ?>/pages/public/jobs.php?q=DevOps+Engineer&exactTitle=1" class="text-decoration-none text-white mx-3 mb-2">DevOps Engineer</a>
          <a href="<?= Config::BASE_URL ?>/pages/public/jobs.php?q=AI+Engineer&exactTitle=1" class="text-decoration-none text-white mx-3 mb-2">AI Engineer</a>
          <a href="<?= Config::BASE_URL ?>/pages/public/jobs.php?q=UX+Designer&exactTitle=1" class="text-decoration-none text-white mx-3 mb-2">UX Designer</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- How It Works Section -->
<section class="py-5 bg-white border-bottom">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-bold">How JOBEST Works</h2>
      <p class="text-muted">Find your dream job in 3 simple steps</p>
    </div>
    
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="text-center p-4">
          <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
            <i class="bi bi-person-plus fs-2 text-dark"></i>
          </div>
          <h4 class="fw-bold mb-3">Create Your Profile</h4>
          <p class="text-muted">Sign up and create your professional profile. Highlight your skills, experience, and preferences.</p>
        </div>
            </div>
      
      <div class="col-lg-4">
        <div class="text-center p-4">
          <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
            <i class="bi bi-search fs-2 text-dark"></i>
          </div>
          <h4 class="fw-bold mb-3">Discover Opportunities</h4>
          <p class="text-muted">Browse thousands of tech jobs from top companies. Filter by role, location, salary, and more.</p>
        </div>
      </div>
      
      <div class="col-lg-4">
        <div class="text-center p-4">
          <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
            <i class="bi bi-send-check fs-2 text-dark"></i>
          </div>
          <h4 class="fw-bold mb-3">Apply with One Click</h4>
          <p class="text-muted">No cover letters needed. Apply instantly with your profile and get directly connected with hiring managers.</p>
        </div>
      </div>
    </div>
    
    <div class="text-center mt-4">
      <a href="<?= Config::BASE_URL ?>/pages/public/register.php" class="btn btn-dark rounded-pill px-4 py-2 fw-bold">Get Started Now</a>
    </div>
  </div>
</section>

<!-- Job Categories Section -->
<section class="py-5 bg-light">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-bold">Popular Job Categories</h2>
      <p class="text-muted">Explore opportunities by specialty</p>
    </div>
    
    <?php
    // Get job counts by category
    $db = Database::getInstance()->getConnection();
    
    // Define the categories we want to display
    $categories = [
      'software-development' => [
        'name' => 'Software Development',
        'icon' => 'bi-code-square',
        'color' => 'text-primary'
      ],
      'data-science' => [
        'name' => 'Data Science',
        'icon' => 'bi-graph-up',
        'color' => 'text-success'
      ],
      'product-management' => [
        'name' => 'Product Management',
        'icon' => 'bi-kanban',
        'color' => 'text-danger'
      ],
      'ux-design' => [
        'name' => 'UX/UI Design',
        'icon' => 'bi-bezier2',
        'color' => 'text-warning'
      ],
      'devops' => [
        'name' => 'DevOps',
        'icon' => 'bi-hdd-network',
        'color' => 'text-info'
      ],
      'marketing' => [
        'name' => 'Marketing',
        'icon' => 'bi-megaphone',
        'color' => 'text-success'
      ],
      'sales' => [
        'name' => 'Sales',
        'icon' => 'bi-currency-dollar',
        'color' => 'text-primary'
      ],
      'all' => [
        'name' => 'View All Categories',
        'icon' => 'bi-grid-3x3-gap',
        'color' => 'text-dark'
      ]
    ];
    
    // Get counts for each category
    $categoryCounts = [];
    
    // For software development
    $stmt = $db->prepare("SELECT COUNT(*) FROM jobs WHERE isActive = 1 AND (jobTitle LIKE '%developer%' OR jobTitle LIKE '%engineer%' OR jobTitle LIKE '%programming%' OR jobTitle LIKE '%software%')");
    $stmt->execute();
    $categoryCounts['software-development'] = $stmt->fetchColumn();
    
    // For data science
    $stmt = $db->prepare("SELECT COUNT(*) FROM jobs WHERE isActive = 1 AND (jobTitle LIKE '%data%' OR jobTitle LIKE '%analyst%' OR jobTitle LIKE '%science%' OR jobTitle LIKE '%ML%' OR jobTitle LIKE '%machine learning%')");
    $stmt->execute();
    $categoryCounts['data-science'] = $stmt->fetchColumn();
    
    // For product management
    $stmt = $db->prepare("SELECT COUNT(*) FROM jobs WHERE isActive = 1 AND (jobTitle LIKE '%product%' OR jobTitle LIKE '%manager%' OR jobTitle LIKE '%management%')");
    $stmt->execute();
    $categoryCounts['product-management'] = $stmt->fetchColumn();
    
    // For UX/UI design
    $stmt = $db->prepare("SELECT COUNT(*) FROM jobs WHERE isActive = 1 AND (jobTitle LIKE '%UX%' OR jobTitle LIKE '%UI%' OR jobTitle LIKE '%design%' OR jobTitle LIKE '%designer%')");
    $stmt->execute();
    $categoryCounts['ux-design'] = $stmt->fetchColumn();
    
    // For DevOps
    $stmt = $db->prepare("SELECT COUNT(*) FROM jobs WHERE isActive = 1 AND (jobTitle LIKE '%devops%' OR jobTitle LIKE '%infrastructure%' OR jobTitle LIKE '%cloud%' OR jobTitle LIKE '%systems%')");
    $stmt->execute();
    $categoryCounts['devops'] = $stmt->fetchColumn();
    
    // For marketing
    $stmt = $db->prepare("SELECT COUNT(*) FROM jobs WHERE isActive = 1 AND (jobTitle LIKE '%marketing%' OR jobTitle LIKE '%brand%' OR jobTitle LIKE '%growth%' OR jobTitle LIKE '%SEO%')");
    $stmt->execute();
    $categoryCounts['marketing'] = $stmt->fetchColumn();
    
    // For sales
    $stmt = $db->prepare("SELECT COUNT(*) FROM jobs WHERE isActive = 1 AND (jobTitle LIKE '%sales%' OR jobTitle LIKE '%account%' OR jobTitle LIKE '%business development%')");
    $stmt->execute();
    $categoryCounts['sales'] = $stmt->fetchColumn();
    
    // For all jobs
    $stmt = $db->prepare("SELECT COUNT(*) FROM jobs WHERE isActive = 1");
    $stmt->execute();
    $categoryCounts['all'] = $stmt->fetchColumn();
    
    // Helper function to format count with plus sign
    if (!function_exists('formatCategoryCount')) {
      function formatCategoryCount($count) {
        return $count . '+';
      }
    }
    ?>
    
    <div class="row g-4 mb-3">
      <?php foreach ($categories as $categorySlug => $category): ?>
      <div class="col-lg-3 col-md-4 col-6 mb-4">
        <a href="<?= Config::BASE_URL ?>/pages/public/jobs.php?category=<?= $categorySlug ?>" class="text-decoration-none d-block h-100">
          <div class="card border-0 shadow-sm h-100 text-center hover-lift" style="border-radius: 12px; overflow: hidden;">
            <div class="card-body py-4 px-3">
              <div class="mb-4 <?= $category['color'] ?>">
                <i class="bi <?= $category['icon'] ?> fs-1"></i>
              </div>
              <h5 class="fw-bold text-dark mb-3"><?= $category['name'] ?></h5>
              <p class="small text-muted mb-0">
                <span class="badge bg-light text-dark px-3 py-2 rounded-pill">
                  <?= formatCategoryCount($categoryCounts[$categorySlug]) ?> Jobs Available
                </span>
              </p>
            </div>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php
// Get statistics from database early for this section
if (!isset($hiresCount) || !isset($jobsCount) || !isset($jobSeekersCount)) {
  $db = Database::getInstance()->getConnection();
  
  // Get job seekers count
  $jobSeekersQuery = "SELECT COUNT(*) FROM users WHERE userType = 'jobSeeker'";
  $jobSeekersStmt = $db->prepare($jobSeekersQuery);
  $jobSeekersStmt->execute();
  $jobSeekersCount = $jobSeekersStmt->fetchColumn();
  
  // Get jobs count
  $jobsQuery = "SELECT COUNT(*) FROM jobs WHERE isActive = 1";
  $jobsStmt = $db->prepare($jobsQuery);
  $jobsStmt->execute();
  $jobsCount = $jobsStmt->fetchColumn();
  
  // Get successful hires count
  $hiresQuery = "SELECT COUNT(*) FROM applications WHERE status = 'hired'";
  $hiresStmt = $db->prepare($hiresQuery);
  $hiresStmt->execute();
  $hiresCount = $hiresStmt->fetchColumn();
}
?>
<!-- Stats Section -->
<section class="py-5 bg-light">
  <div class="container text-center">
    <div class="row justify-content-center g-4">
      <div class="col-md-4">
        <div class="stat-item p-4 mb-4 rounded-3 shadow-sm bg-white">
          <div class="display-4 fw-bold text-primary"><?= $hiresCount ?>+</div>
          <p class="text-muted mt-2">Matches Made</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-item p-4 mb-4 rounded-3 shadow-sm bg-white">
          <div class="display-4 fw-bold text-success"><?= $jobsCount ?>+</div>
          <p class="text-muted mt-2">Tech Jobs</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-item p-4 mb-4 rounded-3 shadow-sm bg-white">
          <div class="display-4 fw-bold text-info"><?= $jobSeekersCount ?>+</div>
          <p class="text-muted mt-2">Startup Ready Candidates</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Company Logos -->
<section class="py-5 border-bottom" style="background: linear-gradient(120deg, #f8fafc 60%, #e0e7ff 100%); background-image: url('https://www.transparenttextures.com/patterns/cubes.png'); background-size: 300px 300px;">
  <div class="container">
    <div class="text-center mb-4">
      <h3 class="h4 fw-bold">Top Companies Hiring Now</h3>
    </div>
    <div class="logo-scroll d-flex flex-wrap justify-content-center align-items-center" style="gap: 60px;">
      <?php if (!empty($topCompanies) && is_array($topCompanies)): ?>
        <?php foreach ($topCompanies as $company): ?>
          <a href="<?= Config::BASE_URL ?>/pages/public/jobs.php?companyId=<?= $company['companyId'] ?>" class="logo-item text-center mx-3 d-block" style="text-decoration: none;">
            <?php if (!empty($company['logo'])): ?>
              <?php
                $logoPath = __DIR__ . '/../../assets/uploads/company_logos/' . $company['logo'];
                $logoSize = @getimagesize($logoPath);
              ?>
              <img src="<?= Config::BASE_URL ?>/assets/uploads/company_logos/<?= htmlspecialchars($company['logo']) ?>" alt="<?= htmlspecialchars($company['name']) ?>" height="40">
              <?php if ($logoSize): ?>
                
              <?php endif; ?>
            <?php else: ?>
              <div class="company-placeholder d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <?= substr(htmlspecialchars($company['name'] ?? 'CO'), 0, 2) ?>
              </div>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <?php for ($i = 1; $i <= 6; $i++): ?>
          <div class="logo-item text-center mx-3">
            <img src="<?= Config::BASE_URL ?>/assets/images/logos/company-<?= $i ?>.png" alt="Company Logo" height="40">
            <div class="mt-2 small fw-medium">Company <?= $i ?></div>
          </div>
        <?php endfor; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Featured Companies Section -->
<section class="py-5 bg-light">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-bold">Top Companies Hiring Now</h2>
      <p class="text-muted">Connect with industry-leading employers on our platform</p>
    </div>
    
    <div class="row g-5">
      <?php 
      // Get top companies from database
      $job = new Job();
      $topCompanies = $job->getTopCompanies(6);
      
      if (!empty($topCompanies) && is_array($topCompanies)):
        foreach ($topCompanies as $company):
          // Create a URL-friendly slug if not available
          $companyUrl = Config::BASE_URL . '/pages/public/jobs.php?companyId=' . $company['companyId'];
          
          // Get the job count
          $jobCount = isset($company['job_count']) ? $company['job_count'] : 0;
      ?>
      <div class="col-md-6 col-lg-4 mb-4">
        <a href="<?= $companyUrl ?>" class="text-decoration-none">
          <div class="card company-card border-0 shadow-sm h-100 hover-lift" style="border-radius: 16px; overflow: hidden;">
            <div class="card-body p-4">
              <div class="d-flex align-items-center mb-4">
                <?php if (!empty($company['logo'])): ?>
                  <img src="<?= Config::BASE_URL ?>/assets/uploads/company_logos/<?= htmlspecialchars($company['logo']) ?>" alt="<?= htmlspecialchars($company['name']) ?>" class="me-3 rounded-circle" height="64" width="64" style="object-fit: cover; border: 2px solid #f5f5f5; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <?php else: ?>
                  <div class="company-placeholder me-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 64px; height: 64px; font-weight: bold; background: linear-gradient(135deg, #f5f7fa, #e4e8eb); box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <?= substr(htmlspecialchars($company['name'] ?? 'CO'), 0, 2) ?>
                  </div>
                <?php endif; ?>
                <div>
                  <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($company['name']) ?></h5>
                  <div class="d-flex align-items-center">
                    <span class="badge bg-light text-dark rounded-pill px-3 py-2">
                      <i class="bi bi-briefcase me-1"></i>
                      <?= $jobCount ?> <?= $jobCount == 1 ? 'Job' : 'Jobs' ?> Available
                    </span>
                  </div>
                </div>
              </div>
              
              <div class="mt-3 pt-2 border-top">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="text-muted small">
                    <?php if (!empty($company['industry'])): ?>
                      <i class="bi bi-building me-1"></i> <?= htmlspecialchars($company['industry']) ?>
                    <?php endif; ?>
                  </span>
                  <button class="btn btn-sm btn-outline-primary rounded-pill px-3">View Jobs</button>
                </div>
              </div>
            </div>
          </div>
        </a>
      </div>
      <?php 
        endforeach;
      else:
        // If no companies, show placeholder message
      ?>
      <div class="col-12 text-center py-5">
        <p class="text-muted">No companies found. Check back soon for updates!</p>
      </div>
      <?php endif; ?>
    </div>
    
    <div class="text-center mt-4">
      <a href="<?= Config::BASE_URL ?>/pages/public/companies.php" class="btn btn-dark rounded-pill px-4 py-2">
        View All Companies <i class="bi bi-arrow-right ms-2"></i>
      </a>
    </div>
  </div>
</section>

<!-- Value Props Section -->
<section class="py-6 bg-white">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-6 d-flex">
        <div class="position-relative p-5 rounded-4 h-100 w-100 d-flex flex-column shadow-sm" style="background: linear-gradient(135deg, rgba(255,255,255,0.5), rgba(240,240,240,0.5)), url('<?= Config::BASE_URL ?>/assets/images/pexels-karolina-grabowska-4467687.jpg'); background-size: cover; background-position: center; border: 1px solid #f0f0f0;">
          <div class="position-absolute top-0 end-0 p-4 opacity-10">
            <i class="bi bi-person-circle" style="font-size: 5rem; color: rgba(0,0,0,0.1);"></i>
          </div>
          <div class="flex-grow-1">
            <h3 class="h2 fw-bold mb-4">Why job seekers love us</h3>
            <ul class="list-unstyled">
              <li class="mb-4 d-flex">
                <div class="me-3 text-primary"><i class="bi bi-check-circle-fill fs-4"></i></div>
                <p class="fw-medium">Connect directly with founders at top startups - no third party recruiters allowed.</p>
              </li>
              <li class="mb-4 d-flex">
                <div class="me-3 text-primary"><i class="bi bi-check-circle-fill fs-4"></i></div>
                <p class="fw-medium">Everything you need to know, all upfront. View salary, stock options, and more before applying.</p>
              </li>
              <li class="mb-4 d-flex">
                <div class="me-3 text-primary"><i class="bi bi-check-circle-fill fs-4"></i></div>
                <p class="fw-medium">Say goodbye to cover letters - your profile is all you need. One click to apply and you're done.</p>
              </li>
              <li class="mb-4 d-flex">
                <div class="me-3 text-primary"><i class="bi bi-check-circle-fill fs-4"></i></div>
                <p class="fw-medium">Unique jobs at startups and tech companies you can't find anywhere else.</p>
              </li>
            </ul>
          </div>
          <div class="mt-auto pt-4">
            <a href="<?= Config::BASE_URL ?>/pages/public/register.php" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold hover-lift">Sign up</a>
            <a href="#" class="btn btn-link text-decoration-none fw-medium ms-3">Learn more <i class="bi bi-arrow-right ms-1"></i></a>
          </div>
        </div>
      </div>
      <div class="col-lg-6 d-flex">
        <div class="p-5 rounded-4 h-100 w-100 d-flex flex-column shadow-sm position-relative" style="background: linear-gradient(135deg, rgba(22, 22, 23, 0.4), rgba(42, 42, 44, 0.4)), url('<?= Config::BASE_URL ?>/assets/images/pexels-tima-miroshnichenko-5439169.jpg'); background-size: cover; background-position: center; color: white; border: 1px solid rgba(255,255,255,0.05);">
          <div class="position-absolute top-0 end-0 p-4 opacity-10">
            <i class="bi bi-building" style="font-size: 5rem; color: rgba(255,255,255,0.1);"></i>
          </div>
          <div class="flex-grow-1">
            <h3 class="h2 fw-bold mb-4">Why recruiters love us</h3>
            <ul class="list-unstyled">
              <li class="mb-4 d-flex">
                <div class="me-3 text-info"><i class="bi bi-check-circle-fill fs-4"></i></div>
                <p class="fw-medium">Tap into a community of 10M+ engaged, startup-ready candidates.</p>
              </li>
              <li class="mb-4 d-flex">
                <div class="me-3 text-info"><i class="bi bi-check-circle-fill fs-4"></i></div>
                <p class="fw-medium">Everything you need to kickstart your recruiting — set up job posts, company branding, and HR tools within 10 minutes, all for free.</p>
              </li>
              <li class="mb-4 d-flex">
                <div class="me-3 text-info"><i class="bi bi-check-circle-fill fs-4"></i></div>
                <p class="fw-medium">A free applicant tracking system, or free integration with any ATS you may already use.</p>
              </li>
            </ul>
          </div>
          <div class="mt-auto pt-4">
            <a href="<?= Config::BASE_URL ?>/pages/public/register.php?type=employer" class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-bold hover-lift">Sign up</a>
            <a href="#" class="btn btn-link text-white text-decoration-none fw-medium ms-3">Learn more <i class="bi bi-arrow-right ms-1"></i></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Featured Jobs Section -->
<section class="py-6 bg-light">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-5">
      <h2 class="h3 fw-bold">Featured Tech Jobs</h2>
      <a href="<?= Config::BASE_URL ?>/pages/public/jobs.php" class="btn btn-link text-dark text-decoration-none d-flex align-items-center">
        View all jobs <i class="bi bi-arrow-right ms-2"></i>
      </a>
    </div>
    
    <div class="row g-5">
      <?php if (!empty($featuredJobs) && is_array($featuredJobs)): ?>
        <?php foreach ($featuredJobs as $job): ?>
          <div class="col-md-6 col-xl-3 mb-4">
            <div class="card border-0 shadow-sm h-100 hover-lift">
              <div class="card-body p-4 d-flex flex-column h-100">
                <?php if (!empty($job['companyLogo'])): ?>
                  <img src="<?= Config::BASE_URL ?>/assets/uploads/company_logos/<?= htmlspecialchars($job['companyLogo']) ?>" alt="<?= htmlspecialchars($job['companyName']) ?>" class="mb-3" height="30" width="60">
                <?php else: ?>
                  <div class="company-placeholder mb-3 fw-bold"><?= substr(htmlspecialchars($job['companyName'] ?? 'CO'), 0, 2) ?></div>
                <?php endif; ?>
                <h5 class="card-title mb-1"><a href="<?= Config::BASE_URL ?>/pages/public/job-details.php?id=<?= $job['jobId'] ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($job['jobTitle']) ?></a></h5>
                <p class="text-muted small mb-3"><?= htmlspecialchars($job['companyName']) ?></p>
                <div class="d-flex align-items-center small mb-3">
                  <i class="bi bi-geo-alt me-1"></i>
                  <span><?= !empty($job['location']) ? htmlspecialchars($job['location']) : 'Remote' ?></span>
                </div>
                <?php if (!empty($job['salaryMin']) && !empty($job['salaryMax'])): ?>
                  <div class="text-success fw-medium small mb-3">
                    $<?= number_format($job['salaryMin']) ?> - $<?= number_format($job['salaryMax']) ?>
                  </div>
                <?php endif; ?>
                <div class="mt-auto pt-2">
                  <a href="<?= Config::BASE_URL ?>/pages/public/job-details.php?id=<?= $job['jobId'] ?>" class="btn btn-sm btn-outline-dark rounded-pill px-3 w-100">View Job</a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12 text-center py-5">
          <p class="text-muted">No featured jobs found. Check back soon!</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Statistics Showcase Section -->
<section class="py-5 bg-light border-top border-bottom">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-6 mb-4 mb-lg-0">
        <h2 class="fw-bold mb-4">Connecting top talent with<br>innovative companies</h2>
        <p class="lead mb-4">Our platform makes it easy to discover opportunities and find the perfect candidates.</p>
        <div class="d-flex flex-wrap gap-3">
          <a href="<?= Config::BASE_URL ?>/pages/public/register.php" class="btn btn-dark btn-lg px-4 rounded-pill fw-bold">
            Create Account
          </a>
          <a href="<?= Config::BASE_URL ?>/pages/public/jobs.php" class="btn btn-outline-dark btn-lg px-4 rounded-pill fw-bold">
            Explore Jobs
          </a>
        </div>
    </div>
    
      <?php
      // Get statistics from database
      $db = Database::getInstance()->getConnection();
      
      // Get job seekers count (users with userType = 'jobSeeker')
      $jobSeekersQuery = "SELECT COUNT(*) FROM users WHERE userType = 'jobSeeker'";
      $jobSeekersStmt = $db->prepare($jobSeekersQuery);
      $jobSeekersStmt->execute();
      $jobSeekersCount = $jobSeekersStmt->fetchColumn();
      
      // Get companies count (users with userType = 'employer')
      $companiesQuery = "SELECT COUNT(*) FROM users WHERE userType = 'employer'";
      $companiesStmt = $db->prepare($companiesQuery);
      $companiesStmt->execute();
      $companiesCount = $companiesStmt->fetchColumn();
      
      // Get jobs count
      $jobsQuery = "SELECT COUNT(*) FROM jobs WHERE isActive = 1";
      $jobsStmt = $db->prepare($jobsQuery);
      $jobsStmt->execute();
      $jobsCount = $jobsStmt->fetchColumn();
      
      // Get successful hires count (applications with status 'hired')
      $hiresQuery = "SELECT COUNT(*) FROM applications WHERE status = 'hired'";
      $hiresStmt = $db->prepare($hiresQuery);
      $hiresStmt->execute();
      $hiresCount = $hiresStmt->fetchColumn();
      
      // Format numbers for display
      function formatCount($count) {
          if ($count >= 1000) {
              return floor($count/1000) . 'k+';
          }
          return $count . '+';
      }
      ?>
      
      <div class="col-lg-6">
        <div class="row g-5 text-center">
          <div class="col-6 mb-4">
            <div class="card border-0 shadow-sm py-4 h-100 hover-lift">
              <div class="card-body">
                <div class="display-4 fw-bold text-primary mb-2"><?= formatCount($jobSeekersCount) ?></div>
                <p class="text-muted mb-0">Active Job Seekers</p>
              </div>
            </div>
          </div>
          <div class="col-6 mb-4">
            <div class="card border-0 shadow-sm py-4 h-100 hover-lift">
              <div class="card-body">
                <div class="display-4 fw-bold text-success mb-2"><?= formatCount($companiesCount) ?></div>
                <p class="text-muted mb-0">Companies</p>
              </div>
            </div>
          </div>
          <div class="col-6 mb-4">
            <div class="card border-0 shadow-sm py-4 h-100 hover-lift">
              <div class="card-body">
                <div class="display-4 fw-bold text-danger mb-2"><?= formatCount($jobsCount) ?></div>
                <p class="text-muted mb-0">Jobs Posted</p>
              </div>
            </div>
          </div>
          <div class="col-6 mb-4">
            <div class="card border-0 shadow-sm py-4 h-100 hover-lift">
              <div class="card-body">
                <div class="display-4 fw-bold text-info mb-2"><?= formatCount($hiresCount) ?></div>
                <p class="text-muted mb-0">Successful Hires</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>




<?php include __DIR__ . '/../../includes/footer.php'; ?>