<?php
require_once __DIR__ . '/../../includes/dependencies.php';

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'jobSeeker') {
    header('Location: /pages/public/login.php');
    exit;
}

$job = new Job();
$featuredJobs = $job->getFeaturedJobs(8);
$topCompanies = method_exists($job, 'getTopCompanies') ? $job->getTopCompanies(6) : [];

$pageTitle = "Welcome to JOBEST | Your Job Search Dashboard";
include __DIR__ . '/../../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section position-relative overflow-hidden" style="height: 100vh; min-height: 600px;">
  <!-- Video Background -->
  <div class="video-background position-absolute w-100 h-100" style="top: 0; left: 0; z-index: 0;">
    <video autoplay muted loop playsinline class="w-100 h-100" style="object-fit: cover;">
      <source src="<?= Config::BASE_URL ?>/assets/videos/4428752-uhd_3840_2160_25fps.mp4" type="video/mp4">
    </video>
    <!-- Overlay to ensure text readability -->
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
          <form action="<?= Config::BASE_URL ?>/pages/user/jobs.php" method="get" class="shadow-lg" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 20px;">
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
        $popularTerms = $job->getPopularSearchTerms(8);
        
        if (!empty($popularTerms)): 
          foreach ($popularTerms as $term => $count):
            $termSlug = urlencode($term);
            $isLocation = !preg_match('/[^a-zA-Z\s]/', $term) && str_word_count($term) > 1;
            $searchParam = $isLocation ? "location=$termSlug" : "q=$termSlug";
        ?>
          <a href="<?= Config::BASE_URL ?>/pages/user/jobs.php?<?= $searchParam ?>" class="text-decoration-none text-white mx-3 mb-2"><?= htmlspecialchars($term) ?></a>
        <?php 
          endforeach;
        else:
        ?>
          <a href="<?= Config::BASE_URL ?>/pages/user/jobs.php?q=full+stack" class="text-decoration-none text-white mx-3 mb-2">Full Stack Developer</a>
          <a href="<?= Config::BASE_URL ?>/pages/user/jobs.php?q=frontend" class="text-decoration-none text-white mx-3 mb-2">Frontend Developer</a>
          <a href="<?= Config::BASE_URL ?>/pages/user/jobs.php?q=ios" class="text-decoration-none text-white mx-3 mb-2">iOS Developer</a>
          <a href="<?= Config::BASE_URL ?>/pages/user/jobs.php?q=android" class="text-decoration-none text-white mx-3 mb-2">Android Developer</a>
          <a href="<?= Config::BASE_URL ?>/pages/user/jobs.php?q=data+scientist" class="text-decoration-none text-white mx-3 mb-2">Data Scientist</a>
          <a href="<?= Config::BASE_URL ?>/pages/user/jobs.php?q=devops" class="text-decoration-none text-white mx-3 mb-2">DevOps Engineer</a>
          <a href="<?= Config::BASE_URL ?>/pages/user/jobs.php?q=ai" class="text-decoration-none text-white mx-3 mb-2">AI Engineer</a>
          <a href="<?= Config::BASE_URL ?>/pages/user/jobs.php?q=ux" class="text-decoration-none text-white mx-3 mb-2">UX Designer</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<style>
.hero-section {
  min-height: 80vh;
  display: flex;
  align-items: center;
  background-color: #000; /* Fallback color */
}

.video-background video {
  min-height: 100%;
  min-width: 100%;
}

@media (max-width: 768px) {
  .hero-section {
    min-height: 60vh;
  }
}

/* Add styles for the search input placeholder */
.form-control::placeholder {
  color: rgba(255, 255, 255, 0.7) !important;
}

/* Style for the search input when focused */
.form-control:focus {
  background-color: rgba(255, 255, 255, 0.15) !important;
  color: white !important;
  box-shadow: none;
}

/* Style for the search icon */
.input-group-text i {
  opacity: 0.8;
}
</style>

