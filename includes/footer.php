<?php
// includes/footer.php
?>
        </main>
        
        <!-- Public Footer -->
        <footer class="bg-black text-white pt-4 pb-3">
            <div class="container">
                <div class="row">
                    <!-- Quick Links -->
                    <div class="col-md-4 mb-4">
                        <h4 class="footer-heading h6 mb-3">Quick Links</h4>
                        <div class="row footer-links">
                            <div class="col-6">
                                <a href="<?= Config::BASE_URL ?>/pages/public/jobs.php">Find Jobs</a>
                                <a href="<?= Config::BASE_URL ?>/pages/public/companies.php">Companies</a>
                                <a href="<?= Config::BASE_URL ?>/pages/public/salary-guide.php">Salary Guide</a>
                                <a href="<?= Config::BASE_URL ?>/pages/public/resources-job-search.php">Job Search Tips</a>
                            </div>
                            <div class="col-6">
                                <a href="<?= Config::BASE_URL ?>/pages/public/resources-interview.php">Interview Tips</a>
                                <a href="<?= Config::BASE_URL ?>/pages/public/about.php">About Us</a>
                                <a href="<?= Config::BASE_URL ?>/pages/public/contact.php">Contact Us</a>
                                <a href="<?= Config::BASE_URL ?>/pages/public/register.php">Register</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Resources -->
                    <div class="col-md-4 mb-4">
                        <h4 class="footer-heading h6 mb-3">Resources</h4>
                        <div class="row footer-links">
                            <div class="col-6">
                                <a href="<?= Config::BASE_URL ?>/pages/public/resources-job-search.php">Job Search Guide</a>
                                <a href="<?= Config::BASE_URL ?>/pages/public/resources-interview.php">Interview Prep</a>
                                <a href="<?= Config::BASE_URL ?>/pages/public/salary-guide.php">Salary Insights</a>
                                <a href="<?= Config::BASE_URL ?>/pages/public/companies.php">Company Reviews</a>
                            </div>
                            <div class="col-6">
                                <a href="<?= Config::BASE_URL ?>/pages/public/blog.php">Career Blog</a>
                                <a href="<?= Config::BASE_URL ?>/pages/public/faq.php">FAQ</a>
                                <a href="<?= Config::BASE_URL ?>/pages/public/events.php">Career Events</a>
                                <a href="<?= Config::BASE_URL ?>/pages/public/newsletter.php">Newsletter</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Legal & Support -->
                    <div class="col-md-4 mb-4">
                        <h4 class="footer-heading h6 mb-3">Legal & Support</h4>
                        <div class="row footer-links">
                            <div class="col-6">
                                <a href="<?= Config::BASE_URL ?>/pages/public/privacy.php">Privacy Policy</a>
                                <a href="<?= Config::BASE_URL ?>/pages/public/terms.php">Terms of Service</a>
                                <a href="<?= Config::BASE_URL ?>/pages/public/cookies.php">Cookie Policy</a>
                                <a href="<?= Config::BASE_URL ?>/pages/public/accessibility.php">Accessibility</a>
                            </div>
                            <div class="col-6">
                                <a href="<?= Config::BASE_URL ?>/pages/public/contact.php">Contact Support</a>
                                <a href="<?= Config::BASE_URL ?>/pages/public/help-center.php">Help Center</a>
                                <a href="<?= Config::BASE_URL ?>/pages/public/report-issue.php">Report an Issue</a>
                                <a href="<?= Config::BASE_URL ?>/pages/public/feedback.php">Send Feedback</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr class="border-secondary my-3">
                
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <div class="social-links mb-2 mb-md-0">
                        <a href="#" class="text-white me-3"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white me-3"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-instagram"></i></a>
                    </div>
                    <div class="copyright">
                        <img src="<?= Config::BASE_URL ?>/assets/images/V5NR.png" alt="JOBEST" height="24" class="me-2">
                        <small>© <?php echo date('Y'); ?> JOBEST. All rights reserved.</small>
                    </div>
                </div>
            </div>
        </footer>
        
        <!-- Toast container for notifications -->
        <div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3"></div>
        
        <!-- Scripts - Load only what's necessary -->
        <?php if (!isset($loadedBootstrap)): ?>
            <script src="<?= Config::BASE_URL ?>/assets/js/bootstrap.bundle.min.js" defer></script>
        <?php endif; ?>
        
        <script src="<?= Config::BASE_URL ?>/assets/js/main.js" defer></script>
        
        <?php if (isset($pageScripts) && is_array($pageScripts)): ?>
            <?php foreach ($pageScripts as $script): ?>
                <script src="<?= htmlspecialchars($script) ?>" defer></script>
            <?php endforeach; ?>
        <?php endif; ?>
    </body>
</html>