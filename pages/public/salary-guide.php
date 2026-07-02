<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$pageTitle = "Salary Guide | JOBEST";
include __DIR__ . '/../../includes/header.php';

// Sample salary data - in a real application, this would come from a database
$techSalaries = [
    [
        'role' => 'Software Engineer',
        'junior' => ['min' => 70000, 'max' => 100000],
        'mid' => ['min' => 95000, 'max' => 140000],
        'senior' => ['min' => 130000, 'max' => 180000]
    ],
    [
        'role' => 'Frontend Developer',
        'junior' => ['min' => 65000, 'max' => 90000],
        'mid' => ['min' => 90000, 'max' => 130000],
        'senior' => ['min' => 125000, 'max' => 170000]
    ],
    [
        'role' => 'Backend Developer',
        'junior' => ['min' => 75000, 'max' => 105000],
        'mid' => ['min' => 100000, 'max' => 145000],
        'senior' => ['min' => 135000, 'max' => 185000]
    ],
    [
        'role' => 'Full Stack Developer',
        'junior' => ['min' => 75000, 'max' => 110000],
        'mid' => ['min' => 105000, 'max' => 150000],
        'senior' => ['min' => 140000, 'max' => 190000]
    ],
    [
        'role' => 'DevOps Engineer',
        'junior' => ['min' => 85000, 'max' => 120000],
        'mid' => ['min' => 110000, 'max' => 160000],
        'senior' => ['min' => 150000, 'max' => 200000]
    ],
    [
        'role' => 'Data Scientist',
        'junior' => ['min' => 85000, 'max' => 120000],
        'mid' => ['min' => 115000, 'max' => 160000],
        'senior' => ['min' => 150000, 'max' => 210000]
    ],
    [
        'role' => 'UX Designer',
        'junior' => ['min' => 60000, 'max' => 85000],
        'mid' => ['min' => 85000, 'max' => 120000],
        'senior' => ['min' => 115000, 'max' => 160000]
    ],
    [
        'role' => 'Product Manager',
        'junior' => ['min' => 80000, 'max' => 110000],
        'mid' => ['min' => 110000, 'max' => 150000],
        'senior' => ['min' => 145000, 'max' => 200000]
    ],
    [
        'role' => 'QA Engineer',
        'junior' => ['min' => 60000, 'max' => 90000],
        'mid' => ['min' => 80000, 'max' => 120000],
        'senior' => ['min' => 115000, 'max' => 150000]
    ],
    [
        'role' => 'Machine Learning Engineer',
        'junior' => ['min' => 90000, 'max' => 130000],
        'mid' => ['min' => 120000, 'max' => 170000],
        'senior' => ['min' => 160000, 'max' => 220000]
    ]
];

// Top cities for tech salaries
$topTechCities = [
    ['city' => 'San Francisco', 'adjustment' => 1.5],
    ['city' => 'New York', 'adjustment' => 1.4], 
    ['city' => 'Seattle', 'adjustment' => 1.3],
    ['city' => 'Boston', 'adjustment' => 1.2],
    ['city' => 'Austin', 'adjustment' => 1.1],
    ['city' => 'Chicago', 'adjustment' => 1.05],
    ['city' => 'Denver', 'adjustment' => 1],
    ['city' => 'Atlanta', 'adjustment' => 0.95],
    ['city' => 'Dallas', 'adjustment' => 0.95],
    ['city' => 'Remote', 'adjustment' => 1]
];
?>

