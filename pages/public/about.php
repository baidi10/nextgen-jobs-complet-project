<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Company.php';
require_once __DIR__ . '/../../classes/Job.php';

// Get statistics from database
$db = Database::getInstance()->getConnection();
$company = new Company();
$job = new Job();

// Get job seekers count
$jobSeekersQuery = "SELECT COUNT(*) FROM users WHERE userType = 'jobSeeker'";
$jobSeekersStmt = $db->prepare($jobSeekersQuery);
$jobSeekersStmt->execute();
$jobSeekersCount = $jobSeekersStmt->fetchColumn();

// Get total jobs count
$jobsCount = $job->countActiveJobs();

// Get successful hires count (professionals placed)
$hiresQuery = "SELECT COUNT(*) FROM applications WHERE status = 'hired'";
$hiresStmt = $db->prepare($hiresQuery);
$hiresStmt->execute();
$hiresCount = $hiresStmt->fetchColumn();

// Get companies count
$companiesCount = $company->countCompanies();
$pageTitle = "About Us | JOBEST";
include __DIR__ . '/../../includes/header.php';
?>

<div class="container py-5">
    <!-- Hero Section -->
    <div class="row align-items-center mb-5">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <h1 class="display-5 fw-bold mb-3">About JOBEST</h1>
            <p class="lead text-secondary mb-4">Connecting talent with opportunity in the tech industry since 2018.</p>
            <p class="mb-4">At JOBEST, we're on a mission to revolutionize the way tech professionals find their dream jobs and how companies discover exceptional talent. We're more than just a job board – we're a career ecosystem designed to create meaningful connections.</p>
            <div class="d-flex align-items-center mb-4">
                <div>
                    <h5 class="mb-1"><?= number_format($jobSeekersCount) ?>+</h5>
                    <p class="mb-0 text-muted">Tech professionals</p>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <div>
                    <h5 class="mb-1"><?= number_format($companiesCount) ?>+</h5>
                    <p class="mb-0 text-muted">Partner companies</p>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="ratio ratio-16x9 rounded-4 shadow-sm overflow-hidden" style="max-width: 600px; margin: 0 auto; height: 300px;">
                <video class="w-100 h-100" autoplay loop muted playsinline style="object-fit: cover;">
                    <source src="<?= Config::BASE_URL ?>/assets/videos/7685215-hd_1920_1080_24fps.mp4" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
        </div>
    </div>

    <!-- Statistics Section -->
    <div class="py-5 bg-light rounded-4 shadow-sm mb-5">
        <div class="row text-center g-4">
            <div class="col-md-3">
                <div class="stat-number display-5 fw-bold text-primary mb-1"><?= number_format($jobsCount) ?>+</div>
                <p class="text-muted">Active Jobs</p>
            </div>
            <div class="col-md-3">
                <div class="stat-number display-5 fw-bold text-success mb-1"><?= number_format($hiresCount) ?>+</div>
                <p class="text-muted">Successful Placements</p>
            </div>
            <div class="col-md-3">
                <div class="stat-number display-5 fw-bold text-info mb-1"><?= number_format($jobSeekersCount) ?>+</div>
                <p class="text-muted">Job Seekers</p>
            </div>
            <div class="col-md-3">
                <div class="stat-number display-5 fw-bold text-warning mb-1"><?= number_format($companiesCount) ?>+</div>
                <p class="text-muted">Partner Companies</p>
            </div>
        </div>
    </div>

    <!-- Mission & Values -->
    <div class="py-5">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="h2 fw-bold mb-4">Our Mission</h2>
                <p class="lead">To empower tech professionals to reach their full potential and help companies build transformative teams.</p>
            </div>
        </div>
        
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="h2 fw-bold mb-4">Our Values</h2>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="d-inline-flex align-items-center justify-content-center mb-3">
                            <i class="bi bi-trophy-fill text-dark fs-1"></i>
                        </div>
                        <h3 class="h5 fw-bold mb-3">Excellence</h3>
                        <p class="text-muted mb-0">We're committed to delivering exceptional service and experiences for both job seekers and employers.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="d-inline-flex align-items-center justify-content-center mb-3">
                            <i class="bi bi-heart-fill text-dark fs-1"></i>
                        </div>
                        <h3 class="h5 fw-bold mb-3">Community</h3>
                        <p class="text-muted mb-0">We foster an inclusive community where diverse talents and perspectives are valued and celebrated.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="d-inline-flex align-items-center justify-content-center mb-3">
                            <i class="bi bi-lightning-charge-fill text-dark fs-1"></i>
                        </div>
                        <h3 class="h5 fw-bold mb-3">Innovation</h3>
                        <p class="text-muted mb-0">We continuously evolve our platform to meet the changing needs of the tech industry and job market.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Story Section -->
    <div class="py-5">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <img src="<?= Config::BASE_URL ?>/assets/images/pexels-kampus-8204373.jpg" alt="Our Story" class="img-fluid rounded-4 shadow-sm">
            </div>
            <div class="col-lg-6">
                <h2 class="h2 fw-bold mb-4">Our Story</h2>
                <p class="mb-4">JOBEST was founded in 2018 by a team of tech industry veterans who recognized the disconnect between talented professionals and the companies that needed them.</p>
                <p class="mb-4">What started as a small job board quickly evolved into a comprehensive platform that serves thousands of tech professionals and companies worldwide. Today, we're proud to be a leading voice in the tech recruitment space.</p>
                <p>Our commitment to quality, personalized service, and cutting-edge technology has made us the go-to platform for tech recruitment, and we're just getting started.</p>
            </div>
        </div>
    </div>

    <!-- Team Section -->
    <div class="py-5">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="h2 fw-bold mb-4">Meet Our Leadership Team</h2>
                <p class="lead mb-0">The dedicated professionals driving our vision forward.</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 text-center">
                    <div class="card-body p-4">
                        <div class="mx-auto mb-3" style="width: 120px; height: 120px; overflow: hidden; border-radius: 50%;">
                            <img src="<?= Config::BASE_URL ?>/assets/images/Sarah Johnson.jpeg" alt="Sarah Johnson" class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <h3 class="h5 fw-bold mb-1">Sarah Johnson</h3>
                        <p class="text-muted mb-3">CEO & Co-Founder</p>
                        <p class="small text-muted mb-3">Former tech recruiter with 15+ years of experience in the industry.</p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="#" class="text-muted fs-5"><i class="bi bi-linkedin"></i></a>
                            <a href="#" class="text-muted fs-5"><i class="bi bi-twitter"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 text-center">
                    <div class="card-body p-4">
                        <div class="mx-auto mb-3" style="width: 120px; height: 120px; overflow: hidden; border-radius: 50%;">
                            <img src="<?= Config::BASE_URL ?>/assets/images/Michael Chen.jpeg" alt="Michael Chen" class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <h3 class="h5 fw-bold mb-1">Michael Chen</h3>
                        <p class="text-muted mb-3">CTO & Co-Founder</p>
                        <p class="small text-muted mb-3">Software engineer turned recruitment technology expert.</p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="#" class="text-muted fs-5"><i class="bi bi-linkedin"></i></a>
                            <a href="#" class="text-muted fs-5"><i class="bi bi-github"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 text-center">
                    <div class="card-body p-4">
                        <div class="mx-auto mb-3" style="width: 120px; height: 120px; overflow: hidden; border-radius: 50%;">
                            <img src="<?= Config::BASE_URL ?>/assets/images/Olivia Martinez.jpeg" alt="Olivia Martinez" class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <h3 class="h5 fw-bold mb-1">Olivia Martinez</h3>
                        <p class="text-muted mb-3">Head of Operations</p>
                        <p class="small text-muted mb-3">Specializes in scaling tech businesses and optimizing customer success.</p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="#" class="text-muted fs-5"><i class="bi bi-linkedin"></i></a>
                            <a href="#" class="text-muted fs-5"><i class="bi bi-instagram"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="py-5 text-center">
        <div class="bg-light rounded-4 p-5 shadow-sm">
            <h2 class="h2 fw-bold mb-4">Join the JOBEST Community</h2>
            <p class="lead mb-4">Whether you're looking for your next career move or searching for top tech talent, we're here to help.</p>
            <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                <a href="<?= Config::BASE_URL ?>/pages/public/register.php" class="btn btn-primary btn-lg px-4 py-3 rounded-pill">Create an Account</a>
                <a href="<?= Config::BASE_URL ?>/pages/public/contact.php" class="btn btn-outline-secondary btn-lg px-4 py-3 rounded-pill">Contact Us</a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?> 