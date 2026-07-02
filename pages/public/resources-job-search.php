<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$pageTitle = "Job Search Tips | JOBEST";
include __DIR__ . '/../../includes/header.php';
?>

<div class="container py-5">
    <!-- Hero Section -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-5 fw-bold mb-4">Job Search Tips</h1>
            <p class="lead text-muted">
                Practical strategies to help you find and land your ideal tech role.
            </p>
        </div>
    </div>
    
    <!-- Resource Navigation -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card bg-light border-0 shadow-sm">
                <div class="card-body p-4">
                    <nav class="nav nav-pills nav-justified flex-column flex-md-row">
                        <a class="nav-link active rounded-pill mb-2 mb-md-0" href="#search-strategy">Search Strategy</a>
                        <a class="nav-link rounded-pill mb-2 mb-md-0" href="#online-presence">Online Presence</a>
                        <a class="nav-link rounded-pill mb-2 mb-md-0" href="#networking">Networking</a>
                        <a class="nav-link rounded-pill mb-2 mb-md-0" href="#job-applications">Job Applications</a>
                        <a class="nav-link rounded-pill mb-2 mb-md-0" href="#following-up">Following Up</a>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Content Column -->
        <div class="col-lg-8">
            <!-- Search Strategy Section -->
            <section id="search-strategy" class="mb-5">
                <h2 class="h3 fw-bold mb-4">Developing Your Job Search Strategy</h2>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">1. Define Your Career Goals</h3>
                        <p>Before diving into job boards, take time to clarify what you're looking for:</p>
                        <ul>
                            <li class="mb-2">Identify your must-have job requirements (salary range, location, remote options)</li>
                            <li class="mb-2">Determine what type of company culture suits you best</li>
                            <li class="mb-2">Consider what growth opportunities are important to you</li>
                            <li class="mb-2">Decide which technologies you want to work with</li>
                        </ul>
                        <div class="alert alert-light border-start border-4 mt-4">
                            <strong>Pro Tip:</strong> Create a career vision document outlining your ideal role, company, and growth path for the next 1-3 years.
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">2. Research Target Companies</h3>
                        <p>Don't just apply to any open position. Research companies that align with your goals:</p>
                        <ul>
                            <li class="mb-2">Create a list of 20-30 target companies you'd love to work for</li>
                            <li class="mb-2">Follow these companies on LinkedIn and set job alerts</li>
                            <li class="mb-2">Research their tech stack, culture, and recent news</li>
                            <li class="mb-2">Check Glassdoor and similar sites for employee reviews</li>
                        </ul>
                        <p class="mt-3">This targeted approach allows you to customize your applications and increases your chances of finding a great culture fit.</p>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">3. Organize Your Job Search</h3>
                        <p>Treat your job search like a project and stay organized:</p>
                        <ul>
                            <li class="mb-2">Set up a dedicated job search tracking system (spreadsheet or tool)</li>
                            <li class="mb-2">Track application dates, follow-ups, and interview stages</li>
                            <li class="mb-2">Schedule dedicated time for job searching activities</li>
                            <li class="mb-2">Set weekly application and networking goals</li>
                        </ul>
                        <div class="alert alert-light border-start border-4 mt-4">
                            <strong>Resource:</strong> 
                            <a href="#" class="text-decoration-none">Download our free job search tracking template</a> to keep your applications organized.
                        </div>
                    </div>
                </div>
            </section>

            <!-- Online Presence Section -->
            <section id="online-presence" class="mb-5">
                <h2 class="h3 fw-bold mb-4">Building Your Online Presence</h2>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">1. Optimize Your LinkedIn Profile</h3>
                        <p>LinkedIn is often the first place recruiters look to find candidates:</p>
                        <ul>
                            <li class="mb-2">Use a professional headshot and compelling background image</li>
                            <li class="mb-2">Write a headline that highlights your expertise and career focus</li>
                            <li class="mb-2">Create an "About" section that tells your professional story</li>
                            <li class="mb-2">Detail your experience with quantifiable achievements</li>
                            <li class="mb-2">List relevant skills and collect endorsements</li>
                            <li class="mb-2">Request recommendations from colleagues and managers</li>
                        </ul>
                        <p class="mt-3">Update your LinkedIn profile settings to show you're "open to work" and set up job alerts for relevant positions.</p>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">2. Create a Professional Portfolio</h3>
                        <p>Showcase your skills and projects with an online portfolio:</p>
                        <ul>
                            <li class="mb-2">Select your best projects that demonstrate relevant skills</li>
                            <li class="mb-2">Include detailed descriptions of your role and contributions</li>
                            <li class="mb-2">Add visuals (screenshots, diagrams) when applicable</li>
                            <li class="mb-2">Share the challenges you faced and how you solved them</li>
                            <li class="mb-2">Include links to live demos or GitHub repositories</li>
                        </ul>
                        <div class="alert alert-light border-start border-4 mt-4">
                            <strong>Pro Tip:</strong> For technical roles, keep your GitHub profile active and organize your repositories with clear READMEs.
                        </div>
                    </div>
                </div>
            </section>

            <!-- Networking Section -->
            <section id="networking" class="mb-5">
                <h2 class="h3 fw-bold mb-4">Effective Networking Strategies</h2>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">1. Connect With Industry Professionals</h3>
                        <p>Networking is often the most effective way to find opportunities:</p>
                        <ul>
                            <li class="mb-2">Identify relevant professionals in your target companies</li>
                            <li class="mb-2">Send personalized connection requests on LinkedIn</li>
                            <li class="mb-2">Ask for informational interviews (15-30 minutes)</li>
                            <li class="mb-2">Prepare thoughtful questions about their role and company</li>
                            <li class="mb-2">Follow up with a thank-you note after conversations</li>
                        </ul>
                        <div class="alert alert-light border-start border-4 mt-4">
                            <strong>Template:</strong> "Hi [Name], I'm a [Your Role] interested in learning more about [Company/Industry]. I admire your work in [specific area], and I'd appreciate 15 minutes to learn about your career path. Would you be open to a brief virtual coffee chat?"
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">2. Participate in Professional Communities</h3>
                        <p>Engage with communities related to your field:</p>
                        <ul>
                            <li class="mb-2">Join relevant Slack groups, Discord servers, and forums</li>
                            <li class="mb-2">Attend virtual and in-person tech meetups and conferences</li>
                            <li class="mb-2">Participate in hackathons and industry events</li>
                            <li class="mb-2">Contribute to discussions and share your knowledge</li>
                            <li class="mb-2">Follow and engage with industry leaders on social media</li>
                        </ul>
                        <p class="mt-3">Active participation helps you build relationships and often leads to referrals and hidden job opportunities.</p>
                    </div>
                </div>
            </section>

            <!-- Job Applications Section -->
            <section id="job-applications" class="mb-5">
                <h2 class="h3 fw-bold mb-4">Crafting Effective Job Applications</h2>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">1. Tailor Your Resume for Each Role</h3>
                        <p>Generic resumes rarely make it past applicant tracking systems:</p>
                        <ul>
                            <li class="mb-2">Analyze the job description for keywords and required skills</li>
                            <li class="mb-2">Customize your resume to highlight relevant experience</li>
                            <li class="mb-2">Quantify achievements with specific metrics and results</li>
                            <li class="mb-2">Use industry-specific terminology that matches the job description</li>
                            <li class="mb-2">Include a brief professional summary that aligns with the role</li>
                        </ul>
                        <div class="alert alert-light border-start border-4 mt-4">
                            <strong>Pro Tip:</strong> Create a "master resume" with all your experience, then tailor a version for each application by selecting the most relevant points.
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">2. Write Compelling Cover Letters</h3>
                        <p>A well-written cover letter can set you apart:</p>
                        <ul>
                            <li class="mb-2">Research the company thoroughly before writing</li>
                            <li class="mb-2">Address a specific person when possible</li>
                            <li class="mb-2">Explain why you're interested in this specific role and company</li>
                            <li class="mb-2">Highlight 2-3 relevant accomplishments that demonstrate your fit</li>
                            <li class="mb-2">Connect your experience to the company's needs and challenges</li>
                            <li class="mb-2">Keep it concise and professional (under one page)</li>
                        </ul>
                        <p class="mt-3">Even when cover letters are optional, including a thoughtful one shows your genuine interest and attention to detail.</p>
                    </div>
                </div>
            </section>

            <!-- Following Up Section -->
            <section id="following-up" class="mb-5">
                <h2 class="h3 fw-bold mb-4">Following Up Effectively</h2>
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">The Art of Following Up</h3>
                        <p>Following up shows your continued interest without being intrusive:</p>
                        <ul>
                            <li class="mb-2">Wait 1-2 weeks after applying before following up</li>
                            <li class="mb-2">Send a brief, professional email checking on your application status</li>
                            <li class="mb-2">Connect with the hiring manager or recruiter on LinkedIn if appropriate</li>
                            <li class="mb-2">After interviews, send a thank-you email within 24 hours</li>
                            <li class="mb-2">Reference specific topics from your conversation in follow-up messages</li>
                        </ul>
                        <div class="alert alert-light border-start border-4 mt-4">
                            <strong>Template:</strong> "Dear [Name], I hope this email finds you well. I'm writing to follow up on my application for the [Position] role submitted on [Date]. I remain very interested in the opportunity and would appreciate any update on the status of my application. Thank you for your time and consideration."
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Next Steps -->
            <div class="card bg-dark text-white border-0">
                <div class="card-body p-4 p-lg-5">
                    <h2 class="h4 fw-bold mb-3">Ready to start your job search?</h2>
                    <p class="mb-4">
                        Put these strategies into action by exploring our current job listings or checking out our other career resources.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="<?= Config::BASE_URL ?>/pages/public/jobs.php" class="btn btn-light rounded-pill px-4">Browse Jobs</a>
                        <a href="<?= Config::BASE_URL ?>/pages/public/resources.php" class="btn btn-outline-light rounded-pill px-4">More Resources</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4 mt-5 mt-lg-0">
            <div class="sticky-lg-top" style="top: 100px;">
                <!-- Resource Downloads -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">Resource Downloads</h3>
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <a href="#" class="d-flex text-decoration-none">
                                    <i class="bi bi-file-earmark-pdf text-danger fs-4 me-2"></i>
                                    <div>
                                        <span class="d-block text-dark">Job Search Checklist</span>
                                        <span class="small text-muted">PDF, 2 pages</span>
                                    </div>
                                </a>
                            </li>
                            <li class="mb-3">
                                <a href="#" class="d-flex text-decoration-none">
                                    <i class="bi bi-file-earmark-excel text-success fs-4 me-2"></i>
                                    <div>
                                        <span class="d-block text-dark">Job Application Tracker</span>
                                        <span class="small text-muted">Excel/Google Sheets</span>
                                    </div>
                                </a>
                            </li>
                            <li class="mb-3">
                                <a href="#" class="d-flex text-decoration-none">
                                    <i class="bi bi-file-earmark-word text-primary fs-4 me-2"></i>
                                    <div>
                                        <span class="d-block text-dark">Email Templates Pack</span>
                                        <span class="small text-muted">Word, 5 pages</span>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="d-flex text-decoration-none">
                                    <i class="bi bi-file-earmark-pdf text-danger fs-4 me-2"></i>
                                    <div>
                                        <span class="d-block text-dark">Networking Conversation Guide</span>
                                        <span class="small text-muted">PDF, 3 pages</span>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Related Resources -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">Related Resources</h3>
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <a href="<?= Config::BASE_URL ?>/pages/public/resources-resume.php" class="d-flex align-items-center text-decoration-none">
                                    <i class="bi bi-file-earmark-text text-primary fs-4 me-2"></i>
                                    <span class="text-dark">Resume & Cover Letter Guide</span>
                                </a>
                            </li>
                            <li class="mb-3">
                                <a href="<?= Config::BASE_URL ?>/pages/public/resources-interview.php" class="d-flex align-items-center text-decoration-none">
                                    <i class="bi bi-chat-left-text text-primary fs-4 me-2"></i>
                                    <span class="text-dark">Interview Preparation</span>
                                </a>
                            </li>
                            <li class="mb-3">
                                <a href="<?= Config::BASE_URL ?>/pages/public/salary-guide.php" class="d-flex align-items-center text-decoration-none">
                                    <i class="bi bi-cash-stack text-primary fs-4 me-2"></i>
                                    <span class="text-dark">Tech Salary Guide</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= Config::BASE_URL ?>/pages/public/resources-remote-work.php" class="d-flex align-items-center text-decoration-none">
                                    <i class="bi bi-laptop text-primary fs-4 me-2"></i>
                                    <span class="text-dark">Remote Work Resources</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Newsletter Signup -->
                <div class="card bg-light border-0">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">Get Career Tips in Your Inbox</h3>
                        <p class="small text-muted mb-3">Subscribe to our newsletter for weekly job search tips and resources.</p>
                        <form>
                            <div class="mb-3">
                                <input type="email" class="form-control" placeholder="Your email address">
                            </div>
                            <button type="submit" class="btn btn-dark rounded-pill px-4 w-100">Subscribe</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Smooth scrolling for anchor links
    document.addEventListener('DOMContentLoaded', function() {
        const links = document.querySelectorAll('.nav-link');
        
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all links
                links.forEach(item => item.classList.remove('active'));
                
                // Add active class to clicked link
                this.classList.add('active');
                
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                }
            });
        });
    });
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?> 