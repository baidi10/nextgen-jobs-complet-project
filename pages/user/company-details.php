<?php
require_once __DIR__ . '/../../includes/dependencies.php';

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'jobSeeker') {
    header('Location: ' . Config::BASE_URL . '/pages/public/login.php');
    exit;
}

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Company.php';
require_once __DIR__ . '/../../classes/Job.php';

// Get company ID from URL
$companyId = $_GET['id'] ?? 0;

// Initialize Company class
$company = new Company();
$jobs = new Job();

// Get company details
$companyData = $company->getDetails($companyId);

// If company not found, redirect to companies list
if (!$companyData) {
    header('Location: ' . Config::BASE_URL . '/pages/user/companies.php');
    exit;
}

// Increment profile view counter
$company->incrementProfileViews($companyId);

// Get jobs from this company - create a basic structure if method doesn't exist
$companyJobs = method_exists($jobs, 'getJobsByCompany') 
    ? $jobs->getJobsByCompany($companyId, 1, 6) 
    : ['jobs' => [], 'total' => 0];

$pageTitle = htmlspecialchars($companyData['companyName']) . " | JOBEST";
include __DIR__ . '/../../includes/header.php';
?>

<div class="container py-5">
    <!-- Company Header -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center mb-3">
                        <?php 
                        $logoFile = !empty($companyData['logo']) ? $companyData['logo'] : 'default.png';
                        $logoPath = Config::BASE_URL . '/assets/uploads/company_logos/' . $logoFile;
                        ?>
                        <div style="
                            width: 100px;
                            height: 100px;
                            border-radius: 16px;
                            overflow: hidden;
                            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
                            border: 1px solid #eee;
                            background: white;
                            margin-right: 30px;
                        ">
                            <img src="<?= $logoPath ?>" 
                                 alt="<?= htmlspecialchars($companyData['companyName']) ?>" 
                                 style="
                                    width: 100%;
                                    height: 100%;
                                    object-fit: cover;
                                    object-position: center;
                                 "
                                 onerror="this.src='<?= getCompanyAvatar($companyId, 100) ?>'">
                        </div>
                        
                        <div>
                            <h1 class="h3 fw-bold mb-2" style="color: #2d3748;">
                                <?= htmlspecialchars($companyData['companyName']) ?>
                                <?php if (!empty($companyData['isVerified'])): ?>
                                <span class="badge bg-success align-middle" style="
                                    font-size: 0.7rem;
                                    padding: 0.35em 0.65em;
                                    border-radius: 6px;
                                    background-color: #10B981 !important;
                                ">
                                    <i class="bi bi-check-circle-fill me-1"></i>Verified
                                </span>
                                <?php endif; ?>
                            </h1>
                            <p class="text-muted mb-0" style="font-size: 1.1rem;">
                                <?= htmlspecialchars($companyData['industry'] ?? 'Technology') ?>
                                <?php if (!empty($companyData['headquarters'])): ?>
                                • <?= htmlspecialchars($companyData['headquarters']) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <?php if (!empty($companyData['websiteUrl'])): ?>
                    <a href="<?= htmlspecialchars($companyData['websiteUrl']) ?>" target="_blank" class="btn btn-outline-primary me-2" style="
                        border-radius: 8px;
                        padding: 0.6rem 1.2rem;
                        font-weight: 500;
                        border-width: 1.5px;
                    ">
                        <i class="bi bi-globe me-1"></i>Visit Website
                    </a>
                    <?php endif; ?>
                    <a href="<?= Config::BASE_URL ?>/pages/user/jobs.php?company=<?= $companyId ?>" class="btn btn-dark" style="
                        border-radius: 8px;
                        padding: 0.6rem 1.2rem;
                        font-weight: 500;
                        background-color: #1a202c;
                    ">
                        View All Jobs
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- Company Details -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-4" style="color: #2d3748;">About <?= htmlspecialchars($companyData['companyName']) ?></h2>
                    
                    <div class="description mb-4" style="
                        line-height: 1.7;
                        color: #4a5568;
                        font-size: 1.05rem;
                    ">
                        <?php if (!empty($companyData['description'])): ?>
                            <p><?= nl2br(htmlspecialchars($companyData['description'])) ?></p>
                        <?php else: ?>
                            <p class="text-muted">No company description available.</p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Company Jobs -->
                    <?php if (!empty($companyJobs['jobs'])): ?>
                    <div class="mt-5">
                        <h3 class="h5 fw-bold mb-4" style="color: #2d3748;">Open Positions</h3>
                        <div class="list-group">
                            <?php foreach ($companyJobs['jobs'] as $job): ?>
                            <a href="<?= Config::BASE_URL ?>/pages/public/job-details.php?id=<?= $job['jobId'] ?>" 
                               class="list-group-item list-group-item-action border-0 mb-2 rounded" 
                               style="
                                border-radius: 12px !important;
                                transition: all 0.2s ease;
                                background-color: #f8fafc;
                                text-decoration: none;
                                color: inherit;
                               "
                               onmouseover="this.style.backgroundColor='#f1f5f9'"
                               onmouseout="this.style.backgroundColor='#f8fafc'">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <h5 class="mb-1" style="color: #2d3748; font-weight: 600;"><?= htmlspecialchars($job['jobTitle']) ?></h5>
                                    <?php if (!empty($job['location'])): ?>
                                    <span class="badge bg-light text-dark" style="
                                        padding: 0.5em 0.8em;
                                        border-radius: 6px;
                                        font-weight: 500;
                                        background-color: #e2e8f0 !important;
                                    "><?= htmlspecialchars($job['location']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <p class="mb-1 text-muted small" style="font-size: 0.95rem;"><?= htmlspecialchars($job['jobType'] ?? 'Full-time') ?></p>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if ($companyJobs['total'] > count($companyJobs['jobs'])): ?>
                        <div class="text-center mt-4">
                            <a href="<?= Config::BASE_URL ?>/pages/user/jobs.php?company=<?= $companyId ?>" class="btn btn-outline-primary btn-sm" style="
                                border-radius: 8px;
                                padding: 0.5rem 1rem;
                                font-weight: 500;
                            ">
                                View All <?= $companyJobs['total'] ?> Jobs
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Company Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <h3 class="h6 fw-bold mb-4" style="color: #2d3748;">Company Information</h3>
                    
                    <ul class="list-unstyled">
                        <?php if (!empty($companyData['industry'])): ?>
                        <li class="d-flex mb-4">
                            <i class="bi bi-building me-3 text-muted" style="font-size: 1.2rem;"></i>
                            <div>
                                <span class="d-block text-muted small mb-1">Industry</span>
                                <span style="color: #2d3748; font-weight: 500;"><?= htmlspecialchars($companyData['industry']) ?></span>
                            </div>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($companyData['employeeCount'])): ?>
                        <li class="d-flex mb-4">
                            <i class="bi bi-people me-3 text-muted" style="font-size: 1.2rem;"></i>
                            <div>
                                <span class="d-block text-muted small mb-1">Company Size</span>
                                <span style="color: #2d3748; font-weight: 500;"><?= htmlspecialchars($companyData['employeeCount']) ?></span>
                            </div>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($companyData['foundedYear'])): ?>
                        <li class="d-flex mb-4">
                            <i class="bi bi-calendar-event me-3 text-muted" style="font-size: 1.2rem;"></i>
                            <div>
                                <span class="d-block text-muted small mb-1">Founded</span>
                                <span style="color: #2d3748; font-weight: 500;"><?= htmlspecialchars($companyData['foundedYear']) ?></span>
                            </div>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($companyData['headquarters'])): ?>
                        <li class="d-flex mb-4">
                            <i class="bi bi-geo-alt me-3 text-muted" style="font-size: 1.2rem;"></i>
                            <div>
                                <span class="d-block text-muted small mb-1">Location</span>
                                <span style="color: #2d3748; font-weight: 500;"><?= htmlspecialchars($companyData['headquarters']) ?></span>
                            </div>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($companyData['websiteUrl'])): ?>
                        <li class="d-flex">
                            <i class="bi bi-globe me-3 text-muted" style="font-size: 1.2rem;"></i>
                            <div>
                                <span class="d-block text-muted small mb-1">Website</span>
                                <a href="<?= htmlspecialchars($companyData['websiteUrl']) ?>" target="_blank" style="
                                    color: #3182ce;
                                    text-decoration: none;
                                    font-weight: 500;
                                ">
                                    <?= htmlspecialchars(preg_replace('#^https?://#', '', $companyData['websiteUrl'])) ?>
                                </a>
                            </div>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            
            <!-- Similar Companies -->
            <div class="card shadow-sm border-0" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <h3 class="h6 fw-bold mb-4" style="color: #2d3748;">Similar Companies</h3>
                    
                    <div class="d-flex flex-column gap-3">
                        <?php if (!empty($companyData['industry'])): ?>
                        <p class="small text-muted mb-2">Explore more companies in <?= htmlspecialchars($companyData['industry']) ?></p>
                        <a href="<?= Config::BASE_URL ?>/pages/user/companies.php?industry=<?= urlencode($companyData['industry']) ?>" class="btn btn-outline-dark btn-sm" style="
                            border-radius: 8px;
                            padding: 0.5rem 1rem;
                            font-weight: 500;
                            border-width: 1.5px;
                        ">
                            View Similar Companies
                        </a>
                        <?php else: ?>
                        <p class="small text-muted mb-2">Explore more companies on our platform</p>
                        <a href="<?= Config::BASE_URL ?>/pages/user/companies.php" class="btn btn-outline-dark btn-sm" style="
                            border-radius: 8px;
                            padding: 0.5rem 1rem;
                            font-weight: 500;
                            border-width: 1.5px;
                        ">
                            Browse All Companies
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?> 