<!-- Quick Actions Section -->
<section class="py-5 bg-white border-bottom">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-bold">Quick Actions</h2>
      <p class="text-muted">Everything you need to manage your job search</p>
    </div>
    
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="text-center p-4 h-100 d-flex flex-column">
          <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px; margin: 0 auto;">
            <i class="bi bi-search-heart fs-2 text-dark" style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%;"></i>
          </div>
          <h4 class="fw-bold mb-3">Find Jobs</h4>
          <p class="text-muted">Browse thousands of tech jobs from top companies. Filter by role, location, salary, and more.</p>
          <div class="mt-auto pt-3">
            <a href="<?= Config::BASE_URL ?>/pages/user/jobs.php" class="btn btn-outline-dark rounded-pill px-4">Browse Jobs</a>
          </div>
        </div>
      </div>
      
      <div class="col-lg-4">
        <div class="text-center p-4 h-100 d-flex flex-column">
          <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px; margin: 0 auto;">
            <i class="bi bi-bank2 fs-2 text-dark" style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%;"></i>
          </div>
          <h4 class="fw-bold mb-3">Explore Companies</h4>
          <p class="text-muted">Discover top companies hiring now. Learn about their culture, benefits, and available positions.</p>
          <div class="mt-auto pt-3">
            <a href="<?= Config::BASE_URL ?>/pages/user/companies.php" class="btn btn-outline-dark rounded-pill px-4">View Companies</a>
          </div>
        </div>
      </div>
      
      <div class="col-lg-4">
        <div class="text-center p-4 h-100 d-flex flex-column">
          <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px; margin: 0 auto;">
            <i class="bi bi-clipboard-check fs-2 text-dark" style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%;"></i>
          </div>
          <h4 class="fw-bold mb-3">Manage Applications</h4>
          <p class="text-muted">Track your job applications, saved jobs, and interview statuses all in one place.</p>
          <div class="mt-auto pt-3">
            <a href="<?= Config::BASE_URL ?>/pages/user/applications.php" class="btn btn-outline-dark rounded-pill px-4">View Applications</a>
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
      <a href="<?= Config::BASE_URL ?>/pages/user/jobs.php" class="btn btn-link text-dark text-decoration-none d-flex align-items-center">
        View all jobs <i class="bi bi-arrow-right ms-2"></i>
      </a>
    </div>
    
    <div class="row g-5">
      <?php if (!empty($featuredJobs) && is_array($featuredJobs)): ?>
        <?php foreach ($featuredJobs as $job): ?>
          <div class="col-md-6 col-xl-3 mb-4">
            <div class="card border-0 shadow-sm h-100 hover-lift">
              <div class="card-body p-4 d-flex flex-column">
                <?php if (!empty($job['companyLogo'])): ?>
                  <img src="<?= Config::BASE_URL ?>/assets/uploads/company_logos/<?= htmlspecialchars($job['companyLogo']) ?>" alt="<?= htmlspecialchars($job['companyName']) ?>" class="mb-3" height="24" style="max-width: 100px; object-fit: contain;">
                <?php else: ?>
                  <div class="company-placeholder mb-3 fw-bold" style="width: 24px; height: 24px; font-size: 12px; display: flex; align-items: center; justify-content: center;"><?= substr(htmlspecialchars($job['companyName'] ?? 'CO'), 0, 2) ?></div>
                <?php endif; ?>
                <h5 class="card-title mb-1"><a href="<?= Config::BASE_URL ?>/pages/user/jobs.php?id=<?= $job['jobId'] ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($job['jobTitle']) ?></a></h5>
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
                <div class="mt-auto">
                  <a href="<?= Config::BASE_URL ?>/pages/user/jobs.php?id=<?= $job['jobId'] ?>" class="btn btn-sm btn-outline-dark rounded-pill px-3">View Job</a>
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

