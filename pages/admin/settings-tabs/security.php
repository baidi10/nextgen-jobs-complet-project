<?php
// Fetch current settings
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT * FROM settings WHERE settingGroup = 'security'");
$settings = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['settingKey']] = $row['settingValue'];
}
?>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white">
    <h3 class="h5 fw-bold mb-0">Security Settings</h3>
  </div>
  <div class="card-body">
    <?php if ($success && $_POST['settings_group'] === 'security'): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if ($error && $_POST['settings_group'] === 'security'): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="POST">
      <input type="hidden" name="settings_group" value="security">
      
      <div class="mb-3">
        <label for="password_min_length" class="form-label">Minimum Password Length</label>
        <input type="number" class="form-control" id="password_min_length" name="password_min_length" 
               value="<?= (int)($settings['password_min_length'] ?? 8) ?>" min="6" max="32">
      </div>
      
      <div class="mb-3">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="require_special_chars" name="require_special_chars" 
                 <?= ($settings['require_special_chars'] ?? '1') === '1' ? 'checked' : '' ?>>
          <label class="form-check-label" for="require_special_chars">
            Require Special Characters in Password
          </label>
        </div>
      </div>
      
      <div class="mb-3">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="require_uppercase" name="require_uppercase" 
                 <?= ($settings['require_uppercase'] ?? '1') === '1' ? 'checked' : '' ?>>
          <label class="form-check-label" for="require_uppercase">
            Require Uppercase Letters in Password
          </label>
        </div>
      </div>
      
      <div class="mb-3">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="require_numbers" name="require_numbers" 
                 <?= ($settings['require_numbers'] ?? '1') === '1' ? 'checked' : '' ?>>
          <label class="form-check-label" for="require_numbers">
            Require Numbers in Password
          </label>
        </div>
      </div>
      
      <div class="mb-3">
        <label for="login_attempts" class="form-label">Maximum Login Attempts</label>
        <input type="number" class="form-control" id="login_attempts" name="login_attempts" 
               value="<?= (int)($settings['login_attempts'] ?? 5) ?>" min="1" max="10">
        <div class="form-text text-muted">Number of failed login attempts before account lock</div>
      </div>
      
      <div class="mb-3">
        <label for="lockout_time" class="form-label">Account Lockout Time (minutes)</label>
        <input type="number" class="form-control" id="lockout_time" name="lockout_time" 
               value="<?= (int)($settings['lockout_time'] ?? 30) ?>" min="5" max="1440">
        <div class="form-text text-muted">Duration in minutes before a locked account is automatically unlocked</div>
      </div>
      
      <div class="mb-3">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="enable_recaptcha" name="enable_recaptcha" 
                 <?= ($settings['enable_recaptcha'] ?? '0') === '1' ? 'checked' : '' ?>>
          <label class="form-check-label" for="enable_recaptcha">
            Enable reCAPTCHA on Forms
          </label>
        </div>
      </div>
      
      <div class="d-grid">
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div> 