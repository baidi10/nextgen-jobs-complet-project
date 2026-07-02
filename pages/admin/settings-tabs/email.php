<?php
// Fetch current settings
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT * FROM settings WHERE settingGroup = 'email'");
$settings = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['settingKey']] = $row['settingValue'];
}
?>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white">
    <h3 class="h5 fw-bold mb-0">Email Settings</h3>
  </div>
  <div class="card-body">
    <?php if ($success && $_POST['settings_group'] === 'email'): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if ($error && $_POST['settings_group'] === 'email'): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="POST">
      <input type="hidden" name="settings_group" value="email">
      
      <div class="mb-3">
        <label for="smtp_host" class="form-label">SMTP Host</label>
        <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
               value="<?= htmlspecialchars($settings['smtp_host'] ?? 'smtp.example.com') ?>">
      </div>
      
      <div class="mb-3">
        <label for="smtp_port" class="form-label">SMTP Port</label>
        <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
               value="<?= (int)($settings['smtp_port'] ?? 587) ?>">
      </div>
      
      <div class="mb-3">
        <label for="smtp_username" class="form-label">SMTP Username</label>
        <input type="text" class="form-control" id="smtp_username" name="smtp_username" 
               value="<?= htmlspecialchars($settings['smtp_username'] ?? '') ?>">
      </div>
      
      <div class="mb-3">
        <label for="smtp_password" class="form-label">SMTP Password</label>
        <input type="password" class="form-control" id="smtp_password" name="smtp_password" 
               value="<?= htmlspecialchars($settings['smtp_password'] ?? '') ?>">
      </div>
      
      <div class="mb-3">
        <label for="smtp_encryption" class="form-label">Encryption</label>
        <select class="form-select" id="smtp_encryption" name="smtp_encryption">
          <option value="tls" <?= ($settings['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS</option>
          <option value="ssl" <?= ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
          <option value="none" <?= ($settings['smtp_encryption'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
        </select>
      </div>
      
      <div class="mb-3">
        <label for="from_email" class="form-label">From Email</label>
        <input type="email" class="form-control" id="from_email" name="from_email" 
               value="<?= htmlspecialchars($settings['from_email'] ?? 'noreply@jobest.com') ?>">
      </div>
      
      <div class="mb-3">
        <label for="from_name" class="form-label">From Name</label>
        <input type="text" class="form-control" id="from_name" name="from_name" 
               value="<?= htmlspecialchars($settings['from_name'] ?? 'JOBEST') ?>">
      </div>
      
      <div class="mb-3">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="email_verification" name="email_verification" 
                 <?= ($settings['email_verification'] ?? '1') === '1' ? 'checked' : '' ?>>
          <label class="form-check-label" for="email_verification">
            Require Email Verification
          </label>
        </div>
      </div>
      
      <div class="d-grid">
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div> 