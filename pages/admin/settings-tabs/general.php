<?php
// Fetch current settings
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT * FROM settings WHERE settingGroup = 'general'");
$settings = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['settingKey']] = $row['settingValue'];
}
?>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white">
    <h3 class="h5 fw-bold mb-0">General Settings</h3>
  </div>
  <div class="card-body">
    <?php if ($success && $_POST['settings_group'] === 'general'): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if ($error && $_POST['settings_group'] === 'general'): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="POST">
      <input type="hidden" name="settings_group" value="general">
      
      <div class="mb-3">
        <label for="site_name" class="form-label">Site Name</label>
        <input type="text" class="form-control" id="site_name" name="site_name" 
               value="<?= htmlspecialchars($settings['site_name'] ?? 'JOBEST') ?>">
        <div class="form-text text-muted">The name of your job board</div>
      </div>
      
      <div class="mb-3">
        <label for="site_description" class="form-label">Site Description</label>
        <textarea class="form-control" id="site_description" name="site_description" rows="2"><?= htmlspecialchars($settings['site_description'] ?? 'Find your dream job') ?></textarea>
        <div class="form-text text-muted">A brief description of your job board</div>
      </div>
      
      <div class="mb-3">
        <label for="admin_email" class="form-label">Admin Email</label>
        <input type="email" class="form-control" id="admin_email" name="admin_email" 
               value="<?= htmlspecialchars($settings['admin_email'] ?? 'admin@example.com') ?>">
        <div class="form-text text-muted">The primary administrative email</div>
      </div>
      
      <div class="mb-3">
        <label for="jobs_per_page" class="form-label">Jobs Per Page</label>
        <input type="number" class="form-control" id="jobs_per_page" name="jobs_per_page" 
               value="<?= (int)($settings['jobs_per_page'] ?? 10) ?>" min="5" max="50">
        <div class="form-text text-muted">Number of jobs to display per page in listings</div>
      </div>
      
      <div class="mb-3">
        <label for="currency" class="form-label">Default Currency</label>
        <select class="form-select" id="currency" name="currency">
          <option value="USD" <?= ($settings['currency'] ?? 'USD') === 'USD' ? 'selected' : '' ?>>USD ($)</option>
          <option value="EUR" <?= ($settings['currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>EUR (€)</option>
          <option value="GBP" <?= ($settings['currency'] ?? '') === 'GBP' ? 'selected' : '' ?>>GBP (£)</option>
          <option value="CAD" <?= ($settings['currency'] ?? '') === 'CAD' ? 'selected' : '' ?>>CAD (C$)</option>
          <option value="AUD" <?= ($settings['currency'] ?? '') === 'AUD' ? 'selected' : '' ?>>AUD (A$)</option>
        </select>
        <div class="form-text text-muted">Default currency for job listings</div>
      </div>
      
      <div class="mb-3">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="enable_registration" name="enable_registration" 
                 <?= ($settings['enable_registration'] ?? '1') === '1' ? 'checked' : '' ?>>
          <label class="form-check-label" for="enable_registration">
            Enable User Registration
          </label>
        </div>
        <div class="form-text text-muted">Allow new users to register</div>
      </div>
      
      <div class="d-grid">
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div> 