<div class="container py-5">
    <!-- Hero Section -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-5 fw-bold mb-4">Tech Salary Guide</h1>
            <p class="lead text-muted">
                Comprehensive salary information for the most in-demand tech roles. 
                Use this guide to benchmark compensation and negotiate better offers.
            </p>
        </div>
    </div>

    <!-- Introduction -->
    <div class="row mb-5">
        <div class="col-lg-10 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <h2 class="h4 fw-bold mb-4">About This Salary Guide</h2>
                    <p>
                        Our salary data is based on thousands of job listings and reported salaries across the tech industry. 
                        We regularly update this guide to reflect current market conditions and trends. Salaries may vary based on:
                    </p>
                    <div class="row g-4 mt-3">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-geo-alt text-primary fs-4 me-3"></i>
                                <div>
                                    <h3 class="h6 fw-bold mb-1">Location</h3>
                                    <p class="small text-muted mb-0">Major tech hubs tend to offer higher salaries</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-briefcase text-primary fs-4 me-3"></i>
                                <div>
                                    <h3 class="h6 fw-bold mb-1">Experience</h3>
                                    <p class="small text-muted mb-0">Junior, mid-level, and senior positions</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-building text-primary fs-4 me-3"></i>
                                <div>
                                    <h3 class="h6 fw-bold mb-1">Company Size</h3>
                                    <p class="small text-muted mb-0">From startups to large enterprises</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Salary Table -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="h3 fw-bold mb-4">Tech Role Salary Ranges</h2>
            <p class="text-muted mb-4">All figures in USD for annual salaries</p>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">Role</th>
                            <th scope="col">Junior (0-2 years)</th>
                            <th scope="col">Mid-Level (3-5 years)</th>
                            <th scope="col">Senior (6+ years)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($techSalaries as $salary): ?>
                        <tr>
                            <td class="fw-medium"><?= htmlspecialchars($salary['role']) ?></td>
                            <td>$<?= number_format($salary['junior']['min']) ?> - $<?= number_format($salary['junior']['max']) ?></td>
                            <td>$<?= number_format($salary['mid']['min']) ?> - $<?= number_format($salary['mid']['max']) ?></td>
                            <td>$<?= number_format($salary['senior']['min']) ?> - $<?= number_format($salary['senior']['max']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Location-Based Adjustments -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-4">Location Adjustments</h2>
                    <p class="mb-4">Salaries vary significantly by location. Use these multipliers to estimate salaries in different cities:</p>
                    
                    <div class="row g-4">
                        <?php foreach ($topTechCities as $city): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-2">
                                <span class="fw-medium"><?= htmlspecialchars($city['city']) ?></span>
                                <span class="badge <?= $city['adjustment'] >= 1.3 ? 'bg-primary' : ($city['adjustment'] >= 1 ? 'bg-success' : 'bg-secondary') ?>">
                                    <?= $city['adjustment'] > 1 ? '+' . (($city['adjustment'] - 1) * 100) . '%' : ($city['adjustment'] < 1 ? '-' . ((1 - $city['adjustment']) * 100) . '%' : 'Baseline') ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Salary Trends -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="h3 fw-bold mb-4">Tech Salary Trends</h2>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-lg-7 pe-lg-5">
                            <h3 class="h5 fw-bold mb-3">Current Market Insights</h3>
                            <ul class="list-unstyled">
                                <li class="mb-3">
                                    <div class="d-flex">
                                        <i class="bi bi-arrow-up-circle text-success me-2 fs-5"></i>
                                        <div>
                                            <p class="fw-medium mb-1">Highest Growth Areas</p>
                                            <p class="text-muted small">Machine Learning, Cloud Engineering, and Cybersecurity are seeing the fastest salary growth.</p>
                                        </div>
                                    </div>
                                </li>
                                <li class="mb-3">
                                    <div class="d-flex">
                                        <i class="bi bi-graph-up text-primary me-2 fs-5"></i>
                                        <div>
                                            <p class="fw-medium mb-1">Remote Work Impact</p>
                                            <p class="text-muted small">Remote positions typically offer 5-15% lower salaries than on-site roles in major tech hubs, but often higher than local market rates.</p>
                                        </div>
                                    </div>
                                </li>
                                <li class="mb-3">
                                    <div class="d-flex">
                                        <i class="bi bi-stars text-warning me-2 fs-5"></i>
                                        <div>
                                            <p class="fw-medium mb-1">Emerging Skills Premium</p>
                                            <p class="text-muted small">Professionals with AI, blockchain, and advanced data analytics skills command 10-20% salary premiums.</p>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="col-lg-5 mt-4 mt-lg-0">
                            <h3 class="h5 fw-bold mb-3">Annual Growth by Role</h3>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span class="small">Machine Learning Engineer</span>
                                    <span class="small fw-bold">+7.5%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 75%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span class="small">DevOps Engineer</span>
                                    <span class="small fw-bold">+6.8%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 68%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span class="small">Data Scientist</span>
                                    <span class="small fw-bold">+6.2%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 62%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span class="small">Full Stack Developer</span>
                                    <span class="small fw-bold">+5.5%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 55%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span class="small">UX Designer</span>
                                    <span class="small fw-bold">+4.2%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 42%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Negotiation Tips -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="h3 fw-bold mb-4">Salary Negotiation Tips</h2>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <div class="row">
                        <div class="col-lg-6 mb-4 mb-lg-0">
                            <h3 class="h5 fw-bold mb-3">Do's</h3>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item bg-transparent px-0">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    Research market rates before negotiations
                                </li>
                                <li class="list-group-item bg-transparent px-0">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    Highlight your specific achievements and skills
                                </li>
                                <li class="list-group-item bg-transparent px-0">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    Consider the full compensation package, not just salary
                                </li>
                                <li class="list-group-item bg-transparent px-0">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    Ask for time to consider offers
                                </li>
                                <li class="list-group-item bg-transparent px-0">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    Practice your negotiation conversation
                                </li>
                            </ul>
                        </div>
                        <div class="col-lg-6">
                            <h3 class="h5 fw-bold mb-3">Don'ts</h3>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item bg-transparent px-0">
                                    <i class="bi bi-x-circle-fill text-danger me-2"></i>
                                    Accept the first offer without negotiating
                                </li>
                                <li class="list-group-item bg-transparent px-0">
                                    <i class="bi bi-x-circle-fill text-danger me-2"></i>
                                    Provide your salary expectations too early
                                </li>
                                <li class="list-group-item bg-transparent px-0">
                                    <i class="bi bi-x-circle-fill text-danger me-2"></i>
                                    Make demands or ultimatums
                                </li>
                                <li class="list-group-item bg-transparent px-0">
                                    <i class="bi bi-x-circle-fill text-danger me-2"></i>
                                    Focus only on salary and ignore benefits
                                </li>
                                <li class="list-group-item bg-transparent px-0">
                                    <i class="bi bi-x-circle-fill text-danger me-2"></i>
                                    Share confidential salary information from other offers
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- CTA -->
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card bg-dark text-white border-0">
                <div class="card-body p-4 p-lg-5 text-center">
                    <h2 class="h4 fw-bold mb-3">Ready to find your next opportunity?</h2>
                    <p class="mb-4">
                        Browse our job listings to find roles that match your experience and salary expectations.
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="<?= Config::BASE_URL ?>/pages/public/jobs.php" class="btn btn-light rounded-pill px-4">Browse Jobs</a>
                        <a href="<?= Config::BASE_URL ?>/pages/public/salary-guide.php" class="btn btn-outline-light rounded-pill px-4">More Resources</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?> 