<!-- Top Companies Section -->
<section class="py-5 border-bottom">
  <div class="container">
    <div class="text-center mb-4">
      <h3 class="h4 fw-bold">Top Companies Hiring Now</h3>
    </div>
    <div class="logo-scroll d-flex flex-wrap justify-content-center align-items-center" style="gap: 60px;">
      <?php if (!empty($topCompanies) && is_array($topCompanies)): ?>
        <?php foreach ($topCompanies as $company): ?>
          <div class="col-md-6 col-lg-4 mb-4">
            <a href="<?= Config::BASE_URL ?>/pages/user/company-details.php?id=<?= $company['companyId'] ?>" class="text-decoration-none">
              <div class="card company-card border-0 shadow-sm h-100 hover-lift" style="border-radius: 16px; overflow: hidden;">
                <div class="card-body p-4">
                  <?php if (!empty($company['logo'])): ?>
                    <img src="<?= Config::BASE_URL ?>/assets/uploads/company_logos/<?= htmlspecialchars($company['logo']) ?>" alt="<?= htmlspecialchars($company['name']) ?>" height="40">
                  <?php else: ?>
                    <div class="company-placeholder d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                      <?= substr(htmlspecialchars($company['name'] ?? 'CO'), 0, 2) ?>
                    </div>
                  <?php endif; ?>
                  <div class="mt-2 small fw-medium"><?= htmlspecialchars($company['name']) ?></div>
                </div>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12 text-center py-5">
          <p class="text-muted">No companies found. Check back soon for updates!</p>
        </div>
      <?php endif; ?>
    </div>
    
    <div class="text-center mt-4">
      <a href="<?= Config::BASE_URL ?>/pages/user/companies.php" class="btn btn-dark rounded-pill px-4 py-2">
        View All Companies
      </a>
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
    // Define the categories we want to display
    $categories = [
      'software-development' => [
        'name' => 'Software Development',
        'icon' => 'bi-code-square',
        'color' => 'text-primary',
        'keywords' => 'developer, engineer, programming, software'
      ],
      'data-science' => [
        'name' => 'Data Science',
        'icon' => 'bi-graph-up',
        'color' => 'text-success',
        'keywords' => 'data, analyst, science, ML, machine learning'
      ],
      'product-management' => [
        'name' => 'Product Management',
        'icon' => 'bi-kanban',
        'color' => 'text-danger',
        'keywords' => 'product, manager, management'
      ],
      'ux-design' => [
        'name' => 'UX/UI Design',
        'icon' => 'bi-bezier2',
        'color' => 'text-warning',
        'keywords' => 'UX, UI, design, designer'
      ],
      'devops' => [
        'name' => 'DevOps',
        'icon' => 'bi-hdd-network',
        'color' => 'text-info',
        'keywords' => 'devops, infrastructure, cloud, systems'
      ],
      'marketing' => [
        'name' => 'Marketing',
        'icon' => 'bi-megaphone',
        'color' => 'text-success',
        'keywords' => 'marketing, brand, growth, SEO'
      ],
      'sales' => [
        'name' => 'Sales',
        'icon' => 'bi-currency-dollar',
        'color' => 'text-primary',
        'keywords' => 'sales, account, business development'
      ],
      'all' => [
        'name' => 'View All Categories',
        'icon' => 'bi-grid-3x3-gap',
        'color' => 'text-dark',
        'keywords' => ''
      ]
    ];
    ?>
    
    <div class="row g-4 mb-3">
      <?php foreach ($categories as $categorySlug => $category): ?>
      <div class="col-lg-3 col-md-4 col-6 mb-4">
        <a href="<?= Config::BASE_URL ?>/pages/user/jobs.php?category=<?= $categorySlug ?>" class="text-decoration-none d-block h-100">
          <div class="card border-0 shadow-sm h-100 text-center hover-lift" style="border-radius: 12px; overflow: hidden;">
            <div class="card-body py-4 px-3">
              <div class="mb-4 <?= $category['color'] ?>">
                <i class="bi <?= $category['icon'] ?> fs-1"></i>
              </div>
              <h5 class="fw-bold text-dark mb-3"><?= $category['name'] ?></h5>
              <p class="small text-muted mb-0">
                <span class="badge bg-light text-dark px-3 py-2 rounded-pill">
                  <?= $category['name'] ?> Jobs
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

