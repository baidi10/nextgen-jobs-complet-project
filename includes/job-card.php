<?php
?>
<div class="card shadow-sm h-100 hover-shadow transition-300 border-hover position-relative">
  <?php if (!empty($job['isFeatured'])): ?>
    <div class="position-absolute badge bg-warning text-dark m-2">
      <i class="bi bi-star-fill me-1"></i> Featured
    </div>
  <?php endif; ?>
  
  <div class="card-body p-4">
    <div class="d-flex align-items-center mb-3">
      <div class="company-logo me-3">
        <img src="<?= !empty($job['companyLogo']) ? Config::BASE_URL . '/assets/uploads/company_logos/' . ltrim($job['companyLogo'], '/') : Config::BASE_URL . '/assets/images/default-company.png' ?>" 
             alt="<?= htmlspecialchars($job['companyName'] ?? 'Company') ?>" 
             class="img-fluid rounded" style="width: 48px; height: 48px; object-fit: contain; background-color: #f8f9fa;">
      </div>
      <div>
        <h3 class="h6 mb-1 text-truncate">
          <a href="<?= Config::BASE_URL ?>/pages/public/job-details.php?slug=<?= htmlspecialchars($job['jobSlug'] ?? '') ?>" class="text-decoration-none stretched-link">
            <?= htmlspecialchars($job['jobTitle'] ?? 'Job Title') ?>
          </a>
        </h3>
        <div class="small text-muted"><?= htmlspecialchars($job['companyName'] ?? 'Company Name') ?></div>
      </div>
    </div>
    
    <div class="mb-3">
      <div class="d-flex align-items-center text-muted small mb-2">
        <i class="bi bi-geo-alt me-2"></i>
        <span>
          <?= $job['isRemote'] ? 'Remote' : htmlspecialchars($job['location'] ?? 'Location') ?>
        </span>
      </div>
      
      <div class="d-flex align-items-center text-muted small">
        <i class="bi bi-briefcase me-2"></i>
        <span><?= htmlspecialchars($job['jobType'] ?? 'Job Type') ?></span>
        <?php if (!empty($job['experienceLevel'])): ?>
          <span class="mx-2">•</span>
          <span><?= htmlspecialchars($job['experienceLevel']) ?></span>
        <?php endif; ?>
      </div>
    </div>
    
    <?php if (!empty($job['salaryMin']) && !empty($job['salaryMax']) && !empty($job['isSalaryVisible'])): ?>
      <div class="salary-range badge bg-light text-success fw-medium mb-3">
        $<?= number_format($job['salaryMin']) ?> - $<?= number_format($job['salaryMax']) ?> 
        <span class="text-muted">/<?= htmlspecialchars($job['salaryPeriod'] ?? 'yearly') ?></span>
      </div>
    <?php endif; ?>
    
    <div class="card-text small mb-3 text-truncate-3" style="height: 60px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;">
      <?= htmlspecialchars(substr($job['jobDescription'] ?? '', 0, 120)) . (strlen($job['jobDescription'] ?? '') > 120 ? '...' : '') ?>
    </div>
    
    <div class="d-flex justify-content-between align-items-center mt-auto">
      <span class="text-muted small">
        <i class="bi bi-clock me-1"></i>
        <?= time_elapsed_string(strtotime($job['createdAt'] ?? 'now')) ?>
      </span>
      
      <?php if (isLoggedIn() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'jobSeeker'): ?>
        <button class="btn btn-sm btn-outline-dark rounded-pill save-job-btn" 
                data-job-id="<?= $job['jobId'] ?? 0 ?>">
          <i class="bi bi-bookmark"></i>
        </button>
      <?php else: ?>
        <a href="<?= Config::BASE_URL ?>/pages/public/login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" 
           class="btn btn-sm btn-outline-dark rounded-pill">
          <i class="bi bi-bookmark"></i>
        </a>
      <?php endif; ?>
    </div>
  </div>
</div> 