<style>
.hover-lift {
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.hover-lift:hover {
  transform: translateY(-5px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.card {
  transition: all 0.3s ease;
}

.card:hover {
  border-color: rgba(0, 0, 0, 0.1);
}

.badge {
  font-weight: 500;
}
</style>

<!-- Success Stories Section -->
<section class="py-5 bg-light">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-bold">Success Stories</h2>
      <p class="text-muted">Hear from people who found their dream jobs</p>
    </div>
    
    <div class="row g-4">
      <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body p-4">
            <div class="d-flex align-items-center mb-3">
              <img src="<?= Config::BASE_URL ?>/assets/images/Sarah Johnson.jpeg" alt="Alex Kumar" class="rounded-circle testimonial-profile" width="48" height="48">
              <div>
                <h6 class="fw-bold mb-0">Aisha Patel</h6>
                <p class="text-muted small mb-0">Full Stack Developer</p>
              </div>
            </div>
            <p class="mb-0">"Landing a role at a fast-growing startup was a game-changer. The platform's smart matching helped me find the perfect tech stack match."</p>
          </div>
        </div>
      </div>
      
      <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body p-4">
            <div class="d-flex align-items-center mb-3">
              <img src="<?= Config::BASE_URL ?>/assets/images/Michael Chen.jpeg" alt="Sofia Martinez" class="rounded-circle testimonial-profile" width="48" height="48">
              <div>
                <h6 class="fw-bold mb-0">Sofia Martinez</h6>
                <p class="text-muted small mb-0">DevOps Engineer</p>
              </div>
            </div>
            <p class="mb-0">"From cloud infrastructure to CI/CD pipelines, I found a role that challenges me daily. The platform's job alerts were spot-on for my skills."</p>
          </div>
        </div>
      </div>
      
      <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body p-4">
            <div class="d-flex align-items-center mb-3">
              <img src="<?= Config::BASE_URL ?>/assets/images/Olivia Martinez.jpeg" alt="Marcus Okafor" class="rounded-circle testimonial-profile" width="48" height="48">
              <div>
                <h6 class="fw-bold mb-0">olivia rashine</h6>
                <p class="text-muted small mb-0">AI/ML Engineer</p>
              </div>
            </div>
            <p class="mb-0">"The AI job market is competitive, but this platform helped me stand out. Now I'm leading machine learning initiatives at a top tech firm."</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- About JOBEST Section -->
<section class="py-5 bg-white">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-6 mb-4 mb-lg-0">
        <img src="<?= Config::BASE_URL ?>/assets/images/64626a4a74818ca87606a317_Frame_288-p-800.webp" alt="JOBEST Team" class="img-fluid rounded-4 shadow" style="max-height: 500px; width: 100%; object-fit: cover;">
      </div>
      <div class="col-lg-6 ps-lg-5">
        <h2 class="fw-bold mb-4">Your Path to Success</h2>
        <p class="lead text-muted mb-4">Essential tips to land your dream tech job</p>
        
        <div class="d-flex mb-4">
          <div class="me-4">
            <h5 class="fw-bold">Optimize Your Profile</h5>
            <ul class="small text-muted list-unstyled ps-3">
              <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i>Keep your skills up-to-date</li>
              <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i>Add relevant certifications</li>
              <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i>Highlight key achievements</li>
            </ul>
          </div>
        </div>
        
        <div class="d-flex mb-4">
          <div class="me-4">
            <h5 class="fw-bold">Smart Job Search</h5>
            <ul class="small text-muted list-unstyled ps-3">
              <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i>Use targeted keywords</li>
              <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i>Set up job alerts</li>
              <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i>Research companies before applying</li>
            </ul>
          </div>
        </div>
        
       
        
        <div class="mt-4">
          <a href="<?= Config::BASE_URL ?>/pages/user/jobs.php" class="btn btn-primary rounded-pill px-4 me-2">Browse Jobs</a>
          <a href="<?= Config::BASE_URL ?>/pages/user/profile.php" class="btn btn-outline-primary rounded-pill px-4">Update Profile</a>
        </div>
      </div>
    </div>
  </div>
</section>

<style>
.testimonial-profile {
  margin-right: 20px !important;
}
</style>


<?php include __DIR__ . '/../../includes/footer.php'; ?> 